<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> Crear Cuenta <?php $__env->endSlot(); ?>

    <div class="mb-6">
        <h2 class="text-2xl font-black text-slate-900">Crea tu espacio en Flow</h2>
        <p class="text-xs text-slate-500 mt-1">Activa tu CRM omnicanal y obtén al instante el código del Chatbot IA para tu sitio web.</p>
    </div>

    <form method="POST" action="<?php echo e(route('register')); ?>" class="space-y-4" x-data="{ showPass: false }">
        <?php echo csrf_field(); ?>

        <!-- Nombre completo -->
        <div>
            <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Tu nombre</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-user"></i>
                </span>
                <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" required autofocus autocomplete="name"
                       placeholder="Ej. Carlos Mendoza"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-rose-600 font-medium"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <!-- Nombre del Negocio / Empresa -->
        <div>
            <label for="company_name" class="block text-xs font-bold text-slate-700 mb-1">Nombre de tu empresa o marca</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-building"></i>
                </span>
                <input id="company_name" type="text" name="company_name" value="<?php echo e(old('company_name')); ?>" required
                       placeholder="Ej. Clínica Dental Sonrisa"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-rose-600 font-medium"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <!-- Correo electrónico -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Correo electrónico</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-envelope"></i>
                </span>
                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="username"
                       placeholder="carlos@tuempresa.com"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-rose-600 font-medium"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <!-- Contraseña y Confirmación -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Contraseña</label>
                <div class="relative">
                    <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="new-password"
                           placeholder="Mín. 8 caracteres"
                           class="w-full px-3.5 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Confirmar</label>
                <div class="relative">
                    <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                           placeholder="Repetir clave"
                           class="w-full pr-9 pl-3.5 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs">
                        <i class="fa-regular" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-xs text-rose-600 font-medium"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <!-- Cloudflare Turnstile (si está configurado en .env) -->
        <?php if(env('TURNSTILE_SITE_KEY')): ?>
            <div class="pt-1 flex justify-center">
                <div class="cf-turnstile" data-sitekey="<?php echo e(env('TURNSTILE_SITE_KEY')); ?>"></div>
            </div>
        <?php endif; ?>

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#1A0B5E] hover:bg-indigo-900 text-white font-bold text-sm shadow-md shadow-indigo-900/15 transition flex items-center justify-center gap-2">
            <span>Crear mi cuenta y comenzar</span>
            <i class="fa-solid fa-rocket text-xs"></i>
        </button>

        <p class="text-[11px] text-slate-400 text-center leading-relaxed">
            Al registrarte aceptas los términos de servicio y políticas de privacidad de Webicónica.
        </p>

        <div class="pt-3 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                ¿Ya tienes una cuenta?
                <a href="<?php echo e(route('login')); ?>" class="font-bold text-indigo-600 hover:text-indigo-800">
                    Inicia sesión aquí
                </a>
            </p>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/auth/register.blade.php ENDPATH**/ ?>