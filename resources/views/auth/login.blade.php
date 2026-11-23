<x-guest-layout>
    <x-slot name="title">Iniciar Sesión</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-black text-slate-900">Bienvenido de vuelta</h2>
        <p class="text-xs text-slate-500 mt-1">Ingresa a tu panel de <strong>Webiconica Flow</strong> para gestionar tus prospectos y chatbot.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPass: false }">
        @csrf

        <!-- Correo electrónico -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Correo electrónico</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-envelope"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="tu@empresa.com"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contraseña -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700">Contraseña</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
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
            @error('password')
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Recordar sesión -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ms-2 text-xs text-slate-600">Mantener sesión iniciada</span>
            </label>
        </div>

        <!-- Cloudflare Turnstile (si está configurado en .env) -->
        @if(env('TURNSTILE_SITE_KEY'))
            <div class="pt-1 flex justify-center">
                <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}"></div>
            </div>
        @endif

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#1A0B5E] hover:bg-indigo-900 text-white font-bold text-sm shadow-md shadow-indigo-900/15 transition flex items-center justify-center gap-2">
            <span>Entrar a mi CRM</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>

        @if (Route::has('register'))
            <div class="pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    ¿Aún no tienes cuenta en Webiconica Flow?
                    <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-800">
                        Crear cuenta gratis
                    </a>
                </p>
            </div>
        @endif
    </form>
</x-guest-layout>