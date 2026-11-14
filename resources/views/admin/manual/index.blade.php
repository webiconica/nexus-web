<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center ml-64">
            <h2 class="font-bold text-2xl text-brandDark uppercase tracking-widest leading-tight">
                {{ __('Manual Operativo y Técnico del CMS') }}
            </h2>
            <span class="bg-purple-100 text-brandDark text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">
                Versión Marca Blanca
            </span>
        </div>
    </x-slot>

    @verbatim
    <div class="py-8 ml-64" x-data="{ 
        section: 'arquitectura',
        copyCode(text) {
            navigator.clipboard.writeText(text);
            alert('Código copiado al portapapeles');
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- ÍNDICE LATERAL DEL MANUAL -->
                <div class="lg:col-span-3 bg-white p-4 rounded-3xl shadow-sm border border-gray-100 sticky top-6 space-y-1">
                    <p class="px-4 py-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">Contenido del Manual</p>
                    
                    <button type="button" @click="section = 'arquitectura'" :class="section === 'arquitectura' ? 'bg-brandDark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3">
                        <i class="fa-solid fa-layer-group w-4 text-center"></i> 1. Flujo General e Inicio
                    </button>
                    <button type="button" @click="section = 'paginas'" :class="section === 'paginas' ? 'bg-brandDark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3">
                        <i class="fa-solid fa-file-code w-4 text-center"></i> 2. Páginas y CodeMirror
                    </button>
                    <button type="button" @click="section = 'formularios'" :class="section === 'formularios' ? 'bg-brandDark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3">
                        <i class="fa-brands fa-whatsapp w-4 text-center"></i> 3. Formularios, CRM y Correo
                    </button>
                    <button type="button" @click="section = 'menu'" :class="section === 'menu' ? 'bg-brandDark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3">
                        <i class="fa-solid fa-compass w-4 text-center"></i> 4. Constructor de Menú
                    </button>
                    <button type="button" @click="section = 'multimedia'" :class="section === 'multimedia' ? 'bg-brandDark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3">
                        <i class="fa-solid fa-photo-film w-4 text-center"></i> 5. Biblioteca Multimedia
                    </button>
                    <button type="button" @click="section = 'blog'" :class="section === 'blog' ? 'bg-brandDark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3">
                        <i class="fa-solid fa-pen-nib w-4 text-center"></i> 6. Blog y Sitemap SEO
                    </button>
                    <button type="button" @click="section = 'settings'" :class="section === 'settings' ? 'bg-brandDark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3">
                        <i class="fa-solid fa-sliders w-4 text-center"></i> 7. Configuración y Turnstile
                    </button>
                    <button type="button" @click="section = 'comandos'" :class="section === 'comandos' ? 'bg-brandDark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3">
                        <i class="fa-solid fa-terminal w-4 text-center"></i> 8. Comandos y Despliegue
                    </button>
                </div>

                <!-- CONTENIDO DETALLADO -->
                <div class="lg:col-span-9 bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">

                    <!-- SECCIÓN 1: ARQUITECTURA E INICIO -->
                    <div x-show="section === 'arquitectura'" class="space-y-6">
                        <div class="border-b pb-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-brandLight">Módulo 1</span>
                            <h3 class="text-2xl font-black text-brandDark mt-1">Arquitectura General e Inicio Híbrido</h3>
                        </div>

                        <p class="text-gray-600 text-sm leading-relaxed">
                            Este CMS funciona bajo una arquitectura <strong>Marca Blanca</strong>. El archivo maestro <code>resources/views/layouts/web.blade.php</code> envuelve automáticamente todas las páginas con el encabezado, menú dinámico, pie de página, etiquetas SEO, botón flotante de WhatsApp y scripts globales.
                        </p>

                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 space-y-3">
                            <h4 class="font-bold text-brandDark text-sm uppercase">¿Cómo funciona la Página de Inicio (Slug: <code>inicio</code>)?</h4>
                            <ul class="list-disc pl-5 text-sm text-gray-600 space-y-2">
                                <li><strong>Modo Plantilla Base (Fallback):</strong> Si en el módulo de <em>Páginas</em> el registro con slug <code>inicio</code> tiene su contenido vacío, el sistema muestra el diseño corporativo genérico de respaldo alojado en <code>welcome.blade.php</code>.</li>
                                <li><strong>Modo Personalizado (CodeMirror):</strong> En cuanto pegas código HTML dentro de la página con slug <code>inicio</code> y guardas, la portada reemplaza automáticamente la plantilla base por tu código personalizado.</li>
                                <li><strong>Redirección SEO 301:</strong> Si un visitante intenta entrar a <code>tudominio.com/inicio</code>, el sistema lo redirige automáticamente a la raíz <code>/</code> para evitar contenido duplicado en Google.</li>
                            </ul>
                        </div>

                        <div class="bg-purple-50/60 p-6 rounded-2xl border border-purple-100 space-y-3">
                            <h4 class="font-bold text-brandDark text-sm uppercase">Flujo Rápido para Iniciar un Proyecto Nuevo</h4>
                            <ol class="list-decimal pl-5 text-sm text-gray-700 space-y-2">
                                <li>Entra a <strong>Configuración Global</strong> y define el Nombre del Sitio, Descripción, Correo de destino, WhatsApp y Logotipos.</li>
                                <li>Sube las imágenes del cliente en el módulo <strong>Multimedia</strong>.</li>
                                <li>Ve a <strong>Configuración Global → Prompts IA</strong>, copia el prompt de páginas, pídele a la IA tus secciones y pégalas en <strong>Páginas → Inicio</strong>.</li>
                                <li>Ajusta los botones de navegación en <strong>Menú Principal</strong>.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: PÁGINAS Y CODEMIRROR -->
                    <div x-show="section === 'paginas'" style="display: none;" class="space-y-6">
                        <div class="border-b pb-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-brandLight">Módulo 2</span>
                            <h3 class="text-2xl font-black text-brandDark mt-1">Creación de Páginas y Reglas de CodeMirror</h3>
                        </div>

                        <p class="text-gray-600 text-sm leading-relaxed">
                            El editor <strong>CodeMirror</strong> acepta código <strong>HTML puro con Tailwind CSS y Alpine.js</strong>. Como el layout global ya incluye el documento base, debes respetar tres reglas al pegar código en el editor:
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200">
                                <h5 class="font-bold text-emerald-900 text-xs uppercase mb-2"><i class="fa-solid fa-check mr-1"></i> Qué SÍ pegar en el editor</h5>
                                <ul class="text-xs text-emerald-800 space-y-1.5 list-disc pl-4">
                                    <li>Bloques <code><section>...</section></code> o <code><div></code>.</li>
                                    <li>Clases de Tailwind CSS (ej. <code>bg-zinc-900</code>, <code>grid-cols-3</code>).</li>
                                    <li>Directivas de Alpine.js (<code>x-data</code>, <code>x-show</code>, <code>x-on:click</code>).</li>
                                    <li>Iconos de FontAwesome 6 (ej. <code><i class="fa-solid fa-star"></i></code>).</li>
                                </ul>
                            </div>
                            <div class="p-5 rounded-2xl bg-red-50 border border-red-200">
                                <h5 class="font-bold text-red-900 text-xs uppercase mb-2"><i class="fa-solid fa-xmark mr-1"></i> Qué NO pegar en el editor</h5>
                                <ul class="text-xs text-red-800 space-y-1.5 list-disc pl-4">
                                    <li>NO incluir etiquetas <code><!DOCTYPE html></code>, <code><html></code>, <code><head></code> ni <code><body></code>.</li>
                                    <li>NO pegar directivas de plantilla de Laravel (extends, section o yield).</li>
                                    <li>NO incluir etiquetas <code><header></code> o <code><footer></code> duplicadas.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 space-y-2">
                            <h4 class="font-bold text-brandDark text-sm">Herramientas integradas del Editor</h4>
                            <p class="text-xs text-gray-600"><strong>Botón Biblioteca:</strong> Abre la ventana modal sin salir de la página; al hacer clic en cualquier archivo subido, copia su URL automáticamente para pegarla con <code>Ctrl + V</code>.</p>
                            <p class="text-xs text-gray-600"><strong>Botón Ordenar Código:</strong> Indenta y limpia automáticamente todo tu HTML sin romper etiquetas ni scripts.</p>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: FORMULARIOS, CRM Y CORREO -->
                    <div x-show="section === 'formularios'" style="display: none;" class="space-y-6">
                        <div class="border-b pb-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-brandLight">Módulo 3</span>
                            <h3 class="text-2xl font-black text-brandDark mt-1">Formularios Universales (Dashboard + Correo + WhatsApp)</h3>
                        </div>

                        <p class="text-gray-600 text-sm leading-relaxed">
                            El sistema cuenta con un motor universal en <code>partials/app-scripts.blade.php</code>. Cualquier formulario que coloques en una página hará <strong>3 acciones simultáneas</strong> al enviarse:
                        </p>
                        <ol class="list-decimal pl-5 text-sm text-gray-600 space-y-1">
                            <li>Guarda todos los campos en el <strong>Dashboard de Leads</strong> (Base de datos).</li>
                            <li>Envía una copia formateada al <strong>Correo de Destino</strong> configurado en Configuración Global.</li>
                            <li>Abre <strong>WhatsApp en una nueva pestaña</strong> con todos los campos capturados listos para enviar.</li>
                        </ol>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <h4 class="font-bold text-brandDark text-xs uppercase">Plantilla Estándar de Formulario (Copiable)</h4>
                                <button type="button" @click="copyCode($refs.formCode.innerText)" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-1.5 px-3 rounded-lg border border-gray-200">
                                    <i class="fa-regular fa-copy mr-1"></i> Copiar HTML
                                </button>
                            </div>
                            <pre x-ref="formCode" class="bg-gray-900 text-gray-100 p-4 rounded-2xl text-xs font-mono overflow-x-auto leading-relaxed"><form x-on:submit.prevent="sendWhatsAppMessage($event)" class="space-y-4">
    <!-- Campo anti-spam Honeypot obligatorio (oculto) -->
    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

    <div>
        <label class="block text-xs font-bold uppercase mb-1">Nombre Completo *</label>
        <input type="text" name="nombre" required class="w-full px-4 py-3 rounded-xl border">
    </div>

    <div>
        <label class="block text-xs font-bold uppercase mb-1">Teléfono / WhatsApp *</label>
        <input type="tel" name="telefono" required class="w-full px-4 py-3 rounded-xl border">
    </div>

    <div>
        <label class="block text-xs font-bold uppercase mb-1">Comentarios</label>
        <textarea name="comentarios" rows="3" class="w-full px-4 py-3 rounded-xl border"></textarea>
    </div>

    <!-- Opcional: Widget Cloudflare Turnstile -->
    <div class="cf-turnstile my-3" data-sitekey="TU_SITE_KEY_AQUI"></div>

    <button type="submit" class="w-full py-4 bg-gray-900 text-white font-bold rounded-xl">
        Enviar Solicitud
    </button>
</form></pre>
                        </div>

                        <p class="text-xs text-gray-500 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <strong>Nota técnica:</strong> Puedes inventar cualquier campo nuevo (ej. <code>name="fecha_evento"</code>, <code>name="numero_personas"</code>, <code>name="presupuesto"</code>). Basta con que el input tenga el atributo <code>name="..."</code> para que el sistema lo reconozca, lo guarde en el CRM, lo mande por correo y lo formatee en el mensaje de WhatsApp.
                        </p>
                    </div>

                    <!-- SECCIÓN 4: CONSTRUCTOR DE MENÚ -->
                    <div x-show="section === 'menu'" style="display: none;" class="space-y-6">
                        <div class="border-b pb-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-brandLight">Módulo 4</span>
                            <h3 class="text-2xl font-black text-brandDark mt-1">Constructor de Menú Principal</h3>
                        </div>

                        <ul class="list-disc pl-5 text-sm text-gray-600 space-y-3">
                            <li><strong>Vincular Página o Post:</strong> Usa los selectores desplegables de la columna izquierda para rellenar automáticamente el título y la ruta de cualquier página o artículo publicado.</li>
                            <li><strong>Enlaces a Secciones (Anclas):</strong> Para desplazarse suavemente a una sección de la portada, usa el formato <code>/#servicios</code> o <code>/#contacto</code> en el campo URL.</li>
                            <li><strong>Submenús Desplegables:</strong> Al crear un enlace, elige un <em>Elemento Padre</em> para convertirlo en sub-botón de segundo nivel.</li>
                            <li><strong>Botón Destacado (CTA):</strong> Marca la casilla <em>Es Botón CTA</em> para que el enlace se muestre resaltado como botón de acción en el encabezado.</li>
                            <li><strong>Reordenar (Drag & Drop):</strong> Arrastra cualquier elemento desde el icono de las tres barras para cambiar su posición; el orden se guarda y limpia la caché automáticamente al soltarlo.</li>
                        </ul>
                    </div>

                    <!-- SECCIÓN 5: MULTIMEDIA -->
                    <div x-show="section === 'multimedia'" style="display: none;" class="space-y-6">
                        <div class="border-b pb-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-brandLight">Módulo 5</span>
                            <h3 class="text-2xl font-black text-brandDark mt-1">Biblioteca de Medios y Formatos Soportados</h3>
                        </div>

                        <p class="text-gray-600 text-sm leading-relaxed">
                            El gestor de medios optimiza automáticamente las fotografías (JPG/PNG) convirtiéndolas a formato ligero <strong>.webp</strong>, y además permite almacenar documentos descargables, audios y videos.
                        </p>

                        <div class="space-y-4">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <h5 class="font-bold text-xs uppercase text-brandDark mb-1">Insertar Botón de Descarga (PDF / Excel)</h5>
                                <code class="text-xs text-purple-800 block font-mono"><a href="URL_DEL_ARCHIVO" target="_blank" download class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold inline-block">Descargar Catálogo PDF</a></code>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <h5 class="font-bold text-xs uppercase text-brandDark mb-1">Insertar Video Nativo</h5>
                                <code class="text-xs text-purple-800 block font-mono"><video src="URL_DEL_VIDEO" controls class="w-full rounded-2xl shadow-lg"></video></code>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <h5 class="font-bold text-xs uppercase text-brandDark mb-1">Insertar Reproductor de Audio</h5>
                                <code class="text-xs text-purple-800 block font-mono"><audio src="URL_DEL_AUDIO" controls class="w-full"></audio></code>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 6: BLOG Y SITEMAP -->
                    <div x-show="section === 'blog'" style="display: none;" class="space-y-6">
                        <div class="border-b pb-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-brandLight">Módulo 6</span>
                            <h3 class="text-2xl font-black text-brandDark mt-1">Blog y Sitemap XML Automático</h3>
                        </div>

                        <ul class="list-disc pl-5 text-sm text-gray-600 space-y-3">
                            <li><strong>Diseño Agnóstico Editorial:</strong> El índice del blog (<code>/blog</code>) y la vista de lectura (<code>/blog/{slug}</code>) heredan automáticamente el nombre y descripción de tu empresa configurados en el panel.</li>
                            <li><strong>SEO por Artículo:</strong> Cada post genera automáticamente sus etiquetas <code>og:type="article"</code>, <code>meta_description</code> y <code>og:image</code> para compartir en redes sociales.</li>
                            <li><strong>Sitemap Dinámico:</strong> El sistema genera en tiempo real el mapa del sitio en <code>tudominio.com/sitemap.xml</code> incluyendo todas las páginas y artículos publicados para Google Search Console.</li>
                        </ul>
                    </div>

                    <!-- SECCIÓN 7: CONFIGURACIÓN Y TURNSTILE -->
                    <div x-show="section === 'settings'" style="display: none;" class="space-y-6">
                        <div class="border-b pb-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-brandLight">Módulo 7</span>
                            <h3 class="text-2xl font-black text-brandDark mt-1">Configuración Global, Scripts y Cloudflare Turnstile</h3>
                        </div>

                        <div class="space-y-4 text-sm text-gray-600">
                            <p><strong>Inyección de Scripts:</strong> En la pestaña <em>Scripts</em> puedes pegar etiquetas de Google Analytics, Meta Pixel o Google Tag Manager en las 3 ubicaciones estándar (<code><head></code>, apertura de <code><body></code> y cierre de <code><body></code>).</p>
                            <p><strong>Activar Cloudflare Turnstile (Anti-Spam):</strong></p>
                            <ol class="list-decimal pl-5 space-y-1.5">
                                <li>Obtén tu <em>Site Key</em> y <em>Secret Key</em> en tu panel gratuito de Cloudflare Turnstile.</li>
                                <li>Agrégalas en tu archivo <code>.env</code>:
                                    <pre class="bg-gray-900 text-gray-100 p-3 rounded-xl text-xs font-mono mt-1">TURNSTILE_SITE_KEY=0x4AAAAAA...
TURNSTILE_SECRET_KEY=0x4AAAAAA...</pre>
                                </li>
                                <li>Coloca el div <code><div class="cf-turnstile my-3" data-sitekey="TU_SITE_KEY"></div></code> dentro de tus formularios HTML. Si dejas vacío el <code>.env</code>, el sistema desactiva la exigencia del captcha automáticamente.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- SECCIÓN 8: COMANDOS Y DESPLIEGUE -->
                    <div x-show="section === 'comandos'" style="display: none;" class="space-y-6">
                        <div class="border-b pb-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-brandLight">Módulo 8</span>
                            <h3 class="text-2xl font-black text-brandDark mt-1">Comandos de Mantenimiento y Despliegue</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h5 class="font-bold text-xs uppercase text-gray-500 mb-1">1. Reiniciar el CMS a Fábrica (Marca Blanca Limpia)</h5>
                                <pre class="bg-gray-900 text-emerald-400 p-4 rounded-2xl text-xs font-mono">php artisan migrate:fresh --seed
php artisan cache:clear
php artisan view:clear</pre>
                            </div>

                            <div>
                                <h5 class="font-bold text-xs uppercase text-gray-500 mb-1">2. Reparar Enlace Simbólico de Imágenes (Si las imágenes dan error 404 en Local/Windows)</h5>
                                <pre class="bg-gray-900 text-amber-300 p-4 rounded-2xl text-xs font-mono">Remove-Item -Recurse -Force public\storage
php artisan storage:link</pre>
                            </div>

                            <div>
                                <h5 class="font-bold text-xs uppercase text-gray-500 mb-1">3. Limpiar Caché tras cambios en .env o Rutas</h5>
                                <pre class="bg-gray-900 text-gray-100 p-4 rounded-2xl text-xs font-mono">php artisan optimize:clear</pre>
                            </div>
                        </div>
                    </div>

                
</div>
        </div>
    </div>
    @endverbatim
</x-app-layout>