@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div>
        <h1 class="text-2xl font-black text-slate-900">Integraciones API, Widget Web, Meta CAPI & Google Ads</h1>
        <p class="text-sm text-slate-500 mt-1">Conecta cualquier sitio web externo, recibe formularios por Webhook POST y envía conversiones multi-etapa a Meta Ads y Google Ads.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- BLOQUE 1: Código Embebible del Chatbot IA -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <h2 class="text-base font-bold text-slate-900">1. Código del Chatbot IA para Sitio Web Externo</h2>
        <p class="text-xs text-slate-500">Pega esta línea antes de cerrar la etiqueta <code>{{ '</body>' }}</code> en WordPress, Shopify, Webflow, React o HTML:</p>
        <pre class="bg-slate-900 text-emerald-300 p-4 rounded-xl text-xs overflow-x-auto select-all">{{ '<script src="' . $baseUrl . '/widget.js?key=' . $tenant->api_key . '" defer></script>' }}</pre>
    </div>

    <!-- BLOQUE 2: Webhook de Entrada (POST JSON) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">2. Webhook de Entrada (Recibir Formularios Externos)</h2>
                <p class="text-xs text-slate-500">Unifica por teléfono y conserva identificadores de Meta (<code>fbclid</code>, <code>fbc</code>, <code>fbp</code>) y Google Ads (<code>gclid</code>, <code>gbraid</code>, <code>wbraid</code>).</p>
            </div>
            <form method="POST" action="{{ route('admin.integrations.regenerate-key') }}" onsubmit="return confirm('¿Regenerar API Key? El código anterior dejará de funcionar.')">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold">
                    Regenerar API Key
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                <span class="text-slate-400 block font-bold">Endpoint POST URL:</span>
                <code class="text-slate-900 font-bold select-all">{{ $baseUrl }}/api/v1/webhooks/leads</code>
            </div>
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                <span class="text-slate-400 block font-bold">Header X-API-Key:</span>
                <code class="text-indigo-700 font-bold select-all">{{ $tenant->api_key }}</code>
            </div>
        </div>

        <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl text-xs overflow-x-auto">{
  "name": "Ana García",
  "phone": "+52 55 1234 5678",
  "email": "ana@ejemplo.com",
  "origen": "Landing Campaña Ads",
  "fbclid": "IwAR0xYz123...",
  "fbc": "fb.1.1700000000.IwAR0xYz123...",
  "fbp": "fb.1.1700000000.987654321",
  "gclid": "Cj0KCQiA...",
  "gbraid": "0AAAAAD...",
  "wbraid": "ClAKCAiA...",
  "servicio": "Paquete Premium"
}</pre>
    </div>

    <!-- BLOQUE 3: Formulario Principal de Configuración -->
    <form method="POST" action="{{ route('admin.integrations.update') }}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        <!-- 3A. Dominios, Modo CRM y Webhook Saliente -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-slate-900">3. Webhook Saliente y Modo de Panel</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dominios autorizados para el Widget (uno por línea o *)</label>
                    <textarea name="allowed_origins_text" rows="2" class="w-full rounded-xl border-slate-300 text-xs">{{ implode("\n", $tenant->allowed_origins ?? ['*']) }}</textarea>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <div class="text-xs font-bold text-slate-900">Modo Solo Chat + CRM</div>
                        <p class="text-[11px] text-slate-500">Oculta Páginas, Blog y Menús del menú lateral para clientes que solo contrataron Chat y CRM.</p>
                    </div>
                    <input type="checkbox" name="crm_only_mode" value="1" {{ $tenant->crm_only_mode ? 'checked' : '' }} class="rounded text-indigo-600 h-5 w-5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Webhook Saliente URL (Make / n8n / Zapier / API Propia)</label>
                    <input type="url" name="outbound_webhook_url" value="{{ $tenant->outbound_webhook_url }}"
                           placeholder="https://hook.us1.make.com/..." class="w-full rounded-xl border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Secret HMAC del Webhook Saliente (Opcional)</label>
                    <input type="text" name="outbound_webhook_secret" value="{{ $tenant->outbound_webhook_secret }}"
                           placeholder="Firma X-Webhook-Signature" class="w-full rounded-xl border-slate-300 text-sm">
                </div>
            </div>
        </div>

        <!-- 3B. Meta Conversions API (CAPI) -->
        <div class="border-t border-slate-100 pt-5 space-y-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-brands fa-meta text-blue-600"></i> 4. Meta Conversions API (CAPI Directa)
                </h3>
                <p class="text-xs text-slate-500">Envía eventos servidor-a-servidor con datos encriptados en SHA-256 al cambiar de etapa en el CRM.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Meta Pixel ID</label>
                    <input type="text" name="meta_pixel_id" value="{{ $tenant->meta_pixel_id }}"
                           placeholder="Ej. 123456789012345" class="w-full rounded-xl border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Meta CAPI Access Token {{ $tenant->meta_capi_token ? '(Configurado ✓ - dejar vacío para conservar)' : '' }}
                    </label>
                    <input type="password" name="meta_capi_token" placeholder="EAABsbCS1iHgBO..."
                           class="w-full rounded-xl border-slate-300 text-sm">
                </div>
            </div>
        </div>

        <!-- 3C. Google Ads (Etiqueta Web + Feed HTTPS Offline) -->
        <div class="border-t border-slate-100 pt-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-brands fa-google text-amber-500"></i> 5. Google Ads (Conversiones Web y Offline)
                    </h3>
                    <p class="text-xs text-slate-500">Mide leads inmediatos con gtag y sincroniza conversiones offline (anticipos/ventas) automáticamente cada 24h.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Google Ads Conversion ID</label>
                    <input type="text" name="google_ads_id" value="{{ $tenant->google_ads_id }}"
                           placeholder="Ej. AW-123456789" class="w-full rounded-xl border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Conversion Label (Lead Web / Chat)</label>
                    <input type="text" name="google_ads_conversion_label" value="{{ $tenant->google_ads_conversion_label }}"
                           placeholder="Ej. AbC1dEfGhIjKlMnO" class="w-full rounded-xl border-slate-300 text-sm">
                </div>
            </div>

            @if($tenant->google_ads_feed_token)
                <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200/80 space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="text-xs font-bold text-slate-800">
                            URL Feed HTTPS para Google Ads Data Manager (Conversiones Avanzadas para Leads):
                        </span>
                        <button type="submit" form="regen-google-feed-form"
                                onclick="return confirm('¿Regenerar URL del Feed? Deberás actualizarla en Google Ads Data Manager.')"
                                class="text-[11px] font-bold text-amber-800 hover:underline">
                            Regenerar URL Feed
                        </button>
                    </div>
                    <code class="block bg-white px-3 py-2 rounded-lg border border-amber-200 text-xs text-slate-900 font-bold select-all overflow-x-auto">{{ $baseUrl }}/api/v1/feeds/google-ads?token={{ $tenant->google_ads_feed_token }}</code>
                    <p class="text-[11px] text-slate-600">
                        En Google Ads ve a <strong>Herramientas → Administrador de datos → + Conectar producto → HTTPS</strong> y pega esta URL con programación diaria.
                    </p>
                </div>
            @endif
        </div>

        <!-- 3D. Matriz Multi-Evento por Etapa del Embudo -->
        <div class="border-t border-slate-100 pt-5 space-y-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">6. Mapeo de Eventos por Etapa del CRM (Meta Ads & Google Ads)</h3>
                <p class="text-xs text-slate-500">Define qué conversión se dispara en cada plataforma cuando un prospecto avanza de etapa. Deja vacío para no enviar evento en esa etapa.</p>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Etapa en el CRM</th>
                            <th class="px-4 py-3 text-left">Evento Meta CAPI</th>
                            <th class="px-4 py-3 text-left">Acción de Conversión Google Ads (Nombre exacto)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($stages as $stageKey => $stageLabel)
                            @php
                                $selectedMeta = $metaEvents[$stageKey] ?? '';
                                $selectedGoogle = $googleEvents[$stageKey] ?? '';
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-800">
                                    {{ $stageLabel }}
                                    <span class="block text-[10px] font-mono text-slate-400">{{ $stageKey }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <select name="meta_capi_events[{{ $stageKey }}]" class="w-full rounded-lg border-slate-300 text-xs">
                                        <option value="">— No enviar a Meta —</option>
                                        <option value="Lead" {{ $selectedMeta === 'Lead' ? 'selected' : '' }}>Lead (Cliente potencial)</option>
                                        <option value="Contact" {{ $selectedMeta === 'Contact' ? 'selected' : '' }}>Contact (Contacto establecido)</option>
                                        <option value="Schedule" {{ $selectedMeta === 'Schedule' ? 'selected' : '' }}>Schedule (Cita / Demostración)</option>
                                        <option value="InitiateCheckout" {{ $selectedMeta === 'InitiateCheckout' ? 'selected' : '' }}>InitiateCheckout (Cotizado)</option>
                                        <option value="AddPaymentInfo" {{ $selectedMeta === 'AddPaymentInfo' ? 'selected' : '' }}>AddPaymentInfo (Datos de pago)</option>
                                        <option value="Purchase" {{ $selectedMeta === 'Purchase' ? 'selected' : '' }}>Purchase (Anticipo / Compra)</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text"
                                           name="google_ads_events[{{ $stageKey }}]"
                                           value="{{ $selectedGoogle }}"
                                           placeholder="{{ match($stageKey) {
                                               'cotizado' => 'Ej. Lead Calificado CRM',
                                               'anticipo_confirmado' => 'Ej. Anticipo Confirmado CRM',
                                               'cerrado' => 'Ej. Venta Cerrada CRM',
                                               default => 'Opcional (ej. Prospecto Contactado)',
                                           } }}"
                                           class="w-full rounded-lg border-slate-300 text-xs">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3E. WhatsApp Cloud API Oficial (Meta) -->
        <div class="border-t border-slate-100 pt-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-600"></i> 7. WhatsApp Cloud API Oficial (Meta)
                    </h3>
                    <p class="text-xs text-slate-500">Conecta tu número oficial en Meta Developers para que el bot responda y registre mensajes en el CRM.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Plan Pro Omnicanal
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <span class="text-slate-400 font-bold block">Callback URL (Pegar en Meta Webhooks):</span>
                    <code class="text-slate-900 font-bold select-all">{{ $baseUrl }}/api/v1/webhooks/whatsapp</code>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block">Verify Token (Token de verificación):</span>
                    <code class="text-emerald-700 font-bold select-all">{{ $tenant->whatsapp_verify_token }}</code>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number ID</label>
                    <input type="text" name="whatsapp_phone_number_id" value="{{ $tenant->whatsapp_phone_number_id }}"
                           placeholder="Ej. 482910384719201" class="w-full rounded-xl border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp Business Account ID</label>
                    <input type="text" name="whatsapp_business_account_id" value="{{ $tenant->whatsapp_business_account_id }}"
                           placeholder="Ej. 391029384710293" class="w-full rounded-xl border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        System User Access Token {{ $tenant->whatsapp_access_token ? '(Guardado ✓)' : '' }}
                    </label>
                    <input type="password" name="whatsapp_access_token" placeholder="EAABsbCS1iHgBO..."
                           class="w-full rounded-xl border-slate-300 text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm">
                Guardar Configuración de Integraciones
            </button>
        </div>
    </form>

    <!-- Formulario oculto para regenerar token de Feed Google Ads -->
    <form id="regen-google-feed-form" method="POST" action="{{ route('admin.integrations.regenerate-google-feed') }}" class="hidden">
        @csrf
    </form>

</div>
@endsection