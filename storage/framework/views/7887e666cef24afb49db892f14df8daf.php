<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Configuración Global del Sitio')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ activeTab: 'generales' }">
            
            <?php if(session('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl flex items-center shadow-sm">
                <i class="fa-solid fa-circle-check mr-3 text-xl"></i>
                <span class="font-semibold"><?php echo e(session('success')); ?></span>
            </div>
            <?php endif; ?>

            <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="border-b border-gray-200 mb-8">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto">
                        <button @click.prevent="activeTab = 'generales'" :class="activeTab === 'generales' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                             <i class="fa-solid fa-building mr-1"></i> Identidad
                        </button>
                        <button @click.prevent="activeTab = 'branding'" :class="activeTab === 'branding' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                             <i class="fa-solid fa-image mr-1"></i> Logotipos
                        </button>
                        <button @click.prevent="activeTab = 'scripts'" :class="activeTab === 'scripts' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                             <i class="fa-solid fa-code mr-1"></i> Scripts
                        </button>
                        <button @click.prevent="activeTab = 'ia'" :class="activeTab === 'ia' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                            <i class="fa-solid fa-robot mr-1"></i> Prompts IA
                        </button>
                    </nav>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">
                    
                    <div x-show="activeTab === 'generales'" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Identidad Principal</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Nombre del Sitio *</label>
                                <input type="text" name="site_name" value="<?php echo e($settings['site_name'] ?? ''); ?>" required class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Descripción principal del sitio web *</label>
                                <textarea name="site_description" rows="3" required class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition"><?php echo e($settings['site_description'] ?? ''); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                    <i class="fa-solid fa-envelope mr-1 text-gray-400"></i> Correo de Destino para Formularios
                                </label>
                                <input type="email" name="contact_email" value="<?php echo e($settings['contact_email'] ?? ''); ?>" placeholder="Ej. ventas@tuempresa.com" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition">
                                <p class="text-[11px] text-gray-400 mt-1">Todos los leads capturados se enviarán automáticamente a este correo.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                    <i class="fa-brands fa-whatsapp mr-1 text-emerald-500"></i> Número de WhatsApp (Con código de país)
                                </label>
                                <input type="text" name="whatsapp_number" value="<?php echo e($settings['whatsapp_number'] ?? ''); ?>" placeholder="Ej. 5215512345678" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition">
                                <p class="text-[11px] text-gray-400 mt-1">Solo números, sin espacios ni símbolo +.</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="activeTab === 'branding'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Identidad Visual</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Logo del Header</label>
                                <?php if(isset($settings['header_logo'])): ?>
                                    <div class="mb-4 bg-gray-50 p-4 rounded-xl border border-gray-200 inline-block shadow-inner">
                                        <img src="<?php echo e(asset('storage/' . $settings['header_logo'])); ?>" class="h-10 object-contain">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="header_logo" accept="image/*" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brandDark file:text-white hover:file:bg-brandLight transition">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Favicon</label>
                                <?php if(isset($settings['favicon'])): ?>
                                    <div class="mb-4 bg-gray-50 p-4 rounded-xl border border-gray-200 inline-block shadow-inner">
                                        <img src="<?php echo e(asset('storage/' . $settings['favicon'])); ?>" class="h-8 w-8">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="favicon" accept=".ico,.png" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brandDark file:text-white hover:file:bg-brandLight transition">
                            </div>
                        </div>
                    </div>

                    <div x-show="activeTab === 'scripts'" style="display: none;" class="space-y-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Inyección de Códigos</h3>
                        
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Scripts en el &lt;HEAD&gt;</label>
                            <textarea name="head_scripts" rows="5" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-sm text-gray-700 leading-relaxed" placeholder=""><?php echo e($settings['head_scripts'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Scripts al inicio del &lt;BODY&gt;</label>
                            <textarea name="body_start_scripts" rows="5" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-sm text-gray-700 leading-relaxed" placeholder=""><?php echo e($settings['body_start_scripts'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Scripts al final del &lt;BODY&gt;</label>
                            <textarea name="body_end_scripts" rows="5" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-sm text-gray-700 leading-relaxed" placeholder=""><?php echo e($settings['body_end_scripts'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div x-show="activeTab === 'ia'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        
                        <div class="border-b pb-2">
                            <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark">Instrucciones Base para IA</h3>
                            <p class="text-xs text-gray-500 mt-1">Guarda los prompts predefinidos de tu marca para generar contenido rápidamente. Usa el botón de copiar para llevarlos a tu IA favorita.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-8">
                            
                            <div x-data="{ copyText() { navigator.clipboard.writeText($refs.promptPages.value); alert('¡Prompt de Páginas copiado al portapapeles!'); } }">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-xs font-bold uppercase text-gray-500">Prompt para Nuevas Páginas / Secciones</label>
                                    <button type="button" @click="copyText()" class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-1.5 px-3 rounded-lg transition shadow-sm border border-gray-200">
                                        <i class="fa-regular fa-copy"></i> Copiar Prompt
                                    </button>
                                </div>
                                <textarea name="ai_prompt_pages" x-ref="promptPages" rows="12" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-xs text-gray-600 leading-relaxed"><?php echo e($settings['ai_prompt_pages'] ?? "Actúa como un desarrollador web experto y copywriter. Por favor, crea el contenido y código HTML de una nueva sección para mi sitio web.

REQUISITOS TÉCNICOS:
- Utiliza Tailwind CSS y Alpine.js.
- Genera un diseño moderno, limpio y responsive.

PALETA DE COLORES CORPORATIVA:
- Primario Oscuro: bg-[#1a0b5e] o text-[#1a0b5e]
- Primario Claro (Acentos): bg-[#9484f7] o text-[#9484f7]
- Textos secundarios: text-[#606060]
- Fondos claros: bg-[#fbfbfe]

ESTILOS ESPECIALES:
- Puedes usar la clase 'glow-effect' para aplicar nuestra sombra corporativa (box-shadow: 0 0 50px -10px rgba(148, 132, 247, 0.35);).
- Tipografía principal: Inter.

El tema específico de esta sección es: [INSERTA TU TEMA AQUÍ]"); ?></textarea>
                            </div>

                            <div x-data="{ copyText() { navigator.clipboard.writeText($refs.promptPosts.value); alert('¡Prompt de Blog copiado al portapapeles!'); } }">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-xs font-bold uppercase text-gray-500">Prompt para Entradas de Blog</label>
                                    <button type="button" @click="copyText()" class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-1.5 px-3 rounded-lg transition shadow-sm border border-gray-200">
                                        <i class="fa-regular fa-copy"></i> Copiar Prompt
                                    </button>
                                </div>
                                <textarea name="ai_prompt_posts" x-ref="promptPosts" rows="10" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-xs text-gray-600 leading-relaxed"><?php echo e($settings['ai_prompt_posts'] ?? "Actúa como un redactor SEO experto y estructurador de contenido web. Escribe una entrada de blog detallada y persuasiva.

REQUISITOS DEL FORMATO:
- Entrega el resultado directamente en código HTML.
- Utiliza etiquetas semánticas (<h2>, <h3>, <p>, <ul>, <strong>).
- Utiliza clases de Tailwind CSS para darle formato (ej. text-gray-700, mb-4, text-2xl, font-bold).
- Para palabras clave, citas o acentos visuales, utiliza nuestro color de marca: text-[#9484f7].

ESTRUCTURA SOLICITADA:
1. Un título h2 atrapante.
2. Una introducción ganadora.
3. Al menos 3 subtítulos h3 con contenido de valor.
4. Una conclusión con un Call to Action (Llamado a la acción).

El artículo debe tratar sobre: [INSERTA TU TEMA AQUÍ]"); ?></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-brandDark hover:bg-brandLight text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Guardar Configuraciones
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/admin/settings/index.blade.php ENDPATH**/ ?>