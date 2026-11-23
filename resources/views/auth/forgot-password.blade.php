<x-guest-layout>
    <x-slot name="title">Recuperar Contraseña</x-slot>

    <div class="mb-6">
        <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3">
            <i class="fa-solid fa-key"></i>
        </div>
        <h2 class="text-xl font-black text-slate-900">¿Olvidaste tu contraseña?</h2>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            Ingresa el correo asociado a tu cuenta de <strong>Webiconica Flow</strong> y te enviaremos un enlace seguro para crear una nueva contraseña.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800">
            <i class="fa-solid fa-circle-check mr-1.5"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Correo electrónico</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                    <i class="fa-regular fa-envelope"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="tu@empresa.com"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#1A0B5E] hover:bg-indigo-900 text-white font-bold text-sm shadow-md transition">
            Enviar enlace de recuperación
        </button>

        <div class="pt-3 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-600 hover:text-indigo-700 inline-flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Volver a iniciar sesión
            </a>
        </div>
    </form>
</x-guest-layout>