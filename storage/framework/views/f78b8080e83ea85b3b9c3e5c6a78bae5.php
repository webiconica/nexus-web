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
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Administrador de Páginas Dinámicas')); ?>

            </h2>
            <a href="<?php echo e(route('admin.pages.create')); ?>" class="bg-brandDark hover:bg-brandLight text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-md">
                + Crear Nueva Página
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <?php if(session('status')): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-xl text-sm font-medium">
                    <?php echo e(session('status') == 'pagina-creada' ? '¡Página creada con éxito!' : (session('status') == 'pagina-actualizada' ? '¡Cambios guardados correctamente!' : 'Página eliminada del sistema.')); ?>

                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100">
                <div class="p-8 text-gray-900">
                    <?php if($pages->isEmpty()): ?>
                        <div class="text-center py-12 text-gray-500">
                            <p class="text-lg">No has creado ninguna página dinámica todavía.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                        <th class="p-4 font-semibold rounded-tl-2xl">Título</th>
                                        <th class="p-4 font-semibold">Ruta / URL (Slug)</th>
                                        <th class="p-4 font-semibold text-right rounded-tr-2xl">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-gray-100">
                                    <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="hover:bg-gray-50/50 transition">
                                            <td class="p-4 font-bold text-gray-800"><?php echo e($p->title); ?></td>
                                            <td class="p-4 text-brandLight font-mono">
                                                <a href="<?php echo e(url('/' . $p->slug)); ?>" target="_blank" class="hover:underline">
                                                    /<?php echo e($p->slug); ?> <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                                                </a>
                                            </td>
                                            <td class="p-4 text-right flex justify-end gap-3">
                                                <a href="<?php echo e(route('admin.pages.edit', $p)); ?>" class="text-brandDark hover:text-brandLight font-bold text-xs uppercase tracking-wide transition">
                                                    Editar
                                                </a>
                                                <form action="<?php echo e(route('admin.pages.destroy', $p)); ?>" method="POST" onsubmit="return confirm('¿Eliminar esta página de forma permanente?');" class="inline-block">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-wide transition">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4"><?php echo e($pages->links()); ?></div>
                    <?php endif; ?>
                </div>
            </div>
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
<?php endif; ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/pages/index.blade.php ENDPATH**/ ?>