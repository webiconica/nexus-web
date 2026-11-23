<x-guest-layout>
    <x-slot name="title">Verificar Correo</x-slot>

    <div class="mb-6">
        <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
            <i class="fa-solid fa-envelope-circle-check text-lg"></i>
        </div>
        <h2 class="text-xl font-black text-slate-900">Confirma tu correo electrónico</h2>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            ¡Gracias por registrarte en <strong>Webiconica Flow</strong>! Antes de comenzar, haz clic en el enlace de verificación que acabamos de enviar a tu bandeja de entrada.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800">
            <i class="fa-solid fa-circle-check mr-1.5"></i> Se ha enviado un nuevo enlace de verificación al correo que registraste.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#1A0B5E] hover:bg-indigo-900 text-white font-bold text-sm shadow-md transition">
                Reenviar correo de verificación
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center pt-2">
            @csrf
            <button type="submit" class="text-xs font-bold text-slate-500 hover:text-rose-600 transition">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>