

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php if(session('success')): ?>
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
            <span><i class="fa-solid fa-circle-check mr-2"></i> <?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?>

    <!-- Cabecera y Métrica de Plan -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <i class="fa-solid fa-filter-circle-dollar text-indigo-600"></i>
                CRM Omnicanal & Embudo de Ventas
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Contactos unificados desde Formularios Web, Chatbot IA y WhatsApp · Contactos ilimitados.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Indicador de conversaciones del plan -->
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs">
                <span class="text-slate-500 block">Mensajes IA este mes (Plan <?php echo e(ucfirst($tenant->plan ?? 'negocio')); ?>)</span>
                <strong class="text-slate-900 text-sm"><?php echo e(number_format($monthlyChatUsed)); ?></strong>
                <span class="text-slate-400">/ <?php echo e(number_format($tenant->monthly_chat_limit ?? 1000)); ?></span>
            </div>

            <a href="<?php echo e(route('admin.integrations.index')); ?>"
               class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs flex items-center gap-2">
                <i class="fa-solid fa-code"></i> Código Widget & Webhook
            </a>

            <a href="<?php echo e(route('admin.crm.export', request()->query())); ?>"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-file-excel"></i> Exportar CSV / Excel
            </a>
        </div>
    </div>

    <!-- Tarjetas de Etapas del Embudo (Filtros Rápidos) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stageKey => $stageLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $active = request('stage') === $stageKey;
                $count  = $stageCounts[$stageKey] ?? 0;
            ?>
            <a href="<?php echo e(route('admin.crm.index', array_merge(request()->except('page'), ['stage' => $active ? null : $stageKey]))); ?>"
               class="p-4 rounded-2xl border transition <?php echo e($active ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:border-indigo-300'); ?>">
                <div class="text-2xl font-black"><?php echo e($count); ?></div>
                <div class="text-xs font-semibold mt-1 <?php echo e($active ? 'text-indigo-100' : 'text-slate-500'); ?>">
                    <?php echo e($stageLabel); ?>

                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <form method="GET" action="<?php echo e(route('admin.crm.index')); ?>" class="bg-white p-4 rounded-2xl border border-slate-200 mb-6 flex flex-col sm:flex-row gap-3">
        <input type="text" name="q" value="<?php echo e(request('q')); ?>"
               placeholder="Buscar por nombre, teléfono, correo o fbclid..."
               class="flex-1 rounded-xl border-slate-300 text-sm">

        <select name="stage" class="rounded-xl border-slate-300 text-sm">
            <option value="">Todas las etapas</option>
            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($k); ?>" <?php echo e(request('stage') === $k ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="origen" class="rounded-xl border-slate-300 text-sm">
            <option value="">Todos los orígenes</option>
            <?php $__currentLoopData = $origins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($orig); ?>" <?php echo e(request('origen') === $orig ? 'selected' : ''); ?>><?php echo e($orig); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800">
            Filtrar
        </button>

        <?php if(request()->hasAny(['q', 'stage', 'origen'])): ?>
            <a href="<?php echo e(route('admin.crm.index')); ?>" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold flex items-center justify-center">
                Limpiar
            </a>
        <?php endif; ?>
    </form>

    <!-- Tabla de Prospectos -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-bold">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Prospecto</th>
                        <th class="px-5 py-3.5 text-left">Contacto</th>
                        <th class="px-5 py-3.5 text-left">Origen & Atribución Meta</th>
                        <th class="px-5 py-3.5 text-left">Etapa (Dispara Webhook / CAPI)</th>
                        <th class="px-5 py-3.5 text-center">Historial</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <a href="<?php echo e(route('admin.crm.show', $lead)); ?>" class="font-bold text-slate-900 hover:text-indigo-600">
                                    <?php echo e($lead->name ?: ($lead->datos_formulario['nombre'] ?? 'Sin nombre')); ?>

                                </a>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    Registrado: <?php echo e($lead->created_at?->format('d/m/Y H:i')); ?>

                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <?php if($lead->phone_normalized): ?>
                                    <div class="font-mono text-xs text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                        <a href="https://wa.me/<?php echo e($lead->phone_normalized); ?>" target="_blank" class="hover:underline">
                                            +<?php echo e($lead->phone_normalized); ?>

                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if($lead->email): ?>
                                    <div class="text-xs text-slate-500 mt-0.5"><?php echo e($lead->email); ?></div>
                                <?php endif; ?>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    <?php echo e($lead->origen); ?>

                                </span>
                                <?php if($lead->fbclid || $lead->fbc || $lead->fbp): ?>
                                    <div class="mt-1 flex items-center gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"
                                              title="fbclid: <?php echo e($lead->fbclid); ?> | fbc: <?php echo e($lead->fbc); ?> | fbp: <?php echo e($lead->fbp); ?>">
                                            <i class="fa-brands fa-meta mr-1"></i> Meta Ads IDs OK
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-5 py-4">
                                <form method="POST" action="<?php echo e(route('admin.crm.update-stage', $lead)); ?>" class="inline-flex items-center gap-2">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <select name="stage" onchange="this.form.submit()"
                                            class="rounded-xl border-slate-300 text-xs font-bold py-1.5 pl-3 pr-7
                                            <?php echo e($lead->stage === 'anticipo_confirmado' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-white text-slate-700'); ?>">
                                        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sKey => $sLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sKey); ?>" <?php echo e($lead->stage === $sKey ? 'selected' : ''); ?>>
                                                <?php echo e($sLabel); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </form>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <a href="<?php echo e(route('admin.crm.show', $lead)); ?>"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold hover:bg-indigo-100">
                                    <i class="fa-regular fa-comments"></i> <?php echo e($lead->messages_count); ?>

                                </a>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="<?php echo e(route('admin.crm.show', $lead)); ?>"
                                   class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-indigo-600 transition">
                                    Abrir Ficha
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                Aún no hay prospectos registrados con estos filtros.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-slate-100">
            <?php echo e($leads->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/admin/crm/index.blade.php ENDPATH**/ ?>