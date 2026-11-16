<?php
    $aiEnabled = (\App\Models\Setting::where('key', 'ai_enabled')->value('value') ?? '0') === '1';
    $aiModels = \App\Services\GeminiService::availableModels();
    $defaultModel = \App\Models\Setting::where('key', 'ai_default_model')->value('value') ?? 'gemini-2.5-flash';
?>

<?php if($aiEnabled): ?>
<div x-data="aiContentAssistant('<?php echo e($module ?? 'posts'); ?>', '<?php echo e($defaultModel); ?>')" class="inline-block">
    <!-- Botón Disparador -->
    <button type="button" @click="open = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-bold rounded-xl shadow-md transition">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Crear con IA
    </button>

    <!-- Modal Principal -->
    <div x-show="open" style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="if(!loading && !libraryOpen) open = false"
             class="bg-white rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl border border-gray-100 space-y-6 max-h-[92vh] overflow-y-auto">

            <!-- Encabezado -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 sticky top-0 bg-white z-10">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-robot"></i>
                    </span>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">
                            <?php echo e(($module ?? 'posts') === 'pages' ? 'Constructor Guiado de Páginas con IA' : 'Asistente de Redacción de Blog con IA'); ?>

                        </h3>
                        <p class="text-xs text-gray-500">
                            <?php echo e(($module ?? 'posts') === 'pages' ? 'Parte de una plantilla rápida y combina libremente cualquier bloque adicional.' : 'Define el tema, enfoque editorial y elementos de tu entrada.'); ?>

                        </p>
                    </div>
                </div>
                <button type="button" @click="open = false" :disabled="loading" class="text-gray-400 hover:text-gray-700 p-1">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- 1. INFORMACIÓN BASE -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-7">
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">
                        <?php echo e(($module ?? 'posts') === 'pages' ? 'Título o Objetivo de la Página *' : 'Título o Tema del Artículo *'); ?>

                    </label>
                    <input type="text" x-model="form.topic"
                           placeholder="<?php echo e(($module ?? 'posts') === 'pages' ? 'Ej: Constitución y registro de empresas en México' : 'Ej: 5 estrategias SEO con IA para negocios locales'); ?>"
                           class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-5">
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Modelo de IA</label>
                    <select x-model="form.model" class="w-full rounded-xl border-gray-300 text-sm">
                        <?php $__currentLoopData = $aiModels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($info['label']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="md:col-span-8">
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">
                        <?php echo e(($module ?? 'posts') === 'pages' ? 'Descripción del Negocio / Contexto (Opcional)' : 'Descripción o Puntos Clave a Tratar (Opcional)'); ?>

                    </label>
                    <textarea x-model="form.description" rows="2"
                              placeholder="<?php echo e(($module ?? 'posts') === 'pages' ? 'Ej: Somos un despacho jurídico en CDMX enfocado en emprendedores y pymes. Tono cercano y profesional...' : 'Ej: Explica qué es, beneficios principales y ejemplos prácticos para pymes...'); ?>"
                              class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Palabras Clave SEO (Opcional)</label>
                    <textarea x-model="form.keywords" rows="2"
                              placeholder="ej. constitución empresas, SAT, trámites legales"
                              class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            <!-- =====================================================================
                 2A. CONSTRUCTOR MODULAR COMBINABLE (SOLO PARA MÓDULO PÁGINAS)
                 ===================================================================== -->
            <?php if(($module ?? 'posts') === 'pages'): ?>
            <div class="space-y-5 border-t border-gray-100 pt-5">

                <!-- PLANTILLAS RÁPIDAS (PRESETS) -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-700">
                            <i class="fa-solid fa-bolt text-amber-500 mr-1"></i> Plantillas Rápidas (Elige una base y personalízala)
                        </span>
                        <button type="button" @click="form.blocks = []; activePreset = 'custom'"
                                class="text-[11px] font-bold text-gray-500 hover:text-red-600">
                            Limpiar selección
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <button type="button" @click="applyPreset('servicios')"
                                :class="activePreset === 'servicios' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white text-gray-800 border-gray-200 hover:border-indigo-400'"
                                class="p-3 rounded-xl border text-left transition">
                            <span class="block text-xs font-black"><i class="fa-solid fa-briefcase mr-1.5"></i> Landing de Servicios</span>
                            <span class="block text-[10px] opacity-80 mt-0.5">Hero + Servicios + Pasos + FAQ + Contacto</span>
                        </button>

                        <button type="button" @click="applyPreset('ventas')"
                                :class="activePreset === 'ventas' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white text-gray-800 border-gray-200 hover:border-indigo-400'"
                                class="p-3 rounded-xl border text-left transition">
                            <span class="block text-xs font-black"><i class="fa-solid fa-tags mr-1.5"></i> Ventas y Planes</span>
                            <span class="block text-[10px] opacity-80 mt-0.5">Hero + Comparativa + Precios + Testimonios + CRM</span>
                        </button>

                        <button type="button" @click="applyPreset('catalogo')"
                                :class="activePreset === 'catalogo' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white text-gray-800 border-gray-200 hover:border-indigo-400'"
                                class="p-3 rounded-xl border text-left transition">
                            <span class="block text-xs font-black"><i class="fa-solid fa-images mr-1.5"></i> Catálogo / Portafolio</span>
                            <span class="block text-[10px] opacity-80 mt-0.5">Slider + Galería con Filtros + Pestañas + Contacto</span>
                        </button>
                    </div>
                </div>

                <!-- BLOQUES ACTUALMENTE ACTIVOS EN LA PÁGINA -->
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black uppercase tracking-wider text-indigo-950">
                            <i class="fa-solid fa-layer-group mr-1.5 text-indigo-600"></i>
                            Bloques incluidos en tu página (<span x-text="form.blocks.length"></span>)
                        </h4>
                        <span class="text-[11px] text-gray-400">Puedes quitar o añadir cualquier bloque extra</span>
                    </div>

                    <div x-show="form.blocks.length === 0" class="p-4 rounded-xl border border-dashed border-gray-300 text-center text-xs text-gray-500">
                        No hay bloques seleccionados. Elige una plantilla rápida arriba o agrega bloques desde la sección inferior.
                    </div>

                    <div x-show="form.blocks.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <template x-for="(blockKey, index) in form.blocks" :key="blockKey">
                            <div class="flex items-start justify-between gap-2 p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl">
                                <div class="flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-md bg-indigo-600 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5"
                                          x-text="index + 1"></span>
                                    <div>
                                        <span class="block text-xs font-bold text-indigo-950" x-text="getBlockMeta(blockKey).title"></span>
                                        <span class="block text-[11px] text-indigo-800/80 leading-snug" x-text="getBlockMeta(blockKey).desc"></span>
                                    </div>
                                </div>
                                <button type="button" @click="removeBlock(blockKey)" title="Quitar bloque"
                                        class="text-indigo-400 hover:text-red-600 p-1 transition shrink-0">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- SECCIÓN: MÁS BLOQUES DISPONIBLES (COMBINABLES LIBREMENTE) -->
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200 space-y-3" x-data="{ filterCat: 'all' }">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-black uppercase tracking-wider text-gray-800">
                            <i class="fa-solid fa-plus-circle text-emerald-600 mr-1"></i> Más Bloques Disponibles (Combinables)
                        </span>

                        <!-- Filtro rápido por categoría -->
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="filterCat = 'all'"
                                    :class="filterCat === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold">Todos</button>
                            <button type="button" @click="filterCat = 'impacto'"
                                    :class="filterCat === 'impacto' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold">Impacto</button>
                            <button type="button" @click="filterCat = 'interactivo'"
                                    :class="filterCat === 'interactivo' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold">Interactivos Alpine.js</button>
                            <button type="button" @click="filterCat = 'estructura'"
                                    :class="filterCat === 'estructura' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold">Estructura y Precios</button>
                        </div>
                    </div>

                    <p class="text-[11px] text-gray-500">
                        ¿Elegiste Catálogo y quieres añadir una <strong>Tabla de Precios</strong> o una <strong>Calculadora</strong>? Haz clic en cualquier bloque para sumarlo a tu página:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-56 overflow-y-auto pr-1">
                        <template x-for="blk in availableExtraBlocks(filterCat)" :key="blk.key">
                            <button type="button" @click="addBlock(blk.key)"
                                    class="p-3 bg-white hover:bg-emerald-50/70 border border-gray-200 hover:border-emerald-400 rounded-xl text-left transition group flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-gray-100 text-gray-600" x-text="blk.catLabel"></span>
                                        <span class="text-xs font-black text-emerald-600 group-hover:scale-110 transition">+ Agregar</span>
                                    </div>
                                    <span class="block text-xs font-bold text-gray-900" x-text="blk.title"></span>
                                    <span class="block text-[10px] text-gray-500 leading-snug mt-0.5" x-text="blk.desc"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- ===============================================================
                     SUB-FORMULARIOS DINÁMICOS (SE ACTIVAN SEGÚN BLOQUES ELEGIDOS)
                     =============================================================== -->

                <!-- 1. SERVICIOS / ELEMENTOS ESPECÍFICOS -->
                <div x-show="hasBlock('services_grid') || hasBlock('tabs_services') || hasBlock('bento_grid')" style="display: none;"
                     class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase text-emerald-900">
                            <i class="fa-solid fa-briefcase mr-1"></i> Servicios o Elementos a destacar (Opcional)
                        </span>
                        <button type="button" @click="addService()" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">
                            + Agregar Servicio
                        </button>
                    </div>
                    <template x-for="(srv, idx) in form.services_list" :key="idx">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                            <input type="text" x-model="srv.name" :placeholder="'Servicio ' + (idx + 1) + ' (ej. Trámites ante el SAT)'" class="sm:col-span-5 rounded-lg border-gray-300 text-xs">
                            <input type="text" x-model="srv.desc" placeholder="Breve descripción o alcance" class="sm:col-span-6 rounded-lg border-gray-300 text-xs">
                            <button type="button" @click="form.services_list.splice(idx, 1)" class="sm:col-span-1 text-red-500 hover:text-red-700 text-xs text-center">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- 2. RUTA PASO A PASO -->
                <div x-show="hasBlock('steps_timeline')" style="display: none;"
                     class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase text-emerald-900">
                            <i class="fa-solid fa-route mr-1"></i> Pasos de la Ruta / Proceso (Opcional)
                        </span>
                        <button type="button" @click="addStep()" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">
                            + Agregar Paso
                        </button>
                    </div>
                    <template x-for="(step, idx) in form.steps_list" :key="idx">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                            <input type="text" x-model="step.title" :placeholder="'Paso ' + (idx + 1) + ' (ej. Alta en Hacienda)'" class="sm:col-span-5 rounded-lg border-gray-300 text-xs">
                            <input type="text" x-model="step.desc" placeholder="¿Qué ocurre en este paso?" class="sm:col-span-6 rounded-lg border-gray-300 text-xs">
                            <button type="button" @click="form.steps_list.splice(idx, 1)" class="sm:col-span-1 text-red-500 hover:text-red-700 text-xs text-center">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- 3. TABLA DE PRECIOS / PRODUCTOS -->
                <div x-show="hasBlock('pricing_table')" style="display: none;"
                     class="p-4 bg-amber-50/60 rounded-2xl border border-amber-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase text-amber-900">
                            <i class="fa-solid fa-tags mr-1"></i> Productos / Paquetes y Precios
                        </span>
                        <button type="button" @click="addPricing()" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold">
                            + Agregar Plan / Producto
                        </button>
                    </div>
                    <template x-for="(plan, idx) in form.pricing_list" :key="idx">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                            <input type="text" x-model="plan.name" placeholder="Nombre del Plan/Producto" class="sm:col-span-4 rounded-lg border-gray-300 text-xs">
                            <input type="text" x-model="plan.price" placeholder="Precio (ej. $2,500 MXN)" class="sm:col-span-3 rounded-lg border-gray-300 text-xs">
                            <input type="text" x-model="plan.desc" placeholder="Qué incluye / Beneficios" class="sm:col-span-4 rounded-lg border-gray-300 text-xs">
                            <button type="button" @click="form.pricing_list.splice(idx, 1)" class="sm:col-span-1 text-red-500 hover:text-red-700 text-xs text-center">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- 4. PREGUNTAS FRECUENTES (FAQ) -->
                <div x-show="hasBlock('faq_accordion')" style="display: none;"
                     class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase text-indigo-900">
                            <i class="fa-solid fa-circle-question mr-1"></i> Preguntas Frecuentes para el Acordeón (Opcional)
                        </span>
                        <button type="button" @click="addFaq()" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">
                            + Agregar Pregunta
                        </button>
                    </div>
                    <template x-for="(faq, idx) in form.faqs_list" :key="idx">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                            <input type="text" x-model="faq.q" placeholder="Ej: ¿Cuánto tarda el trámite?" class="sm:col-span-5 rounded-lg border-gray-300 text-xs">
                            <input type="text" x-model="faq.a" placeholder="Respuesta (o déjalo vacío para que la IA responda)" class="sm:col-span-6 rounded-lg border-gray-300 text-xs">
                            <button type="button" @click="form.faqs_list.splice(idx, 1)" class="sm:col-span-1 text-red-500 hover:text-red-700 text-xs text-center">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- 5. EQUIPO / ESPECIALISTAS -->
                <div x-show="hasBlock('team_credentials')" style="display: none;"
                     class="p-4 bg-slate-100 rounded-2xl border border-slate-300 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase text-slate-800">
                            <i class="fa-solid fa-user-tie mr-1"></i> Especialistas o Miembros del Equipo (Opcional)
                        </span>
                        <button type="button" @click="addTeam()" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold">
                            + Agregar Perfil
                        </button>
                    </div>
                    <template x-for="(member, idx) in form.team_list" :key="idx">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                            <input type="text" x-model="member.name" placeholder="Nombre completo" class="sm:col-span-5 rounded-lg border-gray-300 text-xs">
                            <input type="text" x-model="member.role" placeholder="Cargo, especialidad o cédula" class="sm:col-span-6 rounded-lg border-gray-300 text-xs">
                            <button type="button" @click="form.team_list.splice(idx, 1)" class="sm:col-span-1 text-red-500 hover:text-red-700 text-xs text-center">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
            <?php endif; ?>

            <!-- =====================================================================
                 2B. OPCIONES EDITORIALES SIMPLIFICADAS (SOLO PARA MÓDULO BLOG)
                 ===================================================================== -->
            <?php if(($module ?? 'posts') === 'posts'): ?>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Enfoque del Artículo</label>
                        <select x-model="form.post_style" class="w-full rounded-xl border-gray-300 text-sm">
                            <option value="guia_practica">Guía Práctica / Tutorial Paso a Paso</option>
                            <option value="comparativa">Comparativa / Análisis de Opciones</option>
                            <option value="caso_exito">Caso de Éxito / Estudio Práctico</option>
                            <option value="noticias_tendencia">Tendencias y Novedades del Sector</option>
                        </select>
                    </div>

                    <div class="space-y-2 pt-1">
                        <span class="block text-xs font-bold uppercase text-gray-700">Extras Convenientes</span>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" value="summary_box" x-model="form.post_extras" class="rounded text-indigo-600">
                            <span class="text-xs font-semibold text-gray-700">Recuadro de Resumen Ejecutivo al inicio</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" value="faq_section" x-model="form.post_extras" class="rounded text-indigo-600">
                            <span class="text-xs font-semibold text-gray-700">Preguntas Frecuentes (FAQ) al final</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" value="cta_banner" x-model="form.post_extras" class="rounded text-indigo-600">
                            <span class="text-xs font-semibold text-gray-700">Banner de Llamado a la Acción (CTA) de cierre</span>
                        </label>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 3. SELECTOR DE IMÁGENES DE LA BIBLIOTECA -->
            <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="block text-xs font-bold uppercase text-indigo-900">Imágenes de la Biblioteca (Opcional)</span>
                        <span class="text-[11px] text-indigo-700">La IA insertará estas fotos dentro del diseño HTML generado.</span>
                    </div>
                    <button type="button" @click="openLibrary()"
                            class="px-3 py-1.5 bg-white hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-images"></i>
                        <span x-text="form.selected_images.length > 0 ? 'Agregar / Editar (' + form.selected_images.length + ')' : 'Elegir Imágenes'"></span>
                    </button>
                </div>

                <template x-if="form.selected_images.length > 0">
                    <div class="flex flex-wrap gap-2.5 pt-1">
                        <template x-for="(imgUrl, idx) in form.selected_images" :key="imgUrl">
                            <div class="relative group w-16 h-16 rounded-xl overflow-hidden border-2 border-indigo-400 shadow-sm bg-white">
                                <img :src="imgUrl" class="w-full h-full object-cover">
                                <button type="button" @click="removeSelectedImage(idx)"
                                        class="absolute top-0.5 right-0.5 w-5 h-5 rounded-full bg-red-600 text-white text-[10px] flex items-center justify-center opacity-90 hover:opacity-100 shadow">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- 4. QUÉ GENERAR EN EL FORMULARIO -->
            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" x-model="form.generate_content" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs font-bold text-gray-800">Generar Contenido HTML</span>
                </label>

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" x-model="form.generate_meta" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs font-bold text-gray-800">Generar Metadatos SEO</span>
                </label>

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" x-model="form.generate_image" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs font-bold text-gray-800">Generar Portada IA (.webp)</span>
                </label>
            </div>

            <!-- Mensaje de Error -->
            <template x-if="errorMsg">
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs font-semibold text-red-700" x-text="errorMsg"></div>
            </template>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="open = false" :disabled="loading"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                    Cancelar
                </button>
                <button type="button" @click="generate()" :disabled="loading"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-spinner fa-spin" x-show="loading"></i>
                    <i class="fa-solid fa-sparkles" x-show="!loading"></i>
                    <span x-text="loading ? 'Construyendo con Gemini...' : 'Generar y Rellenar Formulario'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Sub-Modal: Selector Visual de Biblioteca de Medios -->
    <div x-show="libraryOpen" style="display: none;"
         class="fixed inset-0 z-[60] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="libraryOpen = false"
             class="bg-white rounded-3xl max-w-4xl w-full h-[78vh] flex flex-col overflow-hidden shadow-2xl border border-gray-200">

            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <div>
                    <h4 class="text-sm font-black uppercase tracking-wider text-gray-900">
                        <i class="fa-solid fa-images text-indigo-600 mr-1.5"></i> Seleccionar Imágenes para el Contenido
                    </h4>
                    <p class="text-xs text-gray-500">Haz clic sobre una o varias imágenes para marcarlas.</p>
                </div>
                <button type="button" @click="libraryOpen = false"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                    Listo (<span x-text="form.selected_images.length"></span> seleccionadas)
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-gray-50/50">
                <div x-show="loadingLibrary" class="text-center py-16 text-gray-400">
                    <i class="fa-solid fa-spinner fa-spin text-3xl mb-3"></i>
                    <p class="text-xs font-semibold">Cargando imágenes de tu biblioteca...</p>
                </div>

                <div x-show="!loadingLibrary && libraryImages.length === 0" class="text-center py-16 text-gray-400">
                    <i class="fa-regular fa-folder-open text-3xl mb-3"></i>
                    <p class="text-xs font-semibold">No hay imágenes disponibles en la Biblioteca de Medios.</p>
                </div>

                <div x-show="!loadingLibrary && libraryImages.length > 0"
                     class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4">
                    <template x-for="item in libraryImages" :key="item.url">
                        <div @click="toggleImageSelection(item.url)"
                             :class="isImageSelected(item.url) ? 'ring-4 ring-indigo-600 border-indigo-600' : 'border-gray-200 hover:border-indigo-400'"
                             class="cursor-pointer bg-white rounded-2xl border overflow-hidden shadow-sm transition relative group">
                            <div class="aspect-square bg-gray-100 relative">
                                <img :src="item.url" class="w-full h-full object-cover">
                                <div x-show="isImageSelected(item.url)"
                                     class="absolute top-2 right-2 w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs shadow-md">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                            </div>
                            <div class="p-2 text-center border-t border-gray-50">
                                <p class="text-[10px] text-gray-600 truncate font-semibold" x-text="item.name"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('7f6ee522-8c2e-475a-bbf1-50a44e720b52')): $__env->markAsRenderedOnce('7f6ee522-8c2e-475a-bbf1-50a44e720b52'); ?>
