<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadMessage;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\GeminiService;
use App\Services\TenantContext;
use App\Services\WhatsAppCloudService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verificación inicial del Webhook en Meta Developer Portal (GET)
     */
    public function verify(Request $request): Response
    {
        // PHP convierte los puntos de query params (hub.mode -> hub_mode)
        $mode      = $request->query('hub_mode') ?? $request->input('hub.mode');
        $token     = $request->query('hub_verify_token') ?? $request->input('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->input('hub.challenge');

        if ($mode === 'subscribe' && !empty($token)) {
            $tenant = Tenant::where('whatsapp_verify_token', trim((string) $token))
                ->where('is_active', true)
                ->first();

            if ($tenant) {
                return response((string) $challenge, 200)
                    ->header('Content-Type', 'text/plain');
            }
        }

        return response('Token de verificación inválido', 403);
    }

    /**
     * Recepción de mensajes entrantes desde WhatsApp Cloud API (POST)
     */
    public function handle(
        Request $request,
        GeminiService $gemini,
        WhatsAppCloudService $waService
    ): Response {
        $payload = $request->all();

        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return response('EVENT_RECEIVED', 200);
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

                if (empty($phoneNumberId) || empty($value['messages'])) {
                    continue;
                }

                $tenant = Tenant::where('whatsapp_phone_number_id', $phoneNumberId)
                    ->where('is_active', true)
                    ->first();

                if (!$tenant) {
                    Log::warning("WhatsApp Webhook: No existe tenant para phone_number_id {$phoneNumberId}");
                    continue;
                }

                TenantContext::set($tenant);

                // Mapa de nombres de perfil enviados por WhatsApp
                $contactsMap = [];
                foreach ($value['contacts'] ?? [] as $contact) {
                    if (!empty($contact['wa_id'])) {
                        $contactsMap[$contact['wa_id']] = $contact['profile']['name'] ?? null;
                    }
                }

                foreach ($value['messages'] as $msg) {
                    $this->processIncomingMessage($tenant, $msg, $contactsMap, $gemini, $waService);
                }
            }
        }

        // Meta exige siempre HTTP 200 rápido para no reenviar el evento
        return response('EVENT_RECEIVED', 200);
    }

    protected function processIncomingMessage(
        Tenant $tenant,
        array $msg,
        array $contactsMap,
        GeminiService $gemini,
        WhatsAppCloudService $waService
    ): void {
        $wamid = $msg['id'] ?? null;
        $from  = $msg['from'] ?? null;

        if (empty($from)) {
            return;
        }

        // 1. Deduplicación de reintentos de Meta
        if ($wamid && LeadMessage::where('external_message_id', $wamid)->exists()) {
            return;
        }

        // 2. Extraer texto del mensaje (texto normal, botón o respuesta interactiva)
        $type = $msg['type'] ?? 'text';
        $incomingText = match ($type) {
            'text'        => trim((string) ($msg['text']['body'] ?? '')),
            'button'      => trim((string) ($msg['button']['text'] ?? '')),
            'interactive' => trim((string) (
                $msg['interactive']['button_reply']['title']
                ?? $msg['interactive']['list_reply']['title']
                ?? ''
            )),
            default       => '',
        };

        if ($incomingText === '') {
            $incomingText = "[Mensaje multimedia recibido: {$type}]";
        }

        $waService->markAsRead($tenant, (string) $wamid);

        // 3. Unificar o crear Lead en el CRM mediante teléfono normalizado (preserva fbclid, fbc, fbp)
        $phoneNormalized = Lead::normalizePhone($from);
        $profileName     = $contactsMap[$from] ?? null;

        $lead = Lead::where('phone_normalized', $phoneNormalized)->first();

        if ($lead) {
            $lead->update([
                'name'                => $lead->name ?: ($profileName ?: 'Prospecto WhatsApp'),
                'last_interaction_at' => now(),
            ]);
        } else {
            $lead = Lead::create([
                'tenant_id'           => $tenant->id,
                'origen'              => 'WhatsApp Cloud API',
                'name'                => $profileName ?: 'Prospecto WhatsApp',
                'phone_normalized'    => $phoneNormalized,
                'stage'               => 'nuevo',
                'last_interaction_at' => now(),
                'datos_formulario'    => [
                    'nombre'   => $profileName ?: 'Prospecto WhatsApp',
                    'telefono' => $phoneNormalized,
                ],
            ]);
        }

        // 4. Registrar mensaje entrante en el historial unificado del CRM
        LeadMessage::create([
            'tenant_id'           => $tenant->id,
            'lead_id'             => $lead->id,
            'channel'             => 'whatsapp',
            'direction'           => 'inbound',
            'sender_type'         => 'contact',
            'content'             => $incomingText,
            'external_message_id' => $wamid,
        ]);

        // 5. Validar plan y límite mensual de conversaciones IA
        if ($tenant->plan !== 'pro') {
            return;
        }

        $monthlyUsed = LeadMessage::where('direction', 'inbound')
            ->whereIn('channel', ['web_chat', 'whatsapp'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        if ($monthlyUsed > $tenant->monthly_chat_limit) {
            return;
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        if (($settings['ai_enabled'] ?? '1') !== '1' || !$gemini->isEnabled()) {
            return;
        }

        // 6. Construir contexto del negocio + historial previo del contacto (incluyendo formularios web)
        try {
            $knowledge = $this->getTenantKnowledge($tenant, $settings);
            $customContext = $settings['chatbot_context'] ?? '';

            $recentHistory = LeadMessage::where('lead_id', $lead->id)
                ->latest()
                ->limit(10)
                ->get()
                ->reverse()
                ->map(function ($m) {
                    $who = match ($m->sender_type) {
                        'contact' => 'Cliente',
                        'ai'      => 'Asistente',
                        'agent'   => 'Asesor Humano',
                        default   => 'Sistema',
                    };
                    return "[{$m->channel}] {$who}: {$m->content}";
                })
                ->implode("\n");

            $leadProfileSummary = json_encode($lead->datos_formulario ?? [], JSON_UNESCAPED_UNICODE);

            $prompt = <<<EOT
Eres el asistente virtual oficial de "{$tenant->name}" atendiendo directamente por WhatsApp.
Responde de manera cercana, clara, concisa (ideal para lectura rápida en celular) y enfocada en ayudar y cerrar citas o cotizaciones.

CONTEXTO DEL NEGOCIO:
{$customContext}

INFORMACIÓN DEL SITIO Y SERVICIOS:
{$knowledge}

FICHA ACTUAL DEL PROSPECTO EN CRM:
Nombre: {$lead->name} | Etapa: {$lead->stage} | Datos previos: {$leadProfileSummary}

HISTORIAL RECIENTE DEL CONTACTO:
{$recentHistory}

ÚLTIMO MENSAJE DE WHATSAPP DEL CLIENTE:
{$incomingText}

REGLAS:
- Usa formato limpio de WhatsApp (*negritas* con un solo asterisco, listas breves, sin HTML ni Markdown complejo).
- Si el cliente aporta su nombre completo, correo electrónico o servicio de interés, extráelos en los campos JSON.
- Nunca inventes precios ni políticas que no estén en el contexto.
EOT;

            $schema = [
                'type'       => 'OBJECT',
                'properties' => [
                    'reply'         => ['type' => 'STRING'],
                    'lead_name'     => ['type' => 'STRING'],
                    'lead_email'    => ['type' => 'STRING'],
                    'lead_interest' => ['type' => 'STRING'],
                ],
                'required'   => ['reply'],
            ];

            $model = $settings['chatbot_model'] ?? 'gemini-3.5-flash-lite';
            $aiResponse = $gemini->generate($prompt, 'whatsapp_bot', $model, $schema, 0.5);

            $replyText = trim((string) ($aiResponse['reply'] ?? ''));
            if ($replyText === '') {
                return;
            }

            // Actualizar ficha CRM si Gemini detectó correo, nombre más completo o interés
            $this->enrichLeadFromWhatsapp($lead, $aiResponse);

            // 7. Enviar respuesta oficial vía Meta WhatsApp Cloud API y guardar en CRM
            $sendResult = $waService->sendTextMessage($tenant, $from, $replyText);

            if ($sendResult['ok']) {
                LeadMessage::create([
                    'tenant_id'           => $tenant->id,
                    'lead_id'             => $lead->id,
                    'channel'             => 'whatsapp',
                    'direction'           => 'outbound',
                    'sender_type'         => 'ai',
                    'content'             => $replyText,
                    'external_message_id' => $sendResult['message_id'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Error procesando respuesta IA en WhatsApp (Tenant {$tenant->id}, Lead {$lead->id}): " . $e->getMessage());
        }
    }

    protected function enrichLeadFromWhatsapp(Lead $lead, array $aiData): void
    {
        $newName     = trim((string) ($aiData['lead_name'] ?? ''));
        $newEmail    = strtolower(trim((string) ($aiData['lead_email'] ?? '')));
        $newInterest = trim((string) ($aiData['lead_interest'] ?? ''));

        $datos = $lead->datos_formulario ?? [];
        $updates = [];

        if ($newName !== '' && ($lead->name === 'Prospecto WhatsApp' || mb_strlen($newName) > mb_strlen((string) $lead->name))) {
            $updates['name'] = $newName;
            $datos['nombre'] = $newName;
        }
        if ($newEmail !== '' && filter_var($newEmail, FILTER_VALIDATE_EMAIL) && empty($lead->email)) {
            $updates['email'] = $newEmail;
            $datos['email']   = $newEmail;
        }
        if ($newInterest !== '') {
            $prev = $datos['interes'] ?? '';
            if (!str_contains($prev, $newInterest)) {
                $datos['interes'] = $prev !== '' ? "{$prev} | {$newInterest}" : $newInterest;
            }
        }

        if (!empty($updates) || $newInterest !== '') {
            $updates['datos_formulario'] = $datos;
            $lead->update($updates);
        }
    }

    protected function getTenantKnowledge(Tenant $tenant, array $settings): string
    {
        return Cache::remember("chatbot_knowledge_tenant_{$tenant->id}", 1800, function () use ($settings) {
            $parts = [];

            if (Schema::hasTable('pages')) {
                $q = Page::select('title', 'slug', 'meta_description')->limit(15);
                if (Schema::hasColumn('pages', 'is_published')) {
                    $q->where('is_published', true);
                } elseif (Schema::hasColumn('pages', 'status')) {
                    $q->where('status', 'published');
                }
                $pages = $q->get();
                if ($pages->isNotEmpty()) {
                    $parts[] = 'PÁGINAS Y SERVICIOS: ' . $pages->map(fn ($p) => "{$p->title} ({$p->meta_description})")->implode(' | ');
                }
            }

            if (Schema::hasTable('posts')) {
                $q = Post::latest()->select('title', 'excerpt')->limit(8);
                if (Schema::hasColumn('posts', 'status')) {
                    $q->where('status', 'published');
                }
                $posts = $q->get();
                if ($posts->isNotEmpty()) {
                    $parts[] = 'ARTÍCULOS: ' . $posts->map(fn ($p) => "{$p->title}: {$p->excerpt}")->implode(' | ');
                }
            }

            if (!empty($settings['contact_directory_json'])) {
                $parts[] = 'DIRECTORIO: ' . $settings['contact_directory_json'];
            }

            return implode("\n", $parts);
        });
    }
}