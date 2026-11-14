<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Biblioteca Multimedia') }}
            </h2>
            
            <div x-data="{}" class="flex items-center gap-4">
                <span id="loading-indicator" style="display: none;" class="text-sm font-bold text-brandLight animate-pulse">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Procesando...
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="mediaManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 min-h-[500px] flex flex-col relative">
                
                <div class="flex justify-between items-center border-b border-gray-100 pb-6 mb-6">
                    <h3 class="text-lg font-bold uppercase tracking-widest text-brandDark">
                        <i class="fas fa-folder-open mr-2"></i> Todos los Archivos
                    </h3>
                    
                    <label class="cursor-pointer bg-brandDark hover:bg-brandLight text-white px-6 py-3 rounded-xl text-sm font-bold transition shadow-md flex items-center gap-2">
                        <i class="fas fa-cloud-upload-alt"></i> Subir Archivos Nuevos
                        <input type="file" multiple class="hidden" @change="uploadFiles">
                    </label>
                </div>

                <div x-show="isLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/80 text-gray-400 rounded-3xl">
                    <i class="fas fa-spinner fa-spin text-5xl mb-4"></i>
                    <p class="font-bold tracking-widest uppercase text-sm">Sincronizando Biblioteca...</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6" x-show="!isLoading">
                    
                    <template x-for="item in mediaItems" :key="item.url">
                        <div class="group relative bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden hover:border-brandLight hover:shadow-xl transition duration-300">
                            
                            <button type="button" @click.stop="deleteFile(item.name)" class="absolute top-2 right-2 z-20 bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 hover:scale-110 transition-all shadow-md" title="Eliminar archivo">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>

                            <div class="aspect-square flex items-center justify-center relative bg-white cursor-pointer" @click="previewImage(item)">
                                <template x-if="item.is_image">
                                    <img :src="item.url" class="object-cover w-full h-full group-hover:scale-110 transition duration-500">
                                </template>
                                <template x-if="!item.is_image">
                                    <i class="fas fa-file-alt text-5xl text-gray-300 group-hover:scale-110 group-hover:text-brandLight transition duration-500"></i>
                                </template>

                                <div class="absolute inset-0 bg-brandDark/70 opacity-0 group-hover:opacity-100 flex flex-col gap-2 items-center justify-center transition duration-300 backdrop-blur-sm">
                                    <span class="text-white text-xs font-medium tracking-wide" x-text="item.is_image ? '🔍 Ver Grande' : '📄 Archivo'"></span>
                                    <button @click.stop="copyUrl(item.url)" class="bg-white text-brandDark px-4 py-1.5 rounded-full text-[11px] font-bold shadow-lg hover:scale-105 transition hover:bg-brandLight hover:text-white">
                                        <i class="fas fa-link mr-1"></i> Copiar URL
                                    </button>
                                </div>
                            </div>

                            <div class="p-3 text-center border-t border-gray-100 bg-gray-50 group-hover:bg-brandLight transition-colors duration-300">
                                <p class="text-[10px] text-gray-500 group-hover:text-white truncate font-bold" x-text="item.name"></p>
                            </div>
                            
                        </div>
                    </template>

                    <div x-show="mediaItems.length === 0 && !isLoading" class="col-span-full py-12 text-center text-gray-400">
                        <i class="fas fa-folder-open text-6xl mb-4 opacity-50"></i>
                        <p class="text-lg font-bold">Tu biblioteca está vacía</p>
                        <p class="text-sm">Sube algunas imágenes para comenzar.</p>
                    </div>
                </div>

            </div>
        </div>

        <div x-show="isPreviewOpen" style="display: none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/95 backdrop-blur-sm" x-transition @click="isPreviewOpen = false">
            <button type="button" @click="isPreviewOpen = false" class="absolute top-6 right-6 text-white/70 hover:text-white transition text-3xl z-50">
                <i class="fas fa-times"></i>
            </button>

            <div class="max-w-4xl max-h-[85vh] p-4 flex flex-col items-center justify-center" @click.stop>
                <img :src="activePreviewUrl" class="max-w-full max-h-[75vh] object-contain rounded-2xl shadow-2xl border border-white/10">
                <p class="text-white/80 text-sm font-semibold tracking-wide mt-4 bg-brandDark/50 px-4 py-2 rounded-full border border-white/5" x-text="activePreviewName"></p>
            </div>
        </div>
        </div>

    <script>
        function mediaManager() {
            return {
                isLoading: true,
                mediaItems: [],
                // Variables para el Lightbox
                isPreviewOpen: false,
                activePreviewUrl: '',
                activePreviewName: '',

                init() {
                    this.fetchMedia();
                },

                async fetchMedia() {
                    try {
                        let res = await fetch("{{ route('admin.media.api') }}");
                        this.mediaItems = await res.json();
                    } catch (error) {
                        console.error('Error cargando medios:', error);
                    }
                    this.isLoading = false;
                },

                // NUEVA FUNCIÓN: Disparar el previsualizador en grande
                previewImage(item) {
                    if (!item.is_image) return; // Si es un zip o pdf no hace nada
                    this.activePreviewUrl = item.url;
                    this.activePreviewName = item.name;
                    this.isPreviewOpen = true;
                },

                async uploadFiles(event) {
                    const files = event.target.files;
                    if (!files.length) return;

                    document.getElementById('loading-indicator').style.display = 'inline-block';
                    
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
                        alert("Ocurrió un error al subir el archivo.");
                    }

                    document.getElementById('loading-indicator').style.display = 'none';
                    event.target.value = '';
                },

                async deleteFile(filename) {
                    if (!confirm("¿Estás seguro de que deseas eliminar permanentemente este archivo?")) return;

                    document.getElementById('loading-indicator').style.display = 'inline-block';
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
                            alert("No se pudo eliminar el archivo. Revisa la consola.");
                        }
                    } catch (error) {
                        console.error("Error eliminando:", error);
                    }
                    
                    document.getElementById('loading-indicator').style.display = 'none';
                },
                
                copyUrl(url) {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('¡URL copiada al portapapeles!');
                    });
                }
            }
        }
    </script>
</x-app-layout>