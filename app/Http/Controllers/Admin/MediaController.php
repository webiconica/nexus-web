<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// Nuevas clases requeridas para Intervention Image v4
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;

class MediaController extends Controller
{
    private $folder = 'media';

    public function index()
    {
        $files = Storage::disk('public')->files($this->folder);
        
        $media = array_map(function ($file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            // Definimos si es imagen para renderizarla, o si es otro documento
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
            
            return [
                'path' => $file,
                'url' => asset('storage/' . $file),
                'name' => basename($file),
                'extension' => $extension,
                'is_image' => $isImage,
                'size' => round(Storage::disk('public')->size($file) / 1024, 2) . ' KB'
            ];
        }, $files);

        $media = array_reverse($media);

        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg,webp,pdf,mp3,mp4,xlsx,xls,doc,docx,zip|max:20480',
        ]);

        if ($request->hasFile('files')) {
            // 1. Iniciamos el motor de imágenes (Sintaxis V4)
            $manager = ImageManager::usingDriver(Driver::class);

            foreach ($request->file('files') as $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);

                if ($isImage) {
                    $filename = time() . '_' . uniqid() . '.webp';
                    
                    // 2. Leemos la imagen (En V4 es 'decode' en lugar de 'read')
                    $image = $manager->decode($file);
                    
                    // 3. Convertimos a WebP con 80% de calidad (Sintaxis V4)
                    $encoded = $image->encodeUsingFormat(Format::WEBP, quality: 80);
                    
                    // 4. Guardamos en storage (casteando a string para evitar errores binarios)
                    Storage::disk('public')->put($this->folder . '/' . $filename, (string) $encoded);
                } else {
                    // Si es un PDF, Audio, Excel, etc., lo guardamos normal
                    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '', $file->getClientOriginalName());
                    $file->storeAs($this->folder, $filename, 'public');
                }
            }
        }

        return back()->with('success', '¡Archivos subidos y optimizados correctamente!');
    }

    
  	public function destroy(Request $request)
    {
        // 1. Tomamos el input y usamos basename para extraer estrictamente el nombre del archivo
        $filename = basename($request->input('filename'));

        // 2. Armamos la ruta limpia usando la propiedad de tu carpeta (ej: media/nombre.webp)
        $path = $this->folder . '/' . $filename;

        // 3. Verificamos si existe en el disco público
        if (Storage::disk('public')->exists($path)) {
            // Borramos el archivo físico
            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente.'
            ]);
        }

        // 4. Si falla, devolvemos un JSON descriptivo para poder auditar el error en la consola F12
        return response()->json([
            'success' => false,
            'error' => 'Archivo no encontrado en el servidor.',
            'debug_filename_recibido' => $request->input('filename'),
            'debug_ruta_buscada' => $path
        ], 404);
    }

  // Función para alimentar la ventana modal emergente
    public function apiIndex()
    {
        $files = Storage::disk('public')->files($this->folder);
        
        $media = array_map(function ($file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return [
                'url' => asset('storage/' . $file),
                'name' => basename($file),
                'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']),
                'extension' => $extension
            ];
        }, $files);

        // Devolvemos el JSON invertido (los más nuevos primero)
        return response()->json(array_reverse($media));
    }
}