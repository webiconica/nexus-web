<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadMessage;
use App\Models\LeadStageLog;
use App\Services\ConversionDispatcherService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrmController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::current();
        $stages = Lead::STAGES;

        // Conteo por etapa para las tarjetas superiores del embudo
        $stageCounts = Lead::selectRaw('stage, COUNT(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage')
            ->toArray();

        $query = Lead::withCount('messages')
            ->orderByRaw('COALESCE(last_interaction_at, created_at) DESC');

        if ($request->filled('stage') && array_key_exists($request->stage, $stages)) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('origen')) {
            $query->where('origen', $request->origen);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_normalized', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('fbclid', 'like', "%{$search}%");
            });
        }

        $leads = $query->paginate(20)->withQueryString();
        $origins = Lead::select('origen')->distinct()->pluck('origen')->filter();

        // Consumo mensual de conversaciones IA del tenant
        $monthlyChatUsed = LeadMessage::where('direction', 'inbound')
            ->whereIn('channel', ['web_chat', 'whatsapp'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return view('admin.crm.index', compact(
            'tenant',
            'leads',
            'stages',
            'stageCounts',
            'origins',
            'monthlyChatUsed'
        ));
    }

    public function show(Lead $lead): View
    {
        $lead->load(['messages', 'stageLogs.user']);
        $stages = Lead::STAGES;
        $tenant = TenantContext::current();

        return view('admin.crm.show', compact('lead', 'stages', 'tenant'));
    }

    public function updateStage(
        Request $request,
        Lead $lead,
        ConversionDispatcherService $dispatcher
    ) {
        $validated = $request->validate([
            'stage'    => 'required|string|in:' . implode(',', array_keys(Lead::STAGES)),
            'notes'    => 'nullable|string|max:1000',
            'amount'   => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        $oldStage = $lead->stage;
        $newStage = $validated['stage'];
        $tenant   = TenantContext::current();

        $lead->update([
            'stage'               => $newStage,
            'last_interaction_at' => now(),
        ]);

        // Disparar Webhook saliente y/o Meta Conversions API
        $dispatchResult = ['dispatched' => false, 'status_code' => null, 'details' => []];
        if ($tenant && $oldStage !== $newStage) {
            $dispatchResult = $dispatcher->dispatchStageChange(
                $tenant,
                $lead,
                $oldStage,
                $newStage,
                isset($validated['amount']) ? (float) $validated['amount'] : null,
                strtoupper($validated['currency'] ?? 'MXN')
            );
        }

        LeadStageLog::create([
            'tenant_id'               => $lead->tenant_id,
            'lead_id'                 => $lead->id,
            'user_id'                 => Auth::id(),
            'from_stage'              => $oldStage,
            'to_stage'                => $newStage,
            'notes'                   => $validated['notes'] ?? null,
            'webhook_dispatched'      => $dispatchResult['dispatched'],
            'webhook_response_status' => $dispatchResult['status_code'],
        ]);

        $stageLabel = Lead::STAGES[$newStage] ?? $newStage;
        $msg = "Etapa actualizada a «{$stageLabel}».";
        if ($dispatchResult['dispatched']) {
            $msg .= " Webhook / Meta CAPI notificado (HTTP {$dispatchResult['status_code']}).";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status'   => 'success',
                'message'  => $msg,
                'stage'    => $newStage,
                'dispatch' => $dispatchResult,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function storeNote(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        LeadMessage::create([
            'tenant_id'   => $lead->tenant_id,
            'lead_id'     => $lead->id,
            'channel'     => 'system',
            'direction'   => 'outbound',
            'sender_type' => 'agent',
            'content'     => $validated['note'],
            'metadata'    => ['agent_name' => Auth::user()?->name ?? 'Asesor'],
        ]);

        $lead->update(['last_interaction_at' => now()]);

        return back()->with('success', 'Nota interna registrada en el historial del prospecto.');
    }

    /**
     * Exporta a CSV (Excel UTF-8) todos los prospectos filtrados, sus campos dinámicos,
     * identificadores Meta (fbclid, fbc, fbp), historial de mensajes y cambios de etapa.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Lead::with(['messages', 'stageLogs'])
            ->orderByDesc('created_at');

        if ($request->filled('stage') && array_key_exists($request->stage, Lead::STAGES)) {
            $query->where('stage', $request->stage);
        }
        if ($request->filled('origen')) {
            $query->where('origen', $request->origen);
        }
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_normalized', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leads = $query->get();

        // Descubrir todas las llaves personalizadas presentes en datos_formulario
        $dynamicKeys = $leads->flatMap(function (Lead $lead) {
            return is_array($lead->datos_formulario) ? array_keys($lead->datos_formulario) : [];
        })
        ->reject(fn ($k) => in_array($k, ['nombre', 'name', 'telefono', 'phone', 'email', 'correo', 'fbclid', 'fbc', 'fbp', 'ip'], true))
        ->unique()
        ->values()
        ->toArray();

        $filename = 'prospectos-crm-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($leads, $dynamicKeys) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para abrir acentos correctamente en Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            $baseHeaders = [
                'ID',
                'Fecha Registro',
                'Última Interacción',
                'Nombre',
                'Teléfono Normalizado',
                'Correo Electrónico',
                'Etapa Actual',
                'Origen',
                'fbclid',
                'fbc',
                'fbp',
            ];

            $dynamicHeaders = array_map(
                fn ($k) => 'Campo: ' . ucfirst(str_replace('_', ' ', (string) $k)),
                $dynamicKeys
            );

            $auditHeaders = [
                'Total Mensajes',
                'Historial de Conversación',
                'Historial de Cambios de Etapa',
            ];

            fputcsv($handle, array_merge($baseHeaders, $dynamicHeaders, $auditHeaders));

            foreach ($leads as $lead) {
                $custom = is_array($lead->datos_formulario) ? $lead->datos_formulario : [];

                $dynamicValues = [];
                foreach ($dynamicKeys as $key) {
                    $val = $custom[$key] ?? '';
                    $dynamicValues[] = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string) $val;
                }

                $chatTranscript = $lead->messages->map(function ($m) {
                    $who = match ($m->sender_type) {
                        'contact' => 'Prospecto',
                        'ai'      => 'IA',
                        'agent'   => 'Asesor',
                        default   => 'Sistema',
                    };
                    return "[{$m->created_at?->format('Y-m-d H:i')} | {$m->channel} | {$who}]: {$m->content}";
                })->implode(" || ");

                $stageHistory = $lead->stageLogs->map(function ($log) {
                    $from = Lead::STAGES[$log->from_stage] ?? ($log->from_stage ?: 'Inicio');
                    $to   = Lead::STAGES[$log->to_stage] ?? $log->to_stage;
                    $capi = $log->webhook_dispatched ? " [Webhook HTTP {$log->webhook_response_status}]" : '';
                    return "[{$log->created_at?->format('Y-m-d H:i')}]: {$from} -> {$to}{$capi}";
                })->implode(" || ");

                $row = array_merge([
                    $lead->id,
                    $lead->created_at?->format('Y-m-d H:i:s'),
                    $lead->last_interaction_at?->format('Y-m-d H:i:s') ?? $lead->created_at?->format('Y-m-d H:i:s'),
                    $lead->name,
                    $lead->phone_normalized,
                    $lead->email,
                    Lead::STAGES[$lead->stage] ?? $lead->stage,
                    $lead->origen,
                    $lead->fbclid,
                    $lead->fbc,
                    $lead->fbp,
                ], $dynamicValues, [
                    $lead->messages->count(),
                    $chatTranscript,
                    $stageHistory,
                ]);

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function replyWhatsapp(
        Request $request,
        Lead $lead,
        \App\Services\WhatsAppCloudService $waService
    ): RedirectResponse {
        $validated = $request->validate([
            'whatsapp_message' => 'required|string|max:1500',
        ]);

        $tenant = TenantContext::current();

        if (empty($lead->phone_normalized)) {
            return back()->with('error', 'Este prospecto no tiene un número de teléfono registrado.');
        }

        $result = $waService->sendTextMessage($tenant, $lead->phone_normalized, $validated['whatsapp_message']);

        if (!$result['ok']) {
            return back()->with('error', 'No se pudo enviar por WhatsApp Cloud API: ' . ($result['error'] ?? 'Verifica tus credenciales o ventana de 24h.'));
        }

        LeadMessage::create([
            'tenant_id'           => $tenant->id,
            'lead_id'             => $lead->id,
            'channel'             => 'whatsapp',
            'direction'           => 'outbound',
            'sender_type'         => 'agent',
            'content'             => $validated['whatsapp_message'],
            'external_message_id' => $result['message_id'] ?? null,
            'metadata'            => ['agent_name' => Auth::user()?->name ?? 'Asesor'],
        ]);

        $lead->update(['last_interaction_at' => now()]);

        return back()->with('success', 'Mensaje enviado directamente al WhatsApp del prospecto.');
    }
}