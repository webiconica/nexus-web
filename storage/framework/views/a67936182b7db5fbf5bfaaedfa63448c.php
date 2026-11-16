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
                <?php echo e(__('Administrar Blog')); ?>

            </h2>
            <a href="<?php echo e(route('admin.posts.create')); ?>" class="bg-brandDark hover:bg-brandLight text-white font-bold py-2 px-4 rounded-xl transition shadow-md">
                + Nuevo Artículo
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <?php if(session('success')): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-100 p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 tracking-wider">
                            <th class="py-4 px-6 font-bold">Título</th>
                            <th class="py-4 px-6 font-bold">Estado</th>
                            <th class="py-4 px-6 font-bold">Fecha</th>
                            <th class="py-4 px-6 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-4 px-6 font-medium text-gray-900"><?php echo e($post->title); ?></td>
                                <td class="py-4 px-6">
                                    <?php if($post->is_published): ?>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Publicado</span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Borrador</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-500">
                                    <?php echo e($post->published_at ? $post->published_at->format('d M Y') : '-'); ?>

                                </td>
                                <td class="py-4 px-6 text-right space-x-3">
                                    
                                    <?php if($post->is_published): ?>
                                        <a href="<?php echo e(route('blog.show', $post->slug)); ?>" target="_blank" class="text-blue-500 hover:text-blue-700 font-semibold text-sm" title="Ver artículo público">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?php echo e(route('admin.posts.edit', $post)); ?>" class="text-brandLight hover:text-brandDark font-semibold text-sm">Editar</a>
                                    
                                    <form action="<?php echo e(route('admin.posts.destroy', $post)); ?>" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este artículo?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">Aún no hay artículos creados. ¡Anímate a escribir el primero!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="mt-4">
                    <?php echo e($posts->links()); ?>

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
<?php endif; ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/admin/posts/index.blade.php ENDPATH**/ ?>