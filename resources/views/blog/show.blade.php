@extends('layouts.web')

@section('content')
    <article class="py-24 bg-white min-h-screen">
        
        <!-- ENCABEZADO DEL ARTÍCULO -->
        <header class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-16 space-y-6">
            <div class="text-xs font-bold text-gray-400 tracking-widest uppercase flex items-center justify-center gap-2">
                <i class="fa-regular fa-calendar text-[11px]"></i>
                Publicado el {{ $post->published_at ? $post->published_at->format('d M, Y') : ($post->created_at ? $post->created_at->format('d M, Y') : 'Borrador') }}
            </div>
            
            <h1 class="text-4xl md:text-6xl font-black text-gray-900 leading-tight tracking-tight">
                {{ $post->title }}
            </h1>

            @if($post->excerpt)
                <p class="text-lg md:text-xl text-gray-500 max-w-2xl mx-auto leading-relaxed font-normal">
                    {{ $post->excerpt }}
                </p>
            @endif
        </header>

        <!-- IMAGEN DESTACADA DE PORTADA -->
        @if($post->featured_image)
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
                <div class="overflow-hidden rounded-3xl md:rounded-[2.5rem] shadow-xl border border-gray-100 bg-gray-50">
                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-auto max-h-[550px] object-cover">
                </div>
            </div>
        @endif

        <!-- CONTENIDO PRINCIPAL (INYECCIÓN CODEMIRROR / PROSE AGNOSTICO) -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="prose prose-lg max-w-none text-gray-800 leading-relaxed font-normal
                        prose-headings:font-bold prose-headings:text-gray-900 prose-headings:tracking-tight
                        prose-a:font-semibold prose-a:underline hover:prose-a:opacity-80
                        prose-img:rounded-2xl prose-img:shadow-md">
                {!! $post->content !!}
            </div>

            <!-- PIE DEL ARTÍCULO / NAVEGACIÓN Y COMPARTIR -->
            <div class="mt-20 pt-10 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-6"
                 x-data="{
                    sharePost() {
                        if (navigator.share) {
                            navigator.share({
                                title: '{{ addslashes($post->title) }}',
                                text: '{{ addslashes($post->excerpt ?? $post->title) }}',
                                url: window.location.href
                            }).catch(() => {});
                        } else {
                            navigator.clipboard.writeText(window.location.href);
                            if (typeof this.showToast === 'function') {
                                this.showToast('Enlace copiado al portapapeles');
                            } else {
                                alert('Enlace copiado al portapapeles');
                            }
                        }
                    }
                 }">
                
                <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-900 font-bold text-sm transition-colors">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Volver a todos los artículos
                </a>
                
                <button type="button" @click="sharePost()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-full font-bold text-sm transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-share-nodes"></i>
                    <span>Compartir</span>
                </button>
            </div>
            
        </div>
    </article>
@endsection