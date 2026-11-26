<!-- resources/views/partials/app-scripts.blade.php -->
@php
    $currentTenant = class_exists(\App\Services\TenantContext::class)
        ? \App\Services\TenantContext::current()
        : null;
    $resolvedTenantId = $currentTenant?->id ?? ($globalSettings['tenant_id'] ?? 1);
    $googleAdsId = $currentTenant?->google_ads_id ?? '';
    $googleAdsLabel = $currentTenant?->google_ads_conversion_label ?? '';
@endphp

<script>
(function () {
    // Captura y conserva parámetros de atribución (Google Ads / Meta Ads) durante 90 días
    const STORAGE_KEY = 'webiconica_ads_attribution';
    const TRACKING_KEYS = ['gclid', 'gbraid', 'wbraid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign'];

    function readCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }

    function getStoredAttribution() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return {};
            const parsed = JSON.parse(raw);
            if (parsed._expires && Date.now() > parsed._expires) {
                localStorage.removeItem(STORAGE_KEY);
                return {};
            }
            return parsed;
        } catch (e) {
            return {};
        }
    }

    function captureUrlAttribution() {
        try {
            const params = new URLSearchParams(window.location.search);
            const current = getStoredAttribution();
            let updated = false;

            TRACKING_KEYS.forEach((key) => {
                const val = params.get(key);
                if (val && val.trim() !== '') {
                    current[key] = val.trim();
                    updated = true;
                }
            });

            const fbc = readCookie('_fbc');
            const fbp = readCookie('_fbp');
            if (fbc) { current.fbc = fbc; updated = true; }
            if (fbp) { current.fbp = fbp; updated = true; }

            if (updated) {
                current._expires = Date.now() + (90 * 24 * 60 * 60 * 1000);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(current));
            }
        } catch (e) {}
    }

    window.getCmsAttribution = function () {
        const external = (window.WebiconicaTracking && typeof window.WebiconicaTracking.getAttribution === 'function')
            ? window.WebiconicaTracking.getAttribution()
            : {};
        const local = getStoredAttribution();
        const merged = { ...local, ...external };
        delete merged._expires;
        return merged;
    };

    captureUrlAttribution();
})();

