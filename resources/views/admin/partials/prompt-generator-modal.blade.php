@php
    $allSettings = \App\Models\Setting::pluck('value', 'key')->toArray();

    // Helper para normalizar teléfonos mexicanos (10 dígitos para tel:, 521+10 para wa.me) e internacionales
    $normalizePhone = function (?string $raw): ?array {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            return null;
        }

        $mx10 = null;
        if (strlen($digits) === 13 && str_starts_with($digits, '521')) {
            $mx10 = substr($digits, 3);
        } elseif (strlen($digits) === 12 && str_starts_with($digits, '52')) {
            $mx10 = substr($digits, 2);
        } elseif (strlen($digits) === 10) {
            $mx10 = $digits;
        }

        if ($mx10 !== null) {
            $twoDigitArea = in_array(substr($mx10, 0, 2), ['55', '33', '81'], true);
            $display = $twoDigitArea
                ? substr($mx10, 0, 2) . ' ' . substr($mx10, 2, 4) . ' ' . substr($mx10, 6, 4)
                : substr($mx10, 0, 3) . ' ' . substr($mx10, 3, 3) . ' ' . substr($mx10, 6, 4);

            return [
                'raw'         => $raw,
                'display'     => $display,
                'call_digits' => $mx10,
                'call_href'   => 'tel:' . $mx10,
                'wa_digits'   => '521' . $mx10,
                'wa_href'     => 'https://wa.me/521' . $mx10,
            ];
        }

        return [
            'raw'         => $raw,
            'display'     => $raw,
            'call_digits' => $digits,
            'call_href'   => 'tel:+' . $digits,
            'wa_digits'   => $digits,
            'wa_href'     => 'https://wa.me/' . $digits,
        ];
    };

    // 1. Extraer Sistema de Diseño (Google Fonts + tailwind.config)
    $designSystem = $allSettings['tailwind_config_script'] ?? $allSettings['tailwind_config'] ?? '';
    if (empty(trim($designSystem)) && !empty($allSettings['head_scripts'])) {
        $extracted = [];
        if (preg_match('/<link[^>]*fonts\.googleapis\.com[^>]*>/i', $allSettings['head_scripts'], $fontMatch)) {
            $extracted[] = $fontMatch[0];
        }
        if (preg_match('/<script[^>]*>[\s\S]*?tailwind\.config[\s\S]*?<\/script>/i', $allSettings['head_scripts'], $twMatch)) {
            $extracted[] = $twMatch[0];
        }
        $designSystem = implode("\n", $extracted);
    }

    // 2. Extraer y estructurar Directorio Dinámico (Teléfonos, WhatsApp y Redes)
    $rawDirectory = $allSettings['contact_directory_json']
        ?? $allSettings['contact_directory']
        ?? $allSettings['ai_contact_directory']
        ?? '';

    $phonesList = [];
    $seenWaKeys = [];
    $socialsList = [];
    $seenSocialUrls = [];
    $legacyContactText = '';

    $pushPhone = function (string $label, string $number, bool $isCall, bool $isWa) use (&$phonesList, &$seenWaKeys, $normalizePhone) {
        $norm = $normalizePhone($number);
        if (!$norm) {
            return;
        }
        $dedupeKey = $norm['wa_digits'];
        if (isset($seenWaKeys[$dedupeKey])) {
            $idx = $seenWaKeys[$dedupeKey];
            $phonesList[$idx]['use_call'] = $phonesList[$idx]['use_call'] || $isCall;
            $phonesList[$idx]['use_wa'] = $phonesList[$idx]['use_wa'] || $isWa;
            return;
        }
        $seenWaKeys[$dedupeKey] = count($phonesList);
        $phonesList[] = [
            'label'       => trim($label) !== '' ? trim($label) : 'Teléfono',
            'raw'         => $norm['raw'],
            'display'     => $norm['display'],
            'call_digits' => $norm['call_digits'],
            'call_href'   => $norm['call_href'],
            'wa_digits'   => $norm['wa_digits'],
            'wa_href'     => $norm['wa_href'],
            'use_call'    => $isCall,
            'use_wa'      => $isWa,
        ];
    };

    $pushSocial = function (string $network, string $url) use (&$socialsList, &$seenSocialUrls) {
        $cleanUrl = trim($url);
        if ($cleanUrl === '') {
            return;
        }
        $key = strtolower(rtrim($cleanUrl, '/'));
        if (isset($seenSocialUrls[$key])) {
            return;
        }
        $seenSocialUrls[$key] = true;
        $socialsList[] = [
            'network'  => trim($network) !== '' ? trim($network) : 'Red Social',
            'url'      => $cleanUrl,
            'selected' => true,
        ];
    };

    if (!empty(trim($rawDirectory))) {
        $decoded = json_decode($rawDirectory, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach ($decoded['phones'] ?? [] as $p) {
                if (empty($p['number'])) continue;
                $pushPhone(
                    $p['label'] ?? 'Principal',
                    $p['number'],
                    !empty($p['is_call']),
                    !empty($p['is_whatsapp'])
                );
            }
            foreach ($decoded['socials'] ?? [] as $s) {
                if (!empty($s['url'])) {
                    $pushSocial($s['network'] ?? 'Red Social', $s['url']);
                }
            }
        } else {
            $legacyContactText = trim($rawDirectory);
        }
    }

    // Agregar WhatsApp principal del CRM si no estaba en el directorio
    if (!empty($allSettings['whatsapp_number'])) {
        $pushPhone('WhatsApp Principal CRM', $allSettings['whatsapp_number'], true, true);
    }

    // Agregar redes individuales si existen en settings
    $fallbackSocialKeys = [
        'facebook_url'  => 'Facebook',
        'instagram_url' => 'Instagram',
        'tiktok_url'    => 'TikTok',
        'youtube_url'   => 'YouTube',
        'linkedin_url'  => 'LinkedIn',
    ];
    foreach ($fallbackSocialKeys as $key => $label) {
        if (!empty($allSettings[$key])) {
            $pushSocial($label, $allSettings[$key]);
        }
    }

    // 3. Código real de Cloudflare Turnstile
    $turnstileCode = trim($allSettings['turnstile_widget_code'] ?? $allSettings['turnstile_code'] ?? '');
    if (empty($turnstileCode)) {
        $siteKey = $allSettings['turnstile_site_key'] ?? env('TURNSTILE_SITE_KEY', 'TU_SITE_KEY_AQUI');
        $turnstileCode = '<div class="cf-turnstile my-3" data-sitekey="' . e($siteKey) . '"></div>';
    }

    $promptConfigPayload = [
        'site_name'           => $allSettings['site_name'] ?? config('app.name'),
        'site_description'    => $allSettings['site_description'] ?? '',
        'design_system'       => $designSystem,
        'phones'              => $phonesList,
        'socials'             => $socialsList,
        'contact_email'       => trim($allSettings['contact_email'] ?? ''),
        'contact_address'     => trim($allSettings['contact_address'] ?? $allSettings['address'] ?? ''),
        'contact_schedule'    => trim($allSettings['business_hours'] ?? $allSettings['contact_schedule'] ?? ''),
        'contact_coverage'    => trim($allSettings['contact_coverage'] ?? ''),
        'contact_maps_url'    => trim($allSettings['google_maps_url'] ?? $allSettings['maps_url'] ?? ''),
        'legacy_contact_text' => $legacyContactText,
        'turnstile_code'      => $turnstileCode,
        'media_api_url'       => route('admin.media.api'),
    ];
