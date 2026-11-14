<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Muestra el formulario con las configuraciones actuales.
     */
    public function index()
    {
        // pluck() convierte la colección en un array asociativo simple ['key' => 'value']
        $settings = Setting::pluck('value', 'key')->toArray();
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Procesa y guarda las configuraciones enviadas desde el formulario.
     */
    public function update(Request $request)
    {
        // Excluimos los tokens de seguridad de Laravel del bucle
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            
            // 1. LÓGICA PARA ARCHIVOS (Imágenes, Favicon, Logos)
            if ($request->hasFile($key)) {
                
                $file = $request->file($key);
                
                // Buscamos si ya existía una imagen guardada para esta clave
                $oldSetting = Setting::where('key', $key)->first();
                
                // Si existe un archivo anterior, lo borramos del disco public
                if ($oldSetting && $oldSetting->value) {
                    Storage::disk('public')->delete($oldSetting->value);
                }

                // Guardamos el nuevo archivo en la carpeta 'settings'
                // Usamos la extensión original para mantener el formato (ej. .ico, .png, .svg)
                $fileName = $key . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('settings', $fileName, 'public');
                
                // Actualizamos o creamos el registro indicando que es tipo 'image'
                Setting::updateOrCreate(
                    ['key' => $key], 
                    ['value' => $path, 'type' => 'image']
                );
            } 
            
            // 2. LÓGICA PARA TEXTOS Y CÓDIGOS (Scripts, URLs, Nombres)
            else {
                // Determinamos el tipo. Si la clave termina en '_scripts', lo marcamos como 'code'
                $type = str_ends_with($key, '_scripts') ? 'code' : 'text';

                // Si el valor viene vacío (null), lo permitimos para que el usuario pueda borrar scripts
                Setting::updateOrCreate(
                    ['key' => $key], 
                    ['value' => $value, 'type' => $type]
                );
            }
        }

        // 3. LIMPIEZA DE CACHÉ
        // Borramos la caché para que el sitio web refleje los cambios instantáneamente
        Cache::forget('global_settings');

        return redirect()->back()->with('success', '¡Configuración global actualizada correctamente!');
    }
}