<script>
const AI_PAGE_BLOCKS_CATALOG = [
    // Impacto
    { key: 'hero_split', cat: 'impacto', catLabel: 'Impacto', title: 'Hero Split (2 Columnas)', desc: 'Título grande, propuesta de valor y botones a la izquierda; tarjeta visual o imagen a la derecha.' },
    { key: 'hero_slider', cat: 'impacto', catLabel: 'Impacto', title: 'Hero Full-Width con Slider', desc: 'Banner de ancho completo con fondo oscuro y carrusel interactivo de 3 diapositivas.' },
    { key: 'promo_countdown', cat: 'impacto', catLabel: 'Impacto', title: 'Banner de Urgencia / Contador', desc: 'Franja promocional con cuenta regresiva en vivo (días, horas, minutos) y botón CTA.' },
    // Interactivos
    { key: 'funnel_quiz', cat: 'interactivo', catLabel: 'Alpine.js', title: 'Embudo Guiado / Diagnóstico', desc: 'Cuestionario interactivo de 2-3 pasos que recomienda la solución y rellena el contacto.' },
    { key: 'roi_calculator', cat: 'interactivo', catLabel: 'Alpine.js', title: 'Simulador / Calculadora', desc: 'Deslizadores interactivos para estimar ahorro, inversión mensual o cotización instantánea.' },
    { key: 'tabs_services', cat: 'interactivo', catLabel: 'Alpine.js', title: 'Pestañas de Servicios (Tabs)', desc: 'Navegación por pestañas que cambia el contenido sin recargar la página.' },
    { key: 'faq_accordion', cat: 'interactivo', catLabel: 'Alpine.js', title: 'Acordeón de Preguntas (FAQ)', desc: 'Lista desplegable limpia para resolver objeciones de venta, requisitos o temarios.' },
    { key: 'comparison_switch', cat: 'interactivo', catLabel: 'Alpine.js', title: 'Comparativa con Switch', desc: 'Alternador entre dos modalidades o esquemas (ej. Persona Física vs Moral) con checks.' },
    { key: 'filter_gallery', cat: 'interactivo', catLabel: 'Alpine.js', title: 'Galería / Portafolio con Filtros', desc: 'Cuadrícula de proyectos, productos o casos de éxito filtrables por categoría.' },
    // Estructura y Precios
    { key: 'services_grid', cat: 'estructura', catLabel: 'Estructura', title: 'Cuadrícula de Servicios', desc: 'Grid de tarjetas con ícono, título y descripción de cada servicio o beneficio.' },
    { key: 'bento_grid', cat: 'estructura', catLabel: 'Estructura', title: 'Bento Grid Asimétrico', desc: 'Cuadrícula moderna estilo Apple con tarjetas de distintos tamaños y alto contraste.' },
    { key: 'steps_timeline', cat: 'estructura', catLabel: 'Estructura', title: 'Ruta Paso a Paso (Timeline)', desc: 'Pasos numerados (01, 02, 03...) explicando el proceso de principio a fin.' },
    { key: 'pricing_table', cat: 'estructura', catLabel: 'Precios', title: 'Tabla / Tarjetas de Precios', desc: 'Columnas comerciales con precios, beneficios incluidos y plan destacado.' },
    { key: 'vs_table', cat: 'estructura', catLabel: 'Estructura', title: 'Tabla Nosotros vs. Tradicional', desc: 'Tabla comparativa resaltando tus ventajas frente a la competencia.' },
    { key: 'social_proof', cat: 'estructura', catLabel: 'Confianza', title: 'Prueba Social y Testimonios', desc: 'Reseñas de clientes con estrellas, cargo y métricas de autoridad.' },
    { key: 'team_credentials', cat: 'estructura', catLabel: 'Confianza', title: 'Equipo y Certificaciones', desc: 'Perfiles de especialistas con cargo, cédula/certificación y respaldo.' },
    { key: 'contact_crm', cat: 'estructura', catLabel: 'Conversión', title: 'Contacto Integrado al CRM', desc: 'Datos directos y formulario conectado a sendWhatsAppMessage($event).' },
];

