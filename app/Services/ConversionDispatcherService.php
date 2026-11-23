<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConversionDispatcherService
{
    public function dispatchStageChange(
        Tenant $tenant,
        Lead $lead,
        ?string $fromStage,
        string $toStage,
        ?float $amount = null,
        string $currency = 'MXN'
    ): array {
        $dispatched = false;
        $lastStatusCode = null;
        $details = [];

        // 1. Webhook Saliente Universal (Make, n8n, Zapier o API Propia)
        if (!empty($tenant->outbound_webhook_url)) {
            $payload = [
                'event'     => 'lead.stage_updated',
                'timestamp' => now()->toIso8601String(),
                'tenant'    => [
                    'id'   => $tenant->id,
                    'name' => $tenant->name,
                ],
                'stage_change' => [
                    'from'     => $fromStage,
                    'to'       => $toStage,
                    'to_label' => Lead::STAGES[$toStage] ?? $toStage,
                    'amount'   => $amount,
                    'currency' => $currency,
                ],
                'lead' => [
                    'id'               => $lead->id,
                    'name'             => $lead->name,
                    'phone_normalized' => $lead->phone_normalized,
                    'email'            => $lead->email,
                    'origen'           => $lead->origen,
                    'fbclid'           => $lead->fbclid,
                    'fbc'              => $lead->fbc,
                    'fbp'              => $lead->fbp,
                    'gclid'            => $lead->gclid,
                    'gbraid'           => $lead->gbraid,
                    'wbraid'           => $lead->wbraid,
                    'custom_fields'    => $lead->datos_formulario ?? [],
                    'created_at'       => $lead->created_at?->toIso8601String(),
                ],
            ];

            $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $headers = [
                'Content-Type'       => 'application/json',
                'User-Agent'         => 'Webiconica-CRM-Webhook/1.0',
                'X-Webiconica-Event' => 'lead.stage_updated',
            ];

            if (!empty($tenant->outbound_webhook_secret)) {
                $headers['X-Webhook-Signature'] = hash_hmac('sha256', $jsonBody, $tenant->outbound_webhook_secret);
            }

            try {
                $response = Http::withHeaders($headers)
                    ->timeout(10)
                    ->withBody($jsonBody, 'application/json')
                    ->post($tenant->outbound_webhook_url);

                $dispatched = true;
                $lastStatusCode = $response->status();
                $details['outbound_webhook'] = [
                    'ok'     => $response->successful(),
                    'status' => $lastStatusCode,
                ];
            } catch (\Throwable $e) {
                Log::error("Error enviando Webhook saliente (Tenant {$tenant->id}, Lead {$lead->id}): " . $e->getMessage());
                $dispatched = true;
                $lastStatusCode = 500;
                $details['outbound_webhook'] = [
                    'ok'    => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // 2. Envío directo a Meta Conversions API (CAPI) según la matriz de eventos configurada
        $defaultMetaMap = [
            'cotizado'            => 'InitiateCheckout',
            'anticipo_confirmado' => 'Purchase',
            'cerrado'             => 'Purchase',
        ];

        $configuredMetaMap = is_array($tenant->meta_capi_events)
            ? array_filter($tenant->meta_capi_events)
            : $defaultMetaMap;

        $metaEventName = $configuredMetaMap[$toStage] ?? null;

        if (
            !empty($metaEventName)
            && !empty($tenant->meta_pixel_id)
            && !empty($tenant->meta_capi_token)
        ) {
            $metaResult = $this->sendToMetaCapi($tenant, $lead, $toStage, $metaEventName, $amount, $currency);
            $dispatched = true;
            $lastStatusCode = $metaResult['status'] ?? $lastStatusCode;
            $details['meta_capi'] = $metaResult;
        }

        return [
            'dispatched'  => $dispatched,
            'status_code' => $lastStatusCode,
            'details'     => $details,
        ];
    }

    protected function sendToMetaCapi(
        Tenant $tenant,
        Lead $lead,
        string $toStage,
        string $eventName,
        ?float $amount,
        string $currency
    ): array {
        $userData = [];

        if (!empty($lead->phone_normalized)) {
            $userData['ph'] = [hash('sha256', preg_replace('/\D+/', '', $lead->phone_normalized))];
        }
        if (!empty($lead->email)) {
            $userData['em'] = [hash('sha256', strtolower(trim($lead->email)))];
        }
        if (!empty($lead->name)) {
            $firstName = explode(' ', trim($lead->name))[0];
            $userData['fn'] = [hash('sha256', mb_strtolower($firstName))];
        }
        if (!empty($lead->fbc)) {
            $userData['fbc'] = $lead->fbc;
        } elseif (!empty($lead->fbclid)) {
            $timestamp = $lead->created_at?->timestamp ?? time();
            $userData['fbc'] = "fb.1.{$timestamp}.{$lead->fbclid}";
        }
        if (!empty($lead->fbp)) {
            $userData['fbp'] = $lead->fbp;
        }

        $eventPayload = [
            'event_name'    => $eventName,
            'event_time'    => time(),
            'event_id'      => "lead_{$lead->id}_{$toStage}_" . now()->format('YmdHis'),
            'action_source' => 'system_generated',
            'user_data'     => $userData,
            'custom_data'   => array_filter([
                'currency'    => $currency,
                'value'       => $amount ?? 0,
                'crm_stage'   => $toStage,
                'lead_origin' => $lead->origen,
            ], fn ($v) => $v !== null),
        ];

        $endpoint = "https://graph.facebook.com/v21.0/{$tenant->meta_pixel_id}/events";

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'data'         => [$eventPayload],
                'access_token' => $tenant->meta_capi_token,
            ]);

            return [
                'ok'         => $response->successful(),
                'status'     => $response->status(),
                'event_name' => $eventName,
                'body'       => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error("Error enviando evento a Meta CAPI (Tenant {$tenant->id}, Lead {$lead->id}): " . $e->getMessage());
            return [
                'ok'     => false,
                'status' => 500,
                'error'  => $e->getMessage(),
            ];
        }
    }
}