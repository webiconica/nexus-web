<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo e($post->title ?? $page->meta_title ?? $page->title ?? ($globalSettings['site_name'] ?? 'Mi Empresa')); ?></title>
    
    <?php if(!empty($post->meta_description) || !empty($page->meta_description) || !empty($globalSettings['site_description'])): ?>
        <meta name="description" content="<?php echo e($post->meta_description ?? $page->meta_description ?? $globalSettings['site_description']); ?>">
    <?php endif; ?>
    
    <?php if((isset($post) && $post->meta_keywords) || (isset($page) && $page->meta_keywords)): ?>
        <meta name="keywords" content="<?php echo e($post->meta_keywords ?? $page->meta_keywords); ?>">
    <?php endif; ?>
    
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">

    <link rel="icon" type="image/x-icon" href="<?php echo e((isset($page) && $page->favicon_path) ? $page->favicon_path : (isset($globalSettings['favicon']) ? asset('storage/' . $globalSettings['favicon']) : asset('favicon.ico'))); ?>">

    <meta property="og:type" content="<?php echo e(isset($post) ? 'article' : 'website'); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:title" content="<?php echo e($post->title ?? $page->meta_title ?? $page->title ?? ($globalSettings['site_name'] ?? 'Mi Empresa')); ?>">
    
    <?php if(!empty($post->meta_description) || !empty($page->meta_description) || !empty($globalSettings['site_description'])): ?>
        <meta property="og:description" content="<?php echo e($post->meta_description ?? $page->meta_description ?? $globalSettings['site_description']); ?>">
    <?php endif; ?>
    
    <?php
        $ogImage = (isset($post) && $post->og_image_path) ? $post->og_image_path 
                 : ((isset($post) && $post->featured_image) ? $post->featured_image 
                 : ((isset($page) && $page->og_image_path) ? $page->og_image_path : null));
    ?>

    <?php if($ogImage): ?>
        <meta property="og:image" content="<?php echo e($ogImage); ?>">
    <?php endif; ?>

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Fuentes y Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Cloudflare Turnstile & Alpine.js -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Tailwind CSS (Motor JIT en navegador para soportar cualquier plantilla) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <?php if(!empty($globalSettings['tailwind_config_script'])): ?>
        <?php echo $globalSettings['tailwind_config_script']; ?>

    <?php endif; ?>
    <style>
        [x-cloak] { display: none !important; }
    </style>
  
    <?php if(isset($globalSettings['head_scripts']) && !empty(trim($globalSettings['head_scripts']))): ?><?php echo trim($globalSettings['head_scripts']); ?><?php endif; ?>
    <?php if(isset($page) && !empty(trim($page->head_scripts))): ?><?php echo trim($page->head_scripts); ?><?php endif; ?>
    <?php if(isset($post) && !empty(trim($post->head_scripts))): ?><?php echo trim($post->head_scripts); ?><?php endif; ?>
