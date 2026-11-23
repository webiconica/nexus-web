<x-guest-layout>
    <x-slot name="title">Crear Cuenta</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-black text-slate-900">Crea tu espacio en Flow</h2>
        <p class="text-xs text-slate-500 mt-1">Activa tu CRM omnicanal y obtén al instante el código del Chatbot IA para tu sitio web.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ showPass: false }">
        @csrf

        <!-- Nombre completo -->
        <div>
            <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Tu nombre</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-user"></i>
                </span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       placeholder="Ej. Carlos Mendoza"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            @error('name')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nombre del Negocio / Empresa -->
        <div>
            <label for="company_name" class="block text-xs font-bold text-slate-700 mb-1">Nombre de tu empresa o marca</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-building"></i>
                </span>
                <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required
                       placeholder="Ej. Clínica Dental Sonrisa"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            @error('company_name')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Correo electrónico -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Correo electrónico</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-envelope"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       placeholder="carlos@tuempresa.com"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            @error('email')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
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
        @error('password')
            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
        @enderror

        <!-- Cloudflare Turnstile (si está configurado en .env) -->
        @if(env('TURNSTILE_SITE_KEY'))
            <div class="pt-1 flex justify-center">
                <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}"></div>
            </div>
        @endif

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
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-800">
                    Inicia sesión aquí
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>