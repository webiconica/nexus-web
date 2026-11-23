@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Clientes & Cuentas Multi-Tenant</h1>
        <p class="text-sm text-slate-500 mt-1">Crea cuentas aisladas con su propia API Key, límites de plan y modo Solo Chat + CRM.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <!-- Alta de Nuevo Cliente -->
    <form method="POST" action="{{ route('admin.tenants.store') }}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        @csrf
        <h2 class="text-base font-bold text-slate-900">Dar de Alta Nuevo Cliente</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nombre del Negocio *</label>
                <input type="text" name="name" required placeholder="Ej. Clínica Bella" class="w-full rounded-xl border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Plan Contratado *</label>
                <select name="plan" class="w-full rounded-xl border-slate-300 text-sm font-bold">
                    <option value="emprendedor">Emprendedor ($499/mes · 350 chats)</option>
                    <option value="negocio" selected>Negocio ($990/mes · 1,000 chats)</option>
                    <option value="pro">Pro Omnicanal ($1,790/mes · 2,500 chats)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Límite Mensual de Mensajes IA *</label>
                <input type="number" name="monthly_chat_limit" value="1000" required class="w-full rounded-xl border-slate-300 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nombre Administrador Cliente *</label>
                <input type="text" name="admin_name" required class="w-full rounded-xl border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Correo Acceso Panel *</label>
                <input type="email" name="admin_email" required class="w-full rounded-xl border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Contraseña Inicial *</label>
                <input type="text" name="admin_password" required class="w-full rounded-xl border-slate-300 text-sm">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700">
                <input type="checkbox" name="crm_only_mode" value="1" checked class="rounded text-indigo-600">
                Activar «Modo Solo Chat + CRM» (oculta Páginas y Blog para este cliente)
            </label>

            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs">
                Crear Cliente y Generar API Key
            </button>
        </div>
    </form>

    <!-- Listado de Clientes -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-xs">
            <thead class="bg-slate-50 uppercase font-bold text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">ID / Negocio</th>
                    <th class="px-4 py-3 text-left">Plan & Modo</th>
                    <th class="px-4 py-3 text-left">API Key</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($tenants as $t)
                    <tr>
                        <td class="px-4 py-3 font-bold text-slate-900">#{{ $t->id }} — {{ $t->name }}</td>
                        <td class="px-4 py-3">
                            <span class="uppercase font-bold text-indigo-700">{{ $t->plan }}</span>
                            ({{ number_format($t->monthly_chat_limit) }}/mes)
                            @if($t->crm_only_mode)
                                <span class="ml-1 px-2 py-0.5 rounded bg-slate-100 text-slate-600">Solo CRM</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] select-all">{{ $t->api_key }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full font-bold {{ $t->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $t->is_active ? 'Activo' : 'Suspendido' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($t->id !== 1)
                                <form method="POST" action="{{ route('admin.tenants.toggle', $t) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 rounded border border-slate-300 hover:bg-slate-50 font-semibold">
                                        {{ $t->is_active ? 'Suspender' : 'Activar' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection