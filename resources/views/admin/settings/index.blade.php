<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configuración Global del Sitio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ activeTab: 'generales' }">
            
            @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl flex items-center shadow-sm">
                <i class="fa-solid fa-circle-check mr-3 text-xl"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            @endif

            <form id="reset-ai-form" action="{{ route('admin.settings.reset-ai-usage') }}" method="POST" class="hidden">
                @csrf
            </form>

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- NAVEGACIÓN DE PESTAÑAS -->
                <div class="border-b border-gray-200 mb-8">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto">
                        <button type="button" @click.prevent="activeTab = 'generales'" :class="activeTab === 'generales' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                            <i class="fa-solid fa-building mr-1"></i> Identidad
                        </button>
                        <button type="button" @click.prevent="activeTab = 'diseno_contacto'" :class="activeTab === 'diseno_contacto' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                            <i class="fa-solid fa-palette mr-1"></i> Diseño, Contacto y Turnstile
                        </button>
                        <button type="button" @click.prevent="activeTab = 'branding'" :class="activeTab === 'branding' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                            <i class="fa-solid fa-image mr-1"></i> Logotipos
                        </button>
                        <button type="button" @click.prevent="activeTab = 'scripts'" :class="activeTab === 'scripts' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                            <i class="fa-solid fa-code mr-1"></i> Scripts
                        </button>
                        <button type="button" @click.prevent="activeTab = 'ia'" :class="activeTab === 'ia' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Motor IA & Chatbot
                        </button>
                        <button type="button" @click.prevent="activeTab = 'prompts'" :class="activeTab === 'prompts' ? 'border-brandDark text-brandDark' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition-colors">
                            <i class="fa-solid fa-robot mr-1"></i> Prompts IA
                        </button>
                    </nav>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">
                    
                    <!-- PESTAÑA 1: IDENTIDAD PRINCIPAL -->
                    <div x-show="activeTab === 'generales'" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Identidad Principal</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Nombre del Sitio *</label>
                                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? '' }}" required class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Descripción principal del sitio web *</label>
                                <textarea name="site_description" rows="3" required class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition">{{ $settings['site_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        <i class="fa-solid fa-envelope mr-1 text-gray-400"></i> Correo de Destino para Formularios
                                    </label>
                                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" placeholder="Ej. ventas@tuempresa.com" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition">
                                    <p class="text-[11px] text-gray-400 mt-1">Todos los leads capturados se enviarán automáticamente a este correo.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        <i class="fa-brands fa-whatsapp mr-1 text-emerald-500"></i> WhatsApp Principal del CRM (Botón flotante y Leads)
                                    </label>
                                    <input type="text" name="whatsapp_number" id="main_whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="Ej. 5215512345678" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition">
                                    <p class="text-[11px] text-gray-400 mt-1">Solo números con código de país, sin espacios ni símbolo +.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 2: DISEÑO TAILWIND, DIRECTORIO DINÁMICO Y TURNSTILE -->
                    <div x-show="activeTab === 'diseno_contacto'" style="display: none;" x-data="brandAndContactSettings()" class="space-y-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        
                        <!-- 1. GENERADOR DE TAILWIND.CONFIG CON BOTÓN MÁGICO -->
                        <div class="bg-gray-50 p-6 rounded-3xl border border-gray-200 space-y-6">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-widest text-indigo-950 flex items-center gap-2">
                                        <i class="fa-solid fa-palette text-indigo-600"></i> Sistema de Diseño (Tailwind Config & Fuentes)
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">Configura manualmente los colores y fuente de tu marca o usa el botón mágico según el giro del negocio.</p>
                                </div>

                                <!-- Botón Mágico por Giro de Negocio -->
                                <div class="p-5 bg-white rounded-3xl border border-gray-200 shadow-sm w-full md:w-auto md:min-w-[460px]">
                                    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                                        <div class="flex-1">
                                            <select x-model="giro" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-indigo-500 font-semibold text-xs transition-colors">
                                                <option value="">-- Elige un Giro de Negocio --</option>
                                                <option value="Comida / Restaurante / Dark Kitchen">🍔 Comida / Restaurante / Dark Kitchen</option>
                                                <option value="Grupo Musical / Entretenimiento / Eventos">🎸 Grupo Musical / Entretenimiento / Eventos</option>
                                                <option value="Despacho Contable / Corporativo / Finanzas">📊 Despacho Contable / Corporativo / Finanzas</option>
                                                <option value="Clínica Médica / Dental / Bienestar">🩺 Clínica Médica / Dental / Bienestar</option>
                                                <option value="Inmobiliaria / Arquitectura / Construcción">🏢 Inmobiliaria / Arquitectura / Construcción</option>
                                                <option value="Firma Legal / Abogados / Notaría">⚖️ Firma Legal / Abogados / Notaría</option>
                                                <option value="Agencia Digital / SaaS / Tecnología">🚀 Agencia Digital / SaaS / Tecnología</option>
                                                <option value="Gimnasio / Crossfit / Centro Deportivo">🏋️ Gimnasio / Crossfit / Centro Deportivo</option>
                                                <option value="otro">✨ Otro (Describir mi negocio)</option>
                                            </select>
                                        </div>

                                        <button type="button" @click.prevent="generarConfiguracionIA()" :disabled="isGenerating" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-md whitespace-nowrap disabled:opacity-60 disabled:cursor-not-allowed">
                                            <span x-show="!isGenerating" class="flex items-center gap-1.5"><i class="fa-solid fa-wand-magic-sparkles"></i> Botón Mágico</span>
                                            <span x-show="isGenerating" style="display: none;" class="flex items-center gap-1.5"><i class="fa-solid fa-spinner fa-spin"></i> Generando...</span>
                                        </button>
                                    </div>

                                    <!-- Campo Condicional de Texto Libre -->
                                    <div x-show="giro === 'otro'"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         style="display: none;"
                                         class="mt-3 p-3.5 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                                        <label class="block text-[11px] font-bold uppercase text-indigo-950 mb-1.5">
                                            <i class="fa-solid fa-robot mr-1 text-indigo-600"></i> Describe tu negocio y colores para la IA
                                        </label>
                                        <textarea x-model="giroPersonalizado"
                                                  rows="2"
                                                  placeholder="Ej. Restaurante de sushi con colores vibrantes, fondo negro, acentos en morado y naranja..."
                                                  class="w-full px-3 py-2 bg-white rounded-xl border border-gray-200 focus:outline-none focus:border-indigo-500 text-xs"></textarea>
                                        <p class="text-[11px] text-gray-500 mt-1.5">La IA leerá esta descripción para generar una paleta de colores y tipografía que encajen con tu marca.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Selectores Manuales de Color y Fuente -->
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                                <div class="bg-white p-3 rounded-2xl border border-gray-200">
                                    <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">brandDark (Principal)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" x-model="theme.brandDark" @input="buildTailwindScript()" class="h-9 w-10 rounded cursor-pointer border-0 p-0">
                                        <input type="text" x-model="theme.brandDark" @input="buildTailwindScript()" class="w-full text-xs font-mono border-gray-200 rounded-lg py-1.5 px-2">
                                    </div>
                                </div>

                                <div class="bg-white p-3 rounded-2xl border border-gray-200">
                                    <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">brandLight (Acento)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" x-model="theme.brandLight" @input="buildTailwindScript()" class="h-9 w-10 rounded cursor-pointer border-0 p-0">
                                        <input type="text" x-model="theme.brandLight" @input="buildTailwindScript()" class="w-full text-xs font-mono border-gray-200 rounded-lg py-1.5 px-2">
                                    </div>
                                </div>

                                <div class="bg-white p-3 rounded-2xl border border-gray-200">
                                    <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">brandGray (Texto Sec.)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" x-model="theme.brandGray" @input="buildTailwindScript()" class="h-9 w-10 rounded cursor-pointer border-0 p-0">
                                        <input type="text" x-model="theme.brandGray" @input="buildTailwindScript()" class="w-full text-xs font-mono border-gray-200 rounded-lg py-1.5 px-2">
                                    </div>
                                </div>

                                <div class="bg-white p-3 rounded-2xl border border-gray-200">
                                    <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">brandBg (Fondo Base)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" x-model="theme.brandBg" @input="buildTailwindScript()" class="h-9 w-10 rounded cursor-pointer border-0 p-0">
                                        <input type="text" x-model="theme.brandBg" @input="buildTailwindScript()" class="w-full text-xs font-mono border-gray-200 rounded-lg py-1.5 px-2">
                                    </div>
                                </div>

                                <div class="col-span-2 sm:col-span-1 bg-white p-3 rounded-2xl border border-gray-200">
                                    <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">Tipografía Principal</label>
                                    <select x-model="theme.font" @change="buildTailwindScript()" class="w-full text-xs font-semibold border-gray-200 rounded-lg py-2 px-2">
                                        <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                                        <option value="Inter">Inter</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Poppins">Poppins</option>
                                        <option value="Space Grotesk">Space Grotesk</option>
                                        <option value="Playfair Display">Playfair Display</option>
                                        <option value="Oswald">Oswald</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Código Generado -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs font-bold uppercase text-gray-600">
                                        Código <code>tailwind.config</code> (Se inyecta en el sitio y guía a la IA)
                                    </label>
                                    <span class="text-[11px] text-indigo-600 font-semibold">Puedes editarlo manualmente o con los controles superiores</span>
                                </div>
                                <textarea name="tailwind_config_script" x-model="tailwindScript" rows="12"
                                          class="w-full font-mono text-xs bg-slate-950 text-emerald-400 p-4 rounded-2xl border border-slate-800 focus:outline-none focus:border-indigo-500"></textarea>
                                <input type="hidden" name="tailwind_theme_json" :value="JSON.stringify(theme)">
                            </div>
                        </div>

                        <!-- 2. DIRECTORIO DINÁMICO DE TELÉFONOS / WHATSAPP Y REDES SOCIALES -->
                        <div class="bg-gray-50 p-6 rounded-3xl border border-gray-200 space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h3 class="text-sm font-black uppercase tracking-widest text-indigo-950 flex items-center gap-2">
                                    <i class="fa-solid fa-address-book text-indigo-600"></i> Directorio Dinámico de Contacto y Redes (Contexto para IA)
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">Agrega todos los teléfonos, WhatsApps y redes sociales que necesites. Se guardan en un solo campo y la IA los usará automáticamente en módulos de contacto, headers, footers o CTAs.</p>
                            </div>

                            <input type="hidden" name="contact_directory_json" :value="JSON.stringify({ phones: phones, socials: socials })">

                            <!-- A. Lista dinámica de Teléfonos y WhatsApp -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <label class="text-xs font-bold uppercase text-gray-700">Teléfonos y Números de WhatsApp</label>
                                    <button type="button" @click="addPhone()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition flex items-center gap-1">
                                        <i class="fa-solid fa-plus"></i> Agregar Número
                                    </button>
                                </div>

                                <template x-for="(item, index) in phones" :key="index">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-white p-3.5 rounded-2xl border border-gray-200 shadow-sm">
                                        <div class="md:col-span-3">
                                            <input type="text" x-model="item.label" placeholder="Etiqueta (ej. Ventas, Matriz)"
                                                   class="w-full text-xs rounded-xl border-gray-300 py-2 px-3">
                                        </div>
                                        <div class="md:col-span-4">
                                            <input type="text" x-model="item.number" @input="syncMainWhatsapp()" placeholder="Número (ej. +52 55 3979 6157)"
                                                   class="w-full text-xs font-mono rounded-xl border-gray-300 py-2 px-3">
                                        </div>
                                        <div class="md:col-span-4 flex items-center gap-4 px-2">
                                            <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 cursor-pointer">
                                                <input type="checkbox" x-model="item.is_whatsapp" @change="syncMainWhatsapp()" class="rounded text-green-600 focus:ring-green-500">
                                                <i class="fa-brands fa-whatsapp text-green-600 text-sm"></i> Tiene WhatsApp
                                            </label>
                                            <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 cursor-pointer">
                                                <input type="checkbox" x-model="item.is_call" class="rounded text-indigo-600 focus:ring-indigo-500">
                                                <i class="fa-solid fa-phone text-indigo-600 text-xs"></i> Llamadas
                                            </label>
                                        </div>
                                        <div class="md:col-span-1 flex justify-end">
                                            <button type="button" @click="removePhone(index)" class="text-red-500 hover:bg-red-50 p-2 rounded-xl transition">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- B. Lista dinámica de Redes Sociales -->
                            <div class="space-y-3 pt-4 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <label class="text-xs font-bold uppercase text-gray-700">Enlaces de Redes Sociales</label>
                                    <button type="button" @click="addSocial()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition flex items-center gap-1">
                                        <i class="fa-solid fa-plus"></i> Agregar Red Social
                                    </button>
                                </div>

                                <template x-for="(soc, index) in socials" :key="index">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-white p-3.5 rounded-2xl border border-gray-200 shadow-sm">
                                        <div class="md:col-span-4">
                                            <select x-model="soc.network" class="w-full text-xs font-semibold rounded-xl border-gray-300 py-2 px-3">
                                                <option value="Facebook">Facebook (fa-facebook-f)</option>
                                                <option value="Instagram">Instagram (fa-instagram)</option>
                                                <option value="TikTok">TikTok (fa-tiktok)</option>
                                                <option value="YouTube">YouTube (fa-youtube)</option>
                                                <option value="LinkedIn">LinkedIn (fa-linkedin-in)</option>
                                                <option value="X / Twitter">X / Twitter (fa-x-twitter)</option>
                                                <option value="Spotify">Spotify (fa-spotify)</option>
                                                <option value="Google Maps">Google Maps / Ubicación (fa-location-dot)</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-7">
                                            <input type="url" x-model="soc.url" placeholder="https://www.instagram.com/tu_marca"
                                                   class="w-full text-xs font-mono rounded-xl border-gray-300 py-2 px-3">
                                        </div>
                                        <div class="md:col-span-1 flex justify-end">
                                            <button type="button" @click="removeSocial(index)" class="text-red-500 hover:bg-red-50 p-2 rounded-xl transition">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 3. WIDGET DE CLOUDFLARE TURNSTILE PARA FORMULARIOS DE LA IA -->
                        <div class="bg-gray-50 p-6 rounded-3xl border border-gray-200 space-y-4">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-gray-200 pb-3">
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-widest text-indigo-950 flex items-center gap-2">
                                        <i class="fa-solid fa-shield-halved text-indigo-600"></i> Código de Turnstile para Formularios Generados con IA
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">Si este campo tiene código, cada vez que pidas un módulo con formulario, la IA insertará este bloque antes del botón de envío.</p>
                                </div>
                                <button type="button"
                                        @click='turnstileCode = `<div class="cf-turnstile my-4" data-sitekey="{{ env('TURNSTILE_SITE_KEY', 'TU_SITE_KEY') }}"></div>`'
                                        class="bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-bold px-3 py-2 rounded-xl transition whitespace-nowrap">
                                    <i class="fa-solid fa-bolt text-amber-500"></i> Autocompletar con .env
                                </button>
                            </div>

                            <textarea name="turnstile_widget_code" x-model="turnstileCode" rows="2"
                                      placeholder='<div class="cf-turnstile my-4" data-sitekey="0x4AAAAAA..."></div>'
                                      class="w-full font-mono text-xs bg-white p-3 rounded-xl border border-gray-300 focus:outline-none focus:border-indigo-500"></textarea>
                        </div>
                    </div>

                    <!-- PESTAÑA 3: LOGOTIPOS -->
                    <div x-show="activeTab === 'branding'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Identidad Visual</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Logo del Header</label>
                                @if(isset($settings['header_logo']))
                                    <div class="mb-4 bg-gray-50 p-4 rounded-xl border border-gray-200 inline-block shadow-inner">
                                        <img src="{{ asset('storage/' . $settings['header_logo']) }}" class="h-10 object-contain">
                                    </div>
                                @endif
                                <input type="file" name="header_logo" accept="image/*" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brandDark file:text-white hover:file:bg-brandLight transition">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Favicon</label>
                                @if(isset($settings['favicon']))
                                    <div class="mb-4 bg-gray-50 p-4 rounded-xl border border-gray-200 inline-block shadow-inner">
                                        <img src="{{ asset('storage/' . $settings['favicon']) }}" class="h-8 w-8">
                                    </div>
                                @endif
                                <input type="file" name="favicon" accept=".ico,.png" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brandDark file:text-white hover:file:bg-brandLight transition">
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 4: SCRIPTS -->
                    <div x-show="activeTab === 'scripts'" style="display: none;" class="space-y-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Inyección de Códigos</h3>
                        
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Scripts en el <HEAD></label>
                            <textarea name="head_scripts" rows="5" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-sm text-gray-700 leading-relaxed">{{ $settings['head_scripts'] ?? '' }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Scripts al inicio del <BODY></label>
                            <textarea name="body_start_scripts" rows="5" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-sm text-gray-700 leading-relaxed">{{ $settings['body_start_scripts'] ?? '' }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Scripts al final del <BODY></label>
                            <textarea name="body_end_scripts" rows="5" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-sm text-gray-700 leading-relaxed">{{ $settings['body_end_scripts'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <!-- PESTAÑA 5: MOTOR IA Y CHATBOT -->
                    <div x-show="activeTab === 'ia'" style="display: none;"
                         x-data="{
                            testing: false,
                            testResult: null,
                            apiKeyInput: '{{ $settings['gemini_api_key'] ?? '' }}',
                            async testKey() {
                                this.testing = true;
                                this.testResult = null;
                                try {
                                    const res = await fetch('{{ route('admin.settings.test-ai') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ api_key: this.apiKeyInput })
                                    });
                                    this.testResult = await res.json();
                                } catch (e) {
                                    this.testResult = { ok: false, message: 'No se pudo conectar con el servidor.' };
                                } finally {
                                    this.testing = false;
                                }
                            }
                         }">
                        <div class="space-y-8">

                            <!-- 1. INTERRUPTOR MAESTRO Y MONITOR DE GASTO -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                <!-- Control Maestro y API Key -->
                                <div class="lg:col-span-7 bg-gray-50 p-6 rounded-2xl border border-gray-200 space-y-5">
                                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                        <div>
                                            <h3 class="text-base font-bold text-gray-900">Motor de IA (Google Gemini)</h3>
                                            <p class="text-xs text-gray-500">Encendido: constructor modular con IA. Apagado: editor clásico HTML.</p>
                                        </div>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="hidden" name="ai_enabled" value="0">
                                            <input type="checkbox" name="ai_enabled" value="1" class="sr-only peer"
                                                   {{ ($settings['ai_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                            <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Gemini API Key</label>
                                        <div class="flex gap-2">
                                            <input type="password" name="gemini_api_key" x-model="apiKeyInput"
                                                   placeholder="AIzaSy..."
                                                   class="flex-1 rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">
                                            <button type="button" @click="testKey()" :disabled="testing"
                                                    class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl transition shrink-0">
                                                <span x-show="!testing"><i class="fa-solid fa-plug mr-1"></i> Probar Key</span>
                                                <span x-show="testing">Probando...</span>
                                            </button>
                                        </div>

                                        <template x-if="testResult">
                                            <div class="mt-2 p-3 rounded-xl text-xs font-semibold"
                                                 :class="testResult.ok ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'"
                                                 x-text="testResult.message">
                                            </div>
                                        </template>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Modelo por Defecto (Editor)</label>
                                            <select name="ai_default_model" class="w-full rounded-xl border-gray-300 text-sm">
                                                @foreach($aiModels as $modelKey => $modelInfo)
                                                    <option value="{{ $modelKey }}" {{ ($settings['ai_default_model'] ?? 'gemini-3.8-flash') === $modelKey ? 'selected' : '' }}>
                                                        {{ $modelInfo['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Tipo de Cambio (USD → MXN)</label>
                                            <input type="number" step="0.01" name="ai_usd_to_mxn"
                                                   value="{{ $settings['ai_usd_to_mxn'] ?? '20.00' }}"
                                                   class="w-full rounded-xl border-gray-300 text-sm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tarjeta de Estimación de Gasto y Saldo -->
                                <div class="lg:col-span-5 bg-slate-900 text-white p-6 rounded-2xl shadow-lg flex flex-col justify-between space-y-5">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-300">Control de Saldo Estimado</span>
                                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/10 text-gray-300">
                                                {{ number_format($aiSummary['total_requests']) }} peticiones
                                            </span>
                                        </div>

                                        <div class="flex items-baseline justify-between mb-2">
                                            <div>
                                                <span class="text-3xl font-black text-white">${{ number_format($aiSummary['remaining_mxn'], 2) }}</span>
                                                <span class="text-xs text-gray-400">MXN disponibles</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs text-gray-400 block">Gastado: ${{ number_format($aiSummary['spent_mxn'], 2) }} MXN</span>
                                                <span class="text-[11px] text-gray-500">({{ number_format($aiSummary['total_tokens']) }} tokens)</span>
                                            </div>
                                        </div>

                                        <div class="w-full bg-white/10 rounded-full h-2.5 overflow-hidden mb-3">
                                            <div class="h-2.5 rounded-full transition-all duration-500 {{ $aiSummary['low_balance'] ? 'bg-red-500' : 'bg-emerald-400' }}"
                                                 style="width: {{ $aiSummary['percent_used'] }}%"></div>
                                        </div>

                                        @if($aiSummary['low_balance'])
                                            <div class="p-3 rounded-xl bg-red-500/20 border border-red-400/30 text-xs text-red-200 flex items-center gap-2">
                                                <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
                                                <span>Saldo estimado bajo (menos del 15%). Considera recargar crédito en Google AI Studio.</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-4 border-t border-white/10 flex items-end justify-between gap-3">
                                        <div class="flex-1">
                                            <label class="block text-[11px] font-bold uppercase text-gray-400 mb-1">Saldo Cargado (MXN)</label>
                                            <input type="number" step="1" name="ai_budget_mxn"
                                                   value="{{ $settings['ai_budget_mxn'] ?? '100' }}"
                                                   class="w-full rounded-xl bg-white/10 border-white/20 text-white text-sm focus:border-indigo-400">
                                        </div>

                                        <button type="button"
                                                onclick="if(confirm('¿Reiniciar el contador de gasto a $0.00 MXN? Haz esto cuando agregues nuevo saldo en Google.')) document.getElementById('reset-ai-form').submit();"
                                                class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-xs font-bold rounded-xl transition text-gray-200">
                                            <i class="fa-solid fa-rotate-left mr-1"></i> Reiniciar Gasto
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. CONFIGURACIÓN DEL CHATBOT PÚBLICO (ÚNICO BLOQUE ACTIVO) -->
                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 space-y-5">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                            <i class="fa-solid fa-robot text-indigo-600"></i> Asistente Virtual / Chatbot del Sitio Público
                                        </h3>
                                        <p class="text-xs text-gray-500">Muestra un widget de atención con IA en el frontend capaz de responder dudas y capturar leads directo al CRM.</p>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="chatbot_enabled" value="0">
                                        <input type="checkbox" name="chatbot_enabled" value="1" class="sr-only peer"
                                               {{ ($settings['chatbot_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                        <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Nombre del Asistente</label>
                                        <input type="text" name="chatbot_name"
                                               value="{{ $settings['chatbot_name'] ?? 'Asistente Virtual' }}"
                                               placeholder="Ej. Sofía - Asesora Web"
                                               class="w-full rounded-xl border-gray-300 text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Modelo del Chatbot</label>
                                        <select name="chatbot_model" class="w-full rounded-xl border-gray-300 text-sm">
                                            @foreach($aiModels as $modelKey => $modelInfo)
                                                <option value="{{ $modelKey }}" {{ ($settings['chatbot_model'] ?? 'gemini-3.5-flash-lite') === $modelKey ? 'selected' : '' }}>
                                                    {{ $modelInfo['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Mensaje de Bienvenida</label>
                                        <input type="text" name="chatbot_welcome"
                                               value="{{ $settings['chatbot_welcome'] ?? '¡Hola! 👋 ¿En qué puedo ayudarte hoy?' }}"
                                               class="w-full rounded-xl border-gray-300 text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Base de Conocimiento e Instrucciones del Chatbot</label>
                                    <textarea name="chatbot_context" rows="4"
                                              placeholder="Describe aquí los servicios de la empresa, horarios, ubicación, tono de atención y reglas para pedir datos de contacto..."
                                              class="w-full rounded-xl border-gray-300 text-sm">{{ $settings['chatbot_context'] ?? 'Eres el asistente virtual oficial del sitio web. Responde de forma amable, breve y profesional. Si el usuario muestra interés en cotizar o contratar un servicio, pídele su nombre y número de WhatsApp para que un asesor lo contacte.' }}</textarea>
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        <i class="fa-solid fa-circle-info text-indigo-500"></i> Además de este texto, el chatbot leerá automáticamente tus Páginas publicadas, Blog y teléfonos oficiales.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- PESTAÑA 6: PROMPTS IA -->
                    <div x-show="activeTab === 'prompts'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        
                        <div class="border-b pb-2">
                            <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark">Instrucciones Base para IA</h3>
                            <p class="text-xs text-gray-500 mt-1">Estas reglas base se combinan automáticamente con tu Tailwind Config, Directorio de Contacto y Turnstile cada vez que generas un módulo o página.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-8">
                            
                            <div x-data="{ copyText() { navigator.clipboard.writeText($refs.promptPages.value); alert('¡Prompt de Páginas copiado al portapapeles!'); } }">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-xs font-bold uppercase text-gray-500">Prompt Base para Páginas y Módulos</label>
                                    <button type="button" @click="copyText()" class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-1.5 px-3 rounded-lg transition shadow-sm border border-gray-200">
                                        <i class="fa-regular fa-copy"></i> Copiar Prompt
                                    </button>
                                </div>
                                <textarea name="ai_prompt_pages" x-ref="promptPages" rows="12" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-xs text-gray-600 leading-relaxed">{{ $settings['ai_prompt_pages'] ?? "Actúa como un diseñador UI/UX senior y desarrollador web experto. Crea secciones modernas, responsivas y de alto impacto visual usando Tailwind CSS, Alpine.js 3.x e íconos FontAwesome 6.

REGLAS DE DISEÑO:
- Utiliza los colores personalizados definidos en tailwind.config (bg-brandDark, text-brandDark, bg-brandLight, text-brandLight, text-brandGray, bg-brandBg) y la fuente font-sans.
- Usa jerarquía tipográfica clara, espaciado generoso (py-16 md:py-24), bordes redondeados (rounded-2xl / rounded-3xl) y sombras suaves.
- Puedes usar la clase 'glow-effect' para aplicar sombra luminosa de acento." }}</textarea>
                            </div>

                            <div x-data="{ copyText() { navigator.clipboard.writeText($refs.promptPosts.value); alert('¡Prompt de Blog copiado al portapapeles!'); } }">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-xs font-bold uppercase text-gray-500">Prompt para Entradas de Blog</label>
                                    <button type="button" @click="copyText()" class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-1.5 px-3 rounded-lg transition shadow-sm border border-gray-200">
                                        <i class="fa-regular fa-copy"></i> Copiar Prompt
                                    </button>
                                </div>
                                <textarea name="ai_prompt_posts" x-ref="promptPosts" rows="10" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight focus:ring-1 focus:ring-brandLight transition font-mono text-xs text-gray-600 leading-relaxed">{{ $settings['ai_prompt_posts'] ?? "Actúa como un redactor SEO experto y estructurador de contenido web. Escribe una entrada de blog detallada y persuasiva.

REQUISITOS DEL FORMATO:
- Entrega el resultado directamente en código HTML limpio con Tailwind CSS.
- Utiliza etiquetas semánticas (<h2>, <h3>, <p>, <ul>, <strong>).
- Para acentos visuales utiliza text-brandLight y para títulos text-brandDark." }}</textarea>
                            </div>

                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-brandDark hover:bg-brandLight text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Guardar Configuraciones
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- DATOS INICIALES DESDE BASE DE DATOS -->
    <script type="application/json" id="saved-theme-json">{!! $settings['tailwind_theme_json'] ?? 'null' !!}</script>
    <script type="application/json" id="saved-tailwind-script">{!! json_encode($settings['tailwind_config_script'] ?? '') !!}</script>
    <script type="application/json" id="saved-contact-json">{!! $settings['contact_directory_json'] ?? 'null' !!}</script>
    <script type="application/json" id="saved-turnstile-code">{!! json_encode($settings['turnstile_widget_code'] ?? '') !!}</script>

    <script>
    function brandAndContactSettings() {
        const savedTheme = JSON.parse(document.getElementById('saved-theme-json').textContent || 'null');
        const savedScript = JSON.parse(document.getElementById('saved-tailwind-script').textContent || '""');
        const savedContact = JSON.parse(document.getElementById('saved-contact-json').textContent || 'null');
        const savedTurnstile = JSON.parse(document.getElementById('saved-turnstile-code').textContent || '""');

        return {
            giro: '',
            giroPersonalizado: '',
            isGenerating: false,
            theme: savedTheme || {
                brandDark: '#1A0B5E',
                brandLight: '#9484F7',
                brandGray: '#606060',
                brandBg: '#FBFBFE',
                font: 'Plus Jakarta Sans'
            },
            tailwindScript: savedScript,
            phones: (savedContact && savedContact.phones && savedContact.phones.length) ? savedContact.phones : [
                { label: 'Ventas / Principal', number: '{{ $settings['whatsapp_number'] ?? '' }}', is_whatsapp: true, is_call: true }
            ],
            socials: (savedContact && savedContact.socials && savedContact.socials.length) ? savedContact.socials : [
                { network: 'Facebook', url: '' },
                { network: 'Instagram', url: '' }
            ],
            turnstileCode: savedTurnstile,

            init() {
                if (!this.tailwindScript || this.tailwindScript.trim() === '') {
                    this.buildTailwindScript();
                }
            },

            async generarConfiguracionIA() {
                const contextoNegocio = this.giro === 'otro' ? this.giroPersonalizado.trim() : this.giro;
                if (!contextoNegocio) {
                    alert('Por favor selecciona un giro de la lista o escribe la descripción de tu negocio.');
                    return;
                }

                this.isGenerating = true;

                try {
                    const res = await fetch('{{ route('admin.settings.generate-theme') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ contexto: contextoNegocio })
                    });

                    const json = await res.json();

                    if (res.ok && json.status === 'success') {
                        this.theme.brandDark = json.data.brandDark;
                        this.theme.brandLight = json.data.brandLight;
                        this.theme.brandGray = json.data.brandGray;
                        this.theme.brandBg = json.data.brandBg;
                        this.theme.font = json.data.font;

                        this.buildTailwindScript();
                    } else {
                        alert(json.message || 'No se pudo generar la paleta con IA.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Ocurrió un error de conexión con el servidor.');
                } finally {
                    this.isGenerating = false;
                }
            },

            buildTailwindScript() {
                const fontUrlName = this.theme.font.replace(/ /g, '+');
                this.tailwindScript = `<link href="https://fonts.googleapis.com/css2?family=${fontUrlName}:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">\n<script>\n    tailwind.config = {\n        theme: {\n            extend: {\n                fontFamily: {\n                    sans: ['"${this.theme.font}"', 'Inter', 'sans-serif'],\n                },\n                colors: {\n                    brandDark: '${this.theme.brandDark}',\n                    brandLight: '${this.theme.brandLight}',\n                    brandGray: '${this.theme.brandGray}',\n                    brandBg: '${this.theme.brandBg}',\n                }\n            }\n        }\n    }\n<\/script>`;
            },

            syncMainWhatsapp() {
                const firstWa = this.phones.find(p => p.is_whatsapp && p.number.trim() !== '');
                const mainInput = document.getElementById('main_whatsapp_number');
                if (firstWa && mainInput) {
                    mainInput.value = firstWa.number.replace(/[^0-9]/g, '');
                }
            },

            addPhone() {
                this.phones.push({ label: '', number: '', is_whatsapp: true, is_call: true });
            },
            removePhone(index) {
                this.phones.splice(index, 1);
                this.syncMainWhatsapp();
            },
            addSocial() {
                this.socials.push({ network: 'Instagram', url: '' });
            },
            removeSocial(index) {
                this.socials.splice(index, 1);
            }
        }
    }
    </script>
</x-app-layout>