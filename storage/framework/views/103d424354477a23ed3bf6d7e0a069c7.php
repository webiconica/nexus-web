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
        <h2 class="font-bold text-2xl text-brandDark uppercase tracking-widest leading-tight">
            <?php echo e(__('Constructor de Menú Principal')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <!-- Cargamos SortableJS vía CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <div class="py-12" x-data="menuManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <?php if(session('success')): ?>
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            <!-- Notificación flotante de reordenado -->
            <div x-show="toast.show" x-transition 
                 class="fixed bottom-6 right-6 z-50 bg-brandDark text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-brandLight/20 text-sm font-semibold" 
                 style="display: none;">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span x-text="toast.message"></span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- COLUMNA IZQUIERDA: Formulario para añadir elementos -->
                <div class="lg:col-span-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="font-bold text-lg text-brandDark border-b pb-3">Añadir al Menú</h3>

                    <!-- Selector rápido de Páginas o Blog -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Vincular Página Existente</label>
                            <select @change="setFromSelect($event, '/')" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none">
                                <option value="">-- Seleccionar Página --</option>
                                <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($page->slug === 'inicio' ? '/' : '/' . $page->slug); ?>" data-title="<?php echo e($page->title); ?>">
                                        <?php echo e($page->title); ?> (<?php echo e($page->slug); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <?php if(isset($posts) && $posts->count() > 0): ?>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Vincular Artículo de Blog</label>
                                <select @change="setFromSelect($event, '/blog/')" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none">
                                    <option value="">-- Seleccionar Artículo --</option>
                                    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="/blog/<?php echo e($post->slug); ?>" data-title="<?php echo e($post->title); ?>">
                                            <?php echo e(Str::limit($post->title, 35)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Formulario Manual / Personalizado -->
                    <form action="<?php echo e(route('admin.menu.store')); ?>" method="POST" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Texto del Enlace *</label>
                            <input type="text" name="title" x-model="formTitle" required placeholder="Ej. Nosotros o Servicios" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">URL / Ruta *</label>
                            <input type="text" name="url" x-model="formUrl" required placeholder="Ej. /quienes-somos o /#servicios" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none font-mono text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Elemento Padre (Submenú de)</label>
                            <select name="parent_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none">
                                <option value="">-- Ninguno (Botón Principal) --</option>
                                <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($parent->id); ?>"><?php echo e($parent->title); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Destino</label>
                                <select name="target" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none">
                                    <option value="_self">Misma pestaña (_self)</option>
                                    <option value="_blank">Nueva pestaña (_blank)</option>
                                </select>
                            </div>
                            <div class="flex items-center pt-5">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_button" value="1" class="w-4 h-4 rounded text-brandDark border-gray-300">
                                    <span class="text-xs font-bold text-gray-700 uppercase">Es Botón CTA</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-brandDark hover:bg-brandLight text-white font-bold rounded-xl transition shadow-md text-sm">
                            + Agregar al Menú
                        </button>
                    </form>
                </div>

                <!-- COLUMNA DERECHA: Estructura del Árbol con Drag & Drop -->
                <div class="lg:col-span-8 bg-white p-6 rounded-3xl shadow-sm border border-gray-100 space-y-6">
                    <div class="flex justify-between items-center border-b pb-3">
                        <div>
                            <h3 class="font-bold text-lg text-brandDark">Estructura del Menú</h3>
                            <p class="text-xs text-gray-400">Arrastra desde el icono de las barras para reordenar las posiciones.</p>
                        </div>
                        <span class="text-xs text-gray-400 font-semibold">Total: <?php echo e($menuItems->count()); ?> botones</span>
                    </div>

                    <?php if($menuItems->isEmpty()): ?>
                        <div class="p-8 text-center text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            No hay enlaces configurados en la base de datos.
                        </div>
                    <?php else: ?>
                        <!-- Contenedor principal Sortable -->
                        <div id="menu-items-list" class="space-y-3" x-init="initSortable()">
                            <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="menu-item-row bg-gray-50 border border-gray-200 rounded-2xl p-4 transition-all hover:border-gray-300" data-id="<?php echo e($item->id); ?>">
                                    <!-- Elemento Principal -->
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <i class="fa-solid fa-bars drag-handle text-gray-400 hover:text-brandDark cursor-grab active:cursor-grabbing text-base p-1"></i>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-brandDark text-sm"><?php echo e($item->title); ?></span>
                                                    <?php if($item->is_button): ?>
                                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold uppercase">CTA</span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-xs font-mono text-gray-500"><?php echo e($item->url); ?></span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <form action="<?php echo e(route('admin.menu.destroy', $item)); ?>" method="POST" onsubmit="return confirm('¿Eliminar este elemento y todos sus submenús?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-red-400 hover:text-red-600 text-sm p-1.5 rounded-lg transition" title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Sub-elementos (Nivel 2) -->
                                    <?php if($item->children->count() > 0): ?>
                                        <div class="sub-items-list mt-3 pl-6 border-l-2 border-brandLight/40 space-y-2" data-parent-id="<?php echo e($item->id); ?>">
                                            <?php $__currentLoopData = $item->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="menu-subitem-row flex items-center justify-between bg-white border border-gray-200 rounded-xl p-3 hover:border-gray-300 transition-all" data-id="<?php echo e($child->id); ?>">
                                                    <div class="flex items-center gap-3">
                                                        <i class="fa-solid fa-bars drag-handle-sub text-gray-300 hover:text-brandDark cursor-grab active:cursor-grabbing text-xs p-1"></i>
                                                        <div>
                                                            <span class="font-semibold text-gray-800 text-xs"><?php echo e($child->title); ?></span>
                                                            <span class="block text-[10px] font-mono text-gray-400"><?php echo e($child->url); ?></span>
                                                        </div>
                                                    </div>
                                                    <form action="<?php echo e(route('admin.menu.destroy', $child)); ?>" method="POST" onsubmit="return confirm('¿Eliminar este submenú?')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="text-red-400 hover:text-red-600 text-xs p-1 rounded-lg transition" title="Eliminar">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <script>
    function menuManager() {
        return {
            formTitle: '',
            formUrl: '',
            toast: { show: false, message: '' },

            showToast(msg) {
                this.toast.message = msg;
                this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 3000);
            },

            setFromSelect(event, prefix) {
                const select = event.target;
                if (!select.value) return;
                const opt = select.options[select.selectedIndex];
                this.formUrl = select.value;
                this.formTitle = opt.getAttribute('data-title') || '';
            },

            initSortable() {
                const list = document.getElementById('menu-items-list');
                if (!list) return;

                // Sortable para elementos principales
                Sortable.create(list, {
                    handle: '.drag-handle',
                    animation: 200,
                    ghostClass: 'opacity-40',
                    chosenClass: 'bg-indigo-50/50',
                    onEnd: () => this.saveOrder()
                });

                // Sortable para submenús
                document.querySelectorAll('.sub-items-list').forEach(subList => {
                    Sortable.create(subList, {
                        handle: '.drag-handle-sub',
                        animation: 200,
                        ghostClass: 'opacity-40',
                        chosenClass: 'bg-indigo-50/50',
                        onEnd: () => this.saveOrder()
                    });
                });
            },

            async saveOrder() {
                const items = [];
                const parentRows = document.querySelectorAll('#menu-items-list > .menu-item-row');

                parentRows.forEach((row, index) => {
                    const parentId = row.dataset.id;
                    items.push({ id: parentId, parent_id: null, sort_order: index });

                    const subRows = row.querySelectorAll('.sub-items-list > .menu-subitem-row');
                    subRows.forEach((subRow, subIndex) => {
                        items.push({ id: subRow.dataset.id, parent_id: parentId, sort_order: subIndex });
                    });
                });

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const res = await fetch("<?php echo e(route('admin.menu.reorder')); ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ items })
                    });

                    if (res.ok) {
                        this.showToast('Orden guardado correctamente');
                    }
                } catch (e) {
                    console.error('Error guardando el orden:', e);
                }
            }
        };
    }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/admin/menu/index.blade.php ENDPATH**/ ?>