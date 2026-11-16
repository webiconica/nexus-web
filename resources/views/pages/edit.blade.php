<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-brandDark uppercase tracking-widest leading-tight">
                {{ __('Editar Página: ') }} <span class="text-brandLight">{{ $page->title }}</span>
            </h2>
            <div class="flex items-center gap-3">
                @include('admin.partials.ai-generator-modal', ['module' => 'pages'])
                <a href="{{ route('admin.pages.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-4 py-2 rounded-xl transition text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .CodeMirror {
            height: 600px;
            font-family: monospace;
            font-size: 14px;
            border-radius: 0.75rem;
        }
    </style>

    <div class="py-8" x-data="mediaModal()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <form action="{{ route('admin.pages.update', $page) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Contenido de la Página</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Título de la Página *</label>
                            <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">URL Personalizada (Slug)</label>
                            <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-bold uppercase text-gray-500">Contenido HTML / Texto Principal</label>
                            
                            <div class="flex gap-2">
                                <button type="button" @click="openModal('', 'copy')" class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold py-1.5 px-3 rounded-lg transition shadow-sm border border-blue-200">
                                    <i class="fas fa-images"></i> Biblioteca
                                </button>

                                <button type="button" @click="$dispatch('format-code-page')" class="inline-flex items-center gap-1 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold py-1.5 px-3 rounded-lg transition shadow-sm border border-purple-200">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Ordenar Código
                                </button>
                            </div>
                        </div>

                        @include('admin.partials.modular-page-builder', ['initialContent' => old('content', $page->content ?? '')])
                    </div>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Motores de Búsqueda (SEO) y Redes Sociales</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Meta Título SEO</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Palabras Clave (Keywords)</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords) }}" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Meta Descripción SEO</label>
                        <textarea name="meta_description" rows="3" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight">{{ old('meta_description', $page->meta_description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Favicon Específico (URL)</label>
                            <div class="flex gap-2">
                                <input type="text" id="favicon_path" name="favicon_path" value="{{ old('favicon_path', $page->favicon_path ?? '') }}" placeholder="Ej. https://webiconica.com/storage/media/..." class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight text-sm">
                                <button type="button" @click="openModal('favicon_path')" class="bg-brandDark hover:bg-brandLight text-white px-4 py-3 rounded-xl flex items-center justify-center transition shadow-sm" title="Abrir Biblioteca">
                                    <i class="fas fa-folder-open"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Imagen Redes Sociales (OG Image URL)</label>
                            <div class="flex gap-2">
                                <input type="text" id="og_image_path" name="og_image_path" value="{{ old('og_image_path', $page->og_image_path ?? '') }}" placeholder="Ej. https://webiconica.com/storage/media/..." class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight text-sm">
                                <button type="button" @click="openModal('og_image_path')" class="bg-brandDark hover:bg-brandLight text-white px-4 py-3 rounded-xl flex items-center justify-center transition shadow-sm" title="Abrir Biblioteca">
                                    <i class="fas fa-folder-open"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-brandDark border-b pb-2">Herramientas de Marketing e Inyección</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Scripts en el Header</label>
                            <textarea name="head_scripts" rows="5" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight font-mono text-xs text-purple-800">{{ old('head_scripts', $page->head_scripts) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Scripts en el Footer</label>
                            <textarea name="footer_scripts" rows="5" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:border-brandLight font-mono text-xs text-purple-800">{{ old('footer_scripts', $page->footer_scripts) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('admin.pages.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-6 py-3 rounded-xl transition text-sm">Cancelar</a>
                    <button type="submit" class="bg-brandDark hover:bg-brandLight text-white font-bold px-8 py-3 rounded-xl transition text-sm shadow-md">Guardar Cambios</button>
                </div>
            </form>
        </div>

        <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-brandDark/80 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-3xl shadow-2xl w-11/12 max-w-5xl h-[80vh] flex flex-col overflow-hidden" @click.away="isModalOpen = false">
                
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-brandDark uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-images"></i> Biblioteca
                        <span x-show="isUploading" class="text-xs text-brandLight normal-case tracking-normal ml-2">
                            <i class="fas fa-spinner fa-spin"></i> Subiendo...
                        </span>
                    </h3>
                    
                    <div class="flex items-center gap-4">
                        <label class="cursor-pointer bg-brandDark hover:bg-brandLight text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-sm">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Subir Imagen
                            <input type="file" multiple class="hidden" @change="uploadFiles">
                        </label>
                        
                        <button type="button" @click="isModalOpen = false" class="text-gray-400 hover:text-red-500 transition text-2xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 bg-gray-50/50 relative">
                    <div x-show="isLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-gray-50/80 text-gray-400">
                        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                        <p>Cargando biblioteca...</p>
                    </div>
                    
                    <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" x-show="!isLoading">
                        <template x-for="item in mediaItems" :key="item.url">
                            <div class="group cursor-pointer bg-white rounded-xl border border-gray-200 overflow-hidden hover:border-brandLight hover:shadow-lg transition relative">
                                
                                <button type="button" @click.stop="deleteFile(item.name)" class="absolute top-2 right-2 z-20 bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-all shadow-md transform translate-y-2 group-hover:translate-y-0" title="Eliminar archivo">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>

                                <div class="aspect-square bg-gray-50 flex items-center justify-center relative" @click="selectItem(item.url)">
                                    <template x-if="item.is_image">
                                        <img :src="item.url" class="object-cover w-full h-full group-hover:scale-110 transition duration-500">
                                    </template>
                                    <template x-if="!item.is_image">
                                        <i class="fas fa-file text-4xl text-gray-400 group-hover:scale-110 transition duration-500"></i>
                                    </template>
                                    
                                    <div class="absolute inset-0 bg-brandDark/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                        <span class="bg-white text-brandDark px-3 py-1 rounded-full text-xs font-bold shadow-md" x-text="actionType === 'copy' ? 'Copiar URL' : 'Seleccionar'"></span>
                                    </div>
                                </div>
                                <div class="p-2 text-center border-t border-gray-50">
                                    <p class="text-[10px] text-gray-600 truncate font-semibold" x-text="item.name"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function mediaModal() {
            return {
                isModalOpen: false,
                isLoading: false,
                isUploading: false,
                mediaItems: [],
                targetInputId: '',
                actionType: 'input', 
                
                async openModal(target, action = 'input') {
                    this.targetInputId = target; 
                    this.actionType = action; 
                    this.isModalOpen = true; 
                    
                    if (this.mediaItems.length === 0) {
                        await this.fetchMedia();
                    }
                },

                async fetchMedia() {
                    this.isLoading = true;
                    try {
                        let res = await fetch("{{ route('admin.media.api') }}");
                        this.mediaItems = await res.json();
                    } catch (error) {
                        console.error('Error cargando medios:', error);
                    }
                    this.isLoading = false;
                },
                
                selectItem(url) {
                    if (this.actionType === 'copy') {
                        navigator.clipboard.writeText(url).then(() => {
                            alert('¡URL copiada al portapapeles! Ya puedes pegarla en tu editor.');
                            this.isModalOpen = false;
                        });
                    } else {
                        document.getElementById(this.targetInputId).value = url;
                        this.isModalOpen = false;
                    }
                },

                async uploadFiles(event) {
                    const files = event.target.files;
                    if (!files.length) return;

                    this.isUploading = true;
                    let formData = new FormData();
                    
                    for (let i = 0; i < files.length; i++) {
                        formData.append('files[]', files[i]);
                    }
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    try {
                        await fetch("{{ route('admin.media.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        await this.fetchMedia();
                    } catch (error) {
                        console.error("Error al subir:", error);
                        alert("Ocurrió un error al subir el archivo.");
                    }

                    this.isUploading = false;
                    event.target.value = '';
                },

                async deleteFile(filename) {
                    if (!confirm("¿Estás seguro de que deseas eliminar permanentemente este archivo?")) return;

                    this.isLoading = true;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    let formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('filename', filename);

                    try {
                        let res = await fetch("{{ route('admin.media.destroy') }}", {
                            method: 'POST', 
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        if (res.ok) {
                            await this.fetchMedia();
                        } else {
                            let errorData = await res.json();
                            console.error("Error del servidor:", errorData);
                            alert("No se pudo eliminar el archivo. Revisa la consola para más detalles.");
                            this.isLoading = false;
                        }
                    } catch (error) {
                        console.error("Error eliminando:", error);
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>