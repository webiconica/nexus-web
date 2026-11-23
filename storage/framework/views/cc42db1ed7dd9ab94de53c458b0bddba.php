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
     <?php $__env->slot('title', null, []); ?> Iniciar Sesión <?php $__env->endSlot(); ?>

    <div class="mb-6">
        <h2 class="text-2xl font-black text-slate-900">Bienvenido de vuelta</h2>
        <p class="text-xs text-slate-500 mt-1">Ingresa a tu panel de <strong>Webiconica Flow</strong> para gestionar tus prospectos y chatbot.</p>
    </div>

    <?php if(session('status')): ?>
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4" x-data="{ showPass: false }">
        <?php echo csrf_field(); ?>

        <!-- Correo electrónico -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Correo electrónico</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-envelope"></i>
                </span>
                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus autocomplete="username"
                       placeholder="tu@empresa.com"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-xs text-rose-600 font-medium"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <!-- Contraseña -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700">Contraseña</label>
                <?php if(Route::has('password.request')): ?>
                    <a href="<?php echo e(route('password.request')); ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        ¿Olvidaste tu contraseña?
                    </a>
                <?php endif; ?>
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full pl-10 pr-10 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 text-xs">
                    <i class="fa-regular" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-xs text-rose-600 font-medium"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <!-- Recordar sesión -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ms-2 text-xs text-slate-600">Mantener sesión iniciada</span>
            </label>
        </div>

        <!-- Cloudflare Turnstile (si está configurado en .env) -->
        <?php if(env('TURNSTILE_SITE_KEY')): ?>
            <div class="pt-1 flex justify-center">
                <div class="cf-turnstile" data-sitekey="<?php echo e(env('TURNSTILE_SITE_KEY')); ?>"></div>
            </div>
        <?php endif; ?>

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#1A0B5E] hover:bg-indigo-900 text-white font-bold text-sm shadow-md shadow-indigo-900/15 transition flex items-center justify-center gap-2">
            <span>Entrar a mi CRM</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>

        <?php if(Route::has('register')): ?>
            <div class="pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    ¿Aún no tienes cuenta en Webiconica Flow?
                    <a href="<?php echo e(route('register')); ?>" class="font-bold text-indigo-600 hover:text-indigo-800">
                        Crear cuenta gratis
                    </a>
                </p>
            </div>
        <?php endif; ?>
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
<?php endif; ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/auth/login.blade.php ENDPATH**/ ?>