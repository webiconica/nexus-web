@php
    $aiEnabled = (\App\Models\Setting::where('key', 'ai_enabled')->value('value') ?? '0') === '1';
    $aiModels = \App\Services\GeminiService::availableModels();
    $defaultModel = \App\Models\Setting::where('key', 'ai_default_model')->value('value') ?? 'gemini-2.5-flash';
@endphp

@if(!$aiEnabled)
    <!-- =========================================================================
         MODO CLÁSICO (CUANDO LA IA ESTÁ APAGADA EN CONFIGURACIÓN)
         ========================================================================= -->
    <div x-data="codeEditor()" @format-code-page.window="format()" class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <textarea x-ref="textarea" name="content" id="content" class="hidden"></textarea>
        <script type="application/json" x-ref="initialData">
            {!! json_encode($initialContent ?? '') !!}
        </script>
    </div>
@else
    <!-- =========================================================================
         MODO MODULAR CON IA + MÓDULOS GENÉRICOS + MÓDULO ABIERTO + NOTAS EXTRA
         ========================================================================= -->
    <div x-data="modularPageBuilder('{{ $defaultModel }}')"
         @ai-full-page-generated.window="loadFullHtmlFromEvent($event.detail.html)"
         @format-code-page.window="formatRawEditor()"
         class="space-y-4">

        <script type="application/json" x-ref="initialPageContent">
            {!! json_encode($initialContent ?? '') !!}
        </script>

        <!-- BARRA SUPERIOR DE CONTROL DEL EDITOR -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900 text-white p-4 rounded-2xl shadow-md">
            <div class="flex items-center gap-2 bg-slate-800 p-1 rounded-xl">
                <button type="button"
                        @click="switchMode('modular')"
                        :class="viewMode === 'modular' ? 'bg-indigo-600 text-white shadow' : 'text-gray-300 hover:text-white'"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-cubes"></i> Constructor por Módulos
                    <span class="px-1.5 py-0.5 text-[10px] rounded-full bg-black/30" x-text="modules.length"></span>
                </button>
                <button type="button"
                        @click="switchMode('raw')"
                        :class="viewMode === 'raw' ? 'bg-indigo-600 text-white shadow' : 'text-gray-300 hover:text-white'"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-code"></i> Código HTML Completo
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openFreeModuleModal()"
                        class="bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-indigo-500/40 text-xs font-bold py-2.5 px-3.5 rounded-xl transition flex items-center gap-1.5">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> + Módulo Libre (Explicación Abierta)
                </button>
                <button type="button" @click="openAddModal(null)"
                        class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-xs font-black py-2.5 px-4 rounded-xl shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> + Agregar Módulo Guiado
                </button>
            </div>
        </div>

        <!-- =====================================================================
             VISTA 1: LISTA DE MÓDULOS DE LA PÁGINA
             ===================================================================== -->
        <div x-show="viewMode === 'modular'" class="space-y-3">

            <!-- Estado vacío -->
            <template x-if="modules.length === 0">
                <div class="border-2 border-dashed border-indigo-200 bg-indigo-50/40 rounded-3xl p-10 text-center space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto text-2xl shadow-sm">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="max-w-md mx-auto">
                        <h4 class="text-base font-black text-gray-900">Tu página aún no tiene módulos</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Agrega un bloque guiado (Hero, Tarjetas Genéricas, Precios, Informativo) o usa el <strong>Módulo Libre</strong> explicándole a la IA lo que quieres.
                        </p>
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 pt-2">
                        <button type="button" @click="openAddModal(null)"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                            <i class="fa-solid fa-cubes"></i> Agregar Módulo Guiado
                        </button>
                        <button type="button" @click="openFreeModuleModal()"
                                class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Crear Módulo Abierto (Explicación Libre)
                        </button>
                        <button type="button" @click="addEmptyManualBlock()"
                                class="px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 text-xs font-bold rounded-xl transition flex items-center gap-2">
                            <i class="fa-solid fa-code"></i> Bloque HTML Manual
                        </button>
                    </div>
                </div>
            </template>

            <!-- Tarjetas de Módulos Existentes -->
            <template x-for="(mod, idx) in modules" :key="mod.id">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:border-indigo-300 transition overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center shadow-sm"
                                  x-text="idx + 1"></span>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="text" x-model="mod.title" @input="syncModulesToRaw()"
                                       class="text-sm font-black text-gray-900 bg-transparent border-0 border-b border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-0 p-0">
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800"
                                      x-text="getVariantLabel(mod.variant)"></span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"
                                      :class="mod.bg === 'dark' ? 'bg-slate-800 text-white' : 'bg-amber-100 text-amber-800'"
                                      x-text="mod.bg === 'dark' ? 'Fondo Oscuro' : 'Fondo Claro'"></span>
                            </div>
                        </div>

                        <!-- Acciones del bloque -->
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button type="button" @click="moveModule(idx, -1)" :disabled="idx === 0"
                                    title="Subir módulo"
                                    class="p-2 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-200 disabled:opacity-30 transition">
                                <i class="fa-solid fa-arrow-up"></i>
                            </button>
                            <button type="button" @click="moveModule(idx, 1)" :disabled="idx === modules.length - 1"
                                    title="Bajar módulo"
                                    class="p-2 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-200 disabled:opacity-30 transition">
                                <i class="fa-solid fa-arrow-down"></i>
                            </button>

                            <button type="button" @click="toggleVisualInspector(idx)"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold border transition flex items-center gap-1"
                                    :class="mod.showVisuals ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100'">
                                <i class="fa-solid fa-icons"></i> Íconos / Fotos
                            </button>

                            <button type="button" @click="mod.showRefine = !mod.showRefine"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold border transition flex items-center gap-1"
                                    :class="mod.showRefine ? 'bg-purple-600 text-white border-purple-600' : 'bg-purple-50 text-purple-800 border-purple-200 hover:bg-purple-100'">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Ajustar con IA
                            </button>

                            <button type="button" @click="mod.showCode = !mod.showCode"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold border transition flex items-center gap-1"
                                    :class="mod.showCode ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100'">
                                <i class="fa-solid fa-code"></i> HTML
                            </button>

                            <button type="button" @click="openAddModal(idx)"
                                    title="Crear otra propuesta desde cero para este bloque"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1">
                                <i class="fa-solid fa-rotate"></i> Reemplazar
                            </button>

                            <button type="button" @click="removeModule(idx)"
                                    title="Eliminar módulo"
                                    class="p-2 rounded-lg text-xs font-bold text-red-500 hover:bg-red-50 transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PANEL A: CAMBIADOR VISUAL RÁPIDO DE ÍCONOS Y FOTOS (0 TOKENS) -->
                    <div x-show="mod.showVisuals" style="display: none;" class="p-4 bg-emerald-50/60 border-b border-emerald-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h5 class="text-xs font-black uppercase text-emerald-950">
                                    <i class="fa-solid fa-bolt text-amber-500 mr-1"></i> Cambiador Instantáneo de Íconos e Imágenes (Sin gastar saldo IA)
                                </h5>
                                <p class="text-[11px] text-emerald-800">
                                    Haz clic en cualquier ícono detectado en este módulo para cambiarlo por otro ícono o reemplazarlo por una foto de tu Biblioteca.
                                </p>
                            </div>
                            <button type="button" @click="mod.showVisuals = false" class="text-xs font-bold text-gray-500 hover:text-gray-800">Cerrar</button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5">
                            <template x-for="(ic, icIdx) in extractIconsFromModule(mod.html)" :key="icIdx">
                                <div class="bg-white p-3 rounded-xl border border-emerald-200 shadow-sm flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-base shrink-0">
                                            <i :class="ic.fullClass"></i>
                                        </span>
                                        <div class="truncate">
                                            <span class="block text-[11px] font-black text-gray-800" x-text="'Ícono #' + (icIdx + 1)"></span>
                                            <span class="block text-[10px] font-mono text-gray-400 truncate" x-text="ic.shortName"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" @click="openQuickIconSwapper(idx, icIdx, ic.fullClass)"
                                                class="px-2 py-1 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white rounded-lg text-[10px] font-bold transition">
                                            Ícono
                                        </button>
                                        <button type="button" @click="openQuickIconToImageSwapper(idx, icIdx)"
                                                class="px-2 py-1 bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white rounded-lg text-[10px] font-bold transition">
                                            Foto
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- PANEL B: AJUSTAR DISEÑO ACTUAL CON IA (MANTIENE PROPUESTA) -->
                    <div x-show="mod.showRefine" style="display: none;" class="p-4 bg-purple-50/70 border-b border-purple-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h5 class="text-xs font-black uppercase text-purple-950">
                                    <i class="fa-solid fa-wand-magic-sparkles text-purple-600 mr-1"></i> Ajustar este bloque conservando su diseño actual
                                </h5>
                                <p class="text-[11px] text-purple-800">
                                    Pídele a la IA cualquier cambio puntual sobre este módulo (ej. <em>"Cambia el ícono del primer servicio por fa-dog, agrega una 5ta tarjeta o haz que los botones abran WhatsApp"</em>).
                                </p>
                            </div>
                            <button type="button" @click="openMediaPicker('refine', idx)"
                                    class="px-2.5 py-1 bg-white border border-purple-200 rounded-lg text-[11px] font-bold text-purple-700 hover:bg-purple-100">
                                <i class="fa-solid fa-images mr-1"></i> Adjuntar Fotos (<span x-text="(mod.refineImages || []).length"></span>)
                            </button>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" x-model="mod.refinePrompt"
                                   placeholder="Escribe aquí qué quieres modificar en este bloque..."
                                   class="flex-1 rounded-xl border-purple-300 text-xs py-2.5 px-3 bg-white focus:border-purple-600 focus:ring-purple-600">
                            <button type="button" @click="refineExistingModule(idx)" :disabled="mod.refining"
                                    class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-black rounded-xl shadow transition flex items-center justify-center gap-1.5 shrink-0">
                                <i class="fa-solid fa-spinner fa-spin" x-show="mod.refining"></i>
                                <i class="fa-solid fa-paper-plane" x-show="!mod.refining"></i>
                                <span x-text="mod.refining ? 'Aplicando cambio...' : 'Aplicar Ajuste'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- PANEL C: EDITOR DE CÓDIGO HTML DEL BLOQUE -->
                    <div x-show="mod.showCode" style="display: none;" class="p-4 bg-slate-950 space-y-2">
                        <div class="flex justify-between items-center text-[11px] text-slate-400">
                            <span>Código HTML exclusivo de este módulo:</span>
                            <button type="button" @click="mod.bg = (mod.bg === 'dark' ? 'light' : 'dark'); syncModulesToRaw()"
                                    class="text-indigo-400 hover:underline">
                                Cambiar etiqueta de contraste (<span x-text="mod.bg"></span>)
                            </button>
                        </div>
                        <textarea x-model="mod.html" @input="syncModulesToRaw()" rows="9"
                                  class="w-full font-mono text-xs bg-slate-900 text-emerald-400 p-3.5 rounded-xl border border-slate-800 focus:outline-none focus:border-indigo-500"></textarea>
                    </div>
                </div>
            </template>

            <!-- Barra inferior para agregar más bloques -->
            <template x-if="modules.length > 0">
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <button type="button" @click="openAddModal(null)"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> + Agregar Módulo Guiado
                    </button>
                    <button type="button" @click="openFreeModuleModal()"
                            class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> + Agregar Módulo Libre (Explicación Abierta)
                    </button>
                    <button type="button" @click="addEmptyManualBlock()"
                            class="px-4 py-2.5 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 text-xs font-bold rounded-xl transition flex items-center gap-2">
                        <i class="fa-solid fa-code"></i> + Bloque HTML Manual
                    </button>
                </div>
            </template>
        </div>

        <!-- =====================================================================
             VISTA 2: EDITOR CODEMIRROR DE CÓDIGO COMPLETO
             ===================================================================== -->
        <div x-show="viewMode === 'raw'" class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <textarea x-ref="rawTextarea" name="content" id="content" class="hidden"></textarea>
        </div>

        <!-- =====================================================================
             MODAL 1: CONSTRUCTOR DE MÓDULOS (GUIADOS + GENÉRICOS + MÓDULO ABIERTO)
             ===================================================================== -->
        <div x-show="modalOpen" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="if(!loading && !mediaPickerOpen && !iconPickerOpen) modalOpen = false"
                 class="bg-white rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl border border-gray-100 space-y-6 max-h-[92vh] overflow-y-auto">

                <div class="flex items-center justify-between border-b border-gray-100 pb-4 sticky top-0 bg-white z-10">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-cubes"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-black text-gray-900"
                                x-text="replaceIndex !== null ? 'Reemplazar Módulo #' + (replaceIndex + 1) : 'Constructor de Módulo con IA'"></h3>
                            <p class="text-xs text-gray-500">
                                Elige un módulo estructurado o usa <strong>Módulo Libre</strong> para explicarle a la IA cualquier diseño que imagines.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="modalOpen = false" :disabled="loading" class="text-gray-400 hover:text-gray-700 p-1">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- 1. SELECCIÓN DE GRUPO (8 FAMILIAS INCLUYENDO MÓDULO LIBRE) -->
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-gray-700">1. ¿Qué tipo de módulo deseas agregar?</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
                        <template x-for="grp in catalogGroups" :key="grp.key">
                            <button type="button" @click="selectGroup(grp.key)"
                                    :class="builder.group === grp.key
                                        ? (grp.key === 'custom_free' ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'bg-indigo-600 text-white border-indigo-600 shadow-md')
                                        : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-indigo-300'"
                                    class="p-3 rounded-2xl border text-center transition flex flex-col items-center justify-center gap-1.5">
                                <i class="fa-solid text-base" :class="grp.icon"></i>
                                <span class="text-[11px] font-extrabold leading-tight" x-text="grp.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 2. SELECCIÓN DE COMPOSICIÓN / VARIANTE (SI NO ES MÓDULO LIBRE) -->
                <div x-show="builder.group !== 'custom_free'" class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-800">2. Elige la Composición y Estilo Visual</label>
                        <select x-model="builder.model" class="rounded-xl border-gray-300 text-xs font-semibold py-1.5 px-3 bg-white">
                            @foreach($aiModels as $key => $info)
                                <option value="{{ $key }}">{{ $info['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template x-for="v in currentGroupVariants()" :key="v.key">
                            <label @click="builder.variant = v.key"
                                   :class="builder.variant === v.key ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-white' : 'border-gray-200 bg-white/70 hover:border-indigo-300'"
                                   class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition">
                                <input type="radio" name="module_variant" :value="v.key" x-model="builder.variant" class="mt-1 text-indigo-600">
                                <div>
                                    <span class="block text-xs font-black text-gray-900" x-text="v.title"></span>
                                    <span class="block text-[11px] text-gray-500 leading-snug mt-0.5" x-text="v.desc"></span>
                                </div>
                            </label>
                        </template>
                    </div>

                    <!-- Selector de Íconos vs Imágenes en las tarjetas -->
                    <div x-show="['services', 'corporate_info', 'pricing'].includes(builder.group)"
                         class="pt-3 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <span class="text-xs font-bold text-slate-700">¿Qué elemento visual prefieres en las tarjetas?</span>
                        <div class="inline-flex rounded-xl bg-white p-1 border border-slate-200">
                            <button type="button" @click="builder.fields.visual_style = 'icons'"
                                    :class="builder.fields.visual_style === 'icons' ? 'bg-indigo-600 text-white' : 'text-gray-600'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                <i class="fa-solid fa-icons"></i> Usar Íconos
                            </button>
                            <button type="button" @click="builder.fields.visual_style = 'images'"
                                    :class="builder.fields.visual_style === 'images' ? 'bg-indigo-600 text-white' : 'text-gray-600'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                <i class="fa-solid fa-image"></i> Usar Imágenes / Fotos
                            </button>
                            <button type="button" @click="builder.fields.visual_style = 'mixed'"
                                    :class="builder.fields.visual_style === 'mixed' ? 'bg-indigo-600 text-white' : 'text-gray-600'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                <i class="fa-solid fa-layer-group"></i> Foto + Ícono
                            </button>
                        </div>
                    </div>
                </div>

                <!-- =================================================================
                     3. CAMPOS ESPECÍFICOS SEGÚN EL GRUPO
                     ================================================================= -->

                <!-- OPCIÓN ESPECIAL: MÓDULO ABIERTO / EXPLICACIÓN LIBRE DEL USUARIO -->
                <div x-show="builder.group === 'custom_free'" class="space-y-4 bg-purple-50/60 p-5 rounded-2xl border border-purple-200">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-wider text-purple-950 flex items-center gap-1.5">
                                <i class="fa-solid fa-wand-magic-sparkles text-purple-600"></i> Módulo Abierto (Diseñado según tu explicación)
                            </h4>
                            <p class="text-[11px] text-purple-800 mt-0.5">
                                Explica con tus palabras qué quieres mostrar y cómo te gustaría que funcione. La IA lo programará respetando los colores y tipografía de tu sitio.
                            </p>
                        </div>
                        <select x-model="builder.model" class="rounded-xl border-purple-300 text-xs font-semibold py-1.5 px-3 bg-white">
                            @foreach($aiModels as $key => $info)
                                <option value="{{ $key }}">{{ $info['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-purple-950 mb-1">Título o Nombre Interno del Módulo (Opcional)</label>
                        <input type="text" x-model="builder.fields.section_title"
                               placeholder="Ej: Calculadora de cotización / Galería antes y después / Cartelera de eventos"
                               class="w-full rounded-xl border-purple-200 text-sm bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-purple-950 mb-1">
                            Describe detalladamente qué quieres en este módulo *
                        </label>
                        <textarea x-model="builder.fields.free_prompt" rows="5"
                                  placeholder="Ejemplo: Quiero una sección con título 'Nuestras Sucursales', un texto introductorio y 3 tarjetas centradas (Sucursal Norte, Sucursal Centro y Sucursal Sur) con su dirección, horario de Lunes a Sábado y un botón en cada una que diga 'Ubicación en Maps'..."
                                  class="w-full rounded-xl border-purple-300 text-sm bg-white focus:border-purple-600 focus:ring-purple-600"></textarea>
                    </div>
                </div>

                <!-- A. HERO -->
                <div x-show="builder.group === 'hero'" class="space-y-4 bg-indigo-50/40 p-5 rounded-2xl border border-indigo-100">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold uppercase text-indigo-950 mb-1.5">¿Para qué tipo de página es este Hero?</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <button type="button" @click="builder.fields.page_context = 'home'"
                                        :class="builder.fields.page_context === 'home' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200'"
                                        class="p-2.5 rounded-xl border text-xs font-bold text-left transition">
                                    🏠 Página de Inicio (Home)
                                </button>
                                <button type="button" @click="builder.fields.page_context = 'landing'"
                                        :class="builder.fields.page_context === 'landing' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200'"
                                        class="p-2.5 rounded-xl border text-xs font-bold text-left transition">
                                    🚀 Landing Page de Conversión
                                </button>
                                <button type="button" @click="builder.fields.page_context = 'interior'"
                                        :class="builder.fields.page_context === 'interior' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200'"
                                        class="p-2.5 rounded-xl border text-xs font-bold text-left transition">
                                    📄 Página Interior / Servicio
                                </button>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Título Principal (H1) *</label>
                            <input type="text" x-model="builder.fields.headline" placeholder="Ej: Estética canina profesional y libre de estrés" class="w-full rounded-xl border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Insignia Superior</label>
                            <input type="text" x-model="builder.fields.badge" placeholder="Ej: Cuidado Especializado" class="w-full rounded-xl border-gray-300 text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Subtítulo / Promesa</label>
                            <textarea x-model="builder.fields.subheadline" rows="2" class="w-full rounded-xl border-gray-300 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Texto Botón Principal</label>
                            <input type="text" x-model="builder.fields.cta_primary" placeholder="Ej: Agendar Cita por WhatsApp" class="w-full rounded-xl border-gray-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Texto Botón Secundario</label>
                            <input type="text" x-model="builder.fields.cta_secondary" placeholder="Ej: Ver Servicios" class="w-full rounded-xl border-gray-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Métricas / Respaldo</label>
                            <input type="text" x-model="builder.fields.metrics" placeholder="Ej: +1,000 mascotas felices" class="w-full rounded-xl border-gray-300 text-xs">
                        </div>
                    </div>

                    <div x-show="builder.variant === 'hero_video'" class="p-3.5 bg-white rounded-xl border border-indigo-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold uppercase text-indigo-900">
                                <i class="fa-solid fa-video mr-1"></i> URL del Video de Fondo (.mp4 / .webm)
                            </label>
                            <button type="button" @click="openMediaPicker('video')" class="text-xs font-bold text-indigo-600 hover:underline">
                                Elegir video de la Biblioteca
                            </button>
                        </div>
                        <input type="text" x-model="builder.media_video_url" placeholder="https://tudominio.com/storage/media/video-hero.mp4" class="w-full rounded-lg border-gray-300 text-xs font-mono">
                    </div>
                </div>

                <!-- B. INFORMATIVO / CORPORATIVO -->
                <div x-show="builder.group === 'corporate_info'" class="space-y-4 bg-emerald-50/40 p-5 rounded-2xl border border-emerald-100">
                    <div>
                        <label class="block text-xs font-bold uppercase text-emerald-950 mb-2">¿Qué deseas mostrar?</label>
                        <div class="flex flex-wrap gap-2.5">
                            <template x-for="pil in ['Quiénes Somos / Historia', 'Misión y Visión', 'Valores / Filosofía', 'Cifras de Autoridad']" :key="pil">
                                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 cursor-pointer">
                                    <input type="checkbox" :value="pil" x-model="builder.fields.pillars" class="rounded text-emerald-600">
                                    <span x-text="pil"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Quiénes somos / Resumen *</label>
                            <textarea x-model="builder.fields.about_text" rows="2" class="w-full rounded-xl border-gray-300 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Misión y Visión</label>
                            <textarea x-model="builder.fields.mission_vision" rows="2" class="w-full rounded-xl border-gray-300 text-xs"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Valores o Cifras</label>
                            <textarea x-model="builder.fields.stats_values" rows="2" class="w-full rounded-xl border-gray-300 text-xs"></textarea>
                        </div>
                    </div>
                </div>

                <!-- C. PRECIOS -->
                <div x-show="builder.group === 'pricing'" class="space-y-4 bg-amber-50/50 p-5 rounded-2xl border border-amber-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Título de Precios</label>
                            <input type="text" x-model="builder.fields.section_title" placeholder="Ej: Nuestros Paquetes" class="w-full rounded-xl border-gray-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Subtítulo / Nota</label>
                            <input type="text" x-model="builder.fields.section_subtitle" class="w-full rounded-xl border-gray-300 text-xs">
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase text-amber-950">Lista de Productos / Paquetes</span>
                            <button type="button" @click="addPricingItem()" class="px-3 py-1.5 bg-amber-600 text-white rounded-xl text-xs font-bold">+ Agregar Plan</button>
                        </div>
                        <template x-for="(item, idx) in builder.pricingItems" :key="idx">
                            <div class="p-3 bg-white rounded-xl border border-amber-200 space-y-2">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                    <input type="text" x-model="item.name" placeholder="Nombre del Plan *" class="sm:col-span-4 rounded-lg border-gray-300 text-xs font-bold">
                                    <input type="text" x-model="item.price" placeholder="Precio (ej. $450 MXN)" class="sm:col-span-3 rounded-lg border-gray-300 text-xs font-mono">
                                    <input type="text" x-model="item.cta" placeholder="Texto botón" class="sm:col-span-3 rounded-lg border-gray-300 text-xs">
                                    <div class="sm:col-span-2 flex items-center justify-end gap-2">
                                        <label class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800">
                                            <input type="checkbox" x-model="item.featured" class="rounded text-amber-600"> Destacado
                                        </label>
                                        <button type="button" @click="builder.pricingItems.splice(idx, 1)" class="text-red-500 p-1"><i class="fa-solid fa-trash-can text-xs"></i></button>
                                    </div>
                                </div>
                                <input type="text" x-model="item.desc" placeholder="Descripción y qué incluye separado por comas" class="w-full rounded-lg border-gray-200 text-xs">
                            </div>
                        </template>
                    </div>
                </div>

                <!-- D. TARJETAS GENÉRICAS / SERVICIOS / PROCESO / FAQ -->
                <div x-show="['services', 'steps', 'interactive_trust', 'contact'].includes(builder.group)"
                     class="space-y-4 bg-gray-50 p-5 rounded-2xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Título Principal de la Sección</label>
                            <input type="text" x-model="builder.fields.section_title" placeholder="Ej: Nuestros servicios" class="w-full rounded-xl border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Subtítulo / Introducción bajo el título</label>
                            <input type="text" x-model="builder.fields.section_subtitle" placeholder="Ej: Nos enfocamos en la estética y comodidad de tu mascota..." class="w-full rounded-xl border-gray-300 text-sm">
                        </div>
                    </div>

                    <div x-show="builder.group !== 'contact'" class="space-y-2.5 pt-2 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase text-gray-700">
                                Tarjetas / Elementos a mostrar (elige ícono o foto, título, texto y botón)
                            </span>
                            <button type="button" @click="builder.genericItems.push({ title: '', desc: '', cta: 'Ver más', icon: 'fa-solid fa-paw', image: '' })"
                                    class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">
                                + Agregar Tarjeta / Fila
                            </button>
                        </div>

                        <template x-for="(gItem, gIdx) in builder.genericItems" :key="gIdx">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center bg-white p-2.5 rounded-xl border border-gray-200">
                                <div class="sm:col-span-2 flex items-center gap-1">
                                    <button type="button" @click="openBuilderRowIconPicker(gIdx)"
                                            title="Elegir ícono para esta tarjeta"
                                            class="w-9 h-9 rounded-lg border border-gray-300 bg-gray-50 hover:bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                                        <i :class="gItem.icon || 'fa-solid fa-star'"></i>
                                    </button>
                                    <button type="button" @click="openMediaPicker('row_image', gIdx)"
                                            title="Elegir foto para esta tarjeta"
                                            class="w-9 h-9 rounded-lg border border-gray-300 bg-gray-50 hover:bg-amber-50 flex items-center justify-center text-amber-600 overflow-hidden shrink-0">
                                        <template x-if="gItem.image">
                                            <img :src="gItem.image" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!gItem.image">
                                            <i class="fa-solid fa-image text-xs"></i>
                                        </template>
                                    </button>
                                </div>

                                <input type="text" x-model="gItem.title"
                                       :placeholder="'Título #' + (gIdx + 1) + ' (ej. Perros pequeños)'"
                                       class="sm:col-span-3 rounded-lg border-gray-300 text-xs font-semibold">
                                <input type="text" x-model="gItem.desc"
                                       placeholder="Descripción (o vacío para que la IA redacte)"
                                       class="sm:col-span-4 rounded-lg border-gray-300 text-xs">
                                <input type="text" x-model="gItem.cta"
                                       placeholder="Botón (ej. Ver más)"
                                       class="sm:col-span-2 rounded-lg border-gray-300 text-xs">
                                <button type="button" @click="builder.genericItems.splice(gIdx, 1)"
                                        class="sm:col-span-1 text-red-500 hover:text-red-700 text-center">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- =================================================================
                     4. CAMPO UNIVERSAL EN TODOS LOS MÓDULOS: EXPLICACIÓN ADICIONAL
                     ================================================================= -->
                <div class="bg-amber-50/70 p-4 rounded-2xl border border-amber-200 space-y-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-amber-950 flex items-center gap-1.5">
                        <i class="fa-solid fa-comment-dots text-amber-600"></i>
                        ¿Alguna otra explicación, ajuste o detalle adicional para este módulo? (Opcional)
                    </label>
                    <textarea x-model="builder.fields.extra_instructions" rows="2"
                              placeholder="Escribe aquí cualquier detalle extra que quieras indicarle a la IA (ej. 'Que los botones lleven a WhatsApp', 'Usa un fondo verde claro menta como en mi captura', 'Agrega un aviso de garantía debajo de las tarjetas', etc.)"
                              class="w-full rounded-xl border-amber-300 text-xs bg-white focus:border-amber-600 focus:ring-amber-600"></textarea>
                </div>

                <!-- 5. SELECTOR DE IMÁGENES GENERALES PARA EL MÓDULO -->
                <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-bold uppercase text-indigo-900">Imágenes de la Biblioteca para este Módulo (Opcional)</span>
                            <span class="text-[11px] text-indigo-700">Si eliges fotos, la IA usará sus rutas reales dentro del bloque.</span>
                        </div>
                        <button type="button" @click="openMediaPicker('image')"
                                class="px-3 py-1.5 bg-white hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                            <i class="fa-solid fa-images"></i>
                            <span x-text="builder.selected_images.length > 0 ? 'Imágenes (' + builder.selected_images.length + ')' : 'Elegir Imágenes'"></span>
                        </button>
                    </div>

                    <template x-if="builder.selected_images.length > 0">
                        <div class="flex flex-wrap gap-2.5 pt-1">
                            <template x-for="(imgUrl, idx) in builder.selected_images" :key="imgUrl">
                                <div class="relative group w-16 h-16 rounded-xl overflow-hidden border-2 border-indigo-400 shadow-sm bg-white">
                                    <img :src="imgUrl" class="w-full h-full object-cover">
                                    <button type="button" @click="builder.selected_images.splice(idx, 1)"
                                            class="absolute top-0.5 right-0.5 w-5 h-5 rounded-full bg-red-600 text-white text-[10px] flex items-center justify-center">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <template x-if="errorMsg">
                    <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs font-semibold text-red-700" x-text="errorMsg"></div>
                </template>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" @click="modalOpen = false" :disabled="loading"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="button" @click="generateSingleModule()" :disabled="loading"
                            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-black rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin" x-show="loading"></i>
                        <i class="fa-solid fa-wand-magic-sparkles" x-show="!loading"></i>
                        <span x-text="loading ? 'Diseñando Módulo con Gemini...' : 'Generar e Insertar Módulo'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- =====================================================================
             MODAL 2: SELECTOR VISUAL DE ÍCONOS FONTAWESOME
             ===================================================================== -->
        <div x-show="iconPickerOpen" style="display: none;"
             class="fixed inset-0 z-[70] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="iconPickerOpen = false"
                 class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-200 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="text-sm font-black uppercase text-gray-900">Elegir Ícono FontAwesome</h4>
                        <p class="text-xs text-gray-500">Haz clic en un ícono de la galería o escribe su clase manualmente.</p>
                    </div>
                    <button type="button" @click="iconPickerOpen = false" class="text-gray-400 hover:text-gray-700">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="flex gap-2">
                    <input type="text" x-model="customIconInput" placeholder="Ej: fa-solid fa-dog"
                           class="flex-1 rounded-xl border-gray-300 text-xs font-mono">
                    <button type="button" @click="applyPickedIcon(customIconInput)"
                            class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl">
                        Usar este código
                    </button>
                </div>

                <div class="grid grid-cols-6 sm:grid-cols-8 gap-2 max-h-72 overflow-y-auto p-1">
                    <template x-for="icClass in iconCatalog" :key="icClass">
                        <button type="button" @click="applyPickedIcon(icClass)"
                                class="p-3 rounded-xl border border-gray-200 hover:border-indigo-600 hover:bg-indigo-50 flex flex-col items-center gap-1.5 transition group">
                            <i :class="icClass" class="text-lg text-gray-700 group-hover:text-indigo-600"></i>
                            <span class="text-[9px] text-gray-400 truncate w-full text-center" x-text="icClass.replace('fa-solid fa-', '').replace('fa-brands fa-', '')"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- =====================================================================
             MODAL 3: SELECTOR DE BIBLIOTECA MULTIMEDIA
             ===================================================================== -->
        <div x-show="mediaPickerOpen" style="display: none;"
             class="fixed inset-0 z-[70] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="mediaPickerOpen = false"
                 class="bg-white rounded-3xl max-w-4xl w-full h-[75vh] flex flex-col overflow-hidden shadow-2xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <h4 class="text-sm font-black uppercase tracking-wider text-gray-900">
                        <i class="fa-solid fa-photo-film text-indigo-600 mr-1.5"></i> Seleccionar de la Biblioteca
                    </h4>
                    <button type="button" @click="mediaPickerOpen = false"
                            class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl">
                        Listo
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4">
                    <template x-for="mItem in filteredMediaItems()" :key="mItem.url">
                        <div @click="pickMediaItem(mItem.url)"
                             class="cursor-pointer bg-white rounded-2xl border border-gray-200 hover:border-indigo-600 overflow-hidden shadow-sm transition relative flex flex-col justify-between">
                            <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                                <template x-if="mItem.is_image">
                                    <img :src="mItem.url" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!mItem.is_image">
                                    <i class="fa-solid fa-film text-3xl text-indigo-600"></i>
                                </template>
                            </div>
                            <div class="p-2 text-center border-t border-gray-50">
                                <p class="text-[10px] text-gray-600 truncate font-semibold" x-text="mItem.name"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    @once
    <script>
    function modularPageBuilder(defaultModel) {
        return {
            viewMode: 'modular',
            modules: [],
            cmInstance: null,
            modalOpen: false,
            loading: false,
            errorMsg: '',
            replaceIndex: null,

            mediaPickerOpen: false,
            mediaPickerType: 'image',
            mediaTargetIndex: null,
            mediaTargetSubIndex: null,
            mediaLibraryList: [],

            iconPickerOpen: false,
            customIconInput: 'fa-solid fa-dog',
            iconTargetContext: null,

            iconCatalog: [
                'fa-solid fa-dog', 'fa-solid fa-cat', 'fa-solid fa-paw', 'fa-solid fa-bone',
                'fa-solid fa-shield-dog', 'fa-solid fa-scissors', 'fa-solid fa-bath', 'fa-solid fa-shower',
                'fa-solid fa-heart', 'fa-solid fa-medal', 'fa-solid fa-stethoscope', 'fa-solid fa-kit-medical',
                'fa-solid fa-briefcase', 'fa-solid fa-chart-line', 'fa-solid fa-scale-balanced', 'fa-solid fa-building',
                'fa-solid fa-shield-halved', 'fa-solid fa-bolt', 'fa-solid fa-wand-magic-sparkles', 'fa-solid fa-rocket',
                'fa-solid fa-check-double', 'fa-solid fa-award', 'fa-solid fa-headset', 'fa-solid fa-users',
                'fa-solid fa-utensils', 'fa-solid fa-burger', 'fa-solid fa-music', 'fa-solid fa-guitar',
                'fa-solid fa-tooth', 'fa-solid fa-dumbbell', 'fa-solid fa-house-chimney', 'fa-solid fa-key',
                'fa-solid fa-truck-fast', 'fa-solid fa-clock', 'fa-solid fa-star', 'fa-solid fa-thumbs-up'
            ],

            catalogGroups: [
                { key: 'hero', label: 'Módulo Hero', icon: 'fa-panorama' },
                { key: 'services', label: 'Tarjetas / Servicios', icon: 'fa-table-cells-large' },
                { key: 'corporate_info', label: 'Informativo / Misión', icon: 'fa-building-shield' },
                { key: 'pricing', label: 'Precios y Planes', icon: 'fa-tags' },
                { key: 'steps', label: 'Proceso / Pasos', icon: 'fa-route' },
                { key: 'interactive_trust', label: 'FAQ / Testimonios', icon: 'fa-comments' },
                { key: 'contact', label: 'Contacto / CRM', icon: 'fa-paper-plane' },
                { key: 'custom_free', label: '✨ Módulo Libre', icon: 'fa-wand-magic-sparkles' },
            ],

            variantsByGroup: {
                hero: [
                    { key: 'hero_split', title: 'Hero Split (2 Columnas)', desc: 'Propuesta de valor y botones a la izquierda; composición visual a la derecha.' },
                    { key: 'hero_slider', title: 'Hero Slider / Carrusel (Alpine.js)', desc: 'Banner de ancho completo con múltiples diapositivas interactivas.' },
                    { key: 'hero_video', title: 'Hero con Video de Fondo', desc: 'Video en bucle con overlay de contraste y titular centrado.' },
                    { key: 'hero_editorial', title: 'Hero Tipográfico / Editorial', desc: 'Diseño centrado minimalista con badge superior y titular monumental.' },
                ],
                services: [
                    { key: 'cards_centered_clean', title: 'Tarjetas Centradas con Círculo y Botón (Estilo Captura)', desc: 'Título centrado arriba y tarjetas blancas con ícono/foto circular, descripción y botón individual (ej. Ver más).' },
                    { key: 'services_grid', title: 'Cuadrícula Moderna Alineada a la Izquierda', desc: 'Tarjetas de servicios o características con ícono o fotografía superior y enlace.' },
                    { key: 'split_feature_block', title: 'Bloque Genérico Dividido 50/50 (Imagen + Texto)', desc: 'Imagen grande a un lado y explicación detallada con puntos clave y botón al otro.' },
                    { key: 'services_tabs', title: 'Pestañas Interactivas por Categoría (Tabs)', desc: 'Navegación por pestañas con Alpine.js sin recargar la página.' },
                ],
                corporate_info: [
                    { key: 'info_cards_duales', title: 'Tarjetas Institucionales', desc: 'Quiénes Somos junto con tarjetas para Misión, Visión y Valores.' },
                    { key: 'info_editorial_imagen', title: 'Diseño Editorial Asimétrico', desc: 'Narrativa corporativa a un lado y bloque visual con trayectoria al otro.' },
                    { key: 'info_bento_corporativo', title: 'Bento Grid Corporativo', desc: 'Mosaico moderno combinando tarjetas claras y oscuras.' },
                ],
                pricing: [
                    { key: 'pricing_columns', title: 'Columnas de Planes / Paquetes', desc: 'Tarjetas verticales comparativas con paquete destacado.' },
                    { key: 'pricing_switch', title: 'Tarjetas con Switch Interactivo', desc: 'Alternador entre dos modalidades de contratación.' },
                    { key: 'pricing_menu_list', title: 'Catálogo / Menú de Tarifas', desc: 'Lista estructurada de servicios con su precio y detalle.' },
                ],
                steps: [
                    { key: 'steps_timeline', title: 'Ruta Paso a Paso (Timeline)', desc: 'Bloques conectados (01, 02, 03...) explicando el proceso.' },
                ],
                interactive_trust: [
                    { key: 'faq_accordion', title: 'Acordeón de Preguntas Frecuentes', desc: 'Desplegable interactivo en Alpine.js.' },
                    { key: 'social_proof', title: 'Prueba Social y Testimonios', desc: 'Tarjetas de reseñas con estrellas y cliente verificado.' },
                ],
                contact: [
                    { key: 'contact_crm', title: 'Contacto Integrado al CRM + Turnstile', desc: 'Teléfonos/redes reales a la izquierda y formulario con Turnstile a la derecha.' },
                    { key: 'cta_banner', title: 'Banner de Cierre de Conversión', desc: 'Franja final con botones directos a tu WhatsApp.' },
                ],
                custom_free: [
                    { key: 'free_custom_section', title: 'Módulo Abierto a Medida', desc: 'Construido libremente según tu explicación.' },
                ],
            },

            builder: {
                group: 'services',
                variant: 'cards_centered_clean',
                model: defaultModel,
                selected_images: [],
                media_video_url: '',
                fields: {
                    page_context: 'home',
                    visual_style: 'icons',
                    headline: '',
                    badge: '',
                    subheadline: '',
                    cta_primary: '',
                    cta_secondary: '',
                    metrics: '',
                    pillars: ['Quiénes Somos / Historia', 'Misión y Visión'],
                    about_text: '',
                    mission_vision: '',
                    stats_values: '',
                    section_title: '',
                    section_subtitle: '',
                    free_prompt: '',
                    extra_instructions: '',
                },
                pricingItems: [
                    { name: '', price: '', desc: '', cta: 'Solicitar', featured: false },
                    { name: '', price: '', desc: '', cta: 'Contratar', featured: true },
                ],
                genericItems: [
                    { title: '', desc: '', cta: 'Ver más', icon: 'fa-solid fa-dog', image: '' },
                    { title: '', desc: '', cta: 'Ver más', icon: 'fa-solid fa-paw', image: '' },
                    { title: '', desc: '', cta: 'Ver más', icon: 'fa-solid fa-bone', image: '' },
                    { title: '', desc: '', cta: 'Ver más', icon: 'fa-solid fa-shield-dog', image: '' },
                ]
            },

            init() {
                const rawInitial = JSON.parse(this.$refs.initialPageContent.textContent || '""') || '';
                this.$refs.rawTextarea.value = rawInitial;
                this.parseHtmlIntoModules(rawInitial);

                this.$nextTick(() => {
                    if (typeof CodeMirror !== 'undefined') {
                        this.cmInstance = CodeMirror.fromTextArea(this.$refs.rawTextarea, {
                            lineNumbers: true,
                            mode: 'htmlmixed',
                            theme: 'default',
                            tabSize: 4,
                            indentUnit: 4,
                            lineWrapping: true
                        });
                        window.editor = this.cmInstance;
                        this.cmInstance.on('change', () => {
                            this.$refs.rawTextarea.value = this.cmInstance.getValue();
                        });

                        const form = this.$refs.rawTextarea.form;
                        if (form) {
                            form.addEventListener('submit', () => {
                                if (this.viewMode === 'modular') {
                                    this.syncModulesToRaw();
                                } else if (this.cmInstance) {
                                    this.cmInstance.save();
                                }
                            });
                        }
                    }
                });
            },

            switchMode(mode) {
                if (this.viewMode === mode) return;
                if (mode === 'raw') {
                    this.syncModulesToRaw();
                    this.viewMode = 'raw';
                    this.$nextTick(() => { if (this.cmInstance) this.cmInstance.refresh(); });
                } else {
                    const currentRaw = this.cmInstance ? this.cmInstance.getValue() : this.$refs.rawTextarea.value;
                    this.parseHtmlIntoModules(currentRaw);
                    this.viewMode = 'modular';
                }
            },

            parseHtmlIntoModules(html) {
                const clean = (html || '').trim();
                if (!clean) {
                    this.modules = [];
                    return;
                }

                const regex = /<!--\s*\[MODULO:\s*(\{.*?\})\]\s*-->([\s\S]*?)<!--\s*\[\/MODULO\]\s*-->/g;
                const found = [];
                let match;
                while ((match = regex.exec(clean)) !== null) {
                    let meta = {};
                    try { meta = JSON.parse(match[1]); } catch (e) {}
                    found.push({
                        id: 'mod_' + Math.random().toString(36).substr(2, 9),
                        group: meta.group || 'custom',
                        variant: meta.variant || 'custom',
                        title: meta.title || 'Sección',
                        bg: meta.bg || 'light',
                        html: match[2].trim(),
                        showCode: false,
                        showRefine: false,
                        showVisuals: false,
                        refinePrompt: '',
                        refineImages: [],
                        refining: false,
                    });
                }

                if (found.length > 0) {
                    this.modules = found;
                    return;
                }

                const sectionRegex = /<section[\s\S]*?<\/section>/gi;
                const sections = clean.match(sectionRegex);
                if (sections && sections.length > 0) {
                    this.modules = sections.map((secHtml, i) => {
                        const isDark = /bg-(slate-9|gray-9|zinc-9|brandDark|black)/.test(secHtml);
                        const hMatch = secHtml.match(/<h[1-3][^>]*>([\s\S]*?)<\/h[1-3]>/i);
                        const cleanTitle = hMatch ? hMatch[1].replace(/<[^>]+>/g, '').trim().slice(0, 42) : `Sección #${i + 1}`;
                        return {
                            id: 'mod_' + Math.random().toString(36).substr(2, 9),
                            group: i === 0 ? 'hero' : 'services',
                            variant: i === 0 ? 'hero_split' : 'cards_centered_clean',
                            title: cleanTitle || `Sección #${i + 1}`,
                            bg: isDark ? 'dark' : 'light',
                            html: secHtml.trim(),
                            showCode: false,
                            showRefine: false,
                            showVisuals: false,
                            refinePrompt: '',
                            refineImages: [],
                            refining: false,
                        };
                    });
                } else {
                    this.modules = [{
                        id: 'mod_' + Math.random().toString(36).substr(2, 9),
                        group: 'custom',
                        variant: 'custom',
                        title: 'Contenido Principal',
                        bg: 'light',
                        html: clean,
                        showCode: false,
                        showRefine: false,
                        showVisuals: false,
                        refinePrompt: '',
                        refineImages: [],
                        refining: false,
                    }];
                }
            },

            syncModulesToRaw() {
                const assembled = this.modules.map(m => {
                    const meta = JSON.stringify({
                        group: m.group,
                        variant: m.variant,
                        title: m.title,
                        bg: m.bg
                    });
                    return `<!-- [MODULO: ${meta}] -->\n${m.html.trim()}\n<!-- [/MODULO] -->`;
                }).join("\n\n");

                this.$refs.rawTextarea.value = assembled;
                if (this.cmInstance) {
                    this.cmInstance.setValue(assembled);
                }
            },

            toggleVisualInspector(idx) {
                this.modules[idx].showVisuals = !this.modules[idx].showVisuals;
            },

            extractIconsFromModule(html) {
                const results = [];
                const regex = /<i\s+[^>]*class=["']([^"']*fa-[^"']*)["'][^>]*><\/i>/gi;
                let m;
                while ((m = regex.exec(html)) !== null) {
                    const fullClass = m[1].trim();
                    const shortMatch = fullClass.match(/fa-([a-z0-9-]+)(?!.*fa-)/i);
                    results.push({
                        fullTag: m[0],
                        fullClass: fullClass,
                        shortName: shortMatch ? shortMatch[0] : fullClass,
                    });
                }
                return results;
            },

            openQuickIconSwapper(modIdx, iconIdx, currentClass) {
                this.iconTargetContext = { mode: 'module_swap', modIdx, iconIdx };
                this.customIconInput = currentClass;
                this.iconPickerOpen = true;
            },

            openBuilderRowIconPicker(rowIdx) {
                this.iconTargetContext = { mode: 'builder_row', rowIdx };
                this.customIconInput = this.builder.genericItems[rowIdx].icon || 'fa-solid fa-star';
                this.iconPickerOpen = true;
            },

            applyPickedIcon(newIconClass) {
                if (!this.iconTargetContext) return;

                if (this.iconTargetContext.mode === 'builder_row') {
                    this.builder.genericItems[this.iconTargetContext.rowIdx].icon = newIconClass;
                } else if (this.iconTargetContext.mode === 'module_swap') {
                    const { modIdx, iconIdx } = this.iconTargetContext;
                    let count = 0;
                    this.modules[modIdx].html = this.modules[modIdx].html.replace(
                        /<i\s+([^>]*?)class=["']([^"']*fa-[^"']*)["']([^>]*?)><\/i>/gi,
                        (fullMatch, before, oldClasses, after) => {
                            if (count === iconIdx) {
                                count++;
                                const nonFaClasses = oldClasses.split(/\s+/).filter(c => !c.startsWith('fa')).join(' ');
                                const merged = (newIconClass + ' ' + nonFaClasses).trim();
                                return `<i ${before}class="${merged}"${after}></i>`;
                            }
                            count++;
                            return fullMatch;
                        }
                    );
                    this.syncModulesToRaw();
                }

                this.iconPickerOpen = false;
            },

            openQuickIconToImageSwapper(modIdx, iconIdx) {
                this.openMediaPicker('swap_icon_to_img', modIdx, iconIdx);
            },

            async refineExistingModule(idx) {
                const mod = this.modules[idx];
                if (!mod.refinePrompt || !mod.refinePrompt.trim()) {
                    alert('Escribe qué cambio deseas aplicar en este módulo.');
                    return;
                }

                mod.refining = true;
                try {
                    const res = await fetch("{{ route('admin.ai.refine-module') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            current_html: mod.html,
                            instruction: mod.refinePrompt,
                            model: this.builder.model,
                            selected_images: mod.refineImages || [],
                        })
                    });

                    const json = await res.json();
                    if (!res.ok || json.status === 'error') {
                        alert(json.message || 'No se pudo ajustar el módulo.');
                        return;
                    }

                    mod.html = json.data.html;
                    mod.refinePrompt = '';
                    mod.refineImages = [];
                    mod.showRefine = false;
                    this.syncModulesToRaw();
                } catch (e) {
                    alert('Error de conexión al ajustar el módulo.');
                } finally {
                    mod.refining = false;
                }
            },

            loadFullHtmlFromEvent(html) {
                this.parseHtmlIntoModules(html);
                this.syncModulesToRaw();
            },

            moveModule(index, delta) {
                const newIndex = index + delta;
                if (newIndex < 0 || newIndex >= this.modules.length) return;
                const temp = this.modules[index];
                this.modules.splice(index, 1);
                this.modules.splice(newIndex, 0, temp);
                this.syncModulesToRaw();
            },

            removeModule(index) {
                if (!confirm('¿Eliminar este módulo de la página?')) return;
                this.modules.splice(index, 1);
                this.syncModulesToRaw();
            },

            addEmptyManualBlock() {
                this.modules.push({
                    id: 'mod_' + Math.random().toString(36).substr(2, 9),
                    group: 'custom',
                    variant: 'custom',
                    title: 'Bloque HTML Personalizado',
                    bg: 'light',
                    html: '<section class="py-16 bg-white">\n    <div class="max-w-7xl mx-auto px-4">\n        <!-- Tu código aquí -->\n    </div>\n</section>',
                    showCode: true,
                    showRefine: false,
                    showVisuals: false,
                    refinePrompt: '',
                    refineImages: [],
                    refining: false,
                });
                this.syncModulesToRaw();
            },

            selectGroup(groupKey) {
                this.builder.group = groupKey;
                const variants = this.variantsByGroup[groupKey] || [];
                if (variants.length > 0) {
                    this.builder.variant = variants[0].key;
                }
            },

            currentGroupVariants() {
                return this.variantsByGroup[this.builder.group] || [];
            },

            getVariantLabel(variantKey) {
                for (const grp in this.variantsByGroup) {
                    const found = this.variantsByGroup[grp].find(v => v.key === variantKey);
                    if (found) return found.title;
                }
                return 'HTML / Personalizado';
            },

            addPricingItem() {
                this.builder.pricingItems.push({ name: '', price: '', desc: '', cta: 'Contratar', featured: false });
            },

            openAddModal(replaceIdx = null) {
                this.replaceIndex = replaceIdx;
                this.errorMsg = '';
                if (this.builder.group === 'custom_free') {
                    this.selectGroup('services');
                }
                this.modalOpen = true;
            },

            openFreeModuleModal() {
                this.replaceIndex = null;
                this.errorMsg = '';
                this.selectGroup('custom_free');
                this.modalOpen = true;
            },

            async openMediaPicker(type, targetIdx = null, subIdx = null) {
                this.mediaPickerType = type;
                this.mediaTargetIndex = targetIdx;
                this.mediaTargetSubIndex = subIdx;
                this.mediaPickerOpen = true;

                if (this.mediaLibraryList.length === 0) {
                    try {
                        const res = await fetch("{{ route('admin.media.api') }}");
                        this.mediaLibraryList = await res.json();
                    } catch (e) {
                        console.error('Error cargando multimedia:', e);
                    }
                }
            },

            filteredMediaItems() {
                if (this.mediaPickerType === 'video') {
                    return this.mediaLibraryList.filter(i => !i.is_image || /\.(mp4|webm|mov)$/i.test(i.url));
                }
                return this.mediaLibraryList.filter(i => i.is_image);
            },

            pickMediaItem(url) {
                if (this.mediaPickerType === 'video') {
                    this.builder.media_video_url = url;
                    this.mediaPickerOpen = false;
                } else if (this.mediaPickerType === 'row_image' && this.mediaTargetIndex !== null) {
                    this.builder.genericItems[this.mediaTargetIndex].image = url;
                    this.builder.fields.visual_style = 'images';
                    this.mediaPickerOpen = false;
                } else if (this.mediaPickerType === 'refine' && this.mediaTargetIndex !== null) {
                    const mod = this.modules[this.mediaTargetIndex];
                    if (!mod.refineImages) mod.refineImages = [];
                    mod.refineImages.push(url);
                    this.mediaPickerOpen = false;
                } else if (this.mediaPickerType === 'swap_icon_to_img' && this.mediaTargetIndex !== null) {
                    const modIdx = this.mediaTargetIndex;
                    const iconIdx = this.mediaTargetSubIndex;
                    let count = 0;
                    this.modules[modIdx].html = this.modules[modIdx].html.replace(
                        /<i\s+[^>]*class=["'][^"']*fa-[^"']*["'][^>]*><\/i>/gi,
                        (fullMatch) => {
                            if (count === iconIdx) {
                                count++;
                                return `<img src="${url}" alt="Imagen de tarjeta" class="w-16 h-16 rounded-full object-cover shadow-sm mx-auto">`;
                            }
                            count++;
                            return fullMatch;
                        }
                    );
                    this.syncModulesToRaw();
                    this.mediaPickerOpen = false;
                } else {
                    const idx = this.builder.selected_images.indexOf(url);
                    if (idx > -1) {
                        this.builder.selected_images.splice(idx, 1);
                    } else {
                        this.builder.selected_images.push(url);
                    }
                }
            },

            formatRawEditor() {
                if (this.cmInstance && typeof html_beautify === 'function') {
                    const formatted = html_beautify(this.cmInstance.getValue(), { indent_size: 4 });
                    this.cmInstance.setValue(formatted);
                }
            },

            async generateSingleModule() {
                if (this.builder.group === 'custom_free' && !this.builder.fields.free_prompt.trim()) {
                    this.errorMsg = 'Por favor describe qué quieres que la IA diseñe en el Módulo Abierto.';
                    return;
                }

                this.loading = true;
                this.errorMsg = '';

                const pageTitleEl = document.querySelector('[name="title"]');
                const pageTitle = pageTitleEl && pageTitleEl.value ? pageTitleEl.value : 'Página Web';

                let prevBg = null;
                if (this.replaceIndex !== null && this.replaceIndex > 0) {
                    prevBg = this.modules[this.replaceIndex - 1].bg;
                } else if (this.replaceIndex === null && this.modules.length > 0) {
                    prevBg = this.modules[this.modules.length - 1].bg;
                }

                let itemsPayload = [];
                if (this.builder.group === 'pricing') {
                    itemsPayload = this.builder.pricingItems.filter(i => i.name.trim() !== '');
                } else if (['services', 'steps', 'interactive_trust'].includes(this.builder.group)) {
                    itemsPayload = this.builder.genericItems.filter(i => i.title.trim() !== '');
                }

                try {
                    const res = await fetch("{{ route('admin.ai.generate-module') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            group: this.builder.group,
                            variant: this.builder.variant,
                            model: this.builder.model,
                            page_title: pageTitle,
                            previous_bg: prevBg,
                            selected_images: this.builder.selected_images,
                            media_video_url: this.builder.media_video_url,
                            fields: this.builder.fields,
                            items: itemsPayload,
                        })
                    });

                    const json = await res.json();
                    if (!res.ok || json.status === 'error') {
                        this.errorMsg = json.message || 'No se pudo generar el módulo.';
                        return;
                    }

                    const d = json.data;
                    const newMod = {
                        id: 'mod_' + Math.random().toString(36).substr(2, 9),
                        group: d.group,
                        variant: d.variant,
                        title: d.module_title || this.getVariantLabel(d.variant),
                        bg: d.bg_tone === 'dark' ? 'dark' : 'light',
                        html: d.html,
                        showCode: false,
                        showRefine: false,
                        showVisuals: false,
                        refinePrompt: '',
                        refineImages: [],
                        refining: false,
                    };

                    if (this.replaceIndex !== null) {
                        this.modules.splice(this.replaceIndex, 1, newMod);
                    } else {
                        this.modules.push(newMod);
                    }

                    this.syncModulesToRaw();
                    this.modalOpen = false;
                } catch (e) {
                    this.errorMsg = 'Error de conexión con el servidor al generar el módulo.';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
    </script>
    @endonce
@endif