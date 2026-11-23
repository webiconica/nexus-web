<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudService
{
    protected string $graphVersion = 'v21.0';

    /**
     * Envía un mensaje de texto libre (válido dentro de la ventana de 24h de atención al cliente)
     */
    public function sendTextMessage(Tenant $tenant, string $toPhone, string $text): array
    {
        if (empty($tenant->whatsapp_phone_number_id) || empty($tenant->whatsapp_access_token)) {
            return ['ok' => false, 'error' => 'Credenciales de WhatsApp Cloud API no configuradas.'];
        }

        $endpoint = "https://graph.facebook.com/{$this->graphVersion}/{$tenant->whatsapp_phone_number_id}/messages";
        $recipient = preg_replace('/\D+/', '', $toPhone);

        try {
            $response = Http::withToken($tenant->whatsapp_access_token)
                ->timeout(12)
                ->post($endpoint, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $recipient,
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => false,
                        'body'        => $text,
                    ],
                ]);

            $json = $response->json();

            if (!$response->successful()) {
                Log::warning("WhatsApp API error (Tenant {$tenant->id}): " . json_encode($json));
            }

            return [
                'ok'         => $response->successful(),
                'status'     => $response->status(),
                'message_id' => $json['messages'][0]['id'] ?? null,
                'body'       => $json,
            ];
        } catch (\Throwable $e) {
            Log::error("Excepción enviando WhatsApp (Tenant {$tenant->id}): " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envía una plantilla aprobada en Meta Business (para iniciar conversación o seguimiento >24h)
     */
    public function sendTemplateMessage(
        Tenant $tenant,
        string $toPhone,
        string $templateName,
        string $languageCode = 'es_MX',
        array $bodyParameters = []
    ): array {
        if (empty($tenant->whatsapp_phone_number_id) || empty($tenant->whatsapp_access_token)) {
            return ['ok' => false, 'error' => 'Credenciales de WhatsApp Cloud API no configuradas.'];
        }

        $endpoint = "https://graph.facebook.com/{$this->graphVersion}/{$tenant->whatsapp_phone_number_id}/messages";
        $recipient = preg_replace('/\D+/', '', $toPhone);

        $templatePayload = [
            'name'     => $templateName,
            'language' => ['code' => $languageCode],
        ];

        if (!empty($bodyParameters)) {
            $templatePayload['components'] = [
                [
                    'type'       => 'body',
                    'parameters' => array_map(fn ($val) => [
                        'type' => 'text',
                        'text' => (string) $val,
                    ], $bodyParameters),
                ],
            ];
        }

        try {
            $response = Http::withToken($tenant->whatsapp_access_token)
                ->timeout(12)
                ->post($endpoint, [
                    'messaging_product' => 'whatsapp',
                    'to'                => $recipient,
                    'type'              => 'template',
                    'template'          => $templatePayload,
                ]);

            $json = $response->json();

            return [
                'ok'         => $response->successful(),
                'status'     => $response->status(),
                'message_id' => $json['messages'][0]['id'] ?? null,
                'body'       => $json,
            ];
        } catch (\Throwable $e) {
            Log::error("Excepción enviando plantilla WhatsApp (Tenant {$tenant->id}): " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Marca un mensaje entrante como leído en WhatsApp
     */
    public function markAsRead(Tenant $tenant, string $wamid): void
    {
        if (empty($tenant->whatsapp_phone_number_id) || empty($tenant->whatsapp_access_token) || empty($wamid)) {
            return;
        }

        try {
            $endpoint = "https://graph.facebook.com/{$this->graphVersion}/{$tenant->whatsapp_phone_number_id}/messages";
            Http::withToken($tenant->whatsapp_access_token)
                ->timeout(5)
                ->post($endpoint, [
                    'messaging_product' => 'whatsapp',
                    'status'            => 'read',
                    'message_id'        => $wamid,
                ]);
        } catch (\Throwable $e) {
            // Silencioso para no interrumpir el flujo principal
        }
    }
}