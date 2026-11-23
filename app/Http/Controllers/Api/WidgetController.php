<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\Lead;
use App\Models\LeadMessage;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WidgetController extends Controller
{
    /**
     * Sirve el script embebible widget.js con Shadow DOM, captura de GCLID/GBRAID/WBRAID
     * y disparo automático de conversiones Google Ads (gtag).
     */
    public function script(Request $request): Response
    {
        $apiKey = trim((string) $request->query('key', ''));
        $tenant = $apiKey !== ''
            ? Tenant::where('api_key', $apiKey)->where('is_active', true)->first()
            : null;

        if (!$tenant) {
            return response('// Webiconica Widget: API Key inválida o inactiva.', 200, [
                'Content-Type' => 'application/javascript; charset=UTF-8',
            ]);
        }

        // Configuración de marca y chatbot del Tenant
        $settings = Setting::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->pluck('value', 'key')
            ->toArray();

        $botName     = $settings['chatbot_name'] ?? ($tenant->name . ' AI');
        $welcomeMsg  = $settings['chatbot_welcome'] ?? '¡Hola! 👋 ¿En qué puedo ayudarte hoy?';
        $brandDark   = $settings['brand_dark'] ?? '#1a0b5e';
        $brandLight  = $settings['brand_light'] ?? '#6366f1';
        $chatApiUrl  = rtrim(url('/'), '/') . '/api/v1/widget/chat';

        $config = [
            'apiKey'              => $tenant->api_key,
            'chatUrl'             => $chatApiUrl,
            'botName'             => $botName,
            'welcome'             => $welcomeMsg,
            'brandDark'           => $brandDark,
            'brandLight'          => $brandLight,
            'googleAdsId'         => trim((string) ($tenant->google_ads_id ?? '')),
            'googleAdsLabel'      => trim((string) ($tenant->google_ads_conversion_label ?? '')),
        ];

        $jsonConfig = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $js = <<<JS
(function () {
    if (window.__WebiconicaWidgetLoaded) return;
    window.__WebiconicaWidgetLoaded = true;

    const CFG = {$jsonConfig};
    const STORAGE_KEY = 'wk_ads_attribution_' + CFG.apiKey.slice(-8);
    const CHAT_KEY = 'wk_chat_history_' + CFG.apiKey.slice(-8);
    const TTL_MS = 90 * 24 * 60 * 60 * 1000; // 90 días (ventana de atribución Google Ads)

    // 1. Captura y persistencia de GCLID, GBRAID, WBRAID, FBCLID, FBC y FBP (case-sensitive)
    function readCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function syncAttribution() {
        let saved = {};
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (parsed && parsed._ts && (Date.now() - parsed._ts) < TTL_MS) {
                    saved = parsed;
                }
            }
        } catch (e) {}

        const params = new URLSearchParams(window.location.search);
        const urlKeys = ['gclid', 'gbraid', 'wbraid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign'];
        let updated = false;

        urlKeys.forEach(function (k) {
            const val = params.get(k);
            if (val && val.trim() !== '') {
                saved[k] = val.trim(); // Conserva mayúsculas/minúsculas exactas
                updated = true;
            }
        });

        const fbcCookie = readCookie('_fbc');
        const fbpCookie = readCookie('_fbp');
        if (fbcCookie && !saved.fbc) { saved.fbc = fbcCookie; updated = true; }
        if (fbpCookie && !saved.fbp) { saved.fbp = fbpCookie; updated = true; }
        if (saved.fbclid && !saved.fbc) {
            saved.fbc = 'fb.1.' + Math.floor(Date.now() / 1000) + '.' + saved.fbclid;
            updated = true;
        }

        if (updated || !saved._ts) {
            saved._ts = Date.now();
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(saved)); } catch (e) {}
        }

        // Autocompletar campos ocultos en formularios externos si el programador los incluyó
        ['gclid', 'gbraid', 'wbraid', 'fbclid', 'fbc', 'fbp'].forEach(function (field) {
            if (saved[field]) {
                document.querySelectorAll('input[name="' + field + '"]').forEach(function (el) {
                    if (!el.value) el.value = saved[field];
                });
            }
        });

        return saved;
    }

    const attribution = syncAttribution();

    // Exponer helper global por si el sitio externo envía formularios vía fetch/AJAX
    window.WebiconicaTracking = {
        getAttribution: function () {
            const clean = Object.assign({}, syncAttribution());
            delete clean._ts;
            clean.page_url = window.location.href;
            return clean;
        }
    };

    // 2. Inicializar Google Tag (gtag.js) si el tenant configuró google_ads_id
    function initGoogleAdsTag() {
        if (!CFG.googleAdsId) return;

        window.dataLayer = window.dataLayer || [];
        if (typeof window.gtag !== 'function') {
            window.gtag = function () { window.dataLayer.push(arguments); };
            window.gtag('js', new Date());

            const s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(CFG.googleAdsId);
            document.head.appendChild(s);
        }

        window.gtag('config', CFG.googleAdsId, { allow_enhanced_conversions: true });
    }

    initGoogleAdsTag();

    // Disparar conversión web + Enhanced Conversions cuando el chatbot captura un lead
    function fireGoogleAdsLeadConversion(leadData) {
        if (!CFG.googleAdsId || !CFG.googleAdsLabel || typeof window.gtag !== 'function') return;

        const firedKey = 'wk_gads_conv_fired_' + (leadData.lead_id || '1');
        try {
            if (sessionStorage.getItem(firedKey)) return;
            sessionStorage.setItem(firedKey, '1');
        } catch (e) {}

        // Conversiones Avanzadas en la Web (Google hashea automáticamente en SHA-256)
        const userData = {};
        if (leadData.email) userData.email = String(leadData.email).trim().toLowerCase();
        if (leadData.phone) {
            let digits = String(leadData.phone).replace(/\\D+/g, '');
            if (digits.length === 10) digits = '52' + digits;
            if (digits) userData.phone_number = '+' + digits;
        }
        if (Object.keys(userData).length > 0) {
            window.gtag('set', 'user_data', userData);
        }

        window.gtag('event', 'conversion', {
            send_to: CFG.googleAdsId + '/' + CFG.googleAdsLabel,
            transaction_id: leadData.lead_id ? ('LEAD-' + leadData.lead_id) : undefined
        });
    }

    // 3. Interfaz Flotante Aislada con Shadow DOM
    const host = document.createElement('div');
    host.id = 'webiconica-chat-host';
    document.body.appendChild(host);
    const shadow = host.attachShadow({ mode: 'open' });

    let history = [];
    try {
        history = JSON.parse(sessionStorage.getItem(CHAT_KEY) || '[]');
    } catch (e) { history = []; }

    shadow.innerHTML = `
        <style>
            :host { all: initial; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
            .wk-btn {
                position: fixed; bottom: 22px; right: 22px; z-index: 2147483640;
                width: 58px; height: 58px; border-radius: 9999px; border: none; cursor: pointer;
                background: \${CFG.brandDark}; color: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.22);
                display: flex; align-items: center; justify-content: center; transition: transform .2s;
            }
            .wk-btn:hover { transform: scale(1.06); }
            .wk-box {
                position: fixed; bottom: 92px; right: 22px; z-index: 2147483640;
                width: 360px; max-width: calc(100vw - 32px); height: 490px; max-height: calc(100vh - 120px);
                background: #fff; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.2);
                border: 1px solid #e2e8f0; display: none; flex-direction: column; overflow: hidden;
            }
            .wk-box.open { display: flex; }
            .wk-head {
                background: \${CFG.brandDark}; color: #fff; padding: 14px 16px;
                display: flex; align-items: center; justify-content: space-between;
            }
            .wk-title { font-weight: 700; font-size: 14px; }
            .wk-sub { font-size: 11px; opacity: 0.8; }
            .wk-close { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; line-height: 1; }
            .wk-msgs {
                flex: 1; padding: 14px; overflow-y: auto; background: #f8fafc;
                display: flex; flex-direction: column; gap: 10px;
            }
            .wk-msg {
                max-width: 82%; padding: 10px 13px; border-radius: 14px; font-size: 13px; line-height: 1.45;
                word-wrap: break-word; white-space: pre-wrap;
            }
            .wk-msg.bot { background: #fff; color: #1e293b; border: 1px solid #e2e8f0; align-self: flex-start; border-bottom-left-radius: 4px; }
            .wk-msg.user { background: \${CFG.brandDark}; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
            .wk-form {
                display: flex; gap: 8px; padding: 10px; background: #fff; border-top: 1px solid #e2e8f0;
            }
            .wk-input {
                flex: 1; border: 1px solid #cbd5e1; border-radius: 12px; padding: 9px 12px;
                font-size: 13px; outline: none; color: #0f172a;
            }
            .wk-input:focus { border-color: \${CFG.brandLight}; }
            .wk-send {
                background: \${CFG.brandLight}; color: #fff; border: none; border-radius: 12px;
                padding: 0 14px; font-weight: 700; font-size: 13px; cursor: pointer;
            }
            .wk-send:disabled { opacity: 0.5; cursor: not-allowed; }
        </style>

        <button class="wk-btn" id="wkToggle" aria-label="Abrir chat">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </button>

        <div class="wk-box" id="wkBox">
            <div class="wk-head">
                <div>
                    <div class="wk-title"></div>
                    <div class="wk-sub">En línea • Respuesta inmediata</div>
                </div>
                <button class="wk-close" id="wkClose">×</button>
            </div>
            <div class="wk-msgs" id="wkMsgs"></div>
            <form class="wk-form" id="wkForm">
                <input type="text" class="wk-input" id="wkInput" placeholder="Escribe tu mensaje..." autocomplete="off" />
                <button type="submit" class="wk-send" id="wkSend">Enviar</button>
            </form>
        </div>
    `;

    shadow.querySelector('.wk-title').textContent = CFG.botName;
    const box = shadow.getElementById('wkBox');
    const msgsEl = shadow.getElementById('wkMsgs');
    const formEl = shadow.getElementById('wkForm');
    const inputEl = shadow.getElementById('wkInput');
    const sendEl = shadow.getElementById('wkSend');

    function appendMsg(role, text) {
        const div = document.createElement('div');
        div.className = 'wk-msg ' + (role === 'user' ? 'user' : 'bot');
        div.textContent = text;
        msgsEl.appendChild(div);
        msgsEl.scrollTop = msgsEl.scrollHeight;
        return div;
    }

    if (history.length === 0) {
        appendMsg('bot', CFG.welcome);
    } else {
        history.forEach(function (m) { appendMsg(m.role, m.content); });
    }

    shadow.getElementById('wkToggle').addEventListener('click', function () {
        box.classList.toggle('open');
        if (box.classList.contains('open')) inputEl.focus();
    });

    shadow.getElementById('wkClose').addEventListener('click', function () {
        box.classList.remove('open');
    });

    formEl.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = inputEl.value.trim();
        if (!text) return;

        inputEl.value = '';
        appendMsg('user', text);
        history.push({ role: 'user', content: text });

        const typingEl = appendMsg('bot', 'Escribiendo...');
        sendEl.disabled = true;

        try {
            const res = await fetch(CFG.chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-API-Key': CFG.apiKey
                },
                body: JSON.stringify({
                    message: text,
                    history: history.slice(-8),
                    tracking: window.WebiconicaTracking.getAttribution()
                })
            });

            const data = await res.json();
            const reply = data.reply || 'Gracias por escribirnos. ¿Podrías compartirme tu nombre y WhatsApp para atenderte?';
            typingEl.textContent = reply;
            history.push({ role: 'assistant', content: reply });
            try { sessionStorage.setItem(CHAT_KEY, JSON.stringify(history.slice(-12))); } catch (err) {}

            // Si el bot capturó los datos del lead, disparar conversión de Google Ads
            if (data.lead_captured) {
                fireGoogleAdsLeadConversion(data);
            }
        } catch (err) {
            typingEl.textContent = 'Hubo un problema de conexión. Por favor intenta de nuevo.';
        } finally {
            sendEl.disabled = false;
            inputEl.focus();
        }
    });
})();
JS;

        return response($js, 200, [
            'Content-Type'  => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    /**
     * Endpoint POST /api/v1/widget/chat
     * Responde con IA, unifica el lead por teléfono/correo y conserva GCLID/GBRAID/WBRAID y Meta IDs.
     */
    public function chat(Request $request, GeminiService $gemini): JsonResponse
    {
        $apiKey = trim((string) ($request->header('X-API-Key') ?? $request->input('api_key', '')));
        $tenant = $apiKey !== ''
            ? Tenant::where('api_key', $apiKey)->where('is_active', true)->first()
            : null;

        if (!$tenant) {
            return response()->json(['reply' => 'Asistente no autorizado.'], 401);
        }

        $validated = $request->validate([
            'message'  => 'required|string|max:1500',
            'history'  => 'nullable|array|max:12',
            'tracking' => 'nullable|array',
        ]);

        // Verificar límite mensual de conversaciones IA del Tenant
        $monthlyUsed = AiUsageLog::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->where('module', 'chatbot')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        if ($tenant->monthly_chat_limit > 0 && $monthlyUsed >= $tenant->monthly_chat_limit) {
            return response()->json([
                'reply' => 'Por el momento nuestro asistente automático alcanzó su límite mensual. Por favor déjanos tu teléfono o escríbenos directamente.',
            ]);
        }

        $settings = Setting::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->pluck('value', 'key')
            ->toArray();

        $botContext = $settings['chatbot_context'] ?? "Eres el asistente comercial de {$tenant->name}. Sé amable, breve y persuasivo.";
        $model      = $settings['chatbot_model'] ?? 'gemini-2.5-flash-lite';

        // Conocimiento dinámico del negocio cacheado por Tenant
        $siteKnowledge = Cache::remember("widget_knowledge_tenant_{$tenant->id}", 600, function () use ($tenant) {
            $pages = Page::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('is_published', true)
                ->get(['title', 'slug', 'meta_description'])
                ->map(fn ($p) => "- Página: {$p->title} (/{$p->slug}): {$p->meta_description}")
                ->implode("\n");

            $posts = Post::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('is_published', true)
                ->latest()
                ->take(5)
                ->get(['title', 'excerpt'])
                ->map(fn ($p) => "- Artículo: {$p->title}: {$p->excerpt}")
                ->implode("\n");

            return trim($pages . "\n" . $posts);
        });

        $historyText = collect($validated['history'] ?? [])
            ->map(fn ($m) => (($m['role'] ?? 'user') === 'user' ? 'Visitante: ' : 'Asistente: ') . ($m['content'] ?? ''))
            ->implode("\n");

        $prompt = <<<EOT
INSTRUCCIONES DEL NEGOCIO ({$tenant->name}):
{$botContext}

INFORMACIÓN DEL SITIO WEB:
{$siteKnowledge}

REGLAS DE CONVERSACIÓN Y CAPTURA DE PROSPECTOS:
1. Responde de forma cordial, clara y concisa (máximo 3-4 líneas).
2. Si el visitante muestra interés en cotizar, agendar o recibir información personalizada, pídele su nombre y número de WhatsApp (o correo).
3. En cuanto el visitante proporcione su nombre junto con su teléfono o correo electrónico, marca "lead_captured": true y extrae los datos limpios.

HISTORIAL RECIENTE:
{$historyText}

MENSAJE ACTUAL DEL VISITANTE:
{$validated['message']}
EOT;

        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'reply'         => ['type' => 'STRING'],
                'lead_captured' => ['type' => 'BOOLEAN'],
                'lead_name'     => ['type' => 'STRING'],
                'lead_phone'    => ['type' => 'STRING'],
                'lead_email'    => ['type' => 'STRING'],
                'lead_interest' => ['type' => 'STRING'],
            ],
            'required' => ['reply', 'lead_captured'],
        ];

        try {
            $ai = $gemini->generate($prompt, 'chatbot', $model, $schema);
        } catch (\Throwable $e) {
            Log::error("Widget Chat Error (Tenant {$tenant->id}): " . $e->getMessage());
            return response()->json([
                'reply' => 'Disculpa, tuve una breve interrupción. ¿Me compartes tu nombre y WhatsApp para que un asesor te contacte?',
            ]);
        }

        $reply        = $ai['reply'] ?? '¿En qué más puedo ayudarte?';
        $leadCaptured = (bool) ($ai['lead_captured'] ?? false);
        $leadName     = trim((string) ($ai['lead_name'] ?? ''));
        $leadPhone    = trim((string) ($ai['lead_phone'] ?? ''));
        $leadEmail    = trim((string) ($ai['lead_email'] ?? ''));
        $leadInterest = trim((string) ($ai['lead_interest'] ?? ''));

        $leadId = null;

        if ($leadCaptured && ($leadPhone !== '' || $leadEmail !== '')) {
            $tracking = $validated['tracking'] ?? [];
            $phoneNormalized = $leadPhone !== '' ? Lead::normalizePhone($leadPhone) : null;

            // Buscar lead existente por teléfono o correo dentro del mismo Tenant (sin duplicar)
            $lead = Lead::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where(function ($q) use ($phoneNormalized, $leadEmail) {
                    if ($phoneNormalized) {
                        $q->where('phone_normalized', $phoneNormalized);
                    }
                    if ($leadEmail !== '') {
                        $phoneNormalized
                            ? $q->orWhere('email', strtolower($leadEmail))
                            : $q->where('email', strtolower($leadEmail));
                    }
                })
                ->first();

            $isNewLead = !$lead;

            if (!$lead) {
                $lead = new Lead();
                $lead->tenant_id = $tenant->id;
                $lead->stage     = 'nuevo';
                $lead->origen    = 'Chatbot Web';
            }

            $lead->name             = $lead->name ?: ($leadName !== '' ? $leadName : 'Prospecto Chat Web');
            $lead->phone_normalized = $lead->phone_normalized ?: $phoneNormalized;
            $lead->email            = $lead->email ?: ($leadEmail !== '' ? strtolower($leadEmail) : null);

            // Conservar atribución previa sin sobrescribirla (Meta Ads + Google Ads)
            $lead->fbclid = $lead->fbclid ?: (!empty($tracking['fbclid']) ? trim((string) $tracking['fbclid']) : null);
            $lead->fbc    = $lead->fbc    ?: (!empty($tracking['fbc'])    ? trim((string) $tracking['fbc'])    : null);
            $lead->fbp    = $lead->fbp    ?: (!empty($tracking['fbp'])    ? trim((string) $tracking['fbp'])    : null);
            $lead->gclid  = $lead->gclid  ?: (!empty($tracking['gclid'])  ? trim((string) $tracking['gclid'])  : null);
            $lead->gbraid = $lead->gbraid ?: (!empty($tracking['gbraid']) ? trim((string) $tracking['gbraid']) : null);
            $lead->wbraid = $lead->wbraid ?: (!empty($tracking['wbraid']) ? trim((string) $tracking['wbraid']) : null);

            $extraData = is_array($lead->datos_formulario) ? $lead->datos_formulario : [];
            if ($leadInterest !== '') {
                $extraData['interes_chat'] = $leadInterest;
            }
            if (!empty($tracking['page_url'])) {
                $extraData['url_origen'] = $tracking['page_url'];
            }
            $lead->datos_formulario    = $extraData;
            $lead->last_interaction_at = now();
            $lead->save();

            $leadId = $lead->id;

            // Registrar intercambio en la bitácora unificada de mensajes
            LeadMessage::create([
                'tenant_id'   => $tenant->id,
                'lead_id'     => $lead->id,
                'channel'     => 'web_widget',
                'direction'   => 'inbound',
                'sender_type' => 'lead',
                'content'     => $validated['message'],
            ]);

            LeadMessage::create([
                'tenant_id'   => $tenant->id,
                'lead_id'     => $lead->id,
                'channel'     => 'web_widget',
                'direction'   => 'outbound',
                'sender_type' => 'bot',
                'content'     => $reply,
            ]);

            // Solo disparar conversión web en el navegador si es un lead nuevo o recién completado
            $leadCaptured = $isNewLead;
        }

        return response()->json([
            'reply'         => $reply,
            'lead_captured' => $leadCaptured,
            'lead_id'       => $leadId,
            'email'         => $leadEmail,
            'phone'         => $leadPhone,
        ]);
    }
}