@endphp

<div x-data="promptBuilderModal()" x-init="init()">
    <!-- Botón disparador en el header -->
    <button type="button" @click="open()"
            class="inline-flex items-center gap-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold px-4 py-2 rounded-xl transition text-sm shadow-sm">
        <i class="fa-solid fa-wand-magic-sparkles text-amber-600"></i>
        <span>Generar Prompt</span>
    </button>

    <!-- Modal teletransportado al <body> -->
    <template x-teleport="body">
        <div x-show="isOpen" style="display: none;"
             @click.self="if(!mediaPickerOpen) isOpen = false"
             class="fixed inset-0 z-[110] flex items-center justify-center bg-brandDark/80 backdrop-blur-sm p-4"
             x-transition>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-6xl max-h-[92vh] flex flex-col overflow-hidden border border-gray-100">

                <!-- Encabezado del Modal -->
                <div class="px-6 py-4 bg-brandDark text-white flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-brandLight/20 text-brandLight flex items-center justify-center text-lg">
                            <i class="fa-solid fa-terminal"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-base sm:text-lg tracking-wide">Generador de Prompt para Copiar y Pegar</h3>
                            <p class="text-xs text-purple-200/80">
                                Conectado a <strong class="text-white" x-text="cfg.site_name"></strong>, directorio normalizado, GTM/CRM y Turnstile.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="isOpen = false" class="text-purple-200 hover:text-white text-2xl leading-none p-1">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <!-- Cuerpo en 2 columnas -->
                <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 bg-gray-50/50">

                    <!-- COLUMNA IZQUIERDA (7 cols) -->
                    <div class="lg:col-span-7 space-y-5">

                        <!-- 1. Tipo de Página y Paleta -->
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1.5">Tipo de Página</label>
                                    <select x-model="pageType" @change="syncDefaultFormId()"
                                            class="w-full px-3.5 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm font-semibold text-brandDark focus:outline-none focus:border-brandLight">
                                        <option value="home">Página de Inicio (Home)</option>
                                        <option value="landing">Landing Page de Venta / Campaña</option>
                                        <option value="service">Página Interior de Servicio</option>
                                        <option value="about">Página Nosotros / Corporativa</option>
                                        <option value="contact">Página de Contacto</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1.5">Paleta y Diseño</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" @click="colorMode = 'cms'"
                                                :class="colorMode === 'cms' ? 'bg-brandDark text-white border-brandDark' : 'bg-gray-50 text-gray-600 border-gray-200'"
                                                class="py-2.5 px-3 rounded-xl border text-xs font-bold transition">
                                            Sistema CMS
                                        </button>
                                        <button type="button" @click="colorMode = 'custom'"
                                                :class="colorMode === 'custom' ? 'bg-brandDark text-white border-brandDark' : 'bg-gray-50 text-gray-600 border-gray-200'"
                                                class="py-2.5 px-3 rounded-xl border text-xs font-bold transition">
                                            Personalizada
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div x-show="colorMode === 'custom'" style="display: none;">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Describe la paleta o colores HEX del proyecto</label>
                                <input type="text" x-model="customPalette"
                                       placeholder="Ej. Paleta elegante en negro, dorado y blanco con contraste alto..."
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-brandLight">
                            </div>
                        </div>

                        <!-- 2. Información del Negocio e Instrucciones Libres -->
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Servicios Principales o Productos *</label>
                                <input type="text" x-model="services"
                                       placeholder="Ej. Mariachi para fiestas, Serenatas, 15 años, Bodas, Eventos empresariales..."
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-brandLight">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Diferenciadores / Métricas</label>
                                    <input type="text" x-model="differentiators"
                                           placeholder="Ej. +15 años de experiencia, precios accesibles, cobertura 24/7"
                                           class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-brandLight">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Público Objetivo y Tono</label>
                                    <input type="text" x-model="audience"
                                           placeholder="Ej. Familias y eventos corporativos; tono cercano y elegante"
                                           class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-brandLight">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">
                                    Instrucciones Adicionales / Texto Libre (Opcional)
                                </label>
                                <textarea x-model="freeText" rows="3"
                                          placeholder="Promociones, precios base, textos exactos, estilo visual deseado..."
                                          class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-brandLight"></textarea>
                            </div>
                        </div>

                        <!-- 3. Selección de Contacto, Teléfonos Normalizados y Redes Sociales -->
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                                <h4 class="text-xs font-black uppercase tracking-wider text-brandDark flex items-center gap-2">
                                    <i class="fa-solid fa-address-book text-brandLight"></i>
                                    Datos de Contacto y Redes a Incluir
                                </h4>
                                <span class="text-[11px] text-gray-400">Normalización automática <code class="text-brandDark font-bold">tel:</code> / <code class="text-emerald-600 font-bold">wa.me</code></span>
                            </div>

                            <!-- Teléfonos -->
                            <div class="space-y-2">
                                <label class="block text-[11px] font-bold uppercase text-gray-500">
                                    Teléfonos (Elige cuáles usar para Llamada y cuáles para WhatsApp)
                                </label>

                                <div x-show="phones.length === 0" class="text-xs text-gray-400 italic bg-gray-50 p-3 rounded-xl border border-dashed border-gray-200">
                                    No hay teléfonos en Configuración Global. Puedes agregar uno temporal abajo.
                                </div>

                                <div class="space-y-2">
                                    <template x-for="(ph, idx) in phones" :key="idx">
                                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-brandDark" x-text="ph.label"></span>
                                                    <span class="text-xs font-mono text-gray-700" x-text="ph.display"></span>
                                                </div>
                                                <div class="text-[10px] font-mono text-gray-500 flex flex-wrap gap-x-3 gap-y-0.5 mt-0.5">
                                                    <span x-show="ph.use_call" class="text-blue-700" x-text="ph.call_href"></span>
                                                    <span x-show="ph.use_wa" class="text-emerald-700" x-text="ph.wa_href"></span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold cursor-pointer transition"
                                                       :class="ph.use_call ? 'bg-blue-50 border-blue-300 text-blue-800' : 'bg-white border-gray-200 text-gray-400'">
                                                    <input type="checkbox" x-model="ph.use_call" class="rounded text-blue-600 focus:ring-0">
                                                    <i class="fa-solid fa-phone text-[10px]"></i> Llamada
                                                </label>
                                                <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold cursor-pointer transition"
                                                       :class="ph.use_wa ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-white border-gray-200 text-gray-400'">
                                                    <input type="checkbox" x-model="ph.use_wa" class="rounded text-emerald-600 focus:ring-0">
                                                    <i class="fa-brands fa-whatsapp text-xs"></i> WhatsApp
                                                </label>
                                                <button type="button" @click="phones.splice(idx, 1)" class="text-red-400 hover:text-red-600 px-1" title="Quitar de este prompt">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Agregar teléfono rápido -->
                                <div class="flex flex-col sm:flex-row gap-2 pt-1">
                                    <input type="text" x-model="newPhoneLabel" placeholder="Etiqueta (ej. Ventas)"
                                           class="sm:w-36 px-3 py-1.5 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                    <input type="text" x-model="newPhoneRaw" placeholder="Número (ej. +5215539796157)"
                                           class="flex-1 px-3 py-1.5 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                    <button type="button" @click="addCustomPhone()"
                                            class="px-3.5 py-1.5 bg-gray-800 hover:bg-brandDark text-white rounded-xl text-xs font-bold transition">
                                        + Teléfono
                                    </button>
                                </div>
                            </div>

                            <!-- Correo Electrónico Principal -->
                            <div class="pt-2 border-t border-gray-100">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="inline-flex items-center gap-2 text-[11px] font-bold uppercase text-gray-500 cursor-pointer">
                                        <input type="checkbox" x-model="includeEmail" class="rounded text-brandDark focus:ring-brandLight">
                                        Incluir Correo Electrónico Principal
                                    </label>
                                </div>
                                <input type="email" x-model="contactEmail" :disabled="!includeEmail"
                                       placeholder="contacto@tudominio.com"
                                       :class="includeEmail ? 'bg-gray-50 text-gray-800' : 'bg-gray-100 text-gray-400'"
                                       class="w-full px-3.5 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                            </div>

                            <!-- Redes Sociales -->
                            <div class="pt-2 border-t border-gray-100 space-y-2">
                                <label class="block text-[11px] font-bold uppercase text-gray-500">Redes Sociales a Incluir</label>

                                <div class="flex flex-wrap gap-2" x-show="socials.length > 0">
                                    <template x-for="(soc, idx) in socials" :key="idx">
                                        <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs cursor-pointer transition max-w-full"
                                               :class="soc.selected ? 'bg-purple-50 border-brandLight text-brandDark font-bold' : 'bg-gray-50 border-gray-200 text-gray-500'">
                                            <input type="checkbox" x-model="soc.selected" class="rounded text-brandDark focus:ring-brandLight">
                                            <span x-text="soc.network"></span>
                                            <button type="button" @click.prevent="socials.splice(idx, 1)" class="text-gray-400 hover:text-red-500 ml-1">
                                                <i class="fa-solid fa-times text-[10px]"></i>
                                            </button>
                                        </label>
                                    </template>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-2">
                                    <select x-model="newSocialNetwork" class="sm:w-36 px-3 py-1.5 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none">
                                        <option value="Facebook">Facebook</option>
                                        <option value="Instagram">Instagram</option>
                                        <option value="TikTok">TikTok</option>
                                        <option value="YouTube">YouTube</option>
                                        <option value="LinkedIn">LinkedIn</option>
                                        <option value="X / Twitter">X / Twitter</option>
                                        <option value="Google Business">Google Business</option>
                                    </select>
                                    <input type="url" x-model="newSocialUrl" placeholder="https://instagram.com/tuempresa"
                                           class="flex-1 px-3 py-1.5 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                    <button type="button" @click="addCustomSocial()"
                                            class="px-3.5 py-1.5 bg-gray-800 hover:bg-brandDark text-white rounded-xl text-xs font-bold transition">
                                        + Red
                                    </button>
                                </div>
                            </div>

                            <!-- Otros Datos de Contacto -->
                            <div class="pt-2 border-t border-gray-100 space-y-2.5">
                                <label class="block text-[11px] font-bold uppercase text-gray-500">Otros Datos de Contacto (Opcionales)</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <input type="text" x-model="otherContact.address" placeholder="Dirección física (Calle, Colonia, Ciudad)"
                                           class="px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                    <input type="text" x-model="otherContact.schedule" placeholder="Horario (ej. Lun–Dom 24 horas)"
                                           class="px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                    <input type="text" x-model="otherContact.coverage" placeholder="Zona de cobertura (ej. Puebla y zona metropolitana)"
                                           class="px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                    <input type="url" x-model="otherContact.mapsUrl" placeholder="Enlace de Google Maps (https://maps.google.com/...)"
                                           class="px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                </div>
                            </div>
                        </div>

                        <!-- 4. Secciones a Incluir -->
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm space-y-3">
                            <label class="block text-xs font-bold uppercase text-gray-500">Estructura de Secciones</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                <template x-for="sec in sections" :key="sec.id">
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition"
                                           :class="sec.enabled ? 'bg-purple-50/70 border-brandLight text-brandDark font-bold' : 'bg-gray-50 border-gray-200 text-gray-600'">
                                        <input type="checkbox" x-model="sec.enabled" class="rounded text-brandDark focus:ring-brandLight">
                                        <span x-text="sec.label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- 5. Configuración del Formulario de Contacto (#contacto) -->
                        <div x-show="hasContactSection" style="display: none;"
                             class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <h4 class="text-xs font-black uppercase tracking-wider text-brandDark flex items-center gap-2">
                                    <i class="fa-solid fa-envelope-open-text text-brandLight"></i>
                                    Configuración del Formulario (CRM + WhatsApp + GTM)
                                </h4>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-bold text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="formConfig.includeTurnstile" class="rounded text-brandDark">
                                    Incluir Turnstile
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">ID Único del Formulario (para GTM / Turnstile)</label>
                                    <input type="text" x-model="formConfig.formId" @input="formIdTouched = true"
                                           placeholder="form-contacto-inicio"
                                           class="w-full px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs font-mono focus:outline-none focus:border-brandLight">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">Texto del Botón Enviar</label>
                                    <input type="text" x-model="formConfig.submitLabel"
                                           placeholder="Enviar y cotizar por WhatsApp"
                                           class="w-full px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1.5">Campos del Formulario (<code class="text-brandDark">name="..."</code>)</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="field in formConfig.fields" :key="field.name">
                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border text-xs cursor-pointer transition"
                                               :class="field.enabled ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-bold' : 'bg-gray-50 border-gray-200 text-gray-500'">
                                            <input type="checkbox" x-model="field.enabled" class="rounded text-emerald-600">
                                            <span x-text="field.label"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">Campos Extra Opcionales (separados por coma)</label>
                                <input type="text" x-model="formConfig.extraFields"
                                       placeholder="Ej. tipo_evento, numero_invitados, presupuesto_estimado"
                                       class="w-full px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                            </div>
                        </div>

                        <!-- 6. Imágenes ya cargadas en el sitio -->
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <label class="text-xs font-bold uppercase text-gray-500">
                                    Imágenes para el Sitio (URLs reales)
                                </label>
                                <button type="button" @click="openMediaPicker()"
                                        class="inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                    <i class="fas fa-images"></i> Elegir de la Biblioteca
                                </button>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="url" x-model="newImageUrl" placeholder="Pega URL de imagen (https://...)"
                                       class="flex-1 px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                <input type="text" x-model="newImageNote" placeholder="Sección (ej. Hero, Galería)"
                                       class="sm:w-44 px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                <button type="button" @click="addImage(newImageUrl, newImageNote)"
                                        class="px-4 py-2 bg-brandDark hover:bg-brandLight text-white rounded-xl text-xs font-bold transition">
                                    + Agregar
                                </button>
                            </div>

                            <div class="space-y-1.5" x-show="images.length > 0" style="display: none;">
                                <template x-for="(img, idx) in images" :key="idx">
                                    <div class="flex items-center justify-between gap-2 bg-gray-50 px-3 py-2 rounded-xl border border-gray-200 text-xs">
                                        <div class="flex items-center gap-2.5 truncate">
                                            <img :src="img.url" class="w-8 h-8 rounded-lg object-cover shrink-0 bg-white border">
                                            <div class="truncate">
                                                <span class="font-bold text-brandDark" x-show="img.note" x-text="'[' + img.note + '] '"></span>
                                                <span class="text-gray-600" x-text="img.url"></span>
                                            </div>
                                        </div>
                                        <button type="button" @click="images.splice(idx, 1)" class="text-red-500 hover:text-red-700 px-1.5">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 7. Videos (YouTube / Galería) -->
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm space-y-3">
                            <label class="block text-xs font-bold uppercase text-gray-500">
                                Videos para Galería (Opcional — YouTube)
                            </label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="url" x-model="newVideoUrl" placeholder="https://www.youtube.com/watch?v=..."
                                       class="flex-1 px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                <input type="text" x-model="newVideoTitle" placeholder="Título o nota del video"
                                       class="sm:w-44 px-3.5 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-brandLight">
                                <button type="button" @click="addVideo()"
                                        class="px-4 py-2 bg-brandDark hover:bg-brandLight text-white rounded-xl text-xs font-bold transition">
                                    + Agregar
                                </button>
                            </div>

                            <div class="space-y-1.5" x-show="videos.length > 0" style="display: none;">
                                <template x-for="(vid, idx) in videos" :key="idx">
                                    <div class="flex items-center justify-between gap-2 bg-gray-50 px-3 py-2 rounded-xl border border-gray-200 text-xs">
                                        <div class="truncate">
                                            <i class="fab fa-youtube text-red-600 mr-1.5"></i>
                                            <span class="font-bold text-brandDark" x-show="vid.title" x-text="vid.title + ' — '"></span>
                                            <span class="text-gray-600" x-text="vid.url"></span>
                                        </div>
                                        <button type="button" @click="videos.splice(idx, 1)" class="text-red-500 hover:text-red-700 px-1.5">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA: Resultado listo para copiar (5 cols) -->
                    <div class="lg:col-span-5 flex flex-col">
                        <div class="bg-slate-950 text-slate-100 rounded-2xl p-5 flex flex-col border border-slate-800 shadow-lg lg:sticky lg:top-0 lg:h-[calc(92vh-11rem)]">
                            <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-800 shrink-0">
                                <span class="text-xs font-bold uppercase tracking-wider text-brandLight">
                                    Prompt Listo para Copiar
                                </span>
                                <button type="button" @click="copyPrompt()"
                                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-2 shadow"
                                        :class="copied ? 'bg-emerald-500 text-slate-950' : 'bg-brandLight hover:bg-white text-brandDark'">
                                    <i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                                    <span x-text="copied ? '¡Copiado!' : 'Copiar Prompt'"></span>
                                </button>
                            </div>

                            <textarea readonly :value="assembledPrompt"
                                      class="w-full flex-1 min-h-[440px] bg-slate-900/90 text-slate-200 font-mono text-xs leading-relaxed p-4 rounded-xl border border-slate-800 focus:outline-none resize-none"></textarea>

                            <p class="text-[11px] text-slate-400 mt-3 leading-relaxed shrink-0">
                                Pégalo en tu IA favorita, copia el bloque HTML que te devuelva y pégalo directamente en el editor de esta página.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Sub-Modal: Selector rápido de imágenes -->
            <div x-show="mediaPickerOpen" style="display: none;"
                 @click.self.stop="mediaPickerOpen = false"
                 class="fixed inset-0 z-[120] flex items-center justify-center bg-black/75 p-4">
                <div @click.stop
                     class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl h-[75vh] flex flex-col overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h4 class="font-bold text-brandDark uppercase tracking-wider text-sm flex items-center gap-2">
                            <i class="fas fa-images"></i> Seleccionar Imágenes de la Biblioteca
                        </h4>
                        <button type="button" @click.stop="mediaPickerOpen = false"
                                class="bg-brandDark text-white px-4 py-1.5 rounded-xl text-xs font-bold">
                            Listo (<span x-text="images.length"></span>)
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 bg-gray-50/50">
                        <div x-show="loadingMedia" class="text-center py-12 text-gray-400">
                            <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                            <p class="text-xs">Cargando biblioteca...</p>
                        </div>

                        <div x-show="!loadingMedia" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            <template x-for="item in mediaList" :key="item.url">
                                <div @click.stop="toggleMediaImage(item.url)"
                                     :class="images.some(i => i.url === item.url) ? 'ring-4 ring-brandLight border-brandDark' : 'border-gray-200'"
                                     class="cursor-pointer bg-white rounded-xl border overflow-hidden hover:shadow-md transition relative">
                                    <div class="aspect-square bg-gray-100 relative">
                                        <img :src="item.url" class="w-full h-full object-cover">
                                        <div x-show="images.some(i => i.url === item.url)"
                                             class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-brandDark text-white text-xs flex items-center justify-center shadow">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <p class="p-1.5 text-[10px] text-gray-600 truncate text-center font-semibold" x-text="item.name"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script type="application/json" id="cms-prompt-config">@json($promptConfigPayload)</script>

@verbatim
<script>
function promptBuilderModal() {
    return {
        isOpen: false,
        copied: false,
        mediaPickerOpen: false,
        loadingMedia: false,
        mediaList: [],

        cfg: {},
        pageType: 'home',
        colorMode: 'cms',
        customPalette: '',
        services: '',
        differentiators: '',
        audience: '',
        freeText: '',

        // Contacto y redes seleccionables
        phones: [],
        newPhoneLabel: '',
        newPhoneRaw: '',

        includeEmail: false,
        contactEmail: '',

        socials: [],
        newSocialNetwork: 'Instagram',
        newSocialUrl: '',

        otherContact: {
            address: '',
            schedule: '',
            coverage: '',
            mapsUrl: ''
        },

        // Configuración del Formulario (#contacto)
        formIdTouched: false,
        formConfig: {
            formId: 'form-contacto-inicio',
            submitLabel: 'Enviar y cotizar por WhatsApp',
            includeTurnstile: true,
            extraFields: '',
            fields: [
                { name: 'nombre',       label: 'Nombre *',         type: 'text',     required: true,  enabled: true },
                { name: 'telefono',     label: 'Teléfono/WA *',    type: 'tel',      required: true,  enabled: true },
                { name: 'email',        label: 'Correo',           type: 'email',    required: false, enabled: true },
                { name: 'servicio',     label: 'Servicio',         type: 'select',   required: false, enabled: true },
                { name: 'fecha_evento', label: 'Fecha',            type: 'date',     required: false, enabled: false },
                { name: 'ubicacion',    label: 'Ciudad / Zona',    type: 'text',     required: false, enabled: false },
                { name: 'empresa',      label: 'Empresa',          type: 'text',     required: false, enabled: false },
                { name: 'mensaje',      label: 'Mensaje / Detalles', type: 'textarea', required: false, enabled: true }
            ]
        },

        newImageUrl: '',
        newImageNote: '',
        images: [],

        newVideoUrl: '',
        newVideoTitle: '',
        videos: [],

        sections: [
            { id: 'hero',           label: 'Hero + Métricas', enabled: true },
            { id: 'servicios',      label: 'Servicios',       enabled: true },
            { id: 'proceso',        label: 'Proceso / Pasos', enabled: true },
            { id: 'galeria_fotos',  label: 'Galería Fotos',   enabled: false },
            { id: 'galeria_videos', label: 'Galería Videos',  enabled: false },
            { id: 'testimonios',    label: 'Testimonios',     enabled: true },
            { id: 'faq',            label: 'FAQ Acordeón',    enabled: true },
            { id: 'contacto',       label: 'Contacto CRM',    enabled: true },
        ],

        init() {
            try {
                this.cfg = JSON.parse(document.getElementById('cms-prompt-config').textContent || '{}');
            } catch (e) {
                this.cfg = {};
            }

            this.phones = Array.isArray(this.cfg.phones)
                ? this.cfg.phones.map(p => ({ ...p }))
                : [];

            this.socials = Array.isArray(this.cfg.socials)
                ? this.cfg.socials.map(s => ({ ...s }))
                : [];

            this.contactEmail = this.cfg.contact_email || '';
            this.includeEmail = this.contactEmail !== '';

            this.otherContact = {
                address: this.cfg.contact_address || '',
                schedule: this.cfg.contact_schedule || '',
                coverage: this.cfg.contact_coverage || '',
                mapsUrl: this.cfg.contact_maps_url || ''
            };
        },

        open() {
            this.isOpen = true;
        },

        get hasContactSection() {
            return this.sections.some(s => s.id === 'contacto' && s.enabled);
        },

        syncDefaultFormId() {
            if (this.formIdTouched) return;
            const map = {
                home: 'form-contacto-inicio',
                landing: 'form-cotizacion-landing',
                service: 'form-solicitud-servicio',
                about: 'form-contacto-nosotros',
                contact: 'form-contacto-principal'
            };
            this.formConfig.formId = map[this.pageType] || 'form-contacto-web';
        },

        normalizePhoneJs(raw) {
            const cleanedRaw = (raw || '').trim();
            const digits = cleanedRaw.replace(/\D+/g, '');
            if (!digits) return null;

            let mx10 = null;
            if (digits.length === 13 && digits.startsWith('521')) {
                mx10 = digits.slice(3);
            } else if (digits.length === 12 && digits.startsWith('52')) {
                mx10 = digits.slice(2);
            } else if (digits.length === 10) {
                mx10 = digits;
            }

            if (mx10) {
                const isTwoDigitArea = ['55', '33', '81'].includes(mx10.slice(0, 2));
                const display = isTwoDigitArea
                    ? `${mx10.slice(0, 2)} ${mx10.slice(2, 6)} ${mx10.slice(6, 10)}`
                    : `${mx10.slice(0, 3)} ${mx10.slice(3, 6)} ${mx10.slice(6, 10)}`;

                return {
                    raw: cleanedRaw,
                    display,
                    call_digits: mx10,
                    call_href: `tel:${mx10}`,
                    wa_digits: `521${mx10}`,
                    wa_href: `https://wa.me/521${mx10}`
                };
            }

            return {
                raw: cleanedRaw,
                display: cleanedRaw,
                call_digits: digits,
                call_href: `tel:+${digits}`,
                wa_digits: digits,
                wa_href: `https://wa.me/${digits}`
            };
        },

        addCustomPhone() {
            const norm = this.normalizePhoneJs(this.newPhoneRaw);
            if (!norm) return;
            this.phones.push({
                label: (this.newPhoneLabel || '').trim() || 'Contacto',
                ...norm,
                use_call: true,
                use_wa: true
            });
            this.newPhoneLabel = '';
            this.newPhoneRaw = '';
        },

        addCustomSocial() {
            const cleanUrl = (this.newSocialUrl || '').trim();
            if (!cleanUrl) return;
            this.socials.push({
                network: this.newSocialNetwork || 'Red Social',
                url: cleanUrl,
                selected: true
            });
            this.newSocialUrl = '';
        },

        async openMediaPicker() {
            this.mediaPickerOpen = true;
            if (this.mediaList.length === 0 && this.cfg.media_api_url) {
                this.loadingMedia = true;
                try {
                    const res = await fetch(this.cfg.media_api_url);
                    const data = await res.json();
                    this.mediaList = data.filter(i => i.is_image);
                } catch (e) {
                    console.error('Error cargando biblioteca:', e);
                }
                this.loadingMedia = false;
            }
        },

        toggleMediaImage(url) {
            const idx = this.images.findIndex(i => i.url === url);
            if (idx > -1) {
                this.images.splice(idx, 1);
            } else {
                this.addImage(url, '');
            }
        },

        addImage(url, note) {
            const clean = (url || '').trim();
            if (!clean) return;
            if (!this.images.some(i => i.url === clean)) {
                this.images.push({ url: clean, note: (note || '').trim() });
            }
            this.newImageUrl = '';
            this.newImageNote = '';
            if (this.images.length >= 3) {
                const sec = this.sections.find(s => s.id === 'galeria_fotos');
                if (sec) sec.enabled = true;
            }
        },

        addVideo() {
            const clean = (this.newVideoUrl || '').trim();
            if (!clean) return;
            this.videos.push({ url: clean, title: (this.newVideoTitle || '').trim() });
            this.newVideoUrl = '';
            this.newVideoTitle = '';
            const sec = this.sections.find(s => s.id === 'galeria_videos');
            if (sec) sec.enabled = true;
        },

        buildContactBlock() {
            const lines = [];

            const callPhones = this.phones.filter(p => p.use_call);
            if (callPhones.length > 0) {
                lines.push('Números para botones de LLAMADA DIRECTA (usa exactamente el formato tel: indicado):');
                callPhones.forEach(p => {
                    lines.push(`- ${p.label}: Texto visible "${p.display}" -> href="${p.call_href}"`);
                });
            }

            const waPhones = this.phones.filter(p => p.use_wa);
            if (waPhones.length > 0) {
                lines.push('Números para botones de WHATSAPP DIRECTO fuera del formulario (usa target="_blank" rel="noopener noreferrer"):');
                waPhones.forEach(p => {
                    lines.push(`- ${p.label}: Texto visible "${p.display}" -> href="${p.wa_href}"`);
                });
            }

            if (this.includeEmail && this.contactEmail.trim()) {
                const mail = this.contactEmail.trim();
                lines.push(`Correo electrónico principal: ${mail} -> href="mailto:${mail}"`);
            }

            const activeSocials = this.socials.filter(s => s.selected && s.url.trim());
            if (activeSocials.length > 0) {
                lines.push('Redes sociales oficiales:');
                activeSocials.forEach(s => {
                    lines.push(`- ${s.network}: ${s.url.trim()}`);
                });
            }

            const extras = [];
            if (this.otherContact.address.trim())  extras.push(`- Dirección: ${this.otherContact.address.trim()}`);
            if (this.otherContact.schedule.trim()) extras.push(`- Horario de atención: ${this.otherContact.schedule.trim()}`);
            if (this.otherContact.coverage.trim()) extras.push(`- Zona de cobertura: ${this.otherContact.coverage.trim()}`);
            if (this.otherContact.mapsUrl.trim())  extras.push(`- Google Maps: ${this.otherContact.mapsUrl.trim()}`);

            if (extras.length > 0) {
                lines.push('Otros datos de contacto:');
                lines.push(...extras);
            }

            if (this.cfg.legacy_contact_text && lines.length === 0) {
                lines.push(this.cfg.legacy_contact_text);
            }

            if (lines.length === 0) return '';

            return `\nDIRECTORIO DE CONTACTO Y REDES DEL SITIO (Respeta EXACTAMENTE estos números, enlaces tel:/wa.me y redes; NO inventes otros):\n${lines.join('\n')}\n`;
        },

        buildFormRulesBlock() {
            if (!this.hasContactSection) return '';

            const formId = (this.formConfig.formId || 'form-contacto-web').trim().replace(/[^a-zA-Z0-9_-]/g, '-');
            const submitLabel = (this.formConfig.submitLabel || 'Enviar y cotizar por WhatsApp').trim();
            const turnstileTag = this.cfg.turnstile_code || '<div class="cf-turnstile my-3" data-sitekey="TU_SITE_KEY_AQUI"></div>';

            const activeFields = this.formConfig.fields
                .filter(f => f.enabled)
                .map(f => `name="${f.name}" (${f.type}${f.required ? ', required' : ''})`);

            if (this.formConfig.extraFields.trim()) {
                this.formConfig.extraFields
                    .split(',')
                    .map(s => s.trim().toLowerCase().replace(/\s+/g, '_'))
                    .filter(Boolean)
                    .forEach(extra => activeFields.push(`name="${extra}" (text)`));
            }

            const turnstileRule = this.formConfig.includeTurnstile
                ? `5. Incluye el widget de Cloudflare Turnstile justo antes del botón de envío:\n   ${turnstileTag}`
                : `5. No incluyas widget de Cloudflare Turnstile en este formulario.`;

            return `
REGLAS OBLIGATORIAS PARA EL FORMULARIO (#contacto):
El CMS cuenta con un motor global en Alpine.js que captura el formulario, lo guarda en el CRM, dispara el evento de conversión estándar \`cms_lead_submitted\` a Google Tag Manager / Google Ads (con Conversiones Avanzadas) y abre WhatsApp automáticamente. Para que todo funcione sin fallas, el <form> DEBE cumplir exactamente con esto:
1. La etiqueta <form> debe incluir un ID único y el manejador global:
   <form id="${formId}" @submit.prevent="sendWhatsAppMessage($event)" class="space-y-4">
2. Debe incluir como primer elemento el campo Honeypot anti-spam oculto:
   <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
3. Incluye exactamente estos campos usando el atributo \`name\` en minúsculas/guiones bajos (el CMS lee FormData dinámicamente y usa \`telefono\` y \`email\` para CRM y Conversiones Avanzadas):
   ${activeFields.join(', ')}
4. PROHIBIDO poner enlaces wa.me o lógica manual de envío dentro del <form>; el script global del CMS gestiona CRM, GTM y la apertura de WhatsApp.
${turnstileRule}
6. El botón de envío debe ser \`type="submit"\`, incluir \`:disabled="isSubmitting"\` para evitar envíos duplicados y mostrar el texto "${submitLabel}" (cambiando visualmente mientras \`isSubmitting\` es true).
`;
        },

        get assembledPrompt() {
            const pageLabels = {
                home: 'la página de inicio',
                landing: 'una landing page de alta conversión',
                service: 'una página interior de servicio',
                about: 'la página institucional / nosotros',
                contact: 'la página de contacto y atención al cliente'
            };

            const siteName = this.cfg.site_name || '[NOMBRE DE LA EMPRESA]';
            const siteDesc = this.cfg.site_description || '[GIRO O PROPUESTA DE VALOR]';

            let paletteText = '';
            if (this.colorMode === 'cms' && this.cfg.design_system) {
                paletteText = `- Sistema de diseño configurado en el sitio (USA ESTAS CLASES DE TAILWIND Y TIPOGRAFÍA):\n${this.cfg.design_system}`;
            } else if (this.colorMode === 'custom' && this.customPalette.trim()) {
                paletteText = `- Paleta de colores: ${this.customPalette.trim()}`;
            } else {
                paletteText = `- Paleta de colores: Usa colores de alto contraste alternando secciones claras y oscuras.`;
            }

            const contactBlock = this.buildContactBlock();
            const formRulesBlock = this.buildFormRulesBlock();

            let imagesBlock = '';
            if (this.images.length > 0) {
                const items = this.images.map(i => `- ${i.url}${i.note ? ' (Para: ' + i.note + ')' : ''}`).join('\n');
                imagesBlock = `\nIMÁGENES REALES DISPONIBLES (Usa estas URLs exactas en etiquetas <img> con object-cover; NO inventes URLs externas):\n${items}\n`;
            }

            let videosBlock = '';
            if (this.videos.length > 0) {
                const items = this.videos.map(v => `- ${v.url}${v.title ? ' (' + v.title + ')' : ''}`).join('\n');
                videosBlock = `\nVIDEOS PARA GALERÍA DE VIDEOS (Incrústalos en una cuadrícula responsiva convirtiendo cada enlace al formato https://www.youtube.com/embed/ID_DEL_VIDEO dentro de contenedores aspect-video rounded-2xl overflow-hidden):\n${items}\n`;
            }

            const freeBlock = this.freeText.trim()
                ? `\nINFORMACIÓN E INSTRUCCIONES ADICIONALES:\n${this.freeText.trim()}\n`
                : '';

            const sectionDescriptions = {
                hero: '- Hero Section de alto impacto con propuesta de valor clara, 2 botones CTA (uno hacia #contacto o WhatsApp directo y otro hacia #servicios o llamada) y barra de métricas clave.',
                servicios: '- Sección de Servicios / Soluciones (#servicios) en cuadrícula moderna con íconos y botones que al hacer clic seleccionen el servicio en el formulario y hagan scroll suave a #contacto.',
                proceso: '- Sección de Proceso Paso a Paso (01, 02, 03, 04) o Diferenciadores de confianza.',
                galeria_fotos: '- Sección de Galería Fotográfica responsiva usando las imágenes proporcionadas.',
                galeria_videos: '- Sección de Galería de Videos incrustando los enlaces de YouTube indicados.',
                testimonios: '- Sección de Prueba Social (Testimonios).',
                faq: '- Sección de Preguntas Frecuentes (FAQ) con acordeón interactivo en Alpine.js.',
                contacto: '- Sección de Contacto y Conversión (#contacto) con tarjetas de los canales de contacto elegidos (llamada, WhatsApp, correo, dirección/horarios/redes) y el formulario conectado al CRM.'
            };
            const selectedSections = this.sections
                .filter(s => s.enabled)
                .map(s => sectionDescriptions[s.id])
                .join('\n');

            return `Actúa como un Arquitecto Web UI/UX Senior y Copywriter de Conversión. Crea el código HTML completo de ${pageLabels[this.pageType]} para el sitio web de ${siteName}, dedicado a ${siteDesc}.

DATOS DEL NEGOCIO A INTEGRAR:
${paletteText}
- Servicios principales: ${this.services.trim() || '[Detallar servicios principales según el giro del negocio]'}
- Diferenciadores / Métricas: ${this.differentiators.trim() || 'Experiencia, atención profesional y resultados garantizados'}
- Público objetivo y tono: ${this.audience.trim() || 'Profesional, cercano y orientado a conversión'}
${contactBlock}${imagesBlock}${videosBlock}${freeBlock}
REGLAS TÉCNICAS ESTRICTAS DEL CMS (OBLIGATORIAS):
1. Entrega ÚNICAMENTE fragmentos HTML (<section>...</section>). PROHIBIDO incluir <!DOCTYPE>, <html>, <head>, <body>, <header>, <nav> o <footer>, ya que el CMS los inyecta desde el layout principal.
2. No uses sintaxis Blade (@if, @foreach, {{ }}) ni variables PHP. Todo debe ser HTML puro.
3. Usa exclusivamente Tailwind CSS, Alpine.js 3.x (x-data, x-show, @click, @submit.prevent) e íconos de FontAwesome 6 (<i class="fa-solid fa-..."></i> / <i class="fa-brands fa-..."></i>).
4. En cualquier elemento con x-show que inicie oculto, agrega style="display: none;" para evitar parpadeos al cargar.
5. Alterna secciones con fondos claros y oscuros usando contenedores amplios (py-20, max-w-7xl mx-auto px-4 sm:px-6 lg:px-8) y diseño 100% responsive.

ESTRUCTURA DE SECCIONES REQUERIDA:
${selectedSections}
${formRulesBlock}
Devuelve únicamente el código HTML limpio dentro de un solo bloque de código, sin explicaciones adicionales.`;
        },

        async copyPrompt() {
            try {
                await navigator.clipboard.writeText(this.assembledPrompt);
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2500);
            } catch (e) {
                alert('No se pudo copiar automáticamente. Selecciona el texto del cuadro y presiona Ctrl+C.');
            }
        }
    };
}
</script>
@endverbatim