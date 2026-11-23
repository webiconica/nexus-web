<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Webicónica CMS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-brandBg text-gray-800">
        @php
            $currentTenant = \App\Services\TenantContext::current();
            $crmOnly       = $currentTenant?->crm_only_mode ?? false;
            $isMainTenant  = ($currentTenant?->id ?? 1) === 1;
        @endphp

        <div class="min-h-screen bg-gray-50 flex">
            
            <aside class="w-64 bg-white shadow-lg h-screen fixed top-0 left-0 flex flex-col z-50">
                <div class="h-20 flex flex-col items-center justify-center border-b border-gray-100 px-4">
                    <span class="text-2xl font-bold text-brandDark tracking-widest uppercase">Webicónica</span>
                    @if($currentTenant && $currentTenant->id !== 1)
                        <span class="text-[11px] font-semibold text-gray-400 truncate max-w-full">{{ $currentTenant->name }}</span>
                    @endif
                </div>

                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                    <!-- MÓDULOS PROTAGONISTAS: CRM & CHATBOT -->
                    <a href="{{ route('admin.crm.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.crm.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                        <i class="fas fa-comments-dollar w-5 text-center"></i> CRM & Chatbot
                    </a>

                    <a href="{{ route('admin.integrations.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.integrations.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                        <i class="fas fa-plug w-5 text-center"></i> Webhooks & Meta CAPI
                    </a>

                    @if($isMainTenant)
                        <a href="{{ route('admin.tenants.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.tenants.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                            <i class="fas fa-building w-5 text-center"></i> Clientes Multi-Tenant
                        </a>
                    @endif

                    <div class="border-t border-gray-100 my-2"></div>

                    <!-- MÓDULOS WEB CMS (Se ocultan si el cliente solo contrató Chat + CRM) -->
                    @unless($crmOnly)
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                            <i class="fas fa-chart-pie w-5 text-center"></i> Resumen General
                        </a>
                        <a href="{{ route('admin.pages.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.pages.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                            <i class="fas fa-file-lines w-5 text-center"></i> Páginas
                        </a>
                        <a href="{{ route('admin.menu.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.menu.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                            <i class="fas fa-compass w-5 text-center"></i> Menú Principal
                        </a>
                        <a href="{{ route('admin.posts.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.posts.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                            <i class="fas fa-pen-nib w-5 text-center"></i> Blog / Entradas
                        </a>
                        <a href="{{ route('admin.media.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.media.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                            <i class="fas fa-images w-5 text-center"></i> Multimedia
                        </a>
                    @endunless

                    <a href="{{ route('admin.settings.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.settings.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                        <i class="fas fa-cogs w-5 text-center"></i> Configuración Global
                    </a>
                    <a href="{{ route('admin.manual.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300 {{ request()->routeIs('admin.manual.*') ? 'bg-brandDark text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-brandDark' }}">
                        <i class="fas fa-book-open w-5 text-center"></i> Manual del Sistema
                    </a>
                </nav>

                <div class="p-4 border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 text-red-500 font-bold hover:bg-red-50 rounded-xl transition">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </aside>
            
            <div class="flex-1 ml-64 flex flex-col min-h-screen">
                @isset($header)
                    <header class="bg-white shadow-sm border-b border-gray-100 h-20 flex items-center">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 w-full">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1 p-6">
                    @yield('content')
                    @isset($slot)
                        {{ $slot }}
                    @endisset
                </main>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/xml/xml.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/css/css.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/javascript/javascript.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/htmlmixed/htmlmixed.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.7/beautify-html.min.js"></script>

        <script>
        function codeEditor() {
            return {
                editor: null,
                init() {
                    let rawContent = '';
                    try {
                        rawContent = JSON.parse(this.$refs.initialData.textContent);
                    } catch(e) {
                        console.error('Error al desempaquetar el contenido:', e);
                    }
                    
                    this.$refs.textarea.value = rawContent;

                    this.editor = CodeMirror.fromTextArea(this.$refs.textarea, {
                        lineNumbers: true,
                        mode: 'htmlmixed',
                        theme: 'default',
                        tabSize: 4,
                        indentUnit: 4,
                        lineWrapping: false
                    });

                    const parentForm = this.$refs.textarea.form;
                    if (parentForm) {
                        parentForm.addEventListener('submit', () => {
                            this.editor.save();
                        });
                    }

                    this.editor.on('change', (instance) => {
                        this.$refs.textarea.value = instance.getValue();
                    });
                },
                format() {
                    if (!this.editor) return;
                    const unformattedCode = this.editor.getValue();
                    
                    const formattedCode = html_beautify(unformattedCode, {
                        indent_size: 4,
                        indent_char: ' ',
                        max_preserve_newlines: 2,
                        preserve_newlines: true,
                        unformatted: ['script', 'style', 'code', 'span', 'a', 'textarea'],
                        wrap_line_length: 0
                    });
                    
                    const cursor = this.editor.getCursor();
                    this.editor.setValue(formattedCode);
                    this.editor.setCursor(cursor);
                }
            }
        }
        </script>
    </body>
</html>