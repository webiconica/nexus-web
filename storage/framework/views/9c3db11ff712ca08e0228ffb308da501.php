

<?php $__env->startSection('content'); ?>
    <?php if(isset($page) && !empty(trim($page->content))): ?>
        
        <?php echo $page->content; ?>

    <?php else: ?>
        
        <section id="inicio" class="relative pt-32 pb-24 md:pt-48 md:pb-36 bg-gray-50 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-gray-200 text-gray-800 text-xs font-bold uppercase tracking-wider mb-6">
                        Soluciones para Empresas
                    </span>
                    <h1 class="text-4xl sm:text-6xl font-black text-gray-900 tracking-tight leading-tight mb-6">
                        Impulsamos el crecimiento y valor de <span class="text-gray-600">tu negocio</span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                        <?php echo e($globalSettings['site_description'] ?? 'Desarrollamos soluciones integrales y personalizadas para optimizar procesos y alcanzar objetivos comerciales sostenibles.'); ?>

                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#servicios" class="bg-gray-900 text-white px-8 py-4 rounded-xl font-bold hover:bg-gray-800 transition text-center">
                            Nuestros Servicios
                        </a>
                        <a href="#contacto" class="bg-white text-gray-800 border-2 border-gray-200 px-8 py-4 rounded-xl font-bold hover:border-gray-900 transition text-center">
                            Contactar Ahora
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- SERVICIOS -->
        <section id="servicios" class="py-24 bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mb-4">Servicios Principales</h2>
                    <p class="text-gray-500 text-lg">Soluciones estructuradas y adaptadas a las necesidades de tu proyecto.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100">
                        <div class="w-12 h-12 bg-gray-900 text-white rounded-xl flex items-center justify-center text-xl mb-6">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Consultoría</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Diagnóstico estratégico para mejorar la eficiencia y rentabilidad de tu negocio.</p>
                    </div>
                    <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100">
                        <div class="w-12 h-12 bg-gray-900 text-white rounded-xl flex items-center justify-center text-xl mb-6">
                            <i class="fa-solid fa-gears"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Operaciones</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Optimización de flujos de trabajo e integración de herramientas modernas.</p>
                    </div>
                    <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100">
                        <div class="w-12 h-12 bg-gray-900 text-white rounded-xl flex items-center justify-center text-xl mb-6">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Soporte Continuo</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Acompañamiento constante y resolución proactiva para garantizar continuidad.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTACTO -->
        <section id="contacto" class="py-24 bg-gray-50">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white p-8 sm:p-10 rounded-3xl border border-gray-200 shadow-sm">
                    <h2 class="text-2xl font-black text-gray-900 mb-2">Escríbenos</h2>
                    <p class="text-gray-500 text-sm mb-6">Completa tus datos y nos pondremos en contacto contigo a la brevedad.</p>

                    <form @submit.prevent="sendWhatsAppMessage($event)" class="space-y-4">
                        <input type="text" name="website" class="hidden" autocomplete="off" tabindex="-1">                        

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo *</label>
                            <input type="text" name="nombre" required placeholder="Tu nombre" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono / WhatsApp *</label>
                                <input type="tel" name="telefono" required placeholder="+52 55..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Empresa</label>
                                <input type="text" name="empresa" placeholder="Nombre de empresa" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Mensaje o Requerimientos</label>
                            <textarea name="mensaje" rows="3" placeholder="¿En qué te podemos ayudar?" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none"></textarea>
                        </div>
                        
                        <!-- AQUÍ SE COLOCA EL WIDGET VISUAL -->
                        <div class="cf-turnstile my-4" data-sitekey="<?php echo e(env('TURNSTILE_SITE_KEY')); ?>"></div>

                        <button type="submit" class="w-full py-4 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl transition text-sm shadow-md">
                            Enviar Solicitud
                        </button>
                    </form>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.web', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/welcome.blade.php ENDPATH**/ ?>