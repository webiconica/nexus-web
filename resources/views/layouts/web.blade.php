<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>{{ $post->title ?? $page->meta_title ?? $page->title ?? ($globalSettings['site_name'] ?? 'Mi Empresa') }}</title>
    
    @if(!empty($post->meta_description) || !empty($page->meta_description) || !empty($globalSettings['site_description']))
        <meta name="description" content="{{ $post->meta_description ?? $page->meta_description ?? $globalSettings['site_description'] }}">
    @endif
    
    @if((isset($post) && $post->meta_keywords) || (isset($page) && $page->meta_keywords))
        <meta name="keywords" content="{{ $post->meta_keywords ?? $page->meta_keywords }}">
    @endif
    
    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="icon" type="image/x-icon" href="{{ (isset($page) && $page->favicon_path) ? $page->favicon_path : (isset($globalSettings['favicon']) ? asset('storage/' . $globalSettings['favicon']) : asset('favicon.ico')) }}">

    <meta property="og:type" content="{{ isset($post) ? 'article' : 'website' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $post->title ?? $page->meta_title ?? $page->title ?? ($globalSettings['site_name'] ?? 'Mi Empresa') }}">
    
    @if(!empty($post->meta_description) || !empty($page->meta_description) || !empty($globalSettings['site_description']))
        <meta property="og:description" content="{{ $post->meta_description ?? $page->meta_description ?? $globalSettings['site_description'] }}">
    @endif
    
    @php
        $ogImage = (isset($post) && $post->og_image_path) ? $post->og_image_path 
                 : ((isset($post) && $post->featured_image) ? $post->featured_image 
                 : ((isset($page) && $page->og_image_path) ? $page->og_image_path : null));
    @endphp

    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    @if(!empty($globalSettings['tailwind_config_script']))
        {!! $globalSettings['tailwind_config_script'] !!}
    @endif
    <style>
        [x-cloak] { display: none !important; }
    </style>
  
    @if(isset($globalSettings['head_scripts']) && !empty(trim($globalSettings['head_scripts']))){!! trim($globalSettings['head_scripts']) !!}@endif
    @if(isset($page) && !empty(trim($page->head_scripts))){!! trim($page->head_scripts) !!}@endif
    @if(isset($post) && !empty(trim($post->head_scripts))){!! trim($post->head_scripts) !!}@endif
</head>
<body class="bg-white text-gray-800 font-sans antialiased selection:bg-gray-900 selection:text-white" x-data="appState">
  
    @if(isset($globalSettings['body_start_scripts']) && !empty(trim($globalSettings['body_start_scripts']))){!! trim($globalSettings['body_start_scripts']) !!}@endif

    <!-- Notificación Toast Universal -->
    <div x-show="toast.show" x-transition 
         class="fixed bottom-6 right-20 z-50 bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-800 text-sm font-semibold" 
         style="display: none;">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
        <p x-text="toast.message"></p>
    </div>

    {{-- HEADER MODULAR CONECTADO DESDE PARTIALS --}}
    @include('partials.header')

    {{-- ÁREA PRINCIPAL UNIFICADA (Soporta yield y slot) --}}
    <main class="min-h-screen">
        @yield('content')
        @isset($slot)
            {{ $slot }}
        @endisset
    </main>

    <!-- FOOTER DINÁMICO MARCA BLANCA -->
    <footer class="bg-gray-900 text-gray-400 border-t border-gray-800 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-gray-800">
                
                <!-- Información de la Empresa -->
                <div class="space-y-4 md:col-span-2">
                    <a href="/" class="flex items-center gap-3">
                        @if(!empty($globalSettings['footer_logo']) || !empty($globalSettings['header_logo']))
                            <img src="{{ asset('storage/' . ($globalSettings['footer_logo'] ?? $globalSettings['header_logo'])) }}" alt="Logo Footer" class="h-10 w-auto object-contain">
                        @else
                            <span class="text-2xl font-black tracking-tight text-white">
                                {{ $globalSettings['site_name'] ?? 'Mi Empresa' }}
                            </span>
                        @endif
                    </a>
                    <p class="text-sm max-w-sm leading-relaxed text-gray-400">
                        {{ $globalSettings['site_description'] ?? 'Servicios profesionales, soluciones integrales y atención personalizada para potenciar tu presencia digital.' }}
                    </p>
                </div>

                <!-- Enlaces de Navegación ($headerMenu) -->
                <div>
                    <h4 class="text-white font-bold uppercase text-xs tracking-wider mb-4">Navegación</h4>
                    <ul class="space-y-2.5 text-sm">
                        @if(isset($headerMenu))
                            @foreach(array_slice($headerMenu, 0, 5) as $fItem)
                                <li>
                                    <a href="{{ $fItem['url'] }}" class="hover:text-white transition-colors">
                                        {{ $fItem['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                <!-- Redes Sociales y Enlaces Externos -->
                <div>
                    <h4 class="text-white font-bold uppercase text-xs tracking-wider mb-4">Redes</h4>
                    <div class="flex gap-3">
                        @if(!empty($globalSettings['instagram_url']))
                            <a href="{{ $globalSettings['instagram_url'] }}" target="_blank" class="w-10 h-10 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center transition-colors">
                                <i class="fa-brands fa-instagram text-base"></i>
                            </a>
                        @endif
                        @if(!empty($globalSettings['facebook_url']))
                            <a href="{{ $globalSettings['facebook_url'] }}" target="_blank" class="w-10 h-10 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center transition-colors">
                                <i class="fa-brands fa-facebook-f text-base"></i>
                            </a>
                        @endif
                        @if(!empty($globalSettings['tiktok_url']))
                            <a href="{{ $globalSettings['tiktok_url'] }}" target="_blank" class="w-10 h-10 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center transition-colors">
                                <i class="fa-brands fa-tiktok text-base"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 gap-4">
                <p>© {{ date('Y') }} {{ $globalSettings['site_name'] ?? 'Mi Empresa' }}. Todos los derechos reservados.</p>
                <p>Plataforma impulsada por Inteligencia Artificial.</p>
            </div>
        </div>
    </footer>

    <!-- Botón Flotante de WhatsApp -->
    @if(!empty($globalSettings['whatsapp_number']))
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $globalSettings['whatsapp_number']) }}?text=Hola!%20Me%20gustar%C3%ADa%20solicitar%20informaci%C3%B3n." target="_blank" class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-emerald-500 hover:bg-emerald-400 text-white flex items-center justify-center text-2xl shadow-xl hover:scale-105 transition-all shadow-emerald-500/20">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
    @endif

    {{-- SCRIPTS MODULARES (Alpine AppState, CRM y WhatsApp) --}}
    @include('partials.app-scripts')

    @if(isset($globalSettings['body_end_scripts']) && !empty(trim($globalSettings['body_end_scripts']))){!! trim($globalSettings['body_end_scripts']) !!}@endif
    @include('partials.chatbot-widget')
</body>
</html>