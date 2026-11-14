<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>{{ $post->title ?? $page->meta_title ?? $page->title ?? ($globalSettings['site_name'] ?? 'Mi Sitio Web') }}</title>
    
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
    <meta property="og:title" content="{{ $post->title ?? $page->meta_title ?? $page->title ?? ($globalSettings['site_name'] ?? 'Mi Sitio Web') }}">
    
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
    </noscript>
    
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
  
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>  
  
    @if(isset($globalSettings['head_scripts']) && !empty(trim($globalSettings['head_scripts']))){!! trim($globalSettings['head_scripts']) !!}@endif
    @if(isset($page) && !empty(trim($page->head_scripts))){!! trim($page->head_scripts) !!}@endif
    @if(isset($post) && !empty(trim($post->head_scripts))){!! trim($post->head_scripts) !!}@endif
</head>
<body class="bg-brandBg text-gray-800 font-sans antialiased" x-data="appState">
  
    @if(isset($globalSettings['body_start_scripts']) && !empty(trim($globalSettings['body_start_scripts']))){!! trim($globalSettings['body_start_scripts']) !!}@endif

    <div x-show="toast.show" x-transition class="fixed bottom-6 right-6 z-50 bg-brandDark text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-brandLight/20" style="display: none;">
        <span class="w-2.5 h-2.5 rounded-full bg-green-400 animate-ping"></span>
        <p class="text-sm font-semibold" x-text="toast.message"></p>
    </div>

    <!-- HEADER DINÁMICO QUE CONSUME $headerMenu -->
    <header class="fixed top-0 w-full bg-white/90 backdrop-blur-md border-b border-gray-100 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Logo Dinámico -->
            <a href="/" class="flex items-center gap-2 group transition-transform duration-300 hover:scale-[1.02]">
                @if(!empty($globalSettings['header_logo']))
                    <img src="{{ asset('storage/' . $globalSettings['header_logo']) }}" alt="{{ $globalSettings['site_name'] ?? 'Logo' }}" class="h-10 w-auto object-contain">
                @else
                    <span class="font-black text-2xl text-brandDark tracking-tight">{{ $globalSettings['site_name'] ?? 'Mi Sitio Web' }}</span>
                @endif
            </a>
            
            <!-- Navegación Principal Recorriendo $headerMenu -->
            <nav class="hidden lg:flex items-center space-x-6 font-semibold text-sm">
                @if(isset($headerMenu))
                    @foreach($headerMenu as $item)
                        @if(empty($item['children']))
                            <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}" 
                               class="{{ !empty($item['is_button']) ? 'bg-brandDark text-white px-5 py-2.5 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-brandLight shadow-md' : 'text-brandGray hover:text-brandDark' }} transition-all">
                                {{ $item['title'] }}
                            </a>
                        @else
                            <!-- Submenú desplegable -->
                            <div x-data="{ openDropdown: false }" @mouseenter="openDropdown = true" @mouseleave="openDropdown = false" class="relative py-2">
                                <a href="{{ $item['url'] }}" class="text-brandGray hover:text-brandDark transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                                    {{ $item['title'] }}
                                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="openDropdown ? 'rotate-180 text-brandLight' : ''"></i>
                                </a>
                                <div x-show="openDropdown" x-transition 
                                     class="absolute left-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50" style="display: none;">
                                    @foreach($item['children'] as $subItem)
                                        <a href="{{ $subItem['url'] }}" target="{{ $subItem['target'] ?? '_self' }}" 
                                           class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-purple-50 hover:text-brandDark font-medium transition">
                                            {{ $subItem['title'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </nav>

            <div class="hidden md:flex items-center gap-6">
                @php $whaNumber = $globalSettings['whatsapp_number'] ?? ''; @endphp
                @if(!empty($whaNumber))
                    <a href="https://wa.me/{{ $whaNumber }}?text=Hola!%20Me%20gustar%C3%ADa%20cotizar%20un%20proyecto." target="_blank" class="bg-brandDark text-white px-6 py-3 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-brandLight transition-all duration-300 shadow-lg flex items-center gap-2">
                        <i class="fab fa-whatsapp text-base"></i> Contactar
                    </a>
                @endif
            </div>

            <!-- Botón Sándwich Móvil -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-brandDark p-2">
                <i class="fa-solid text-2xl" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
            </button>
        </div>
        
        <!-- Menú Móvil Dinámico con Acordeón -->
        <div x-show="mobileMenuOpen" x-transition class="lg:hidden bg-white px-6 py-6 space-y-4 shadow-xl border-t border-gray-100" style="display: none;">
            @if(isset($headerMenu))
                @foreach($headerMenu as $item)
                    @if(empty($item['children']))
                        <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}" @click="mobileMenuOpen = false" class="block font-bold text-gray-800">
                            {{ $item['title'] }}
                        </a>
                    @else
                        <div x-data="{ subOpen: false }" class="space-y-2">
                            <button @click="subOpen = !subOpen" class="w-full flex justify-between items-center font-bold text-gray-800">
                                <span>{{ $item['title'] }}</span>
                                <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="subOpen ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="subOpen" x-transition class="pl-4 border-l-2 border-brandLight/30 space-y-2">
                                @foreach($item['children'] as $subItem)
                                    <a href="{{ $subItem['url'] }}" target="{{ $subItem['target'] ?? '_self' }}" @click="mobileMenuOpen = false" class="block text-sm font-medium text-gray-600">
                                        {{ $subItem['title'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </header>

    {{-- ÁREA PRINCIPAL UNIFICADA (Soporta yield y slot) --}}
    <main class="pt-16 min-h-screen">
        @yield('content')
        @isset($slot)
            {{ $slot }}
        @endisset
    </main>
  
    <!-- FOOTER DINÁMICO MARCA BLANCA -->
    <footer class="bg-brandDark text-white pt-16 pb-8 border-t-[6px] border-brandLight">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                <div class="space-y-4">
                    <span class="font-black text-2xl tracking-tight">{{ $globalSettings['site_name'] ?? 'Mi Sitio Web' }}</span>
                    <p class="text-purple-200/80 text-sm leading-relaxed max-w-sm">
                        {{ $globalSettings['site_description'] ?? 'Plataforma digital corporativa y soluciones web profesionales.' }}
                    </p>
                </div>
                
                <div>
                    <h4 class="text-brandLight font-bold uppercase tracking-widest text-xs mb-6">Enlaces Rápidos</h4>
                    <ul class="space-y-3 text-sm text-purple-100">
                        @if(isset($headerMenu))
                            @foreach(array_slice($headerMenu, 0, 5) as $fItem)
                                <li><a href="{{ $fItem['url'] }}" class="hover:text-brandLight transition">{{ $fItem['title'] }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-brandLight font-bold uppercase tracking-widest text-xs mb-6">Contacto</h4>
                    <ul class="space-y-4 text-sm text-purple-100">
                        @if(!empty($globalSettings['whatsapp_number']))
                            <li class="flex items-center gap-3">
                                <i class="fab fa-whatsapp text-brandLight text-lg"></i>
                                <a href="https://wa.me/{{ $globalSettings['whatsapp_number'] }}" target="_blank" class="hover:text-white transition">+{{ $globalSettings['whatsapp_number'] }}</a>
                            </li>
                        @endif
                        <li class="flex items-center gap-3 mt-4">
                            @if(!empty($globalSettings['facebook_url']))
                                <a href="{{ $globalSettings['facebook_url'] }}" target="_blank" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-brandLight hover:text-brandDark transition text-lg"><i class="fab fa-facebook-f"></i></a>
                            @endif
                            @if(!empty($globalSettings['instagram_url']))
                                <a href="{{ $globalSettings['instagram_url'] }}" target="_blank" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-brandLight hover:text-brandDark transition text-lg"><i class="fab fa-instagram"></i></a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-purple-300">
                <p>© {{ date('Y') }} {{ $globalSettings['site_name'] ?? 'Mi Empresa' }}. Todos los derechos reservados.</p>
                <p>Plataforma impulsada por Inteligencia Artificial.</p>
            </div>
        </div>
    </footer>

    <!-- SCRIPT ALPINEJS DINÁMICO UNIVERSAL -->
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('appState', () => ({
            mobileMenuOpen: false,
            toast: { show: false, message: '' },
            isSubmitting: false,

            showToast(msg) {
                this.toast.message = msg; 
                this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 4000);
            },

            async sendWhatsAppMessage(event) {
                if (this.isSubmitting) return;

                // 1. Obtener el elemento form desde el evento de submit
                const formElement = event ? (event.target || event.currentTarget) : null;
                let dynamicData = {};

                if (formElement) {
                    const formData = new FormData(formElement);

                    // Verificación de Honeypot anti-spam si existe
                    if (formData.has('website') && formData.get('website') !== '') {
                        console.warn("Spam detectado.");
                        return;
                    }

                    // Extrae todos los campos dinámicamente con su etiqueta/nombre
                    formData.forEach((value, key) => {
                        if (key !== '_token' && key !== 'website' && key !== 'cf-turnstile-response') {
                            dynamicData[key] = value;
                        }
                    });
                }

                // Fallback: Si el formulario no tiene inputs con "name", tomamos lo que haya en variables
                if (Object.keys(dynamicData).length === 0 && this.form) {
                    dynamicData = { ...this.form };
                    delete dynamicData.website;
                }

                this.isSubmitting = true;

                // 2. Guardar en el CRM (LeadController)
                try {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfMeta ? csrfMeta.content : '';

                    // Obtenemos token de Turnstile si está presente en el formulario
                    const turnstileInput = formElement ? formElement.querySelector('[name="cf-turnstile-response"]') : null;

                    const payload = {
                        tenant_id: {{ $globalSettings['tenant_id'] ?? 1 }},
                        origen: document.title || 'Formulario Web',
                        datos_formulario: dynamicData
                    };

                    if (turnstileInput && turnstileInput.value) {
                        payload.turnstile_token = turnstileInput.value;
                    }

                    // Enviar lead en segundo plano al CRM
                    await fetch('/api/capturar-lead', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                } catch (error) {
                    console.error("Error guardando lead en CRM:", error);
                }

                // 3. Formatear y Enviar a WhatsApp
                let whaNumber = '{{ preg_replace("/[^0-9]/", "", $globalSettings["whatsapp_number"] ?? "") }}';

                // Construir el texto del mensaje iterando sobre cualquier campo enviado
                let text = `¡Hola! Vengo desde el sitio web *{{ $globalSettings['site_name'] ?? 'BURGER CRAFT' }}*:\n\n`;
                for (const [campo, valor] of Object.entries(dynamicData)) {
                    if (valor && typeof valor === 'string' && valor.trim() !== '') {
                        // Limpia el nombre del campo para que se vea legible (ej. nombre_completo -> Nombre completo)
                        const label = campo.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        text += `📌 *${label}:* ${valor}\n`;
                    }
                }

                this.isSubmitting = false;

                if (whaNumber) {
                    window.open(`https://wa.me/${whaNumber}?text=${encodeURIComponent(text)}`, '_blank');
                } else {
                    this.showToast("¡Solicitud recibida con éxito! Nos pondremos en contacto.");
                }
            }
        }));
    });
    </script>

    @if(isset($globalSettings['body_end_scripts']) && !empty(trim($globalSettings['body_end_scripts']))){!! trim($globalSettings['body_end_scripts']) !!}@endif
</body>
</html>