<!-- resources/views/partials/header.blade.php -->
<header x-data="{ mobileMenuOpen: false }" class="fixed top-0 w-full bg-white/90 backdrop-blur-md border-b border-gray-100 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        
        <!-- Logo Dinámico -->
        <a href="/#inicio" class="flex items-center gap-2 group transition-transform duration-300 hover:scale-[1.02]">
            @if(!empty($globalSettings['header_logo']))
                <img src="{{ asset('storage/' . $globalSettings['header_logo']) }}" alt="{{ $globalSettings['site_name'] ?? 'Logo' }}" class="h-10 w-auto object-contain">
            @else
                <img src="{{ asset('logo-webiconica.png') }}" alt="Logo" class="h-10 w-auto object-contain" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='flex'">
                <div id="fallback-logo" class="hidden items-center">
                    <span class="font-black text-2xl text-brandDark">{{ $globalSettings['site_name'] ?? 'Mi Sitio Web' }}</span>
                </div>
            @endif
        </a>

        <!-- Navegación Desktop (Soporta botones simples y sub-botones) -->
        <nav class="hidden lg:flex items-center space-x-8 font-semibold text-sm">
            @foreach($headerMenu as $item)
                @if(empty($item['children']))
                    <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}" 
                       class="{{ !empty($item['is_button']) ? 'bg-brandDark text-white px-5 py-2.5 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-brandLight shadow-md' : 'text-brandGray hover:text-brandDark' }} transition-all">
                        {{ $item['title'] }}
                    </a>
                @else
                    <!-- Sub-menú desplegable con Alpine.js -->
                    <div x-data="{ openDropdown: false }" @mouseenter="openDropdown = true" @mouseleave="openDropdown = false" class="relative py-2">
                        <a href="{{ $item['url'] }}" class="text-brandGray hover:text-brandDark transition-colors inline-flex items-center gap-1.5">
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
        </nav>

        <!-- CTA WhatsApp + Botón Sándwich -->
        <div class="flex items-center gap-4">
            @php $whaNumber = $globalSettings['whatsapp_number'] ?? '525539796157'; @endphp
            <a href="https://wa.me/{{ $whaNumber }}?text=Hola!%20Me%20gustar%C3%ADa%20cotizar%20un%20proyecto." target="_blank" class="hidden md:inline-flex bg-brandDark text-white px-6 py-3 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-brandLight transition-all duration-300 shadow-lg items-center gap-2">
                <i class="fab fa-whatsapp text-base"></i> Cotizar por WhatsApp
            </a>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-brandDark p-2">
                <i class="fa-solid text-2xl" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
            </button>
        </div>
    </div>

    <!-- Menú Móvil / Sándwich (Con soporte de acordeón para sub-botones) -->
    <div x-show="mobileMenuOpen" x-transition class="lg:hidden bg-white px-6 py-6 space-y-3 shadow-xl border-t border-gray-100" style="display: none;">
        @foreach($headerMenu as $item)
            @if(empty($item['children']))
                <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}" @click="mobileMenuOpen = false" class="block font-semibold text-brandDark py-1">
                    {{ $item['title'] }}
                </a>
            @else
                <div x-data="{ subOpen: false }" class="space-y-2">
                    <button @click="subOpen = !subOpen" class="w-full flex justify-between items-center font-semibold text-brandDark py-1">
                        <span>{{ $item['title'] }}</span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="subOpen ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="subOpen" x-transition class="pl-4 border-l-2 border-brandLight/30 space-y-2">
                        @foreach($item['children'] as $subItem)
                            <a href="{{ $subItem['url'] }}" target="{{ $subItem['target'] ?? '_self' }}" @click="mobileMenuOpen = false" class="block text-sm font-medium text-brandGray hover:text-brandDark py-1">
                                {{ $subItem['title'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</header>