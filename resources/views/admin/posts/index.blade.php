<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Administrar Blog') }}
            </h2>
            <a href="{{ route('admin.posts.create') }}" class="bg-brandDark hover:bg-brandLight text-white font-bold py-2 px-4 rounded-xl transition shadow-md">
                + Nuevo Artículo
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-100 p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 tracking-wider">
                            <th class="py-4 px-6 font-bold">Título</th>
                            <th class="py-4 px-6 font-bold">Estado</th>
                            <th class="py-4 px-6 font-bold">Fecha</th>
                            <th class="py-4 px-6 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-4 px-6 font-medium text-gray-900">{{ $post->title }}</td>
                                <td class="py-4 px-6">
                                    @if($post->is_published)
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Publicado</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Borrador</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-500">
                                    {{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-3">
                                    
                                    @if($post->is_published)
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-blue-500 hover:text-blue-700 font-semibold text-sm" title="Ver artículo público">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.posts.edit', $post) }}" class="text-brandLight hover:text-brandDark font-semibold text-sm">Editar</a>
                                    
                                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este artículo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">Aún no hay artículos creados. ¡Anímate a escribir el primero!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>