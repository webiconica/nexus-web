<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Administrador de Páginas Dinámicas') }}
            </h2>
            <a href="{{ route('admin.pages.create') }}" class="bg-brandDark hover:bg-brandLight text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-md">
                + Crear Nueva Página
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('status'))
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-xl text-sm font-medium">
                    {{ session('status') == 'pagina-creada' ? '¡Página creada con éxito!' : (session('status') == 'pagina-actualizada' ? '¡Cambios guardados correctamente!' : 'Página eliminada del sistema.') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100">
                <div class="p-8 text-gray-900">
                    @if($pages->isEmpty())
                        <div class="text-center py-12 text-gray-500">
                            <p class="text-lg">No has creado ninguna página dinámica todavía.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                        <th class="p-4 font-semibold rounded-tl-2xl">Título</th>
                                        <th class="p-4 font-semibold">Ruta / URL (Slug)</th>
                                        <th class="p-4 font-semibold text-right rounded-tr-2xl">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-gray-100">
                                    @foreach($pages as $p)
                                        <tr class="hover:bg-gray-50/50 transition">
                                            <td class="p-4 font-bold text-gray-800">{{ $p->title }}</td>
                                            <td class="p-4 text-brandLight font-mono">
                                                <a href="{{ url('/' . $p->slug) }}" target="_blank" class="hover:underline">
                                                    /{{ $p->slug }} <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                                                </a>
                                            </td>
                                            <td class="p-4 text-right flex justify-end gap-3">
                                                <a href="{{ route('admin.pages.edit', $p) }}" class="text-brandDark hover:text-brandLight font-bold text-xs uppercase tracking-wide transition">
                                                    Editar
                                                </a>
                                                <form action="{{ route('admin.pages.destroy', $p) }}" method="POST" onsubmit="return confirm('¿Eliminar esta página de forma permanente?');" class="inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-wide transition">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $pages->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>