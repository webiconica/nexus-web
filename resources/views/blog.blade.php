<x-web-layout>
    <div class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="bg-purple-100 text-purple-700 text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full inline-block mb-4 shadow-sm">
                    Recursos y Estrategias
                </span>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-4 tracking-tight">
                    Blog de <span class="text-brandLight">Webiconica</span>
                </h1>
                <p class="text-xl text-gray-500">
                    Guías, tecnología y herramientas para escalar tu infraestructura digital.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <article class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col group">
                        
                        <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-video bg-gray-100 overflow-hidden relative">
                            @if($post->featured_image)
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-image text-4xl"></i>
                                </div>
                            @endif
                        </a>

                        <div class="p-8 flex flex-col flex-1">
                            <div class="text-xs font-bold text-brandLight tracking-widest uppercase mb-3">
                                {{ $post->published_at ? $post->published_at->format('d M, Y') : 'Borrador' }}
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mb-4 leading-tight group-hover:text-brandLight transition-colors">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>
                            <p class="text-gray-500 text-sm mb-6 flex-1 line-clamp-3">
                                {{ $post->excerpt }}
                            </p>
                            <a href="{{ route('blog.show', $post->slug) }}" class="font-bold text-brandDark group-hover:text-brandLight transition-colors inline-flex items-center gap-2 text-sm mt-auto">
                                Leer artículo <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-16">
                {{ $posts->links() }}
            </div>
            
        </div>
    </div>
</x-web-layout>