<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webicónica | Ecosistemas Digitales e Ingeniería Web</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
   
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandDark: '#1a0b5e',
                        brandLight: '#9484f7',
                        brandGray: '#606060',
                        brandBg: '#fbfbfe',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .glow-effect {
            box-shadow: 0 0 50px -10px rgba(148, 132, 247, 0.35);
        }
    </style>
   <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-brandBg text-gray-800 font-sans antialiased" x-data="appState">

    <!-- NOTIFICACIÓN TOAST -->
    <div x-show="toast.show" 
         x-transition
         class="fixed bottom-6 right-6 z-50 bg-brandDark text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-brandLight/20"
         style="display: none;">
        <span class="w-2.5 h-2.5 rounded-full bg-green-400 animate-ping"></span>
        <p class="text-sm font-semibold" x-text="toast.message"></p>
    </div>

    <!-- BARRA DE NAVEGACIÓN -->
    <header class="fixed top-0 w-full bg-white/90 backdrop-blur-md border-b border-gray-100 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#inicio" class="flex items-center gap-2 group transition-transform duration-300 hover:scale-[1.02]">
                <img src="logo-webiconica.com (2).png" alt="Webicónica Logo" class="h-10 w-auto object-contain" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='flex'">
                <div id="fallback-logo" class="hidden items-center">
                    <svg class="h-9 w-auto" viewBox="0 0 320 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 15C12 11.6863 14.6863 9 18 9C21.3137 9 24 11.6863 24 15V32L31 15C32.5 11 35.5 9 39 9C42.5 9 45.5 11 47 15L54 32V15C54 11.6863 56.6863 9 60 9C63.3137 9 66 11.6863 66 15V45C66 49.9706 61.9706 54 57 54C53.134 54 49.8268 51.5645 48.5133 48L40 28L31.4867 48C30.1732 51.5645 26.866 54 23 54C18.0294 54 14 49.9706 14 45V15Z" fill="#1A0B5E"/>
                        <path d="M10 40L23 48L17.5 51.5L25 62L21 65L13.5 54.5L8 58L10 40Z" fill="#9484F7"/>
                        <text x="75" y="47" font-family="'Inter', sans-serif" font-weight="700" font-size="34" fill="#606060" letter-spacing="-1">iconica</text>
                        <text x="212" y="47" font-family="'Inter', sans-serif" font-weight="500" font-size="34" fill="#9484F7" letter-spacing="-1">.com</text>
                    </svg>
                </div>
            </a>

            <nav class="hidden lg:flex items-center space-x-8 font-semibold text-sm">
                <a href="#inicio" class="text-brandDark hover:text-brandLight transition-colors">Inicio</a>
                <a href="#servicios" class="text-brandGray hover:text-brandDark transition-colors">Servicios</a>
                <a href="#ecosistema" class="text-brandGray hover:text-brandDark transition-colors">Ecosistema</a>
                <a href="#simulador" class="text-brandGray hover:text-brandDark transition-colors">Simulador</a>
                <a href="#contacto" class="text-brandGray hover:text-brandDark transition-colors">Contacto</a>
            </nav>

            <div class="hidden md:flex items-center gap-6">
                <a href="https://wa.me/525539796157?text=Hola!%20Me%20gustar%C3%ADa%20cotizar%20un%20proyecto." 
                   target="_blank" 
                   class="bg-brandDark text-white px-6 py-3 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-brandLight transition-all duration-300 shadow-lg flex items-center gap-2">
                    <i class="fab fa-whatsapp text-base"></i> Cotizar por WhatsApp
                </a>
            </div>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-brandDark p-2">
                <i class="fa-solid text-2xl" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
            </button>
        </div>

        <div x-show="mobileMenuOpen" x-transition class="lg:hidden bg-white px-6 py-6 space-y-4 shadow-xl" style="display: none;">
            <a href="#inicio" @click="mobileMenuOpen = false" class="block font-bold">Inicio</a>
            <a href="#servicios" @click="mobileMenuOpen = false" class="block font-semibold">Servicios</a>
            <a href="#ecosistema" @click="mobileMenuOpen = false" class="block font-semibold">Ecosistema Digital</a>
            <a href="#simulador" @click="mobileMenuOpen = false" class="block font-semibold">Simulador</a>
            <a href="#contacto" @click="mobileMenuOpen = false" class="block font-semibold">Contacto</a>
            <div class="pt-4 border-t flex flex-col gap-3">
                <a href="https://wa.me/525539796157" target="_blank" class="bg-brandDark text-white text-center py-3 rounded-xl font-bold flex justify-center gap-2"><i class="fab fa-whatsapp"></i> Chat Directo</a>
            </div>
        </div>
    </header>

    <!-- SECCIÓN HERO -->
    <section id="inicio" class="relative pt-32 pb-24 md:pt-48 md:pb-36 overflow-hidden">
        <div class="absolute top-1/4 -right-20 w-96 h-96 bg-brandLight/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-10 -left-20 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-7 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 bg-purple-100/60 text-brandDark px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider mb-6">
                        <span class="w-2 h-2 rounded-full bg-brandLight animate-ping"></span>
                        Ecosistemas Comerciales en Línea
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-brandDark leading-tight mb-6">
                        No solo creamos webs, creamos <span class="text-brandLight relative inline-block">máquinas de conversión</span>
                    </h1>
                    <p class="text-lg md:text-xl text-brandGray mb-8 max-w-2xl mx-auto lg:mx-0">
                        Diseñamos, programamos e integramos todo tu ecosistema de negocios. Desde pautas publicitarias de alto retorno hasta aplicaciones web robustas que escalan tus procesos comerciales.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="#servicios" class="w-full sm:w-auto bg-brandDark text-white px-8 py-4 rounded-2xl font-bold hover:bg-brandLight transition-all">Explorar Servicios</a>
                        <a href="#simulador" class="w-full sm:w-auto bg-white text-brandDark border-2 border-gray-200 px-8 py-4 rounded-2xl font-bold hover:border-brandDark transition-all flex justify-center gap-2"><i class="fa-solid fa-calculator text-brandLight"></i> Simular mi Impacto</a>
                    </div>
                </div>

                <div class="lg:col-span-5 flex justify-center items-center">
                    <div class="relative w-full max-w-md aspect-square flex items-center justify-center">
                        <div class="absolute inset-0 bg-gradient-to-tr from-brandLight/10 to-transparent rounded-full animate-pulse glow-effect"></div>
                        <div class="absolute w-4/5 h-4/5 border border-dashed border-brandLight/30 rounded-full animate-spin" style="animation-duration: 25s;"></div>
                        
                        <div class="relative p-12 bg-white rounded-3xl shadow-2xl border border-gray-100 transform hover:scale-[1.03] transition flex flex-col items-center">
                            <img src="icono-webiconica.com (1).png" alt="Ícono Webicónica" class="w-48 h-auto drop-shadow-xl" onerror="this.style.display='none'; document.getElementById('fallback-icon').style.display='block'">
                            <div id="fallback-icon" class="hidden">
                                <svg class="w-48 h-auto drop-shadow-xl" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 30C20 23.3726 25.3726 18 32 18C38.6274 18 44 23.3726 44 30V64L58 30C61 22 67 18 74 18C81 18 87 22 90 30L104 64V30C104 23.3726 109.373 18 116 18C122.627 18 128 23.3726 128 30V90C128 99.9412 119.941 108 110 108C102.268 108 95.6536 103.129 93.0266 96L76 56L58.9734 96C56.3464 103.129 49.732 108 42 108C32.0588 108 24 99.9412 24 90V30Z" fill="#1A0B5E"/>
                                    <path class="animate-bounce" style="animation-duration: 2s;" d="M15 80L41 96L30 103L45 124L37 130L22 109L11 116L15 80Z" fill="#9484F7"/>
                                </svg>
                            </div>
                            <p class="text-brandDark font-bold text-xl mt-6">webiconica<span class="text-brandLight">.com</span></p>
                            <p class="text-brandGray text-xs font-medium mt-1">Diseño, Código, Tráfico & Apps</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE SERVICIOS -->
    <section id="servicios" class="py-24 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <span class="text-xs font-bold tracking-widest text-brandLight uppercase block mb-3">Especialidades de Negocio</span>
                <h2 class="text-3xl sm:text-4xl font-black text-brandDark mb-4">Nuestras Soluciones</h2>
                <div class="h-1 w-20 bg-brandLight mx-auto rounded-full mb-6"></div>
                <p class="text-lg text-brandGray">Ingeniería digital combinada con diseño y marketing para escalar tu negocio.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Servicio 1 -->
                <div class="bg-brandBg p-8 rounded-3xl border border-gray-100 hover:border-brandLight/40 hover:bg-white transition flex flex-col justify-between group">
                    <div>
                        <div class="w-14 h-14 bg-purple-100/50 text-brandDark rounded-2xl flex justify-center items-center text-2xl font-bold mb-8 group-hover:bg-brandDark group-hover:text-white transition"><i class="fa-solid fa-palette"></i></div>
                        <h3 class="text-xl font-bold text-brandDark mb-4">Diseño Web</h3>
                        <p class="text-brandGray text-sm mb-6">Interfaces intuitivas que facilitan el viaje del usuario (UX/UI). Maquetaciones estéticas, modernas y pensadas para mejorar tu conversión.</p>
                    </div>
                    <a href="#contacto" @click="selectService('Diseño Web')" class="text-brandDark font-bold text-sm flex items-center gap-2 group-hover:text-brandLight">Me interesa <i class="fa-solid fa-arrow-right text-xs"></i></a>
                </div>

                <!-- Servicio 2 -->
                <div class="bg-brandBg p-8 rounded-3xl border border-gray-100 hover:border-brandLight/40 hover:bg-white transition flex flex-col justify-between group">
                    <div>
                        <div class="w-14 h-14 bg-purple-100/50 text-brandDark rounded-2xl flex justify-center items-center text-2xl font-bold mb-8 group-hover:bg-brandDark group-hover:text-white transition"><i class="fa-solid fa-bullseye"></i></div>
                        <h3 class="text-xl font-bold text-brandDark mb-4">Google & Facebook Ads</h3>
                        <p class="text-brandGray text-sm mb-6">Campañas orientadas a resultados. Atraemos tráfico calificado e hiper-segmentado en el momento exacto de su búsqueda o navegación.</p>
                    </div>
                    <a href="#contacto" @click="selectService('Publicidad en Google y Facebook Ads')" class="text-brandDark font-bold text-sm flex items-center gap-2 group-hover:text-brandLight">Me interesa <i class="fa-solid fa-arrow-right text-xs"></i></a>
                </div>

                <!-- Servicio 3 -->
                <div class="bg-brandBg p-8 rounded-3xl border border-gray-100 hover:border-brandLight/40 hover:bg-white transition flex flex-col justify-between group">
                    <div>
                        <div class="w-14 h-14 bg-purple-100/50 text-brandDark rounded-2xl flex justify-center items-center text-2xl font-bold mb-8 group-hover:bg-brandDark group-hover:text-white transition"><i class="fa-solid fa-circle-nodes"></i></div>
                        <h3 class="text-xl font-bold text-brandDark mb-4">Ecosistema Digital (Redes)</h3>
                        <p class="text-brandGray text-sm mb-6">Integración omnicanal total. Alineamos tu presencia en redes sociales con tu landing page para unificar el embudo y fidelizar a tu audiencia.</p>
                    </div>
                    <a href="#contacto" @click="selectService('Creación de ecosistema digital')" class="text-brandDark font-bold text-sm flex items-center gap-2 group-hover:text-brandLight">Me interesa <i class="fa-solid fa-arrow-right text-xs"></i></a>
                </div>

                <!-- Servicio 4 -->
                <div class="bg-brandBg p-8 rounded-3xl border border-gray-100 hover:border-brandLight/40 hover:bg-white transition flex flex-col justify-between group">
                    <div>
                        <div class="w-14 h-14 bg-purple-100/50 text-brandDark rounded-2xl flex justify-center items-center text-2xl font-bold mb-8 group-hover:bg-brandDark group-hover:text-white transition"><i class="fab fa-wordpress-simple"></i></div>
                        <h3 class="text-xl font-bold text-brandDark mb-4">WordPress Profesional</h3>
                        <p class="text-brandGray text-sm mb-6">Desarrollo rápido, escalable y optimizado para SEO. Sitios corporativos, blogs o e-commerce autoadministrables y estables en cualquier servidor.</p>
                    </div>
                    <a href="#contacto" @click="selectService('Desarrollo web en WordPress')" class="text-brandDark font-bold text-sm flex items-center gap-2 group-hover:text-brandLight">Me interesa <i class="fa-solid fa-arrow-right text-xs"></i></a>
                </div>

                <!-- Servicio 5 -->
                <div class="bg-brandBg p-8 rounded-3xl border border-gray-100 hover:border-brandLight/40 hover:bg-white transition flex flex-col justify-between group">
                    <div>
                        <div class="w-14 h-14 bg-purple-100/50 text-brandDark rounded-2xl flex justify-center items-center text-2xl font-bold mb-8 group-hover:bg-brandDark group-hover:text-white transition"><i class="fa-solid fa-code"></i></div>
                        <h3 class="text-xl font-bold text-brandDark mb-4">Aplicaciones Web</h3>
                        <p class="text-brandGray text-sm mb-6">Sistemas a la medida en la nube. Orientados a solucionar flujos operativos, cotizadores automatizados, CRM o portales privados.</p>
                    </div>
                    <a href="#contacto" @click="selectService('Desarrollo de aplicaciones web')" class="text-brandDark font-bold text-sm flex items-center gap-2 group-hover:text-brandLight">Me interesa <i class="fa-solid fa-arrow-right text-xs"></i></a>
                </div>

                <!-- Llamada a la Acción (CTA) -->
                <div class="bg-gradient-to-tr from-brandDark to-brandLight p-8 rounded-3xl text-white flex flex-col justify-between shadow-xl">
                    <div>
                        <span class="text-[10px] bg-white/20 font-bold px-3 py-1 rounded-full uppercase tracking-widest mb-6 inline-block">Proyecto Integral</span>
                        <h3 class="text-2xl font-black mb-4">¿Buscas una solución llave en mano?</h3>
                        <p class="text-purple-100 text-sm mb-6">Combinamos el diseño web, configuramos tus pautas publicitarias e integramos tus redes en un ecosistema listo para vender.</p>
                    </div>
                    <a href="https://wa.me/525539796157" target="_blank" class="w-full bg-white text-brandDark font-bold text-center py-4 rounded-2xl hover:bg-purple-100 transition flex justify-center gap-2"><i class="fab fa-whatsapp text-lg text-green-500"></i> Consultar Ahora</a>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN ECOSISTEMA DIGITAL -->
    <section id="ecosistema" class="py-24 bg-brandBg overflow-hidden border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-3xl mx-auto mb-16 relative z-10">
                <span class="text-xs font-bold tracking-widest text-brandLight uppercase block mb-3">Nuestra Metodología</span>
                <h2 class="text-3xl sm:text-4xl font-black text-brandDark mb-4">¿Qué es un Ecosistema Digital?</h2>
                <div class="h-1 w-20 bg-brandLight mx-auto rounded-full mb-6"></div>
                <p class="text-brandGray">No tener una presencia conectada es perder ventas. Diseñamos una estrategia fluida donde cada canal alimenta al otro, automatizando el proceso de venta para maximizar tu retorno (ROI).</p>
            </div>

            <!-- Cuadrícula del Ecosistema -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative">
                <!-- Línea conectora de fondo para pantallas grandes -->
                <div class="hidden lg:block absolute top-1/2 left-0 w-full h-1 bg-gradient-to-r from-brandLight/20 via-brandDark/20 to-brandLight/20 -translate-y-1/2 z-0"></div>

                <!-- Pilar 1 -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-xl relative z-10 hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-14 h-14 bg-brandDark text-white rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-md"><i class="fa-solid fa-magnet"></i></div>
                    <h4 class="font-bold text-brandDark text-lg mb-3">1. Atracción</h4>
                    <p class="text-sm text-brandGray">Utilizamos Google y Facebook Ads para captar tráfico altamente calificado e interesado en el momento exacto.</p>
                </div>

                <!-- Pilar 2 -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-xl relative z-10 hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-14 h-14 bg-brandLight text-white rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-md"><i class="fa-solid fa-heart"></i></div>
                    <h4 class="font-bold text-brandDark text-lg mb-3">2. Conexión</h4>
                    <p class="text-sm text-brandGray">Las Redes Sociales generan confianza, educan a tu cliente potencial y humanizan la voz de tu marca en el mercado.</p>
                </div>

                <!-- Pilar 3 -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-xl relative z-10 hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-14 h-14 bg-purple-500 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-md"><i class="fa-solid fa-filter"></i></div>
                    <h4 class="font-bold text-brandDark text-lg mb-3">3. Conversión</h4>
                    <p class="text-sm text-brandGray">El tráfico se dirige a una Landing Page o sitio optimizado, transformando simples visitantes en clientes y leads.</p>
                </div>

                <!-- Pilar 4 -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-xl relative z-10 hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-14 h-14 bg-green-500 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-md"><i class="fa-solid fa-gears"></i></div>
                    <h4 class="font-bold text-brandDark text-lg mb-3">4. Retención</h4>
                    <p class="text-sm text-brandGray">Sistemas Web y CRMs automatizan el seguimiento, permitiendo retener clientes y facilitar la recompra constante.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SIMULADOR INTERACTIVO -->
    <section id="simulador" class="py-24 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold tracking-widest text-brandLight uppercase block mb-3">Estima tu Impacto</span>
            <h2 class="text-3xl sm:text-4xl font-black text-brandDark mb-4">Simulador de Ecosistema</h2>
            <p class="text-brandGray">Configura tu pauta y componentes para visualizar la proyección de resultados.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <div class="lg:col-span-7 bg-brandBg p-8 rounded-3xl border border-gray-100 shadow-lg">
                <h3 class="text-xl font-bold text-brandDark mb-6 flex items-center gap-2"><i class="fa-solid fa-sliders text-brandLight"></i> Elige tus Componentes</h3>
                <div class="space-y-4">
                    <label class="flex items-center justify-between p-4 bg-white rounded-2xl border-2 cursor-pointer transition-all shadow-sm hover:shadow-md" :class="sim.googleAds ? 'border-brandLight bg-purple-50/20' : 'border-gray-100'">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-lg"><i class="fa-brands fa-google"></i></div>
                            <div><h4 class="font-bold text-brandDark text-sm">Google Ads</h4></div>
                        </div>
                        <input type="checkbox" x-model="sim.googleAds" class="w-5 h-5 accent-brandDark rounded">
                    </label>
                    <div x-show="sim.googleAds" class="pl-14 transition-all">
                        <input type="range" min="3000" max="30000" step="1000" x-model="sim.budgetGoogle" class="w-full h-2 accent-blue-600">
                    </div>

                    <label class="flex items-center justify-between p-4 bg-white rounded-2xl border-2 cursor-pointer transition-all shadow-sm hover:shadow-md" :class="sim.facebookAds ? 'border-brandLight bg-purple-50/20' : 'border-gray-100'">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center text-lg"><i class="fa-brands fa-facebook-f"></i></div>
                            <div><h4 class="font-bold text-brandDark text-sm">Facebook Ads</h4></div>
                        </div>
                        <input type="checkbox" x-model="sim.facebookAds" class="w-5 h-5 accent-brandDark rounded">
                    </label>
                    <div x-show="sim.facebookAds" class="pl-14 transition-all">
                        <input type="range" min="3000" max="30000" step="1000" x-model="sim.budgetFacebook" class="w-full h-2 accent-blue-600">
                    </div>

                    <label class="flex items-center justify-between p-4 bg-white rounded-2xl border-2 cursor-pointer transition-all shadow-sm" :class="sim.web ? 'border-brandLight bg-purple-50/20' : 'border-gray-100'">
                        <span class="font-bold text-sm text-brandDark">Sitio Web / Landing Page</span>
                        <input type="checkbox" x-model="sim.web" class="w-5 h-5 accent-brandDark rounded">
                    </label>
                </div>
            </div>

            <div class="lg:col-span-5 bg-brandDark text-white p-8 rounded-3xl shadow-xl">
                <h3 class="text-xl font-extrabold mb-6 flex items-center gap-2 text-purple-200"><i class="fa-solid fa-chart-line"></i> Proyección Mensual</h3>
                
                <div class="mb-6 p-4 bg-white/10 rounded-2xl border border-white/20">
                    <span class="text-xs text-purple-300 block uppercase font-bold tracking-wider mb-1">Inversión Total Estimada</span>
                    <span class="text-3xl font-black" x-text="formatCurrency((sim.googleAds ? parseInt(sim.budgetGoogle) : 0) + (sim.facebookAds ? parseInt(sim.budgetFacebook) : 0))"></span>
                </div>

                <div class="mb-6 p-4 bg-white/5 rounded-2xl border border-white/10">
                    <span class="text-xs text-purple-300 block uppercase font-bold tracking-wider mb-1">Leads Potenciales</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black" x-text="calculateMetrics().leads > 0 ? calculateMetrics().leads : '---'"></span>
                        <span class="text-xs font-bold" :class="calculateMetrics().leads > 0 ? 'text-green-400' : 'text-purple-300'" 
                              x-text="calculateMetrics().leads > 0 ? 'contactos' : 'activa Ads para generar'"></span>
                    </div>
                </div>

                <div class="mb-6 space-y-3 pt-4 border-t border-white/10">
                    <h4 class="text-sm font-bold text-purple-200 mb-3">Tus Datos para la Consulta</h4>
                  
                  	<div style="display:none;">
                        <input type="text" x-model="leadForm.website" tabindex="-1" autocomplete="off">
                    </div>
                    
                    <input type="text" x-model="leadForm.name" placeholder="Tu Nombre *" 
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-purple-300 focus:outline-none focus:border-brandLight transition">
                    
                    <input type="tel" x-model="leadForm.phone" placeholder="Tu WhatsApp *" 
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-purple-300 focus:outline-none focus:border-brandLight transition">
                    
                    <input type="email" x-model="leadForm.email" placeholder="Tu Correo Electrónico" 
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-purple-300 focus:outline-none focus:border-brandLight transition">
                </div>

                <button @click="generateSimulatorWha()" class="w-full bg-green-500 hover:bg-green-400 text-white font-bold py-4 rounded-2xl transition shadow-lg flex justify-center items-center gap-2">
                    <i class="fab fa-whatsapp text-lg"></i> Solicitar Ecosistema
                </button>
            </div>
        </div>
    </div>