document.addEventListener('alpine:init', () => {
    Alpine.data('appState', () => ({
        mobileMenuOpen: false,
        toast: { show: false, message: '' },
        isSubmitting: false,

        showToast(msg) {
            this.toast.message = msg;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 4000);
        },

        async sendWhatsAppMessage(event) {
            if (this.isSubmitting) return;

            const formElement = event ? (event.target || event.currentTarget) : null;
            let dynamicData = {};

            if (formElement) {
                const formData = new FormData(formElement);

                if (formData.has('website') && formData.get('website') !== '') {
                    console.warn("Spam detectado.");
                    return;
                }

                formData.forEach((value, key) => {
                    if (key !== '_token' && key !== 'website' && key !== 'cf-turnstile-response') {
                        dynamicData[key] = value;
                    }
                });
            }

            if (Object.keys(dynamicData).length === 0 && this.form) {
                dynamicData = { ...this.form };
                delete dynamicData.website;
            }

            let rawNumber = @json($globalSettings['whatsapp_number'] ?? '');
            let whaNumber = String(rawNumber).replace(/[^0-9]/g, '');

            if (!whaNumber) {
                whaNumber = '5215539796157';
            }

            let siteName = @json($globalSettings['site_name'] ?? 'Mi sitio web');
            let text = `¡Hola! Vengo desde el sitio web *${siteName}*:\n\n`;

            for (const [campo, valor] of Object.entries(dynamicData)) {
                if (valor && typeof valor === 'string' && valor.trim() !== '') {
                    const label = campo.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    text += `📌 *${label}:* ${valor}\n`;
                }
            }

            const whatsappUrl = `https://api.whatsapp.com/send?phone=${whaNumber}&text=${encodeURIComponent(text)}`;

            // Abrir pestaña de forma síncrona para evitar bloqueo de pop-ups
            let whatsappTab = window.open('about:blank', '_blank');

            this.isSubmitting = true;

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.content : '';
                const turnstileInput = formElement ? formElement.querySelector('[name="cf-turnstile-response"]') : null;
                const trackingData = typeof window.getCmsAttribution === 'function' ? window.getCmsAttribution() : {};

                const payload = {
                    tenant_id: @json((int) $resolvedTenantId),
                    origen: document.title || 'Formulario Web',
                    datos_formulario: dynamicData,
                    tracking: trackingData,
                    gclid: trackingData.gclid || null,
                    gbraid: trackingData.gbraid || null,
                    wbraid: trackingData.wbraid || null,
                    fbclid: trackingData.fbclid || null,
                    fbc: trackingData.fbc || null,
                    fbp: trackingData.fbp || null
                };

                if (turnstileInput && turnstileInput.value) {
                    payload.turnstile_token = turnstileInput.value;
                }

                const response = await fetch('/api/capturar-lead', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const resData = await response.json().catch(() => ({}));

                // Si falla Turnstile o la validación del backend, abortar sin disparar conversión
                if (response.status === 422 || response.status === 403) {
                    if (whatsappTab && !whatsappTab.closed) {
                        whatsappTab.close();
                    }
                    this.showToast(resData.error || 'Por favor completa la verificación de seguridad e intenta de nuevo.');
                    this.isSubmitting = false;
                    return;
                }

                if (response.ok) {
                    const formId = (formElement && formElement.id) ? formElement.id : 'cms-form-default';

                    // Normalizar teléfono a E.164 (+52...) y correo para Conversiones Avanzadas
                    const rawPhone = String(
                        dynamicData.telefono || dynamicData.phone || dynamicData.whatsapp || dynamicData.celular || ''
                    ).replace(/\D+/g, '');
                    const e164Phone = rawPhone.length === 10
                        ? '+52' + rawPhone
                        : (rawPhone ? '+' + rawPhone : '');
                    const cleanEmail = String(
                        dynamicData.email || dynamicData.correo || ''
                    ).trim().toLowerCase();

                    // 1. Evento universal para Google Tag Manager (GTM)
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'cms_lead_submitted',
                        'form_id': formId,
                        'form_origin': payload.origen,
                        'lead_id': resData.lead_id || null,
                        'user_data': {
                            'email': cleanEmail,
                            'phone_number': e164Phone
                        }
                    });

                    // 2. Disparo directo a Google Ads (si el tenant configuró AW-ID y Label sin GTM)
                    const gAdsId = @json($googleAdsId);
                    const gAdsLabel = @json($googleAdsLabel);
                    if (gAdsId && gAdsLabel && typeof window.gtag === 'function') {
                        if (cleanEmail || e164Phone) {
                            window.gtag('set', 'user_data', {
                                ...(cleanEmail ? { email: cleanEmail } : {}),
                                ...(e164Phone ? { phone_number: e164Phone } : {})
                            });
                        }
                        window.gtag('event', 'conversion', {
                            'send_to': gAdsId + '/' + gAdsLabel,
                            'transaction_id': resData.lead_id ? ('LEAD-' + resData.lead_id) : undefined
                        });
                    }

                    this.showToast('¡Solicitud recibida! Redirigiendo a WhatsApp...');
                }
            } catch (error) {
                console.error("Error guardando lead en CRM:", error);
            }

            if (whatsappTab && !whatsappTab.closed) {
                whatsappTab.location.href = whatsappUrl;
            } else {
                const link = document.createElement('a');
                link.href = whatsappUrl;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                document.body.appendChild(link);
                link.click();
                link.remove();
            }

            if (formElement) {
                formElement.reset();
            }
            if (this.form) {
                Object.keys(this.form).forEach(k => this.form[k] = '');
            }
            if (window.turnstile && typeof window.turnstile.reset === 'function') {
                try { window.turnstile.reset(); } catch (e) {}
            }

            this.isSubmitting = false;
        }
    }));
});
</script>