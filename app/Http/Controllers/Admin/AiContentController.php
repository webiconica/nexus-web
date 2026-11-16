<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\Setting;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Exception;

class AiContentController extends Controller
{
    /**
     * Generación completa (Páginas, Posts o Productos)
     */
    public function generate(Request $request, GeminiService $gemini)
    {
        if (!$gemini->isEnabled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'La Inteligencia Artificial está desactivada o falta configurar la API Key en Configuración Global.'
            ], 403);
        }

        $validated = $request->validate([
            'module' => 'required|in:posts,pages,products',
            'topic' => 'required|string|max:500',
            'description' => 'nullable|string|max:1500',
            'keywords' => 'nullable|string|max:255',
            'model' => 'required|string',
            'generate_content' => 'boolean',
            'generate_meta' => 'boolean',
            'generate_image' => 'boolean',
            'selected_images' => 'nullable|array',
            'selected_images.*' => 'string',
            // Estructura guiada para Páginas
            'blocks' => 'nullable|array',
            'blocks.*' => 'string',
            'services_list' => 'nullable|array',
            'steps_list' => 'nullable|array',
            'pricing_list' => 'nullable|array',
            'faqs_list' => 'nullable|array',
            'team_list' => 'nullable|array',
            // Opciones guiadas para Blog
            'post_style' => 'nullable|string',
            'post_extras' => 'nullable|array',
        ]);

        $module = $validated['module'];
        $genContent = $request->boolean('generate_content');
        $genMeta = $request->boolean('generate_meta');
        $genImage = $request->boolean('generate_image');
        $selectedImages = $validated['selected_images'] ?? [];

        if (!$genContent && !$genMeta && !$genImage) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selecciona al menos una opción para generar (Contenido, Metadatos SEO o Imagen).'
            ], 422);
        }

        try {
            $result = [];

            if ($genContent || $genMeta || $genImage) {
                $promptParts = [];
                $properties = [];
                $required = [];

                $promptKey = match ($module) {
                    'pages' => 'ai_prompt_pages',
                    'products' => 'ai_prompt_products',
                    default => 'ai_prompt_posts',
                };

                $baseInstruction = Setting::where('key', $promptKey)->value('value')
                    ?? 'Eres un experto copywriter y desarrollador web. Usa HTML limpio con clases de Tailwind CSS y Alpine.js.';

                $promptParts[] = $baseInstruction;

                // Inyectar contexto de marca (tailwind.config), directorio de contacto/redes y Turnstile
                $siteContext = $this->buildSiteContextForPrompt();
                if (!empty($siteContext)) {
                    $promptParts[] = $siteContext;
                }

                $promptParts[] = "MÓDULO DESTINO: " . strtoupper($module);
                $promptParts[] = "TÍTULO O TEMA PRINCIPAL: " . $validated['topic'];

                if (!empty($validated['description'])) {
                    $promptParts[] = "CONTEXTO / DETALLES ADICIONALES: " . $validated['description'];
                }

                if (!empty($validated['keywords'])) {
                    $promptParts[] = "PALABRAS CLAVE OBJETIVO: " . $validated['keywords'];
                }

                if (!empty($selectedImages) && $genContent) {
                    $imagesList = implode("\n- ", $selectedImages);
                    $promptParts[] = <<<EOT
IMÁGENES REALES DE LA BIBLIOTECA A INTEGRAR (OBLIGATORIO):
Debes insertar TODAS estas URLs reales de forma estética y responsiva dentro del código HTML (<img src="..." class="...">) respetando exactamente sus rutas sin inventar URLs externas:
- {$imagesList}
EOT;
                }

                if ($genContent) {
                    if ($module === 'pages') {
                        $promptParts[] = $this->buildPagesBlueprint($validated);
                    } else {
                        $promptParts[] = $this->buildPostsBlueprint($validated);
                    }

                    $properties['title'] = ['type' => 'STRING'];
                    $properties['content'] = ['type' => 'STRING'];
                    $required[] = 'title';
                    $required[] = 'content';

                    if ($module === 'posts') {
                        $properties['excerpt'] = ['type' => 'STRING'];
                        $required[] = 'excerpt';
                    }
                }

                if ($genMeta) {
                    $promptParts[] = "INSTRUCCIÓN SEO: Genera metadatos optimizados para buscadores: slug amigable en minúsculas con guiones (slug), meta_title (máx 60 caracteres), meta_description (máx 155 caracteres) y meta_keywords separadas por comas.";

                    $properties['slug'] = ['type' => 'STRING'];
                    $properties['meta_title'] = ['type' => 'STRING'];
                    $properties['meta_description'] = ['type' => 'STRING'];
                    $properties['meta_keywords'] = ['type' => 'STRING'];

                    $required = array_merge($required, ['slug', 'meta_title', 'meta_description', 'meta_keywords']);
                }

                if ($genImage) {
                    $promptParts[] = "INSTRUCCIÓN VISUAL: Escribe un prompt detallado en INGLÉS (image_prompt) para generar una fotografía o ilustración profesional de portada acorde al tema, sin texto incrustado.";
                    $properties['image_prompt'] = ['type' => 'STRING'];
                    $required[] = 'image_prompt';
                }

                $schema = [
                    'type' => 'OBJECT',
                    'properties' => $properties,
                    'required' => array_unique($required),
                ];

                $aiData = $gemini->generate(
                    implode("\n\n", $promptParts),
                    $module,
                    $validated['model'],
                    $schema
                );

                // Respaldo automático de Turnstile si se generó contenido con <form>
                if (!empty($aiData['content'])) {
                    $aiData['content'] = $this->ensureTurnstileInForms($aiData['content']);
                }

                $result = array_merge($result, $aiData);
            }

            if ($genImage && !empty($result['image_prompt'])) {
                $imageUrl = $this->generateAndOptimizeImage($result['image_prompt'], $validated['topic'], $module);
                $result['image_url'] = $imageUrl;
            }

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generación Modular por Bloques Individuales (Para construir o editar páginas sección por sección)
     */
    public function generateModule(Request $request, GeminiService $gemini)
    {
        if (!$gemini->isEnabled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'La Inteligencia Artificial está desactivada o falta configurar la API Key.'
            ], 403);
        }

        $validated = $request->validate([
            'group' => 'required|in:hero,services,corporate_info,pricing,steps,interactive_trust,contact,custom_free',
            'variant' => 'required|string|max:60',
            'model' => 'required|string',
            'page_title' => 'nullable|string|max:255',
            'previous_bg' => 'nullable|in:light,dark,accent',
            'selected_images' => 'nullable|array',
            'selected_images.*' => 'string',
            'media_video_url' => 'nullable|string|max:500',
            'fields' => 'nullable|array',
            'items' => 'nullable|array',
        ]);

        try {
            $baseInstruction = Setting::where('key', 'ai_prompt_pages')->value('value')
                ?? 'Eres un diseñador UI/UX senior y desarrollador experto en Tailwind CSS y Alpine.js.';

            $siteContext = $this->buildSiteContextForPrompt();
            $moduleBlueprint = $this->buildSingleModuleBlueprint($validated);

            $promptParts = [
                $baseInstruction,
                $siteContext,
                $moduleBlueprint,
            ];

            if (!empty($validated['selected_images'])) {
                $imagesList = implode("\n- ", $validated['selected_images']);
                $promptParts[] = "IMÁGENES REALES DE LA BIBLIOTECA A INTEGRAR EN ESTE MÓDULO (USA ESTAS URLS EXACTAS):\n- {$imagesList}";
            }

            if (!empty($validated['media_video_url'])) {
                $promptParts[] = "VIDEO DE FONDO SELECCIONADO (USA ESTA URL EN <video autoplay muted loop playsinline>):\n" . $validated['media_video_url'];
            }

            $schema = [
                'type' => 'OBJECT',
                'properties' => [
                    'module_title' => ['type' => 'STRING'],
                    'bg_tone' => ['type' => 'STRING'], // 'light' o 'dark'
                    'html' => ['type' => 'STRING'],
                ],
                'required' => ['module_title', 'bg_tone', 'html'],
            ];

            $aiData = $gemini->generate(
                implode("\n\n", array_filter($promptParts)),
                'pages_module',
                $validated['model'],
                $schema
            );

            $htmlContent = preg_replace('/^```html\s*|\s*```$/i', '', trim($aiData['html'] ?? ''));
            $htmlContent = $this->ensureTurnstileInForms($htmlContent);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'group' => $validated['group'],
                    'variant' => $validated['variant'],
                    'module_title' => $aiData['module_title'] ?? 'Módulo Generado',
                    'bg_tone' => $aiData['bg_tone'] ?? 'light',
                    'html' => $htmlContent,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refina o modifica un módulo HTML existente manteniendo intacto su diseño y estructura
     */
    public function refineModule(Request $request, GeminiService $gemini)
    {
        if (!$gemini->isEnabled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'La Inteligencia Artificial está desactivada.'
            ], 403);
        }

        $validated = $request->validate([
            'current_html' => 'required|string',
            'instruction' => 'required|string|max:1500',
            'model' => 'required|string',
            'selected_images' => 'nullable|array',
            'selected_images.*' => 'string',
        ]);

        try {
            $siteContext = $this->buildSiteContextForPrompt();

            $imagesNote = '';
            if (!empty($validated['selected_images'])) {
                $imagesList = implode("\n- ", $validated['selected_images']);
                $imagesNote = "\nIMÁGENES SELECCIONADAS DE LA BIBLIOTECA PARA USAR EN ESTE CAMBIO:\n- {$imagesList}";
            }

            $prompt = <<<EOT
Eres un desarrollador experto en Tailwind CSS, Alpine.js y FontAwesome 6.
El usuario ya tiene este módulo HTML diseñado y LE GUSTA SU DISEÑO Y ESTRUCTURA ACTUAL.
Tu única tarea es aplicar la modificación solicitada respetando al 100% el diseño, colores, sombras, espaciados y estructura original en todo lo demás.

{$siteContext}

=== CÓDIGO HTML ACTUAL DEL MÓDULO (BASE OBLIGATORIA) ===
{$validated['current_html']}

=== CAMBIO SOLICITADO POR EL USUARIO ===
{$validated['instruction']}
{$imagesNote}

REGLAS ESTRICTAS:
1. Mantén exactamente la misma estructura visual y clases Tailwind del bloque original, modificando únicamente los íconos, imágenes, textos o elementos que el usuario pidió cambiar.
2. Si el usuario pide cambiar un ícono por otro, usa clases válidas de FontAwesome 6 (ej. `fa-solid fa-dog`, `fa-solid fa-paw`, `fa-solid fa-bone`, `fa-solid fa-scissors`, `fa-solid fa-shield-halved`, `fa-solid fa-bath`, `fa-solid fa-heart`, etc.).
3. Si el usuario pide usar imágenes en lugar de íconos, reemplaza el contenedor del ícono por una imagen estilizada (`<img src="..." class="w-full h-48 object-cover rounded-2xl mb-5">` o avatar circular según convenga al diseño de la tarjeta).
4. Devuelve únicamente el código HTML actualizado de la sección en el campo `html`.
EOT;

            $schema = [
                'type' => 'OBJECT',
                'properties' => [
                    'html' => ['type' => 'STRING'],
                ],
                'required' => ['html'],
            ];

            $aiData = $gemini->generate(
                $prompt,
                'pages_module_refine',
                $validated['model'],
                $schema
            );

            $htmlContent = preg_replace('/^```html\s*|\s*```$/i', '', trim($aiData['html'] ?? ''));
            $htmlContent = $this->ensureTurnstileInForms($htmlContent);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'html' => $htmlContent,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Construye el prompt arquitectónico para UN solo módulo según su grupo, variante y campos
     */
    protected function buildSingleModuleBlueprint(array $data): string
    {
        $group = $data['group'];
        $variant = $data['variant'];
        $fields = $data['fields'] ?? [];
        $items = $data['items'] ?? [];
        $pageTitle = $data['page_title'] ?? 'Página Web';
        $prevBg = $data['previous_bg'] ?? null;

        $contrastRule = match ($prevBg) {
            'dark' => 'CONTRASTE VISUAL SUGERIDO: El módulo anterior de la página tiene fondo OSCURO. A menos que el usuario pida lo contrario en sus instrucciones adicionales, usa fondo CLARO (bg-white o bg-brandBg con texto text-brandDark) y devuelve bg_tone = "light".',
            'light' => 'CONTRASTE VISUAL SUGERIDO: El módulo anterior tiene fondo CLARO. Alterna visualmente usando un fondo suave (bg-brandBg) o un bloque oscuro elegante (bg-brandDark text-white) para dar ritmo visual.',
            default => 'Define un fondo de alto impacto acorde al tipo de sección y reporta bg_tone como "dark" o "light".',
        };

        $variantSpecs = [
            // 1. GRUPO HERO
            'hero_split' => 'HERO SPLIT (2 COLUMNAS): Sección inicial dividida en 2 columnas (grid-cols-1 lg:grid-cols-12). Izquierda (7 cols): badge superior, titular principal grande (text-4xl md:text-6xl font-black), subtítulo persuasivo, 2 botones CTA y barra de métricas clave. Derecha (5 cols): tarjeta visual flotante con imagen o panel destacado.',
            'hero_slider' => 'HERO CARRUSEL / SLIDER (ALPINE.JS): Banner de ancho completo con x-data="{ active: 0, slides: [...] }" con 3 diapositivas interactivas, transiciones suaves, controles anterior/siguiente e indicadores inferiores.',
            'hero_video' => 'HERO CON VIDEO DE FONDO: Contenedor relativo con overflow-hidden, etiqueta <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover"> con la URL de video indicada, capa overlay degradada (bg-brandDark/80) y contenido tipográfico centrado de alto contraste en blanco.',
            'hero_editorial' => 'HERO TIPOGRÁFICO / EDITORIAL CENTRADO: Diseño limpio y sofisticado centrado (max-w-4xl mx-auto text-center), etiqueta superior en mayúsculas, titular monumental con palabra resaltada en text-brandLight, párrafo de promesa y botones centrados.',

            // 2. GRUPO TARJETAS GENÉRICAS / SERVICIOS (INCLUYE EL DISEÑO DE LA CAPTURA)
            'cards_centered_clean' => 'CUADRÍCULA DE TARJETAS CENTRADAS LIMPIAS (ESTILO AGENCIA): Encabezado centrado con H2 grande en text-brandDark y subtítulo descriptivo. Debajo, grid responsivo (grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6) con tarjetas blancas (bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition text-center flex flex-col justify-between). Arriba un círculo centrado de color de acento (w-16 h-16 rounded-full bg-brandLight/20 o bg-brandLight text-white mx-auto mb-6 flex items-center justify-center) con el ícono o imagen circular, luego título en bold, párrafo descriptivo centrado y abajo un botón compacto centrado (px-5 py-2.5 bg-brandDark text-white text-xs font-bold rounded-lg hover:bg-brandLight transition).',
            'services_grid' => 'CUADRÍCULA DE TARJETAS MODERNAS ALINEADAS A LA IZQUIERDA: Grid responsivo (3 o 4 columnas) con tarjetas modernas, ícono o fotografía superior según el estilo visual elegido, título, descripción y enlace o botón de acción.',
            'split_feature_block' => 'BLOQUE GENÉRICO DIVIDIDO 50/50 (IMAGEN + TEXTO + LISTA): Sección de 2 columnas (grid-cols-1 lg:grid-cols-2 gap-12 items-center). Una columna con imagen destacada o composición visual redondeada y la otra con título, explicación detallada, viñetas con checks y botón CTA.',
            'services_tabs' => 'PESTAÑAS INTERACTIVAS DE CATEGORÍAS / SERVICIOS (ALPINE.JS): Selector por categorías x-data="{ tab: 0 }" con paneles detallados x-show (añade style="display: none;" en paneles inactivos).',

            // 3. GRUPO INFORMATIVO / CORPORATIVO
            'info_cards_duales' => 'MÓDULO CORPORATIVO EN TARJETAS DUALES: Encabezado de presentación institucional acompañado de tarjetas estructuradas con íconos o imágenes, bordes sutiles, sombras elegantes y jerarquía clara para cada pilar solicitado.',
            'info_editorial_imagen' => 'MÓDULO CORPORATIVO EDITORIAL ASIMÉTRICO: Diseño de 2 columnas combinando narrativa institucional cercana a un lado y composición visual con imagen/insignia de trayectoria al otro.',
            'info_bento_corporativo' => 'MÓDULO CORPORATIVO BENTO GRID: Cuadrícula moderna asimétrica (col-span-2) combinando bloques claros y bloques en bg-brandDark text-white para destacar identidad, filosofía y cifras clave.',

            // 4. GRUPO PRECIOS Y OFERTAS
            'pricing_columns' => 'TABLA DE PRECIOS EN COLUMNAS: Tarjetas comerciales lado a lado mostrando nombre del paquete/producto, precio destacado, descripción, lista de beneficios con checks (<i class="fa-solid fa-check text-brandLight"></i>) y botón de acción. Destaca visualmente el plan marcado como popular/recomendado.',
            'pricing_switch' => 'TABLA DE PRECIOS CON ALTERNADOR (ALPINE.JS): Incluye switch interactivo x-data="{ alt: false }" para alternar modalidades o periodos de pago, con tarjetas comparativas claras y botones de contratación.',
            'pricing_menu_list' => 'LISTA / CATÁLOGO DE SERVICIOS Y TARIFAS: Diseño elegante tipo menú o catálogo estructurado con filas limpias, nombre del producto/servicio, descripción detallada y precio alineado a la derecha con botón de consulta.',

            // 5. GRUPO PROCESO / PASOS
            'steps_timeline' => 'RUTA PASO A PASO (TIMELINE): Pasos numerados (01, 02, 03...) conectados visualmente explicando el proceso de principio a fin.',

            // 6. GRUPO INTERACTIVO / CONFIANZA
            'faq_accordion' => 'ACORDEÓN DE PREGUNTAS FRECUENTES (ALPINE.JS): Lista desplegable con x-data="{ open: 0 }" y style="display: none;" en respuestas cerradas.',
            'social_proof' => 'PRUEBA SOCIAL Y TESTIMONIOS: Tarjetas de reseñas verificadas con estrellas, nombre, cargo y métricas de confianza.',

            // 7. GRUPO CONTACTO / CIERRE
            'contact_crm' => 'MÓDULO DE CONTACTO INTEGRADO AL CRM: Sección de 2 columnas. Izquierda: invitación directa y los teléfonos, WhatsApps y redes sociales REALES del directorio configurado. Derecha: tarjeta con formulario <form @submit.prevent="sendWhatsAppMessage($event)"> que incluya campo honeypot <input type="text" name="website" class="hidden" autocomplete="off" tabindex="-1">, campos nombre, telefono, email, mensaje, el widget de Turnstile y botón submit.',
            'cta_banner' => 'BANNER DE CIERRE / LLAMADO A LA ACCIÓN: Franja de alto impacto visual con titular persuasivo y botones directos conectados al WhatsApp o teléfono real del negocio.',

            // 8. MÓDULO ABIERTO / LIBRE A MEDIDA
            'free_custom_section' => 'MÓDULO ABIERTO DISEÑADO A MEDIDA SEGÚN LA EXPLICACIÓN DEL USUARIO: Construye exactamente la sección, componente interactivo Alpine.js, calculadora, galería, comparativa o bloque visual que el usuario describe a continuación, aplicando diseño de agencia con Tailwind CSS, sombras refinadas, tipografía jerarquizada y total responsividad.',
        ];

        $specText = $variantSpecs[$variant] ?? "Sección web moderna variante {$variant}.";

        // Contexto de uso específico para Hero (Inicio vs Landing vs Interior)
        $heroContextNote = '';
        if ($group === 'hero' && !empty($fields['page_context'])) {
            $heroContextNote = match ($fields['page_context']) {
                'home' => 'PROPÓSITO DEL HERO: Es para la PÁGINA DE INICIO principal del sitio. Debe transmitir autoridad de marca global, propuesta de valor amplia y navegación clara (altura generosa py-20 lg:py-28).',
                'landing' => 'PROPÓSITO DEL HERO: Es para una LANDING PAGE DE CONVERSIÓN/VENTA. Enfoque 100% persuasivo hacia una oferta concreta, beneficios inmediatos y llamado a la acción directo.',
                'interior' => 'PROPÓSITO DEL HERO: Es para una PÁGINA INTERIOR (detalle de servicio o sección institucional). Más compacto y elegante (py-12 lg:py-16), con breadcrumb superior.',
                default => '',
            };
        }

        // Regla de estilo visual para tarjetas (Íconos vs Imágenes)
        $visualStyleRule = '';
        if (in_array($group, ['services', 'corporate_info', 'pricing'])) {
            $visualStyle = $fields['visual_style'] ?? 'icons';
            $visualStyleRule = match ($visualStyle) {
                'images' => 'ELEMENTO VISUAL DE LAS TARJETAS (OBLIGATORIO): En lugar de íconos FontAwesome, diseña cada tarjeta con una FOTOGRAFÍA / IMAGEN destacada en la parte superior (`w-full h-48 object-cover rounded-2xl mb-5`) o avatar circular grande (`w-20 h-20 rounded-full object-cover mx-auto mb-5 shadow-md`), usando las URLs de imágenes proporcionadas.',
                'mixed' => 'ELEMENTO VISUAL DE LAS TARJETAS: Combina fotografía superior en la tarjeta con un badge de ícono FontAwesome flotante.',
                default => 'ELEMENTO VISUAL DE LAS TARJETAS: Usa íconos de FontAwesome 6 semánticamente acordes al giro del negocio y al título de cada tarjeta dentro de un contenedor circular o redondeado estilizado.',
            };
        }

        // Extraer el campo universal de instrucciones adicionales para darle prioridad explícita
        $extraInstructions = trim((string) ($fields['extra_instructions'] ?? ''));
        $freePrompt = trim((string) ($fields['free_prompt'] ?? ''));

        // Serializar el resto de campos llenados por el usuario
        $userInputs = [];
        foreach ($fields as $k => $v) {
            if (in_array($k, ['page_context', 'visual_style', 'extra_instructions', 'free_prompt']) || empty($v)) {
                continue;
            }
            if (is_array($v)) {
                $userInputs[] = "- " . strtoupper($k) . ": " . implode(', ', $v);
            } else {
                $userInputs[] = "- " . strtoupper($k) . ": {$v}";
            }
        }

        if (!empty($items)) {
            $userInputs[] = "\nELEMENTOS / TARJETAS EXACTAS PROPORCIONADAS POR EL USUARIO (ÚSALAS TODAS EN ESTE ORDEN):";
            foreach ($items as $idx => $item) {
                $cleanItem = array_filter($item, fn($val) => $val !== '' && $val !== null);
                if (!empty($cleanItem)) {
                    $parts = [];
                    foreach ($cleanItem as $ik => $iv) {
                        if ($ik === 'icon' && !empty($iv)) {
                            $parts[] = "ÍCONO OBLIGATORIO: <i class=\"{$iv}\"></i>";
                        } elseif ($ik === 'image' && !empty($iv)) {
                            $parts[] = "IMAGEN OBLIGATORIA DE ESTA TARJETA: {$iv}";
                        } elseif ($ik === 'cta' && !empty($iv)) {
                            $parts[] = "TEXTO DEL BOTÓN: {$iv}";
                        } else {
                            $parts[] = is_bool($iv) ? ($iv ? "[DESTACADO]" : "") : "{$ik}: {$iv}";
                        }
                    }
                    $userInputs[] = ($idx + 1) . ". " . implode(' | ', array_filter($parts));
                }
            }
        }

        $userInputsBlock = !empty($userInputs)
            ? "DATOS ESTRUCTURADOS DEL MÓDULO:\n" . implode("\n", $userInputs)
            : "";

        $freePromptBlock = !empty($freePrompt)
            ? "\n=== EXPLICACIÓN ABIERTA DEL USUARIO DE LO QUE DESEA EN ESTE MÓDULO (OBLIGATORIO) ===\n{$freePrompt}\n"
            : "";

        $extraNotesBlock = !empty($extraInstructions)
            ? "\n=== INDICACIONES ADICIONALES Y DETALLES EXTRA DEL USUARIO PARA ESTE MÓDULO (PRIORIDAD ALTA) ===\nCumple estrictamente estas indicaciones adicionales sobre el diseño, comportamiento o contenido:\n{$extraInstructions}\n"
            : "";

        return <<<EOT
TAREA: GENERAR ÚNICAMENTE UN MÓDULO / SECCIÓN HTML (<section>...</section>) PARA INSERTAR EN LA PÁGINA "{$pageTitle}".
ARQUITECTURA DEL MÓDULO SOLICITADO:
{$specText}
{$heroContextNote}
{$visualStyleRule}

{$contrastRule}

{$userInputsBlock}
{$freePromptBlock}
{$extraNotesBlock}

REGLAS TÉCNICAS ESTRICTAS:
1. Devuelve ÚNICAMENTE el código HTML de esta única sección (<section class="...">...</section>) en el campo `html`.
2. PROHIBIDO incluir <html>, <head>, <body>, <header>, <nav>, <footer> o sintaxis Blade (@if, {{ }}).
Si el usuario pidió un formulario en este módulo, usa `<form @submit.prevent="sendWhatsAppMessage(\$event)">` e incluye `<input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">`.
4. Usa contenedores max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 y espaciado profesional (py-16 md:py-24).
5. En cualquier elemento Alpine.js con x-show que inicie oculto, agrega style="display: none;".
6. Usa los colores y tipografía de `tailwind.config` (brandDark, brandLight, brandGray, brandBg, font-sans) y los datos reales de contacto/redes si aplica.
EOT;
    }

    /**
     * Construye las instrucciones estructurales exactas para Páginas completas
     */
    protected function buildPagesBlueprint(array $data): string
    {
        $blocks = $data['blocks'] ?? [];
        $blockDefinitions = [
            'hero_split' => 'HERO SPLIT (2 COLUMNAS): Sección inicial de alto impacto con badge superior, H1 grande, subtítulo persuasivo, 2 botones CTA y métricas clave a la izquierda; tarjeta visual destacada o imagen a la derecha.',
            'hero_slider' => 'HERO FULL-WIDTH CON SLIDER (ALPINE.JS): Banner de ancho completo con fondo oscuro/degradado y carrusel interactivo x-data="{ activeSlide: 0, slides: [...] }" con 3 diapositivas, botones anterior/siguiente e indicadores.',
            'promo_countdown' => 'BANNER DE URGENCIA / OFERTA CON CONTADOR (ALPINE.JS): Franja promocional de alto impacto con cuenta regresiva dinámica en Alpine.js (días, horas, minutos, segundos) y botón CTA directo.',
            'funnel_quiz' => 'EMBUDO GUIADO / DIAGNÓSTICO INTERACTIVO (ALPINE.JS): Componente x-data de 2 o 3 pasos donde el visitante selecciona su necesidad o perfil, recibe una recomendación personalizada y al hacer clic rellena automáticamente el formulario de contacto.',
            'roi_calculator' => 'SIMULADOR / CALCULADORA INTERACTIVA (ALPINE.JS): Deslizadores (<input type="range">) y selectores en x-data para que el cliente estime ahorro, inversión, mensualidades o cotización instantánea en tiempo real.',
            'tabs_services' => 'PESTAÑAS DE SERVICIOS O CATEGORÍAS (ALPINE.JS): Navegación horizontal o lateral interactiva con x-data="{ tab: \'tab1\' }" y paneles x-show con style="display: none;" para explorar cada línea sin recargar.',
            'faq_accordion' => 'ACORDEÓN DE PREGUNTAS FRECUENTES / TEMARIO (ALPINE.JS): Lista desplegable limpia con x-data="{ open: 1 }" y style="display: none;" para resolver objeciones de venta o requisitos.',
            'comparison_switch' => 'TARJETAS COMPARATIVAS CON SWITCH (ALPINE.JS): Alternador interactivo x-data="{ alt: false }" entre dos modalidades, planes o esquemas con insignias y listas con checks (<i class="fa-solid fa-check"></i>).',
            'filter_gallery' => 'GALERÍA / PORTAFOLIO CON FILTROS (ALPINE.JS): Cuadrícula de proyectos, productos o casos de éxito con botones de filtro por categoría usando x-data="{ filter: \'all\' }".',
            'services_grid' => 'CUADRÍCULA DE BENEFICIOS / SERVICIOS: Grid responsivo (3 o 4 columnas) con tarjetas modernas, íconos FontAwesome en contenedor redondeado, descripción clara y micro-enlace de acción.',
            'bento_grid' => 'BENTO GRID ASIMÉTRICO: Cuadrícula moderna estilo Bento con tarjetas de distintos tamaños (col-span-2, row-span-2) combinando bloques claros y oscuros para destacar ventajas clave.',
            'steps_timeline' => 'RUTA PASO A PASO (TIMELINE / PROCESO): Bloques numerados (01, 02, 03...) conectados visualmente explicando el flujo de trabajo de principio a fin.',
            'pricing_table' => 'TABLA / TARJETAS DE PRECIOS Y PAQUETES: Diseño comercial de columnas de precios claras, destacando el paquete recomendado, con lista de características incluidas y botón de contratación.',
            'vs_table' => 'TABLA COMPARATIVA "NOSOTROS VS. TRADICIONAL": Tabla estructurada comparando nuestras ventajas frente a alternativas tradicionales con íconos de check verde y equis roja.',
            'social_proof' => 'PRUEBA SOCIAL (TESTIMONIOS Y AUTORIDAD): Sección con reseñas de clientes verificados (estrellas, nombre, cargo) y métricas de confianza.',
            'team_credentials' => 'EQUIPO / LIDERAZGO Y CERTIFICACIONES: Cuadrícula profesional de especialistas o líderes con avatar/foto, especialidad, credenciales/cédulas y sellos de respaldo.',
            'contact_crm' => 'BLOQUE DE CONTACTO INTEGRADO AL CRM: Sección dividida con datos de atención directa a la izquierda y formulario a la derecha usando <form @submit.prevent="sendWhatsAppMessage($event)"> con campo honeypot oculto <input type="text" name="website" class="hidden" autocomplete="off" tabindex="-1"> y campos: nombre, telefono, email y mensaje.',
        ];

        $selectedInstructions = [];
        foreach ($blocks as $blockKey) {
            if (isset($blockDefinitions[$blockKey])) {
                $selectedInstructions[] = "- " . $blockDefinitions[$blockKey];
            }
        }

        $customDataNotes = [];

        $services = array_filter($data['services_list'] ?? [], fn($s) => !empty(trim($s['name'] ?? '')));
        if (!empty($services)) {
            $lines = ["SERVICIOS / ELEMENTOS ESPECÍFICOS A MOSTRAR (USA EXACTAMENTE ESTOS):"];
            foreach ($services as $i => $srv) {
                $desc = !empty($srv['desc']) ? " — Detalle: {$srv['desc']}" : "";
                $lines[] = ($i + 1) . ". {$srv['name']}{$desc}";
            }
            $customDataNotes[] = implode("\n", $lines);
        }

        $steps = array_filter($data['steps_list'] ?? [], fn($s) => !empty(trim($s['title'] ?? '')));
        if (!empty($steps)) {
            $lines = ["PASOS EXACTOS PARA LA RUTA / PROCESO (RESPETA ESTE ORDEN):"];
            foreach ($steps as $i => $st) {
                $num = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
                $desc = !empty($st['desc']) ? " — Descripción: {$st['desc']}" : "";
                $lines[] = "Paso {$num}: {$st['title']}{$desc}";
            }
            $customDataNotes[] = implode("\n", $lines);
        }

        $pricing = array_filter($data['pricing_list'] ?? [], fn($p) => !empty(trim($p['name'] ?? '')));
        if (!empty($pricing)) {
            $lines = ["PRODUCTOS / PLANES Y PRECIOS EXACTOS PARA LA TABLA DE PRECIOS:"];
            foreach ($pricing as $plan) {
                $price = $plan['price'] ?? 'Consultar';
                $desc = $plan['desc'] ?? '';
                $lines[] = "- Plan/Producto: {$plan['name']} | Precio: {$price} | Incluye/Descripción: {$desc}";
            }
            $customDataNotes[] = implode("\n", $lines);
        }

        $faqs = array_filter($data['faqs_list'] ?? [], fn($f) => !empty(trim($f['q'] ?? '')));
        if (!empty($faqs)) {
            $lines = ["PREGUNTAS FRECUENTES EXACTAS A INCLUIR EN EL ACORDEÓN:"];
            foreach ($faqs as $f) {
                $ans = !empty($f['a']) ? " (Respuesta sugerida: {$f['a']})" : " (Redacta una respuesta clara y profesional)";
                $lines[] = "- Pregunta: {$f['q']}{$ans}";
            }
            $customDataNotes[] = implode("\n", $lines);
        }

        $team = array_filter($data['team_list'] ?? [], fn($m) => !empty(trim($m['name'] ?? '')));
        if (!empty($team)) {
            $lines = ["MIEMBROS DEL EQUIPO / ESPECIALISTAS A MOSTRAR:"];
            foreach ($team as $m) {
                $role = !empty($m['role']) ? " | Cargo/Especialidad: {$m['role']}" : "";
                $lines[] = "- {$m['name']}{$role}";
            }
            $customDataNotes[] = implode("\n", $lines);
        }

        $sectionsBlock = !empty($selectedInstructions)
            ? "ENSAMBLA ÚNICAMENTE LAS SIGUIENTES SECCIONES EN ESTE ORDEN EXACTO:\n" . implode("\n", $selectedInstructions)
            : "Diseña una página web moderna completa combinando Hero, Beneficios, Proceso paso a paso y Cierre de conversión.";

        $customBlock = !empty($customDataNotes)
            ? "\n\nDATOS OBLIGATORIOS DEL USUARIO PARA RELLENAR LAS SECCIONES:\n" . implode("\n\n", $customDataNotes)
            : "";

        return <<<EOT
INSTRUCCIÓN DE DISEÑO UI Y CONTENIDO (OBLIGATORIO):
Genera el título principal (title) y el código HTML completo de la página (content) usando Tailwind CSS, Alpine.js 3.x e íconos FontAwesome 6 (<i class="fa-solid fa-..."></i>).
PROHIBIDO incluir etiquetas <html>, <head>, <body>, <header>, <nav>, <footer> o sintaxis Blade (@if, {{ }}).
En elementos con x-show que inicien ocultos añade style="display: none;".
Usa contenedores amplios (max-w-7xl mx-auto px-4 sm:px-6 lg:px-8), espaciado generoso (py-16 md:py-24) y alterna fondos claros con bloques de contraste oscuro.

{$sectionsBlock}{$customBlock}
EOT;
    }

    /**
     * Construye las instrucciones editoriales para entradas de Blog
     */
    protected function buildPostsBlueprint(array $data): string
    {
        $style = $data['post_style'] ?? 'guia_practica';
        $extras = $data['post_extras'] ?? [];

        $styleInstruction = match ($style) {
            'comparativa' => 'Enfoque de comparativa detallada con tabla o tarjetas de pros/contras y recomendación final.',
            'caso_exito' => 'Enfoque narrativo de caso de estudio: reto inicial, solución aplicada paso a paso y resultados medibles.',
            'noticias_tendencia' => 'Enfoque analítico de tendencias actuales, impacto en el sector y recomendaciones accionables.',
            default => 'Enfoque de guía práctica paso a paso, didáctica, escaneable y orientada a resolver dudas reales.',
        };

        $extrasInstructions = [];
        if (in_array('summary_box', $extras)) {
            $extrasInstructions[] = "- Incluye al inicio del artículo un recuadro destacado de 'Puntos Clave / Resumen Ejecutivo' con viñetas.";
        }
        if (in_array('faq_section', $extras)) {
            $extrasInstructions[] = "- Incluye al final una sección de Preguntas Frecuentes (FAQ) con acordeón interactivo en Alpine.js (x-data).";
        }
        if (in_array('cta_banner', $extras)) {
            $extrasInstructions[] = "- Incluye al cierre del artículo un banner elegante de Llamado a la Acción (CTA) invitando al lector a contactar o solicitar asesoría.";
        }

        $extrasText = !empty($extrasInstructions)
            ? "\nELEMENTOS ADICIONALES SOLICITADOS:\n" . implode("\n", $extrasInstructions)
            : "";

        return <<<EOT
INSTRUCCIÓN DE CONTENIDO EDITORIAL PARA BLOG:
Genera el título atractivo optimizado para SEO (title), un extracto persuasivo de 2 líneas (excerpt) y el cuerpo completo del artículo en HTML limpio con Tailwind CSS (content), sin etiquetas <html>, <head> ni <body>.
ESTILO EDITORIAL: {$styleInstruction}
Estructura el artículo con encabezados H2 y H3 bien jerarquizados, párrafos ligeros, listas con viñetas o numeradas y tipografía legible.{$extrasText}
EOT;
    }

    /**
     * Construye el contexto obligatorio de Marca, Contacto y Turnstile desde Settings
     */
    protected function buildSiteContextForPrompt(): string
    {
        $settings = Setting::whereIn('key', [
            'site_name',
            'tailwind_config_script',
            'contact_directory_json',
            'turnstile_widget_code'
        ])->pluck('value', 'key');

        $context = [];

        if (!empty($settings['tailwind_config_script'])) {
            $context[] = "=== CONFIGURACIÓN DE ESTILO TAILWIND ACTIVA EN EL SITIO ===\n"
                . $settings['tailwind_config_script'] . "\n"
                . "REGLA DE DISEÑO OBLIGATORIA: Utiliza las clases personalizadas de la marca (`bg-brandDark`, `text-brandDark`, `bg-brandLight`, `text-brandLight`, `text-brandGray`, `bg-brandBg`, `border-brandLight`, `font-sans`) para mantener identidad visual de agencia y armonía cromática.";
        }

        if (!empty($settings['contact_directory_json'])) {
            $dir = json_decode($settings['contact_directory_json'], true);
            $contactLines = [];

            if (!empty($dir['phones'])) {
                foreach ($dir['phones'] as $p) {
                    if (empty($p['number'])) continue;
                    $cleanNum = preg_replace('/[^0-9]/', '', $p['number']);
                    $flags = [];
                    if (!empty($p['is_whatsapp'])) $flags[] = "WhatsApp activo (enlace: https://wa.me/{$cleanNum})";
                    if (!empty($p['is_call'])) $flags[] = "Llamada directa (enlace: tel:+{$cleanNum})";
                    $contactLines[] = "- Teléfono ({$p['label']}): {$p['number']} [" . implode(' | ', $flags) . "]";
                }
            }

            if (!empty($dir['socials'])) {
                foreach ($dir['socials'] as $s) {
                    if (empty($s['url'])) continue;
                    $contactLines[] = "- Red Social {$s['network']}: {$s['url']}";
                }
            }

            if (!empty($contactLines)) {
                $context[] = "=== DATOS REALES DE CONTACTO Y REDES SOCIALES DEL NEGOCIO ===\n"
                    . implode("\n", $contactLines) . "\n"
                    . "REGLA DE CONTACTO: Si el bloque incluye botones de WhatsApp, llamadas, ubicación o redes sociales, DEBES usar estos números y URLs reales (nunca inventes teléfonos ni uses `#`).";
            }
        }

        $turnstileHtml = $this->getTurnstileSnippet();
        if (!empty($turnstileHtml)) {
            $context[] = "=== SEGURIDAD EN FORMULARIOS (CLOUDFLARE TURNSTILE) ===\n"
                . "REGLA OBLIGATORIA PARA FORMULARIOS: Si el HTML contiene una etiqueta `<form>`, DEBES incluir exactamente este bloque antes del botón `<button type=\"submit\">`:\n"
                . $turnstileHtml;
        }

        return implode("\n\n", $context);
    }

    /**
     * Obtiene el snippet de Turnstile desde Settings o .env
     */
    protected function getTurnstileSnippet(): string
    {
        $turnstileHtml = trim((string) Setting::where('key', 'turnstile_widget_code')->value('value'));
        if (empty($turnstileHtml) && env('TURNSTILE_SITE_KEY')) {
            $turnstileHtml = '<div class="cf-turnstile my-4" data-sitekey="' . env('TURNSTILE_SITE_KEY') . '"></div>';
        }
        return $turnstileHtml;
    }

    /**
     * Respaldo automático: inyecta Turnstile dentro de cualquier <form> si la IA lo omitió
     */
    protected function ensureTurnstileInForms(string $html): string
    {
        $turnstileHtml = $this->getTurnstileSnippet();
        if (!empty($turnstileHtml) && str_contains($html, '<form') && !str_contains($html, 'cf-turnstile')) {
            $html = preg_replace(
                '/(<button[^>]*type=["\']submit["\'][^>]*>)/i',
                $turnstileHtml . "\n    $1",
                $html,
                1
            ) ?? $html;
        }
        return $html;
    }

    /**
     * Llama a Google Imagen 3, convierte a .webp y guarda en storage/app/public/media
     */
    protected function generateAndOptimizeImage(string $imagePrompt, string $topic, string $module): string
    {
        $dbKey = Setting::where('key', 'gemini_api_key')->value('value');
        $apiKey = !empty(trim((string) $dbKey)) ? trim($dbKey) : (string) env('GEMINI_API_KEY', '');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-002:predict?key={$apiKey}";

        $response = Http::timeout(60)->post($url, [
            'instances' => [
                ['prompt' => $imagePrompt]
            ],
            'parameters' => [
                'sampleCount' => 1,
                'aspectRatio' => '16:9',
            ]
        ]);

        if ($response->failed()) {
            $err = $response->json('error.message') ?? $response->body();
            throw new Exception("No se pudo generar la imagen: " . $err);
        }

        $base64Image = $response->json('predictions.0.bytesBase64Encoded');
        if (empty($base64Image)) {
            throw new Exception("La API no devolvió datos de imagen válidos.");
        }

        $binaryData = base64_decode($base64Image);

        $manager = ImageManager::usingDriver(Driver::class);
        $image = $manager->decode($binaryData);
        $encodedWebp = $image->encodeUsingFormat(Format::WEBP, quality: 82);

        $slug = Str::slug(Str::limit($topic, 40, ''));
        $filename = "media/{$slug}-ai-" . Str::random(6) . ".webp";

        Storage::disk('public')->put($filename, (string) $encodedWebp);

        $rate = (float) (Setting::where('key', 'ai_usd_to_mxn')->value('value') ?? 20.00);
        $costUsd = 0.03;

        AiUsageLog::create([
            'module' => $module . '_image',
            'model' => 'imagen-3.0-generate-002',
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
            'cost_usd' => $costUsd,
            'cost_mxn' => $costUsd * $rate,
        ]);

        return asset('storage/' . $filename);
    }
}