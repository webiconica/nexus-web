<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validación Principal
        $request->validate([
            'tenant_id' => 'required|integer',
            'origen' => 'required|string|max:100',
            'datos_formulario' => 'required|array',
        ]);

        // 2. Validación Anti-Spam Cloudflare Turnstile (si está configurado en .env)
        $secretKey = env('TURNSTILE_SECRET_KEY');
        if (!empty($secretKey)) {
            $turnstileToken = $request->input('turnstile_token');

            if (!$turnstileToken) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Falta la verificación de seguridad.'
                ], 422);
            }

            $verify = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => $secretKey,
                'response' => $turnstileToken,
                'remoteip' => $request->ip(),
            ]);

            if (!$verify->json('success')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validación de seguridad fallida.'
                ], 403);
            }
        }

        // 3. Sanitización de seguridad capa por capa
        $datosDinamicos = $request->input('datos_formulario');
        $datosLimpios = [];

        foreach ($datosDinamicos as $key => $value) {
            $datosLimpios[$key] = is_string($value) ? strip_tags($value) : $value;
        }

        // 4. Guardado en Base de Datos
        $lead = Lead::create([
            'tenant_id' => $request->tenant_id,
            'origen' => $request->origen,
            'datos_formulario' => $datosLimpios
        ]);

        // 5. Envío por Correo Electrónico al destinatario de Configuración Global
        $destinationEmail = Setting::where('key', 'contact_email')->value('value');
        $siteName = Setting::where('key', 'site_name')->value('value') ?? config('app.name', 'Sitio Web');

        if (!empty($destinationEmail) && filter_var($destinationEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                $filasHtml = '';
                foreach ($datosLimpios as $campo => $valor) {
                    if (!empty($valor)) {
                        $etiqueta = ucwords(str_replace('_', ' ', $campo));
                        $valorSeguro = e(is_array($valor) ? implode(', ', $valor) : $valor);
                        $filasHtml .= "
                            <tr>
                                <td style='padding: 10px 14px; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #374151; width: 35%; background-color: #f9fafb;'>{$etiqueta}</td>
                                <td style='padding: 10px 14px; border-bottom: 1px solid #e5e7eb; color: #111827;'>{$valorSeguro}</td>
                            </tr>";
                    }
                }

                $origenSeguro = e($request->origen);
                $fecha = now()->format('d/m/Y H:i');

                $htmlBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;'>
                    <div style='background-color: #111827; color: #ffffff; padding: 20px 24px;'>
                        <h2 style='margin: 0; font-size: 18px;'>Nuevo Lead Recibido - {$siteName}</h2>
                        <p style='margin: 4px 0 0; font-size: 12px; color: #9ca3af;'>Origen: {$origenSeguro} | Fecha: {$fecha}</p>
                    </div>
                    <div style='padding: 24px; background-color: #ffffff;'>
                        <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                            {$filasHtml}
                        </table>
                    </div>
                    <div style='background-color: #f3f4f6; padding: 12px 24px; text-align: center; font-size: 12px; color: #6b7280;'>
                        Este mensaje fue generado automáticamente desde el formulario de contacto de {$siteName}.
                    </div>
                </div>";

                Mail::html($htmlHtml = $htmlBody, function ($message) use ($destinationEmail, $siteName, $request) {
                    $message->to($destinationEmail)
                            ->subject("Nuevo prospecto desde {$siteName} ({$request->origen})");
                });
            } catch (\Throwable $e) {
                // Si el servidor SMTP falla o no está configurado, registramos el aviso en el log sin romper la experiencia del usuario
                Log::warning("No se pudo enviar el correo del lead #{$lead->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lead guardado y procesado correctamente.'
        ]);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('dashboard')->with('status', 'lead-eliminado');
    }
}