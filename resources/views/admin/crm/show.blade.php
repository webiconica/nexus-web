@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.crm.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Volver al Embudo CRM
        </a>
        <span class="text-xs text-slate-400">Ficha de Prospecto #{{ $lead->id }}</span>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- COLUMNA IZQUIERDA: Perfil, Atribución Google/Meta y Campos Personalizados -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Datos Principales -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-xl font-black text-slate-900">{{ $lead->name ?: 'Prospecto sin nombre' }}</h1>
                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                {{ $lead->origen }}
                            </span>

                            @if($lead->gclid || $lead->gbraid || $lead->wbraid)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i class="fa-brands fa-google text-[10px]"></i> Google Ads
                                </span>
                            @endif

                            @if($lead->fbclid || $lead->fbc || $lead->fbp)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fa-brands fa-meta text-[10px]"></i> Meta Ads
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-5 space-y-2.5 text-sm border-t border-slate-100 pt-4">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Teléfono:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $lead->phone_normalized ? '+' . $lead->phone_normalized : '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Correo:</span>
                        <span class="font-medium text-slate-800">{{ $lead->email ?: '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Alta:</span>
                        <span class="text-slate-700">{{ $lead->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Identificadores Publicitarios Google Ads -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h2 class="text-xs font-black uppercase tracking-wider text-amber-700 mb-3 flex items-center gap-2">
                    <i class="fa-brands fa-google text-amber-500"></i> Atribución Publicitaria Google Ads
                </h2>
                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400 block">gclid (Clic Web / Search):</span>
                        <code class="block p-1.5 rounded border break-all {{ $lead->gclid ? 'bg-amber-50/60 border-amber-200 text-slate-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-500' }}">
                            {{ $lead->gclid ?: 'No capturado' }}
                        </code>
                    </div>
                    <div>
                        <span class="text-slate-400 block">gbraid (App / iOS):</span>
                        <code class="block p-1.5 rounded border break-all {{ $lead->gbraid ? 'bg-amber-50/60 border-amber-200 text-slate-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-500' }}">
                            {{ $lead->gbraid ?: 'No capturado' }}
                        </code>
                    </div>
                    <div>
                        <span class="text-slate-400 block">wbraid (Web-to-App / iOS):</span>
                        <code class="block p-1.5 rounded border break-all {{ $lead->wbraid ? 'bg-amber-50/60 border-amber-200 text-slate-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-500' }}">
                            {{ $lead->wbraid ?: 'No capturado' }}
                        </code>
                    </div>
                </div>
            </div>

            <!-- Identificadores Publicitarios Meta Ads -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h2 class="text-xs font-black uppercase tracking-wider text-blue-700 mb-3 flex items-center gap-2">
                    <i class="fa-brands fa-meta"></i> Atribución Publicitaria Meta
                </h2>
                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400 block">fbclid:</span>
                        <code class="block p-1.5 rounded border break-all {{ $lead->fbclid ? 'bg-blue-50/60 border-blue-200 text-slate-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-500' }}">
                            {{ $lead->fbclid ?: 'No capturado' }}
                        </code>
                    </div>
                    <div>
                        <span class="text-slate-400 block">fbc:</span>
                        <code class="block p-1.5 rounded border break-all {{ $lead->fbc ? 'bg-blue-50/60 border-blue-200 text-slate-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-500' }}">
                            {{ $lead->fbc ?: 'No capturado' }}
                        </code>
                    </div>
                    <div>
                        <span class="text-slate-400 block">fbp:</span>
                        <code class="block p-1.5 rounded border break-all {{ $lead->fbp ? 'bg-blue-50/60 border-blue-200 text-slate-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-500' }}">
                            {{ $lead->fbp ?: 'No capturado' }}
                        </code>
                    </div>
                </div>
            </div>

            <!-- Todos los Campos Personalizados Capturados -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h2 class="text-xs font-black uppercase tracking-wider text-slate-600 mb-3">
                    Campos Personalizados (JSON)
                </h2>
                @if(!empty($lead->datos_formulario))
                    <dl class="divide-y divide-slate-100 text-xs">
                        @foreach($lead->datos_formulario as $key => $value)
                            <div class="py-2 flex flex-col">
                                <dt class="font-bold text-slate-500">{{ ucfirst(str_replace('_', ' ', (string) $key)) }}</dt>
                                <dd class="text-slate-900 mt-0.5 break-words">
                                    {{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="text-xs text-slate-400">Sin campos adicionales.</p>
                @endif
            </div>

        </div>

        <!-- COLUMNA DERECHA: Cambio de Etapa + Timeline de Conversación + Bitácora -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Cambio Manual de Etapa (Dispara Webhook Saliente / Meta CAPI / Feed Google Ads) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h2 class="text-sm font-black text-slate-900 mb-1">Actualizar Etapa del Embudo</h2>
                <p class="text-xs text-slate-500 mb-4">
                    Al avanzar de etapa (como <strong>«Cotizado»</strong>, <strong>«Anticipo Confirmado»</strong> o <strong>«Cerrado»</strong>), se dispara tu Webhook saliente, el evento configurado en <strong>Meta CAPI</strong> y se registra para el Feed de <strong>Google Ads</strong>.
                </p>

                <form method="POST" action="{{ route('admin.crm.update-stage', $lead) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    @csrf
                    @method('PATCH')

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-slate-600 mb-1">Nueva Etapa</label>
                        <select name="stage" class="w-full rounded-xl border-slate-300 text-sm font-bold">
                            @foreach($stages as $sKey => $sLabel)
                                <option value="{{ $sKey }}" {{ $lead->stage === $sKey ? 'selected' : '' }}>{{ $sLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-600 mb-1">Monto (MXN)</label>
                        <input type="number" step="0.01" name="amount" placeholder="Ej. 2500"
                               class="w-full rounded-xl border-slate-300 text-sm">
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-600 mb-1">Nota (Opcional)</label>
                        <input type="text" name="notes" placeholder="Transferencia recibida"
                               class="w-full rounded-xl border-slate-300 text-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Historial Unificado de Conversaciones (Web + WhatsApp + Formularios + Notas) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h2 class="text-sm font-black text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-regular fa-comments text-indigo-600"></i>
                    Historial Unificado de Conversaciones y Actividad
                </h2>

                <div class="space-y-3 max-h-[460px] overflow-y-auto pr-2 mb-5">
                    @forelse($lead->messages as $msg)
                        @php
                            $isInbound = $msg->direction === 'inbound';
                            $badgeColor = match($msg->channel) {
                                'whatsapp'                 => 'bg-emerald-100 text-emerald-800',
                                'web_chat', 'web_widget'   => 'bg-indigo-100 text-indigo-800',
                                'webhook_form'             => 'bg-amber-100 text-amber-800',
                                default                    => 'bg-slate-200 text-slate-700',
                            };
                        @endphp
                        <div class="p-3.5 rounded-2xl border {{ $isInbound ? 'bg-slate-50 border-slate-200 mr-8' : 'bg-indigo-50/50 border-indigo-100 ml-8' }}">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded {{ $badgeColor }}">
                                    {{ $msg->channel }} · {{ $msg->sender_type }}
                                </span>
                                <span class="text-[11px] text-slate-400">{{ $msg->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="text-xs text-slate-800 whitespace-pre-wrap leading-relaxed">{{ $msg->content }}</div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">Aún no hay mensajes registrados para este contacto.</p>
                    @endforelse
                </div>

                <!-- Agregar Nota Interna -->
                <form method="POST" action="{{ route('admin.crm.store-note', $lead) }}" class="flex gap-2 border-t border-slate-100 pt-4">
                    @csrf
                    <input type="text" name="note" required placeholder="Escribir nota interna de seguimiento..."
                           class="flex-1 rounded-xl border-slate-300 text-xs">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800">
                        Agregar Nota
                    </button>
                </form>

                @if($lead->phone_normalized && $tenant?->whatsapp_phone_number_id)
                    <form method="POST" action="{{ route('admin.crm.reply-whatsapp', $lead) }}" class="flex gap-2 mt-3 pt-3 border-t border-slate-100">
                        @csrf
                        <input type="text" name="whatsapp_message" required
                               placeholder="Responder directamente por WhatsApp Cloud API..."
                               class="flex-1 rounded-xl border-emerald-300 focus:border-emerald-500 text-xs">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5">
                            <i class="fa-brands fa-whatsapp"></i> Enviar WhatsApp
                        </button>
                    </form>
                @endif
            </div>

            <!-- Bitácora de Cambios de Etapa y Auditoría Webhooks / Meta CAPI -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h2 class="text-sm font-black text-slate-900 mb-3">Bitácora de Cambios de Etapa y Webhooks</h2>
                @if($lead->stageLogs->isNotEmpty())
                    <div class="space-y-2.5 text-xs">
                        @foreach($lead->stageLogs as $log)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80 gap-2">
                                <div>
                                    <span class="font-bold text-slate-700">{{ $stages[$log->from_stage] ?? ($log->from_stage ?: 'Inicial') }}</span>
                                    <i class="fa-solid fa-arrow-right mx-1.5 text-slate-400"></i>
                                    <span class="font-black text-indigo-700">{{ $stages[$log->to_stage] ?? $log->to_stage }}</span>
                                    @if($log->notes)
                                        <span class="text-slate-500 ml-2">— "{{ $log->notes }}"</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($log->webhook_dispatched)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ ($log->webhook_response_status >= 200 && $log->webhook_response_status < 300) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            Webhook / CAPI: HTTP {{ $log->webhook_response_status }}
                                        </span>
                                    @endif
                                    <span class="text-slate-400">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400">Este prospecto aún permanece en su etapa inicial.</p>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection