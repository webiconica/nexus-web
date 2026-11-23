<x-guest-layout>
    <x-slot name="title">Nueva Contraseña</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-black text-slate-900">Restablecer contraseña</h2>
        <p class="text-xs text-slate-500 mt-1">Define una nueva contraseña segura para acceder a tu panel.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4" x-data="{ showPass: false }">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="w-full px-3.5 py-2.5 rounded-xl border-slate-300 text-sm bg-slate-50 focus:border-indigo-600 focus:ring-indigo-600">
            @error('email')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Nueva contraseña</label>
            <div class="relative">
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="new-password"
                       placeholder="Mínimo 8 caracteres"
                       class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 text-xs">
                    <i class="fa-regular" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Confirmar nueva contraseña</label>
            <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                   placeholder="Repite la contraseña"
                   class="w-full px-3.5 py-2.5 rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            @error('password_confirmation')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#1A0B5E] hover:bg-indigo-900 text-white font-bold text-sm shadow-md transition">
            Guardar nueva contraseña
        </button>
    </form>
</x-guest-layout>