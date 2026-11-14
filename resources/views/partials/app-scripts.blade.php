<!-- resources/views/partials/app-scripts.blade.php -->
<script>
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

            let rawNumber = '{{ $globalSettings["whatsapp_number"] ?? "" }}';
            let whaNumber = rawNumber.replace(/[^0-9]/g, '');

            if (!whaNumber) {
                whaNumber = '5215539796157';
            }

            let siteName = '{{ $globalSettings["site_name"] ?? "Mi sitio web" }}';
            let text = `¡Hola! Vengo desde el sitio web *${siteName}*:\n\n`;
            
            for (const [campo, valor] of Object.entries(dynamicData)) {
                if (valor && typeof valor === 'string' && valor.trim() !== '') {
                    const label = campo.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    text += `📌 *${label}:* ${valor}\n`;
                }
            }

            const whatsappUrl = `https://api.whatsapp.com/send?phone=${whaNumber}&text=${encodeURIComponent(text)}`;

            let whatsappTab = window.open('about:blank', '_blank');

            this.isSubmitting = true;

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.content : '';
                const turnstileInput = formElement ? formElement.querySelector('[name="cf-turnstile-response"]') : null;

                const payload = {
                    tenant_id: {{ $globalSettings['tenant_id'] ?? 1 }},
                    origen: document.title || 'Formulario Web',
                    datos_formulario: dynamicData
                };

                if (turnstileInput && turnstileInput.value) {
                    payload.turnstile_token = turnstileInput.value;
                }

                await fetch('/api/capturar-lead', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
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

            this.isSubmitting = false;
        }
    }));
});
</script>