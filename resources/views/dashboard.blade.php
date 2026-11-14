<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('CRM Webicónica - Prospectos') }}
            </h2>
            <span class="bg-brandLight/20 text-brandDark px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">
                {{ count($leads) }} Registros
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100">
                <div class="p-8 text-gray-900">
                    
                    @if($leads->isEmpty())
                        <div class="text-center py-12 text-gray-500">
                            <i class="fa-solid fa-inbox text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg">Aún no hay prospectos registrados.</p>
                            <p class="text-sm">Ve a la página principal y envía una prueba en el simulador.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                              <thead>
                                  <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                      <th class="p-4 font-semibold rounded-tl-2xl">Fecha</th>
                                      <th class="p-4 font-semibold">Origen</th>
                                      <th class="p-4 font-semibold">Contacto Principal</th>
                                      <th class="p-4 font-semibold text-right rounded-tr-2xl">Acciones</th>
                                  </tr>
                              </thead>

                              @foreach($leads as $lead)
                                  <tbody x-data="{ open: false }" class="text-sm border-b border-gray-100">

                                      <tr class="hover:bg-gray-50/50 transition group cursor-pointer" @click="open = !open">
                                          <td class="p-4 text-gray-500 whitespace-nowrap">
                                              {{ $lead->created_at->format('d M, Y \a \l\a\s H:i') }}
                                          </td>
                                          <td class="p-4 font-medium text-brandDark">
                                              <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded-md text-xs border border-purple-100">
                                                  {{ $lead->origen }}
                                              </span>
                                          </td>
                                          <td class="p-4">
                                              <p class="font-bold text-gray-800">{{ $lead->datos_formulario['nombre'] ?? 'Sin Nombre' }}</p>
                                              <p class="text-xs text-gray-500">{{ $lead->datos_formulario['telefono'] ?? $lead->datos_formulario['correo'] ?? 'Sin datos de contacto' }}</p>
                                          </td>
                                          <td class="p-4 text-right">
                                              <div class="flex items-center justify-end gap-4">
                                                <button class="text-brandLight hover:text-brandDark font-bold text-xs uppercase tracking-wide transition inline-flex items-center gap-1">
                                                  <span x-text="open ? 'Ocultar' : 'Ver Detalles'"></span>
                                                  <i class="fa-solid transition-transform" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                </button>

                                                <form action="{{ route('leads.destroy', $lead) }}" method="POST" 
                                                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar este lead de forma permanente?');" 
                                                      class="inline-block" 
                                                      @click.stop>
                                                  @csrf
                                                  @method('DELETE')
                                                  <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-wide transition">
                                                    <i class="fa-solid fa-trash-can mr-1"></i> Eliminar
                                                  </button>
                                                </form>
                                              </div>
                                          </td>
                                      </tr>

                                      <tr x-show="open" x-transition class="bg-gray-50/50" style="display: none;">
                                          <td colspan="4" class="p-6">
                                              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                  @foreach($lead->datos_formulario as $key => $value)
                                                      <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                                                          <span class="block text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1">
                                                              {{ str_replace('_', ' ', $key) }}
                                                          </span>
                                                          <span class="text-sm font-medium text-gray-800">
                                                              @if(is_bool($value))
                                                                  {!! $value ? '<span class="text-green-500 font-bold">Sí</span>' : '<span class="text-gray-400">No</span>' !!}
                                                              @elseif(is_numeric($value) && str_contains($key, 'inversion'))
                                                                  ${{ number_format($value, 2) }} MXN
                                                              @elseif(is_array($value) || is_object($value))
                                                                  <span class="text-xs text-gray-500 break-words">{{ json_encode($value, JSON_UNESCAPED_UNICODE) }}</span>
                                                              @else
                                                                  {{ $value ?: '-' }}
                                                              @endif
                                                          </span>
                                                      </div>
                                                  @endforeach
                                              </div>
                                          </td>
                                      </tr>
                                  </tbody>
                              @endforeach
                          </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