</section>

    <!-- FORMULARIO DE CONTACTO -->
    <section id="contacto" class="py-24 bg-brandDark text-white relative overflow-hidden">
        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-brandLight/10 rounded-full blur-3xl"></div>
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                <div class="lg:col-span-5 text-center lg:text-left">
                    <span class="text-xs font-bold tracking-widest text-brandLight uppercase block mb-3">Hablemos</span>
                    <h2 class="text-3xl sm:text-4xl font-black mb-6">¿Preparado para arrancar?</h2>
                    <p class="text-purple-200/80 mb-10 leading-relaxed">Completa el formulario a la derecha para generar una cotización estructurada directo a nuestro canal de WhatsApp.</p>
                    
                    <div class="space-y-6 max-w-sm mx-auto lg:mx-0">
                        <div class="flex items-center gap-4 p-4 bg-white/5 rounded-2xl border border-white/10">
                            <div class="w-12 h-12 bg-brandLight text-brandDark rounded-xl flex items-center justify-center text-xl shrink-0"><i class="fab fa-whatsapp"></i></div>
                            <div class="text-left">
                                <span class="text-xs text-purple-300 block">Escríbenos por WhatsApp</span>
                                <a href="https://wa.me/525539796157" target="_blank" class="font-bold text-lg hover:text-brandLight transition">Contacto Directo</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <div class="bg-white text-gray-800 p-8 sm:p-10 rounded-3xl shadow-2xl relative">
                        <h3 class="text-2xl font-bold text-brandDark mb-6 text-center sm:text-left">Configurar Proyecto</h3>
                        <form @submit.prevent="sendWhatsAppMessage()" class="space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                              
                              	<div style="display:none;">
                                    <input type="text" x-model="leadForm.website" tabindex="-1" autocomplete="off">
                                </div>
                              
                                <div>
                                    <label class="block text-xs font-bold uppercase text-brandGray mb-2">Tu Nombre *</label>
                                    <input type="text" x-model="form.name" required class="w-full px-4 py-3 bg-brandBg rounded-xl border-2 border-gray-100 focus:border-brandLight focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-brandGray mb-2">Empresa</label>
                                    <input type="text" x-model="form.company" class="w-full px-4 py-3 bg-brandBg rounded-xl border-2 border-gray-100 focus:border-brandLight focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-brandGray mb-2">Servicio Principal</label>
                                <select x-model="form.service" class="w-full px-4 py-3 bg-brandBg rounded-xl border-2 border-gray-100 focus:border-brandLight focus:outline-none font-semibold">
                                    <option>Consulta General o Auditoría</option>
                                    <option>Diseño Web</option>
                                    <option>Google & Facebook Ads</option>
                                    <option>Ecosistema Digital (Redes)</option>
                                    <option>Desarrollo WordPress</option>
                                    <option>Aplicaciones Web</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-brandGray mb-2">Detalles del Proyecto</label>
                                <textarea x-model="form.details" rows="3" class="w-full px-4 py-3 bg-brandBg rounded-xl border-2 border-gray-100 focus:border-brandLight focus:outline-none"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-brandDark hover:bg-brandLight text-white font-bold py-4 rounded-xl transition flex justify-center gap-2 shadow-lg">
                                <i class="fab fa-whatsapp text-xl"></i> Cotizar en WhatsApp
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('appState', () => ({
            // Estado de la interfaz
            mobileMenuOpen: false,
            toast: { show: false, message: '' },
            
            // Formularios
            form: { name: '', company: '', service: 'Consulta General o Auditoría', details: '' },
            leadForm: { name: '', phone: '', email: '' },
            
            // Simulador
            sim: { 
                web: true, 
                googleAds: true, 
                facebookAds: false, 
                socials: false, 
                app: false, 
                budgetGoogle: 5000,
                budgetFacebook: 5000 
            },
            
            // Formato de moneda
            formatCurrency(val) {
                return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 }).format(val);
            },
            
            // Motor de cálculo
            calculateMetrics() {
                let hasTraffic = this.sim.googleAds || this.sim.facebookAds;
                let baseReach = hasTraffic ? 1000 : 0;
                let baseLeads = 0; 
                let conversionMultiplier = 1.0;

                if (this.sim.web) { conversionMultiplier += 1.5; }
                if (this.sim.socials) { baseReach += 5000; conversionMultiplier += 0.5; }
                if (this.sim.app) { conversionMultiplier += 1.2; }
                
                // Google Ads
                if (this.sim.googleAds) {
                    let b = parseFloat(this.sim.budgetGoogle) || 3000;
                    baseReach += b * 2.0; 
                    baseLeads += (b * 0.01); 
                    conversionMultiplier += 1.2;
                }
                
                // Facebook Ads
                if (this.sim.facebookAds) {
                    let b = parseFloat(this.sim.budgetFacebook) || 3000;
                    baseReach += b * 2.5; 
                    baseLeads += (b * 0.006);
                    conversionMultiplier += 0.8;
                }

                // Si no hay tráfico, los leads son 0
                let finalLeads = hasTraffic ? Math.round(baseLeads * (conversionMultiplier * 0.7)) : 0;

                return {
                    reach: baseReach.toLocaleString(),
                    leads: finalLeads,
                    multiplier: conversionMultiplier.toFixed(1) + 'x'
                };
            },

            // Interacciones de UI
            selectService(serviceName) {
                this.form.service = serviceName;
                this.showToast(`Servicio seleccionado: ${serviceName}`);
                
                let contactoEl = document.getElementById('contacto');
                if(contactoEl) contactoEl.scrollIntoView({ behavior: 'smooth' });
            },

            showToast(msg) {
                this.toast.message = msg; 
                this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 4000);
            },

            // Envío del Simulador
            async generateSimulatorWha() {
          
          		// Si el honeypot tiene texto, es un bot. Abortamos silenciosamente.
                if (this.leadForm.website && this.leadForm.website !== '') {
                    console.warn("Actividad sospechosa bloqueada.");
                    return; 
                }
                                       
                // Validación de campos requeridos
                if (!this.leadForm.name || !this.leadForm.phone) {
                    this.showToast("Por favor ingresa tu nombre y WhatsApp para continuar.");
                    return; 
                }

                let m = this.calculateMetrics();
                let inversionTotal = (this.sim.googleAds ? parseInt(this.sim.budgetGoogle) : 0) + (this.sim.facebookAds ? parseInt(this.sim.budgetFacebook) : 0);

                // Payload Atrapalotodo para Laravel
                let payload = {
                    tenant_id: 1,
                    origen: 'Simulador Ecosistema',
                    datos_formulario: {
                        nombre: this.leadForm.name,
                        telefono: this.leadForm.phone,
                        correo: this.leadForm.email,
                        quiere_web: this.sim.web,
                        quiere_socials: this.sim.socials,
                        quiere_app: this.sim.app,
                        inversion_google: this.sim.budgetGoogle,
                        inversion_facebook: this.sim.budgetFacebook,
                        inversion_total_ads: inversionTotal,
                        leads_estimados: m.leads,
                        alcance_estimado: m.reach
                    }
                };

                // Petición silenciosa
                try {
                    let metaToken = document.head.querySelector('meta[name="csrf-token"]');
                    let token = metaToken ? metaToken.content : '';

                    await fetch('/api/capturar-lead', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                } catch (error) {
                    console.error("Error guardando lead en background:", error);
                }

                // Redirección a WhatsApp
                let s = [];
                if (this.sim.web) s.push("Sitio Web de Conversión");
                if (this.sim.googleAds) s.push(`Google Ads (${this.formatCurrency(this.sim.budgetGoogle)})`);
                if (this.sim.facebookAds) s.push(`Facebook Ads (${this.formatCurrency(this.sim.budgetFacebook)})`);
                if (this.sim.socials) s.push("Ecosistema Redes Sociales");
                if (this.sim.app) s.push("Aplicación Personalizada");
                
                let text = `¡Hola Webicónica! Soy ${this.leadForm.name}. Me interesa armar este ecosistema:\n\n💼 Componentes:\n` + s.map(x => `• ${x}`).join("\n") + `\n\n🎯 Proyección:\n• Inversión Ads: ${this.formatCurrency(inversionTotal)}\n• Alcance: ${m.reach}\n• Leads estimadas: ${m.leads}\n\n¿Agendamos una consulta?`;
                
                window.open(`https://wa.me/525539796157?text=${encodeURIComponent(text)}`, '_blank');
            },

            // Envío del Formulario General de Contacto
            async sendWhatsAppMessage() {
                // Payload Atrapalotodo
                let payload = {
                    tenant_id: 1,
                    origen: 'Formulario de Contacto',
                    datos_formulario: {
                        nombre: this.form.name,
                        empresa: this.form.company,
                        servicio_interes: this.form.service,
                        detalles: this.form.details
                    }
                };

                // Petición silenciosa
                try {
                    let metaToken = document.head.querySelector('meta[name="csrf-token"]');
                    let token = metaToken ? metaToken.content : '';

                    await fetch('/api/capturar-lead', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                } catch (error) {
                    console.error("Error guardando lead en background:", error);
                }

                // Redirección a WhatsApp
                let text = `¡Hola Webicónica! Soy ${this.form.name}.\n`;
                if (this.form.company) text += `🏢 Empresa: ${this.form.company}\n`;
                text += `🎯 Me interesa: *${this.form.service}*\n`;
                if (this.form.details) text += `📝 Detalles: ${this.form.details}`;
                
                window.open(`https://wa.me/525539796157?text=${encodeURIComponent(text)}`, '_blank');
            }
        }));
    });
</script>
</body>
</html>