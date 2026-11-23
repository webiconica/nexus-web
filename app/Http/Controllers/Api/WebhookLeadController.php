<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadMessage;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebhookLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // 1. Autenticación estricta por API Key del Tenant
        $apiKey = $request->header('X-API-Key')
            ?? $request->bearerToken()
            ?? $request->query('key')
            ?? $request->input('api_key');

        if (empty($apiKey)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Falta el encabezado X-API-Key o parámetro key.',
            ], 401);
        }

        $tenant = Tenant::where('api_key', trim($apiKey))
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'API Key inválida o cuenta inactiva.',
            ], 403);
        }

        TenantContext::set($tenant);

        // 2. Extraer payload (soporta JSON plano o dentro de 'data'/'fields')
        $payload = $request->all();
        if (isset($payload['fields']) && is_array($payload['fields'])) {
            $payload = array_merge($payload, $payload['fields']);
        } elseif (isset($payload['data']) && is_array($payload['data'])) {
            $payload = array_merge($payload, $payload['data']);
        }

        // 3. Mapear campos estándar con tolerancia a nombres en español o inglés
        $rawName  = $this->pickFirst($payload, ['name', 'nombre', 'full_name', 'nombre_completo']);
        $rawPhone = $this->pickFirst($payload, ['phone', 'telefono', 'tel', 'whatsapp', 'celular', 'mobile']);
        $rawEmail = $this->pickFirst($payload, ['email', 'correo', 'correo_electronico', 'mail']);
        $origin   = $this->pickFirst($payload, ['origen', 'source', 'form_name', 'formulario']) ?: 'Webhook Formulario Web';

        // Parámetros de atribución de Meta Ads
        $fbclid = $this->pickFirst($payload, ['fbclid']);
        $fbc    = $this->pickFirst($payload, ['fbc', '_fbc']);
        $fbp    = $this->pickFirst($payload, ['fbp', '_fbp']);

        $gclid  = $request->input('gclid')  ?? data_get($request->all(), 'tracking.gclid');
        $gbraid = $request->input('gbraid') ?? data_get($request->all(), 'tracking.gbraid');
        $wbraid = $request->input('wbraid') ?? data_get($request->all(), 'tracking.wbraid');

        // Al actualizar o crear el Lead, conserva el valor previo si ya existía:
        $lead->gclid  = $lead->gclid  ?: ($gclid ? trim((string) $gclid) : null);
        $lead->gbraid = $lead->gbraid ?: ($gbraid ? trim((string) $gbraid) : null);
        $lead->wbraid = $lead->wbraid ?: ($wbraid ? trim((string) $wbraid) : null);


        $phoneNormalized = Lead::normalizePhone($rawPhone);
        $emailNormalized = $rawEmail ? strtolower(trim($rawEmail)) : null;

        if (empty($phoneNormalized) && empty($emailNormalized)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Debes enviar al menos un teléfono o correo electrónico válido.',
            ], 422);
        }

        // Limpiar llaves técnicas antes de guardar los campos personalizados
        $excludedKeys = ['api_key', 'key', 'fields', 'data', '_token', 'cf-turnstile-response'];
        $customFields = collect($payload)
            ->except($excludedKeys)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->toArray();

        // 4. Buscar contacto existente por teléfono normalizado (prioridad) o correo
        $lead = null;
        if ($phoneNormalized) {
            $lead = Lead::where('phone_normalized', $phoneNormalized)->first();
        }
        if (!$lead && $emailNormalized) {
            $lead = Lead::where('email', $emailNormalized)->first();
        }

        $isNew = false;

        if ($lead) {
            // Actualizar sin sobrescribir datos previos valiosos ni parámetros de Meta
            $mergedDatos = array_merge($lead->datos_formulario ?? [], $customFields);

            $lead->update([
                'name'                => $rawName ?: $lead->name,
                'phone_normalized'    => $phoneNormalized ?: $lead->phone_normalized,
                'email'               => $emailNormalized ?: $lead->email,
                'fbclid'              => $fbclid ?: $lead->fbclid,
                'fbc'                 => $fbc ?: $lead->fbc,
                'fbp'                 => $fbp ?: $lead->fbp,
                'datos_formulario'    => $mergedDatos,
                'last_interaction_at' => now(),
            ]);
        } else {
            $isNew = true;
            $lead = Lead::create([
                'tenant_id'           => $tenant->id,
                'origen'              => $origin,
                'name'                => $rawName ?: 'Prospecto Web',
                'phone_normalized'    => $phoneNormalized,
                'email'               => $emailNormalized,
                'stage'               => 'nuevo',
                'fbclid'              => $fbclid,
                'fbc'                 => $fbc,
                'fbp'                 => $fbp,
                'datos_formulario'    => $customFields,
                'last_interaction_at' => now(),
            ]);
        }

        // 5. Registrar envío en el historial unificado de mensajes del contacto
        $summaryLines = collect($customFields)
            ->map(fn ($v, $k) => ucfirst(str_replace('_', ' ', (string) $k)) . ': ' . (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v))
            ->implode("\n");

        LeadMessage::create([
            'tenant_id'   => $tenant->id,
            'lead_id'     => $lead->id,
            'channel'     => 'webhook_form',
            'direction'   => 'inbound',
            'sender_type' => 'contact',
            'content'     => "Formulario recibido ({$origin}):\n" . $summaryLines,
            'metadata'    => [
                'is_new_lead' => $isNew,
                'ip'          => $request->ip(),
                'fbclid'      => $fbclid,
                'fbc'         => $fbc,
                'fbp'         => $fbp,
            ],
        ]);

        // 6. Notificación opcional por correo al dueño del tenant
        $this->notifyOwner($tenant, $lead, $customFields, $isNew);

        return response()->json([
            'status'      => 'success',
            'action'      => $isNew ? 'created' : 'merged',
            'lead_id'     => $lead->id,
            'stage'       => $lead->stage,
            'attribution' => [
                'fbclid' => $lead->fbclid,
                'fbc'    => $lead->fbc,
                'fbp'    => $lead->fbp,
            ],
        ], $isNew ? 201 : 200);
    }

    protected function pickFirst(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($payload[$key]) && is_scalar($payload[$key]) && trim((string) $payload[$key]) !== '') {
                return trim((string) $payload[$key]);
            }
        }
        return null;
    }

    protected function notifyOwner(Tenant $tenant, Lead $lead, array $fields, bool $isNew): void
    {
        try {
            $notifyEmail = Setting::where('key', 'contact_email')->value('value');
            if (empty($notifyEmail)) {
                return;
            }

            $badge = $isNew ? 'Nuevo Prospecto' : 'Prospecto Actualizado';
            $body  = "{$badge} en {$tenant->name}\n\n"
                   . "Nombre: {$lead->name}\n"
                   . "Teléfono: {$lead->phone_normalized}\n"
                   . "Correo: {$lead->email}\n"
                   . "Etapa: {$lead->stage}\n\n"
                   . "Datos recibidos:\n" . json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            Mail::raw($body, function ($msg) use ($notifyEmail, $badge, $lead) {
                $msg->to($notifyEmail)
                    ->subject("[{$badge}] {$lead->name}");
            });
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar correo de webhook lead: ' . $e->getMessage());
        }
    }
}