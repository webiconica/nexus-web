<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AiUsageLog;
use App\Services\GeminiService;
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
        $settings = Setting::pluck('value', 'key')->toArray();
        $aiModels = GeminiService::availableModels();
        $aiSummary = GeminiService::getUsageSummary();
        
        return view('admin.settings.index', compact('settings', 'aiModels', 'aiSummary'));
    }

    /**
     * Procesa y guarda las configuraciones enviadas desde el formulario.
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        $booleanKeys = ['ai_enabled', 'chatbot_enabled'];
        foreach ($booleanKeys as $boolKey) {
            Setting::updateOrCreate(
                ['key' => $boolKey],
                ['value' => $request->has($boolKey) ? '1' : '0']
            );
        }

        Cache::forget('global_settings');

        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $oldSetting = Setting::where('key', $key)->first();
                
                if ($oldSetting && $oldSetting->value) {
                    Storage::disk('public')->delete($oldSetting->value);
                }

                $fileName = $key . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('settings', $fileName, 'public');
                
                Setting::updateOrCreate(
                    ['key' => $key], 
                    ['value' => $path, 'type' => 'image']
                );
            } else {
                $type = str_ends_with($key, '_scripts') ? 'code' : 'text';

                Setting::updateOrCreate(
                    ['key' => $key], 
                    ['value' => $value, 'type' => $type]
                );
            }
        }

        Cache::forget('global_settings');

        return redirect()->back()->with('success', '¡Configuración global actualizada correctamente!');
    }

    public function testAiConnection(Request $request, GeminiService $gemini)
    {
        $result = $gemini->testConnection($request->input('api_key'));
        return response()->json($result);
    }

    public function resetAiUsage()
    {
        AiUsageLog::truncate();
        return back()->with('success', 'El historial de consumo de IA ha sido reiniciado a $0.00 MXN.');
    }

    /**
     * Genera la paleta de colores y tipografía con IA según el giro o descripción del negocio.
     */
    public function generateTheme(Request $request, GeminiService $gemini)
    {
        if (!$gemini->isEnabled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'La Inteligencia Artificial está desactivada o falta configurar la API Key en Configuración Global.'
            ], 403);
        }

        $contexto = trim((string) $request->input('contexto', ''));

        if ($contexto === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Por favor selecciona o describe el giro de tu negocio.'
            ], 422);
        }

        $prompt = <<<EOT
Actúa como un director de arte y diseñador UI/UX experto en Branding.
Genera una paleta de 4 colores armónicos en formato Hexadecimal (#RRGGBB) y elige la mejor tipografía para el siguiente giro o descripción de negocio:
"{$contexto}"

REGLAS ESTRICTAS:
1. Respeta cualquier preferencia de color o atmósfera que el usuario haya mencionado en su descripción.
2. El campo "brandDark" debe ser el color principal oscuro o de fuerte contraste (para fondos principales oscuros o textos titulares).
3. El campo "brandLight" debe ser el color de acento vibrante (para botones, íconos y llamados a la acción).
4. El campo "brandGray" debe ser un tono neutro/gris armónico para textos secundarios.
5. El campo "brandBg" debe ser el color de fondo base del sitio.
6. El campo "font" DEBE ser exactamente una de estas opciones: "Plus Jakarta Sans", "Inter", "Montserrat", "Poppins", "Space Grotesk", "Playfair Display", "Oswald".
EOT;

        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'brandDark'  => ['type' => 'STRING'],
                'brandLight' => ['type' => 'STRING'],
                'brandGray'  => ['type' => 'STRING'],
                'brandBg'    => ['type' => 'STRING'],
                'font'       => ['type' => 'STRING'],
            ],
            'required' => ['brandDark', 'brandLight', 'brandGray', 'brandBg', 'font'],
        ];

        try {
            $aiData = $gemini->generate(
                $prompt,
                'settings_theme',
                null,
                $schema
            );

            if (!empty($aiData['brandDark']) && !empty($aiData['font'])) {
                return response()->json([
                    'status' => 'success',
                    'data'   => $aiData,
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'La IA no devolvió un formato de colores válido.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error de IA: ' . $e->getMessage()
            ], 500);
        }
    }
}