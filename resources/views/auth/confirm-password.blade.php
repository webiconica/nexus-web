<x-guest-layout>
    <x-slot name="title">Confirmar Seguridad</x-slot>

    <div class="mb-6">
        <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center mb-3">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h2 class="text-xl font-black text-slate-900">Área protegida</h2>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            Por tu seguridad, confirma tu contraseña actual antes de continuar con esta acción.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Contraseña actual</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full px-3.5 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            @error('password')
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#1A0B5E] hover:bg-indigo-900 text-white font-bold text-sm shadow-md transition">
            Confirmar y continuar
        </button>
    </form>
</x-guest-layout>