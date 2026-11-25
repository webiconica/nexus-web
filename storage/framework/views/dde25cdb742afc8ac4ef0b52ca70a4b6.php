<?php
    $allSettings = \App\Models\Setting::pluck('value', 'key')->toArray();

    // 1. Extraer Sistema de Diseño (Google Fonts + tailwind.config) desde settings o head_scripts
    $designSystem = $allSettings['tailwind_config'] ?? '';
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

    // 2. Formatear Directorio Dinámico de Contacto y Redes
    $rawDirectory = $allSettings['contact_directory']
        ?? $allSettings['contact_directory_json']
        ?? $allSettings['ai_contact_directory']
        ?? '';

    $formattedDirectory = [];
    if (!empty(trim($rawDirectory))) {
        $decoded = json_decode($rawDirectory, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach ($decoded['phones'] ?? [] as $p) {
                if (empty($p['number'])) continue;
                $clean = preg_replace('/[^0-9]/', '', $p['number']);
                $tags = [];
                if (!empty($p['is_whatsapp'])) $tags[] = "WhatsApp: https://wa.me/{$clean}";
                if (!empty($p['is_call'])) $tags[] = "Llamada: tel:{$clean}";
                $formattedDirectory[] = "- " . ($p['label'] ?? 'Teléfono') . ": {$p['number']}" . ($tags ? ' (' . implode(' | ', $tags) . ')' : '');
            }
            foreach ($decoded['socials'] ?? [] as $s) {
                if (!empty($s['url'])) {
                    $formattedDirectory[] = "- " . ($s['network'] ?? 'Red Social') . ": {$s['url']}";
                }
            }
        } else {
            $formattedDirectory[] = trim($rawDirectory);
        }
    }

    if (!empty($allSettings['whatsapp_number'])) {
        $formattedDirectory[] = "- WhatsApp Principal del CRM: " . $allSettings['whatsapp_number'];
    }
    if (!empty($allSettings['contact_email'])) {
        $formattedDirectory[] = "- Correo de Contacto / Formularios: " . $allSettings['contact_email'];
    }

    // 3. Código real de Cloudflare Turnstile
    $turnstileCode = trim($allSettings['turnstile_widget_code'] ?? $allSettings['turnstile_code'] ?? '');
    if (empty($turnstileCode)) {
        $siteKey = $allSettings['turnstile_site_key'] ?? env('TURNSTILE_SITE_KEY', 'TU_SITE_KEY_AQUI');
        $turnstileCode = '<div class="cf-turnstile my-3" data-sitekey="' . e($siteKey) . '"></div>';
    }

    $promptConfigPayload = [
        'site_name'        => $allSettings['site_name'] ?? config('app.name'),
        'site_description' => $allSettings['site_description'] ?? '',
        'design_system'    => $designSystem,
        'contact_context'  => implode("\n", array_unique(array_filter($formattedDirectory))),
        'turnstile_code'   => $turnstileCode,
        'media_api_url'    => route('admin.media.api'),
    ];
?>

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
             class="fixed inset-0 z-[110] flex items-center justify-center bg-brandDark/80 backdrop-blur-sm p-4"
             x-transition>
            <div @click.away="if(!mediaPickerOpen) isOpen = false"
                 class="bg-white rounded-3xl shadow-2xl w-full max-w-6xl max-h-[92vh] flex flex-col overflow-hidden border border-gray-100">

                <!-- Encabezado del Modal -->
                <div class="px-6 py-4 bg-brandDark text-white flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-brandLight/20 text-brandLight flex items-center justify-center text-lg">
                            <i class="fa-solid fa-terminal"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-base sm:text-lg tracking-wide">Generador de Prompt para Copiar y Pegar</h3>
                            <p class="text-xs text-purple-200/80">
                                Conectado automáticamente a <strong class="text-white" x-text="cfg.site_name"></strong>, tu directorio de contacto y Turnstile.
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
                                    <select x-model="pageType" class="w-full px-3.5 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm font-semibold text-brandDark focus:outline-none focus:border-brandLight">
                                        <option value="home">Página de Inicio (Home)</option>
                                        <option value="landing">Landing Page de Venta / Campaña</option>
                                        <option value="service">Página Interior de Servicio</option>
                                        <option value="about">Página Nosotros / Corporativa</option>
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
                                       placeholder="Ej. Elige una paleta que proyecte el Mariachi y la costa de la Riviera Maya mexicana..."
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-brandLight">
                            </div>
                        </div>

                        <!-- 2. Información del Negocio e Instrucciones Libres -->
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Servicios Principales o Productos *</label>
                                <input type="text" x-model="services"
                                       placeholder="Ej. Mariachi para fiestas, Serenatas, 15 años, Cumpleaños, Funerales..."
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-brandLight">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Diferenciadores / Métricas</label>
                                    <input type="text" x-model="differentiators"
                                           placeholder="Ej. +10 años de experiencia, precios accesibles, cobertura 24/7"
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
                                          placeholder="Agrega aquí cualquier información extra que deba llevar el sitio: zonas de cobertura, precios, promociones, teléfono específico para botones de llamada, textos exactos..."
                                          class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-brandLight"></textarea>
                            </div>
                        </div>

                        <!-- 3. Secciones a Incluir -->
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

                        <!-- 4. Imágenes ya cargadas en el sitio -->
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

                        <!-- 5. Videos (YouTube / Galería) -->
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
                        <div class="bg-slate-950 text-slate-100 rounded-2xl p-5 flex-1 flex flex-col border border-slate-800 shadow-lg">
                            <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-800">
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

                            <p class="text-[11px] text-slate-400 mt-3 leading-relaxed">
                                Pégalo en tu IA favorita, copia el bloque HTML que te devuelva y pégalo directamente en el editor de esta página.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Sub-Modal: Selector rápido de imágenes -->
            <div x-show="mediaPickerOpen" style="display: none;"
                 class="fixed inset-0 z-[120] flex items-center justify-center bg-black/75 p-4">
                <div @click.away="mediaPickerOpen = false"
                     class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl h-[75vh] flex flex-col overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h4 class="font-bold text-brandDark uppercase tracking-wider text-sm flex items-center gap-2">
                            <i class="fas fa-images"></i> Seleccionar Imágenes de la Biblioteca
                        </h4>
                        <button type="button" @click="mediaPickerOpen = false"
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
                                <div @click="toggleMediaImage(item.url)"
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