</head>
<body class="bg-white text-gray-800 font-sans antialiased selection:bg-gray-900 selection:text-white" x-data="appState">
  
    <?php if(isset($globalSettings['body_start_scripts']) && !empty(trim($globalSettings['body_start_scripts']))): ?><?php echo trim($globalSettings['body_start_scripts']); ?><?php endif; ?>

    <!-- Notificación Toast Universal -->
    <div x-show="toast.show" x-transition 
         class="fixed bottom-6 right-20 z-50 bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-800 text-sm font-semibold" 
         style="display: none;">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
        <p x-text="toast.message"></p>
    </div>

    <!-- HEADER DINÁMICO MARCA BLANCA -->
    <header class="fixed top-0 left-0 w-full z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo del Sitio -->
                <a href="/" class="flex items-center gap-3 group">
                    <?php if(!empty($globalSettings['header_logo'])): ?>
                        <img src="<?php echo e(asset('storage/' . $globalSettings['header_logo'])); ?>" alt="<?php echo e($globalSettings['site_name'] ?? 'Logo'); ?>" class="h-10 w-auto object-contain">
                    <?php else: ?>
                        <span class="text-2xl font-black tracking-tight text-gray-900">
                            <?php echo e($globalSettings['site_name'] ?? 'Mi Empresa'); ?>

                        </span>
                    <?php endif; ?>
                </a>

                <!-- Navegación Principal ($headerMenu) Desktop -->
                <nav class="hidden md:flex items-center gap-8 font-semibold text-sm text-gray-600">
                    <?php if(isset($headerMenu)): ?>
                        <?php $__currentLoopData = $headerMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(empty($item['children'])): ?>
                                <a href="<?php echo e($item['url']); ?>" target="<?php echo e($item['target'] ?? '_self'); ?>" 
                                   class="<?php echo e(!empty($item['is_button']) ? 'px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold transition-all shadow-sm' : 'hover:text-gray-900 transition-colors'); ?>">
                                    <?php echo e($item['title']); ?>

                                </a>
                            <?php else: ?>
                                <div x-data="{ openDropdown: false }" @mouseenter="openDropdown = true" @mouseleave="openDropdown = false" class="relative py-2">
                                    <a href="<?php echo e($item['url']); ?>" class="hover:text-gray-900 transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                                        <?php echo e($item['title']); ?>

                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="openDropdown ? 'rotate-180 text-gray-900' : ''"></i>
                                    </a>
                                    <div x-show="openDropdown" x-transition 
                                         class="absolute left-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50" style="display: none;">
                                        <?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e($subItem['url']); ?>" target="<?php echo e($subItem['target'] ?? '_self'); ?>" 
                                               class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 font-medium transition">
                                                <?php echo e($subItem['title']); ?>

                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </nav>

                <!-- Botón de Contacto Rápido / WhatsApp Desktop -->
                <div class="hidden md:flex items-center gap-4">
                    <?php $whaNumber = $globalSettings['whatsapp_number'] ?? ''; ?>
                    <?php if(!empty($whaNumber)): ?>
                        <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $whaNumber)); ?>?text=Hola!%20Me%20gustar%C3%ADa%20solicitar%20informaci%C3%B3n." target="_blank" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-900 font-bold transition-all flex items-center gap-2 text-sm">
                            <i class="fa-brands fa-whatsapp text-lg text-emerald-600"></i>
                            <span>Contacto</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Botón Menú Mobile -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 hover:text-gray-900 p-2 rounded-lg focus:outline-none">
                    <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark text-2xl' : 'fa-bars text-2xl'"></i>
                </button>
            </div>
        </div>

        <!-- Menú Móvil Desplegable -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white border-b border-gray-200 px-6 pt-2 pb-6 space-y-3 shadow-lg" style="display: none;">
            <?php if(isset($headerMenu)): ?>
                <?php $__currentLoopData = $headerMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(empty($item['children'])): ?>
                        <a href="<?php echo e($item['url']); ?>" target="<?php echo e($item['target'] ?? '_self'); ?>" @click="mobileMenuOpen = false" class="block py-2 text-gray-800 hover:text-gray-900 font-semibold">
                            <?php echo e($item['title']); ?>

                        </a>
                    <?php else: ?>
                        <div x-data="{ subOpen: false }" class="space-y-2">
                            <button @click="subOpen = !subOpen" class="w-full flex justify-between items-center py-2 text-gray-800 font-semibold">
                                <span><?php echo e($item['title']); ?></span>
                                <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="subOpen ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="subOpen" x-transition class="pl-4 border-l-2 border-gray-200 space-y-2">
                                <?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e($subItem['url']); ?>" target="<?php echo e($subItem['target'] ?? '_self'); ?>" @click="mobileMenuOpen = false" class="block text-sm font-medium text-gray-600 hover:text-gray-900">
                                        <?php echo e($subItem['title']); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <?php if(!empty($whaNumber)): ?>
                <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $whaNumber)); ?>?text=Hola!%20Me%20gustar%C3%ADa%20solicitar%20informaci%C3%B3n." target="_blank" class="w-full mt-4 py-3 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-center flex items-center justify-center gap-2">
                    <i class="fa-brands fa-whatsapp text-lg text-emerald-400"></i>
                    <span>Contactar por WhatsApp</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    
    <main class="min-h-screen">
        <?php echo $__env->yieldContent('content'); ?>
        <?php if(isset($slot)): ?>
            <?php echo e($slot); ?>

        <?php endif; ?>
    </main>

    <!-- FOOTER DINÁMICO MARCA BLANCA -->
    <footer class="bg-gray-900 text-gray-400 border-t border-gray-800 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-gray-800">
                
                <!-- Información de la Empresa -->
                <div class="space-y-4 md:col-span-2">
                    <a href="/" class="flex items-center gap-3">
                        <?php if(!empty($globalSettings['footer_logo']) || !empty($globalSettings['header_logo'])): ?>
                            <img src="<?php echo e(asset('storage/' . ($globalSettings['footer_logo'] ?? $globalSettings['header_logo']))); ?>" alt="Logo Footer" class="h-10 w-auto object-contain">
                        <?php else: ?>
                            <span class="text-2xl font-black tracking-tight text-white">
                                <?php echo e($globalSettings['site_name'] ?? 'Mi Empresa'); ?>

                            </span>
                        <?php endif; ?>
                    </a>
                    <p class="text-sm max-w-sm leading-relaxed text-gray-400">
                        <?php echo e($globalSettings['site_description'] ?? 'Servicios profesionales, soluciones integrales y atención personalizada para potenciar tu presencia digital.'); ?>

                    </p>
                </div>

                <!-- Enlaces de Navegación ($headerMenu) -->
                <div>
                    <h4 class="text-white font-bold uppercase text-xs tracking-wider mb-4">Navegación</h4>
                    <ul class="space-y-2.5 text-sm">
                        <?php if(isset($headerMenu)): ?>
                            <?php $__currentLoopData = array_slice($headerMenu, 0, 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e($fItem['url']); ?>" class="hover:text-white transition-colors">
                                        <?php echo e($fItem['title']); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Redes Sociales y Enlaces Externos -->
                <div>
                    <h4 class="text-white font-bold uppercase text-xs tracking-wider mb-4">Redes</h4>
                    <div class="flex gap-3">
                        <?php if(!empty($globalSettings['instagram_url'])): ?>
                            <a href="<?php echo e($globalSettings['instagram_url']); ?>" target="_blank" class="w-10 h-10 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center transition-colors">
                                <i class="fa-brands fa-instagram text-base"></i>
                            </a>
                        <?php endif; ?>
                        <?php if(!empty($globalSettings['facebook_url'])): ?>
                            <a href="<?php echo e($globalSettings['facebook_url']); ?>" target="_blank" class="w-10 h-10 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center transition-colors">
                                <i class="fa-brands fa-facebook-f text-base"></i>
                            </a>
                        <?php endif; ?>
                        <?php if(!empty($globalSettings['tiktok_url'])): ?>
                            <a href="<?php echo e($globalSettings['tiktok_url']); ?>" target="_blank" class="w-10 h-10 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center transition-colors">
                                <i class="fa-brands fa-tiktok text-base"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 gap-4">
                <p>© <?php echo e(date('Y')); ?> <?php echo e($globalSettings['site_name'] ?? 'Mi Empresa'); ?>. Todos los derechos reservados.</p>
                <p>Plataforma impulsada por Inteligencia Artificial.</p>
            </div>
        </div>
    </footer>

    <!-- Botón Flotante de WhatsApp -->
    <?php if(!empty($globalSettings['whatsapp_number'])): ?>
        <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $globalSettings['whatsapp_number'])); ?>?text=Hola!%20Me%20gustar%C3%ADa%20solicitar%20informaci%C3%B3n." target="_blank" class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-emerald-500 hover:bg-emerald-400 text-white flex items-center justify-center text-2xl shadow-xl hover:scale-105 transition-all shadow-emerald-500/20">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
    <?php endif; ?>

    
    <?php echo $__env->make('partials.app-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(isset($globalSettings['body_end_scripts']) && !empty(trim($globalSettings['body_end_scripts']))): ?><?php echo trim($globalSettings['body_end_scripts']); ?><?php endif; ?>
</body>
</html><?php /**PATH C:\laragon\www\nexus-web\resources\views/layouts/web.blade.php ENDPATH**/ ?>