function aiContentAssistant(moduleName, defaultModel) {
    return {
        open: false,
        loading: false,
        errorMsg: '',
        libraryOpen: false,
        loadingLibrary: false,
        libraryImages: [],
        activePreset: 'servicios',
        form: {
            module: moduleName,
            topic: '',
            description: '',
            keywords: '',
            model: defaultModel,
            generate_content: true,
            generate_meta: true,
            generate_image: false,
            selected_images: [],
            blocks: moduleName === 'pages' ? ['hero_split', 'services_grid', 'steps_timeline', 'faq_accordion', 'contact_crm'] : [],
            services_list: [
                { name: '', desc: '' },
                { name: '', desc: '' }
            ],
            steps_list: [
                { title: '', desc: '' },
                { title: '', desc: '' },
                { title: '', desc: '' }
            ],
            pricing_list: [
                { name: '', price: '', desc: '' },
                { name: '', price: '', desc: '' }
            ],
            faqs_list: [
                { q: '', a: '' }
            ],
            team_list: [
                { name: '', role: '' },
                { name: '', role: '' }
            ],
            post_style: 'guia_practica',
            post_extras: ['summary_box', 'cta_banner'],
        },
        applyPreset(preset) {
            this.activePreset = preset;
            if (preset === 'servicios') {
                this.form.blocks = ['hero_split', 'services_grid', 'steps_timeline', 'faq_accordion', 'contact_crm'];
            } else if (preset === 'ventas') {
                this.form.blocks = ['hero_split', 'vs_table', 'pricing_table', 'social_proof', 'contact_crm'];
            } else if (preset === 'catalogo') {
                this.form.blocks = ['hero_slider', 'filter_gallery', 'tabs_services', 'contact_crm'];
            }
        },
        hasBlock(key) {
            return this.form.blocks.includes(key);
        },
        addBlock(key) {
            if (!this.hasBlock(key)) {
                this.form.blocks.push(key);
                this.activePreset = 'custom';
            }
        },
        removeBlock(key) {
            this.form.blocks = this.form.blocks.filter(k => k !== key);
            this.activePreset = 'custom';
        },
        getBlockMeta(key) {
            return AI_PAGE_BLOCKS_CATALOG.find(b => b.key === key) || { title: key, desc: '' };
        },
        availableExtraBlocks(filterCat) {
            return AI_PAGE_BLOCKS_CATALOG.filter(b => {
                const notSelected = !this.hasBlock(b.key);
                const matchesCat = filterCat === 'all' || b.cat === filterCat;
                return notSelected && matchesCat;
            });
        },
        addService() {
            this.form.services_list.push({ name: '', desc: '' });
        },
        addStep() {
            this.form.steps_list.push({ title: '', desc: '' });
        },
        addPricing() {
            this.form.pricing_list.push({ name: '', price: '', desc: '' });
        },
        addFaq() {
            this.form.faqs_list.push({ q: '', a: '' });
        },
        addTeam() {
            this.form.team_list.push({ name: '', role: '' });
        },
        async openLibrary() {
            this.libraryOpen = true;
            if (this.libraryImages.length === 0) {
                this.loadingLibrary = true;
                try {
                    const res = await fetch("<?php echo e(route('admin.media.api')); ?>");
                    const allItems = await res.json();
                    this.libraryImages = allItems.filter(item => item.is_image);
                } catch (e) {
                    console.error('Error al cargar biblioteca:', e);
                } finally {
                    this.loadingLibrary = false;
                }
            }
        },
        isImageSelected(url) {
            return this.form.selected_images.includes(url);
        },
        toggleImageSelection(url) {
            const idx = this.form.selected_images.indexOf(url);
            if (idx > -1) {
                this.form.selected_images.splice(idx, 1);
            } else {
                this.form.selected_images.push(url);
            }
        },
        removeSelectedImage(index) {
            this.form.selected_images.splice(index, 1);
        },
        setFieldValue(selectors, value) {
            if (value === undefined || value === null) return;
            for (const sel of selectors) {
                const el = document.querySelector(sel);
                if (el) {
                    el.value = value;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    const cmWrapper = el.parentElement ? el.parentElement.querySelector('.CodeMirror') : null;
                    if (cmWrapper && cmWrapper.CodeMirror) {
                        cmWrapper.CodeMirror.setValue(value);
                    }
                    break;
                }
            }
        },
        async generate() {
            if (!this.form.topic.trim()) {
                this.errorMsg = 'Por favor escribe el título o tema principal antes de generar.';
                return;
            }
            this.loading = true;
            this.errorMsg = '';

            try {
                const res = await fetch('<?php echo e(route('admin.ai.generate')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const json = await res.json();

                if (!res.ok || json.status === 'error') {
                    this.errorMsg = json.message || 'Ocurrió un error al generar el contenido.';
                    return;
                }

                const d = json.data;

                if (d.title) this.setFieldValue(['[name="title"]', '#title'], d.title);
                if (d.excerpt) this.setFieldValue(['[name="excerpt"]', '#excerpt'], d.excerpt);
                if (d.content) {
                    this.setFieldValue(['[name="content"]', '#content', '#code-editor'], d.content);
                    if (window.editor && typeof window.editor.setValue === 'function') {
                        window.editor.setValue(d.content);
                    }
                    // Notificar al Constructor Modular para que separe automáticamente los bloques generados
                    window.dispatchEvent(new CustomEvent('ai-full-page-generated', { detail: { html: d.content } }));
                }
                /*
                if (d.content) {
                    this.setFieldValue(['[name="content"]', '#content', '#code-editor'], d.content);
                    if (window.editor && typeof window.editor.setValue === 'function') {
                        window.editor.setValue(d.content);
                    }
                }
                    */

                if (d.slug) this.setFieldValue(['[name="slug"]', '#slug'], d.slug);
                if (d.meta_title) this.setFieldValue(['[name="meta_title"]', '#meta_title'], d.meta_title);
                if (d.meta_description) this.setFieldValue(['[name="meta_description"]', '#meta_description'], d.meta_description);
                if (d.meta_keywords) this.setFieldValue(['[name="meta_keywords"]', '#meta_keywords'], d.meta_keywords);

                const coverUrl = d.image_url || (this.form.selected_images.length > 0 ? this.form.selected_images[0] : null);
                if (coverUrl) {
                    this.setFieldValue([
                        '[name="featured_image"]',
                        '#featured_image',
                        '[name="og_image_path"]',
                        '#og_image_path'
                    ], coverUrl);
                }

                this.open = false;
            } catch (e) {
                this.errorMsg = 'Error de conexión con el servidor.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
<?php endif; ?>
<?php endif; ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/admin/partials/ai-generator-modal.blade.php ENDPATH**/ ?>