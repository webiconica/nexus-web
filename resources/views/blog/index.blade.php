@extends('layouts.web')

@section('content')
    <div class="py-24 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- ENCABEZADO AGNOSTICO Y DINAMICO -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-4">
                <span class="bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-widest px-4 py-1.5 rounded-full inline-block shadow-sm border border-gray-200">
                    Artículos & Novedades
                </span>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight leading-tight">
                    Blog de {{ $globalSettings['site_name'] ?? 'Noticias' }}
                </h1>
                <p class="text-lg text-gray-500 leading-relaxed">
                    {{ $globalSettings['site_description'] ?? 'Explora nuestras últimas publicaciones, guías y contenido de interés.' }}
                </p>
            </div>

            <!-- GRILLA DE ARTICULOS -->
            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <article class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                            
                            <!-- Imagen destacada -->
                            <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-video bg-gray-100 overflow-hidden relative">
                                @if($post->featured_image)
                                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                        <i class="fa-solid fa-newspaper text-4xl"></i>
                                    </div>
                                @endif
                            </a>

                            <!-- Contenido de la tarjeta -->
                            <div class="p-8 flex flex-col flex-1">
                                <div class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-3 flex items-center gap-2">
                                    <i class="fa-regular fa-calendar text-[11px]"></i>
                                    {{ $post->published_at ? $post->published_at->format('d M, Y') : ($post->created_at ? $post->created_at->format('d M, Y') : 'Publicación') }}
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 mb-3 leading-snug group-hover:text-gray-600 transition-colors">
                                    <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                                </h2>

                                <p class="text-gray-500 text-sm mb-6 flex-1 line-clamp-3 leading-relaxed">
                                    {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 120) }}
                                </p>

                                <a href="{{ route('blog.show', $post->slug) }}" class="font-bold text-gray-900 group-hover:text-gray-600 transition-colors inline-flex items-center gap-2 text-sm mt-auto">
                                    Leer artículo <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- PAGINACION -->
                <div class="mt-16 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @else
                <!-- Estado vacío sin publicaciones -->
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200 max-w-xl mx-auto p-8">
                    <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Próximamente nuevos artículos</h3>
                    <p class="text-sm text-gray-500">Estamos preparando contenido de valor para compartirlo contigo muy pronto.</p>
                </div>
            @endif

        </div>
    </div>
@endsection