<script type="application/json" id="cms-prompt-config"><?php echo json_encode($promptConfigPayload, 15, 512) ?></script>


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

        newImageUrl: '',
        newImageNote: '',
        images: [],

        newVideoUrl: '',
        newVideoTitle: '',
        videos: [],

        sections: [
            { id: 'hero', label: 'Hero + Métricas', enabled: true },
            { id: 'servicios', label: 'Servicios', enabled: true },
            { id: 'proceso', label: 'Proceso / Pasos', enabled: true },
            { id: 'galeria_fotos', label: 'Galería Fotos', enabled: false },
            { id: 'galeria_videos', label: 'Galería Videos', enabled: false },
            { id: 'testimonios', label: 'Testimonios', enabled: true },
            { id: 'faq', label: 'FAQ Acordeón', enabled: true },
            { id: 'contacto', label: 'Contacto CRM', enabled: true },
        ],

        init() {
            try {
                this.cfg = JSON.parse(document.getElementById('cms-prompt-config').textContent || '{}');
            } catch (e) {
                this.cfg = {};
            }
        },

        open() {
            this.isOpen = true;
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

        get assembledPrompt() {
            const pageLabels = {
                home: 'la página de inicio',
                landing: 'una landing page de alta conversión',
                service: 'una página interior de servicio',
                about: 'la página institucional / nosotros'
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

            const contactBlock = this.cfg.contact_context
                ? `\nDIRECTORIO DE CONTACTO Y REDES DEL SITIO (Úsalos en botones de llamada, WhatsApp directo y enlaces):\n${this.cfg.contact_context}\n`
                : '';

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
                hero: '- Hero Section de alto impacto con propuesta de valor clara, 2 botones CTA (uno hacia #contacto y otro hacia #servicios) y barra de métricas clave.',
                servicios: '- Sección de Servicios / Soluciones (#servicios) en cuadrícula moderna con íconos y botones que al hacer clic seleccionen el servicio en el formulario y hagan scroll suave a #contacto.',
                proceso: '- Sección de Proceso Paso a Paso (01, 02, 03, 04) o Diferenciadores de confianza.',
                galeria_fotos: '- Sección de Galería Fotográfica responsiva usando las imágenes proporcionadas.',
                galeria_videos: '- Sección de Galería de Videos incrustando los enlaces de YouTube indicados.',
                testimonios: '- Sección de Prueba Social (Testimonios).',
                faq: '- Sección de Preguntas Frecuentes (FAQ) con acordeón interactivo en Alpine.js.',
                contacto: '- Sección de Contacto y Conversión (#contacto) conectada al CRM y WhatsApp.'
            };
            const selectedSections = this.sections
                .filter(s => s.enabled)
                .map(s => sectionDescriptions[s.id])
                .join('\n');

            const turnstileTag = this.cfg.turnstile_code || '<div class="cf-turnstile my-3" data-sitekey="TU_SITE_KEY_AQUI"></div>';

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
3. Usa exclusivamente Tailwind CSS, Alpine.js 3.x (x-data, x-show, @click, @submit.prevent) e íconos de FontAwesome 6 (<i class="fa-solid fa-..."></i>).
4. En cualquier elemento con x-show que inicie oculto, agrega style="display: none;" para evitar parpadeos al cargar.
5. Alterna secciones con fondos claros y oscuros usando contenedores amplios (py-20, max-w-7xl mx-auto px-4 sm:px-6 lg:px-8) y diseño 100% responsive.

ESTRUCTURA DE SECCIONES REQUERIDA:
${selectedSections}

REGLAS OBLIGATORIAS PARA EL FORMULARIO (#contacto):
El CMS cuenta con un motor global en Alpine.js que captura cualquier formulario, lo guarda en la base de datos del CRM y abre WhatsApp con los datos llenados. Para que funcione, el <form> DEBE cumplir exactamente con esto:
1. La etiqueta <form> debe tener el evento: @submit.prevent="sendWhatsAppMessage($event)"
2. Debe incluir como primer elemento el campo Honeypot anti-spam oculto:
   <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
3. TODOS los campos (<input>, <select>, <textarea>) deben tener el atributo \`name\` en español y en minúsculas/guiones bajos (ej. name="nombre", name="telefono", name="email", name="servicio", name="mensaje"), ya que el script lee FormData dinámicamente usando esos nombres para armar el mensaje de WhatsApp y guardar el JSON en el CRM.
4. No pongas enlaces wa.me ni números de teléfono hardcodeados en la lógica de envío del formulario; el script global toma el número desde la configuración del CMS.
5. Incluye el contenedor de Cloudflare Turnstile justo antes del botón de envío:
   ${turnstileTag}
6. El botón de envío debe ser type="submit" e indicar visualmente que enviará la solicitud por WhatsApp.

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
<?php /**PATH C:\laragon\www\nexus-web\resources\views/admin/partials/prompt-generator-modal.blade.php ENDPATH**/ ?>