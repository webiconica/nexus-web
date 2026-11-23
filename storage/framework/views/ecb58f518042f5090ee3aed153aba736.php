<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? 'Acceso'); ?> — Webiconica Flow</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <?php if(env('TURNSTILE_SITE_KEY')): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">

        <!-- COLUMNA IZQUIERDA: Branding Webiconica Flow (Visible en Desktop) -->
        <div class="hidden lg:flex lg:col-span-5 xl:col-span-6 bg-gradient-to-br from-[#120742] via-[#1A0B5E] to-[#2E1A8A] text-white p-12 flex-col justify-between relative overflow-hidden">
            <!-- Resplandores decorativos -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-violet-400/20 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Marca -->
            <div class="relative z-10 flex items-center justify-between">
                <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center backdrop-blur-sm">
                        <i class="fa-solid fa-bolt text-indigo-300 text-lg"></i>
                    </div>
                    <div>
                        <span class="font-black text-xl tracking-tight block leading-none">WEBICÓNICA</span>
                        <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-indigo-300">Flow • Chat & CRM</span>
                    </div>
                </a>
                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 border border-white/15 text-indigo-200">
                    Plataforma IA
                </span>
            </div>

            <!-- Propuesta de Valor -->
            <div class="relative z-10 max-w-lg space-y-6 my-auto py-12">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-200 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Atención 24/7 y seguimiento sin prospectos perdidos
                </div>

                <h1 class="text-3xl xl:text-4xl font-black leading-tight">
                    Convierte visitas en ventas con <span class="text-indigo-300">Chatbot IA y CRM</span> conectado a tus anuncios.
                </h1>

                <p class="text-sm text-indigo-100/80 leading-relaxed">
                    Instala el asistente en cualquier sitio web con una sola línea de código, unifica leads por WhatsApp o teléfono sin duplicados y envía conversiones reales a Meta Ads y Google Ads.
                </p>

                <div class="space-y-3 pt-2 text-xs text-indigo-100">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span><strong>Widget embebible universal:</strong> compatible con WordPress, Shopify, React y HTML.</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span><strong>CRM Omnicanal:</strong> historial unificado de chat web, formularios y WhatsApp Cloud API.</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span><strong>Atribución publicitaria real:</strong> captura automática de GCLID, FBCLID y envío de anticipos confirmados.</span>
                    </div>
                </div>
            </div>

            <!-- Pie izquierdo -->
            <div class="relative z-10 flex items-center justify-between text-xs text-indigo-200/70 border-t border-white/10 pt-6">
                <span>© <?php echo e(date('Y')); ?> Webicónica. Todos los derechos reservados.</span>
                <a href="https://webiconica.com" target="_blank" class="hover:text-white transition">webiconica.com ↗</a>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Contenedor del Formulario -->
        <div class="lg:col-span-7 xl:col-span-6 flex flex-col justify-center items-center px-4 sm:px-8 py-10">
            <!-- Header móvil -->
            <div class="lg:hidden mb-8 text-center">
                <a href="<?php echo e(url('/')); ?>" class="inline-flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#1A0B5E] text-white flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-bolt text-indigo-300"></i>
                    </div>
                    <div class="text-left">
                        <span class="font-black text-lg text-[#1A0B5E] block leading-none">WEBICÓNICA</span>
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-600">Flow • Chat & CRM</span>
                    </div>
                </a>
            </div>

            <!-- Tarjeta Auth -->
            <div class="w-full max-w-md bg-white p-7 sm:p-9 rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-200/50">
                <?php echo e($slot); ?>

            </div>

            <!-- Pie móvil / discreto -->
            <p class="mt-6 text-center text-xs text-slate-400">
                Protegido con cifrado TLS y aislamiento multi-tenant • <a href="https://webiconica.com" target="_blank" class="text-slate-600 hover:text-indigo-600 font-semibold">Webicónica</a>
            </p>
        </div>

    </div>
</body>
</html><?php /**PATH C:\laragon\www\nexus-web\resources\views/layouts/guest.blade.php ENDPATH**/ ?>