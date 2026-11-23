<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadMessage;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\TenantContext;
use App\Services\WhatsAppCloudService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLeadFollowUps extends Command
{
    protected $signature = 'crm:send-followups';
    protected $description = 'Envía recordatorios automáticos a prospectos sin respuesta tras 24 horas (Correo y WhatsApp Pro)';

    public function handle(WhatsAppCloudService $waService): int
    {
        $tenants = Tenant::where('is_active', true)
            ->whereIn('plan', ['negocio', 'pro'])
            ->get();

        $totalSent = 0;

        foreach ($tenants as $tenant) {
            TenantContext::set($tenant);
            $settings = Setting::pluck('value', 'key')->toArray();
            $brandName = $settings['site_name'] ?? $tenant->name;

            // Prospectos activos en etapas iniciales con 24h a 72h sin interacción
            $leads = Lead::whereIn('stage', ['nuevo', 'en_seguimiento'])
                ->whereRaw('COALESCE(last_interaction_at, created_at) <= ?', [now()->subHours(24)])
                ->whereRaw('COALESCE(last_interaction_at, created_at) >= ?', [now()->subHours(72)])
                ->get();

            foreach ($leads as $lead) {
                $datos = is_array($lead->datos_formulario) ? $lead->datos_formulario : [];

                // Evitar duplicar el seguimiento de 24h
                if (!empty($datos['_followup_24h_sent_at'])) {
                    continue;
                }

                $firstName = explode(' ', trim((string) ($lead->name ?: 'Hola')))[0];
                $sentChannels = [];

                // 1. Seguimiento por Correo (Planes Negocio y Pro)
                if (!empty($lead->email) && filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $subject = "¿Pudiste revisar la información de {$brandName}?";
                        $body = "Hola {$firstName},\n\n"
                              . "Queríamos asegurarnos de que hayas recibido toda la información que solicitaste en {$brandName}.\n\n"
                              . "Si tienes alguna duda o deseas agendar una llamada o cotización personalizada, simplemente responde a este correo y con gusto te atendemos.\n\n"
                              . "Saludos,\nEquipo de {$brandName}";

                        Mail::raw($body, function ($message) use ($lead, $subject) {
                            $message->to($lead->email)->subject($subject);
                        });

                        $sentChannels[] = 'email';
                    } catch (\Throwable $e) {
                        Log::warning("Error enviando email de seguimiento 24h (Lead {$lead->id}): " . $e->getMessage());
                    }
                }

                // 2. Seguimiento por WhatsApp Cloud API (Exclusivo Plan Pro)
                if (
                    $tenant->plan === 'pro'
                    && !empty($lead->phone_normalized)
                    && !empty($tenant->whatsapp_phone_number_id)
                    && !empty($tenant->whatsapp_access_token)
                ) {
                    $hasRecentInboundWa = LeadMessage::where('lead_id', $lead->id)
                        ->where('channel', 'whatsapp')
                        ->where('direction', 'inbound')
                        ->where('created_at', '>=', now()->subHours(23)->subMinutes(45))
                        ->exists();

                    $templateName = trim((string) ($settings['whatsapp_followup_template'] ?? ''));

                    if ($hasRecentInboundWa) {
                        $waText = "Hola {$firstName} 👋 Seguimos al pendiente por aquí en *{$brandName}*. ¿Te gustaría que te apoyemos con alguna duda o cotización?";
                        $res = $waService->sendTextMessage($tenant, $lead->phone_normalized, $waText);
                        if ($res['ok']) {
                            $sentChannels[] = 'whatsapp_text';
                        }
                    } elseif ($templateName !== '') {
                        $res = $waService->sendTemplateMessage(
                            $tenant,
                            $lead->phone_normalized,
                            $templateName,
                            'es_MX',
                            [$firstName, $brandName]
                        );
                        if ($res['ok']) {
                            $sentChannels[] = 'whatsapp_template';
                        }
                    }
                }

                if (!empty($sentChannels)) {
                    $datos['_followup_24h_sent_at'] = now()->toIso8601String();
                    $datos['_followup_24h_channels'] = $sentChannels;

                    $lead->update([
                        'datos_formulario' => $datos,
                    ]);

                    LeadMessage::create([
                        'tenant_id'   => $tenant->id,
                        'lead_id'     => $lead->id,
                        'channel'     => 'system',
                        'direction'   => 'outbound',
                        'sender_type' => 'ai',
                        'content'     => 'Seguimiento automático 24h enviado vía: ' . implode(', ', $sentChannels),
                    ]);

                    $totalSent++;
                }
            }
        }

        $this->info("Seguimientos automáticos enviados: {$totalSent}");
        return self::SUCCESS;
    }
}