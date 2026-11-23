<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IntegrationSettingsController extends Controller
{
    public function index(): View
    {
        $tenant = TenantContext::current();
        $baseUrl = rtrim(url('/'), '/');
        $stages = collect(Lead::STAGES)->except(['descartado'])->toArray();

        $defaultMetaEvents = [
            'cotizado'            => 'InitiateCheckout',
            'anticipo_confirmado' => 'Purchase',
            'cerrado'             => 'Purchase',
        ];

        $metaEvents = is_array($tenant->meta_capi_events)
            ? $tenant->meta_capi_events
            : $defaultMetaEvents;

        $googleEvents = is_array($tenant->google_ads_events)
            ? $tenant->google_ads_events
            : [];

        return view('admin.crm.integrations', compact(
            'tenant',
            'baseUrl',
            'stages',
            'metaEvents',
            'googleEvents'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = TenantContext::current();

        $validated = $request->validate([
            'allowed_origins_text'         => 'nullable|string|max:2000',
            'outbound_webhook_url'         => 'nullable|url|max:500',
            'outbound_webhook_secret'      => 'nullable|string|max:255',
            'meta_pixel_id'                => 'nullable|string|max:100',
            'meta_capi_token'              => 'nullable|string|max:1000',
            'meta_capi_events'             => 'nullable|array',
            'meta_capi_events.*'           => 'nullable|string|max:100',
            'google_ads_id'                => 'nullable|string|max:50',
            'google_ads_conversion_label'  => 'nullable|string|max:100',
            'google_ads_events'            => 'nullable|array',
            'google_ads_events.*'          => 'nullable|string|max:150',
            'whatsapp_phone_number_id'     => 'nullable|string|max:100',
            'whatsapp_business_account_id' => 'nullable|string|max:100',
            'whatsapp_access_token'        => 'nullable|string|max:1000',
        ]);

        $origins = collect(preg_split('/[\r\n,]+/', (string) ($validated['allowed_origins_text'] ?? '*')))
            ->map(fn ($d) => trim($d))
            ->filter()
            ->values()
            ->toArray();

        if (empty($origins)) {
            $origins = ['*'];
        }

        // Limpiar mapas de eventos dejando solo etapas válidas con valor
        $allowedStages = array_keys(Lead::STAGES);

        $cleanMetaEvents = collect($validated['meta_capi_events'] ?? [])
            ->only($allowedStages)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->toArray();

        $cleanGoogleEvents = collect($validated['google_ads_events'] ?? [])
            ->only($allowedStages)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->toArray();

        $updateData = [
            'allowed_origins'              => $origins,
            'outbound_webhook_url'         => $validated['outbound_webhook_url'] ?? null,
            'outbound_webhook_secret'      => $validated['outbound_webhook_secret'] ?? null,
            'meta_pixel_id'                => $validated['meta_pixel_id'] ?? null,
            'meta_capi_events'             => $cleanMetaEvents,
            'google_ads_id'                => $validated['google_ads_id'] ?? null,
            'google_ads_conversion_label'  => $validated['google_ads_conversion_label'] ?? null,
            'google_ads_events'            => $cleanGoogleEvents,
            'whatsapp_phone_number_id'     => $validated['whatsapp_phone_number_id'] ?? null,
            'whatsapp_business_account_id' => $validated['whatsapp_business_account_id'] ?? null,
            'crm_only_mode'                => $request->has('crm_only_mode'),
        ];

        if (!empty($validated['meta_capi_token'])) {
            $updateData['meta_capi_token'] = trim($validated['meta_capi_token']);
        }

        if (!empty($validated['whatsapp_access_token'])) {
            $updateData['whatsapp_access_token'] = trim($validated['whatsapp_access_token']);
        }

        $tenant->update($updateData);

        return back()->with('success', 'Configuración de Webhooks, Meta CAPI, Google Ads y WhatsApp guardada correctamente.');
    }

    public function regenerateKey(): RedirectResponse
    {
        $tenant = TenantContext::current();
        $tenant->update([
            'api_key' => 'wk_live_' . Str::random(40),
        ]);

        return back()->with('success', 'Se generó una nueva API Key. Recuerda actualizarla en tu sitio web.');
    }

    public function regenerateGoogleFeedToken(): RedirectResponse
    {
        $tenant = TenantContext::current();
        $tenant->update([
            'google_ads_feed_token' => 'wk_gads_' . Str::random(32),
        ]);

        return back()->with('success', 'Se generó una nueva URL privada para el Feed de Google Ads.');
    }
}