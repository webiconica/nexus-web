<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Exception;

class ChatbotController extends Controller
{
    public function sendMessage(Request $request, GeminiService $gemini)
    {
        $settings = Setting::whereIn('key', [
            'ai_enabled',
            'chatbot_enabled',
            'chatbot_model',
            'ai_default_model',
            'chatbot_name',
            'chatbot_context',
            'site_name',
            'whatsapp_number',
            'contact_directory_json',
        ])->pluck('value', 'key');

        if (($settings['ai_enabled'] ?? '0') !== '1' || ($settings['chatbot_enabled'] ?? '0') !== '1' || !$gemini->isEnabled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'El asistente virtual no está disponible en este momento.'
            ], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:600',
            'history' => 'nullable|array|max:10',
            'history.*.role' => 'required_with:history|in:user,bot',
            'history.*.text' => 'required_with:history|string|max:800',
        ]);

        try {
            $siteName = $settings['site_name'] ?? config('app.name', 'Nuestro Sitio');
            $botName = !empty($settings['chatbot_name']) ? $settings['chatbot_name'] : 'Asistente Virtual';
            $customContext = $settings['chatbot_context'] ?? '';

            // Obtener conocimiento dinámico del sitio verificando columnas existentes
            $siteKnowledge = Cache::remember('chatbot_site_knowledge_v2', 600, function () use ($settings) {
                $lines = [];

                if (Schema::hasTable('pages')) {
                    $pageQuery = Page::query()->select('title', 'slug', 'meta_description')->limit(15);
                    if (Schema::hasColumn('pages', 'is_published')) {
                        $pageQuery->where('is_published', true);
                    } elseif (Schema::hasColumn('pages', 'status')) {
                        $pageQuery->where('status', 'published');
                    }

                    $pages = $pageQuery->get();
                    if ($pages->isNotEmpty()) {
                        $lines[] = "PÁGINAS Y SERVICIOS DEL SITIO:";
                        foreach ($pages as $p) {
                            $desc = $p->meta_description ? " — {$p->meta_description}" : "";
                            $lines[] = "- {$p->title} (URL: " . url('/' . ltrim($p->slug, '/')) . "){$desc}";
                        }
                    }
                }

                if (Schema::hasTable('posts')) {
                    $postQuery = Post::query()->latest()->select('title', 'slug', 'excerpt')->limit(8);
                    if (Schema::hasColumn('posts', 'status')) {
                        $postQuery->where('status', 'published');
                    }

                    $posts = $postQuery->get();
                    if ($posts->isNotEmpty()) {
                        $lines[] = "\nARTÍCULOS RECIENTES DEL BLOG:";
                        foreach ($posts as $post) {
                            $lines[] = "- {$post->title}: {$post->excerpt}";
                        }
                    }
                }

                $hasPhones = false;
                if (!empty($settings['contact_directory_json'])) {
                    $dir = json_decode($settings['contact_directory_json'], true);
                    if (!empty($dir['phones'])) {
                        $lines[] = "\nTELÉFONOS Y WHATSAPP OFICIALES:";
                        foreach ($dir['phones'] as $ph) {
                            if (empty($ph['number'])) continue;
                            $hasPhones = true;
                            $clean = preg_replace('/[^0-9]/', '', $ph['number']);
                            $wa = !empty($ph['is_whatsapp']) ? " | WhatsApp: https://wa.me/{$clean}" : "";
                            $lines[] = "- {$ph['label']}: {$ph['number']}{$wa}";
                        }
                    }
                }

                if (!$hasPhones && !empty($settings['whatsapp_number'])) {
                    $cleanMain = preg_replace('/[^0-9]/', '', $settings['whatsapp_number']);
                    $lines[] = "\nWHATSAPP OFICIAL: {$settings['whatsapp_number']} (https://wa.me/{$cleanMain})";
                }

                return implode("\n", $lines);
            });

            $historyText = '';
            if (!empty($validated['history'])) {
                $turns = [];
                foreach ($validated['history'] as $turn) {
                    $who = $turn['role'] === 'user' ? 'Visitante' : $botName;
                    $turns[] = "{$who}: {$turn['text']}";
                }
                $historyText = "HISTORIAL RECIENTE DE LA CONVERSACIÓN:\n" . implode("\n", $turns) . "\n\n";
            }

            $systemPrompt = <<<EOT
Eres "{$botName}", el asistente virtual oficial de "{$siteName}".
Tu objetivo es atender a los visitantes de forma amable, breve, clara y orientada a resolver dudas o convertirlos en clientes.

INFORMACIÓN Y REGLAS DEL NEGOCIO (CONFIGURADAS POR EL ADMINISTRADOR):
{$customContext}

CONOCIMIENTO ACTUALIZADO DEL SITIO WEB:
{$siteKnowledge}

REGLAS DE RESPUESTA:
1. Responde siempre en español, en párrafos cortos y fáciles de leer en un chat móvil (máximo 3-4 líneas por respuesta salvo que pidan detalle).
2. Usa únicamente información real del negocio y del sitio. Si no sabes un precio o dato específico, invita al visitante a dejar su nombre y teléfono/WhatsApp para que un asesor lo contacte.
3. No inventes enlaces ni teléfonos; usa solo los proporcionados arriba.
4. CAPTURA DE PROSPECTOS (LEADS): Si el usuario expresa interés en contratar, cotizar o ser contactado, pídele amablemente su nombre y su teléfono/WhatsApp (o correo). Si en el mensaje actual o historial ya proporcionó su nombre y al menos un medio de contacto (teléfono o email), marca `lead_captured` como `true` y extrae sus datos.

{$historyText}NUEVO MENSAJE DEL VISITANTE:
{$validated['message']}
EOT;

            $schema = [
                'type' => 'OBJECT',
                'properties' => [
                    'reply' => ['type' => 'STRING'],
                    'lead_captured' => ['type' => 'BOOLEAN'],
                    'lead_name' => ['type' => 'STRING'],
                    'lead_phone' => ['type' => 'STRING'],
                    'lead_email' => ['type' => 'STRING'],
                    'lead_interest' => ['type' => 'STRING'],
                ],
                'required' => ['reply', 'lead_captured', 'lead_name', 'lead_phone', 'lead_email', 'lead_interest'],
            ];

            $model = !empty($settings['chatbot_model'])
                ? $settings['chatbot_model']
                : (!empty($settings['ai_default_model']) ? $settings['ai_default_model'] : 'gemini-2.5-flash');

            $aiResponse = $gemini->generate(
                $systemPrompt,
                'chatbot',
                $model,
                $schema,
                0.5
            );

            if (!empty($aiResponse['lead_captured']) && (!empty($aiResponse['lead_phone']) || !empty($aiResponse['lead_email']))) {
                $this->storeCapturedLead($aiResponse, $request->ip());
            }

            return response()->json([
                'status' => 'success',
                'reply' => $aiResponse['reply'] ?? '¿En qué más puedo ayudarte?',
                'lead_saved' => (bool) ($aiResponse['lead_captured'] ?? false),
            ]);

        } catch (Exception $e) {
            Log::error('Error en ChatbotController: ' . $e->getMessage());

            $friendlyMsg = 'Tuve un pequeño problema técnico al procesar tu mensaje. Por favor intenta de nuevo o contáctanos por WhatsApp.';
            if (config('app.debug')) {
                $friendlyMsg .= "\n\n[Debug]: " . $e->getMessage();
            }

            return response()->json([
                'status' => 'error',
                'message' => $friendlyMsg
            ], 500);
        }
    }

    /**
     * Registra o actualiza el prospecto capturado en la tabla leads evitando duplicados en la misma sesión
     */
    protected function storeCapturedLead(array $data, ?string $ip): void
    {
        $phone = trim((string) ($data['lead_phone'] ?? ''));
        $email = trim((string) ($data['lead_email'] ?? ''));
        $name = trim((string) ($data['lead_name'] ?? ''));
        $interest = trim((string) ($data['lead_interest'] ?? ''));

        $recentLeads = Lead::where('origen', 'Chatbot IA')
            ->where('created_at', '>=', now()->subHours(2))
            ->latest()
            ->get();

        $existingLead = null;

        foreach ($recentLeads as $recent) {
            $datos = is_array($recent->datos_formulario) ? $recent->datos_formulario : [];

            $sameIp = !empty($ip) && ($datos['ip'] ?? '') === $ip;
            $samePhone = $phone !== '' && ($datos['telefono'] ?? '') === $phone;
            $sameEmail = $email !== '' && ($datos['email'] ?? '') === $email;

            if ($sameIp || $samePhone || $sameEmail) {
                $existingLead = $recent;
                break;
            }
        }

        if ($existingLead) {
            $current = is_array($existingLead->datos_formulario) ? $existingLead->datos_formulario : [];

            // Conservar el nombre más completo (ej. "Julio Iglesias" sobre "Julio")
            $bestName = $current['nombre'] ?? 'Visitante Chat IA';
            if ($name !== '' && mb_strlen($name) >= mb_strlen($bestName)) {
                $bestName = $name;
            }

            // Combinar intereses si aportó nuevos detalles
            $bestInterest = $current['interes'] ?? '';
            if ($interest !== '' && !str_contains($bestInterest, $interest)) {
                $bestInterest = $bestInterest !== '' ? "{$bestInterest} | {$interest}" : $interest;
            }

            $existingLead->update([
                'datos_formulario' => array_filter([
                    'nombre'   => $bestName,
                    'telefono' => $phone !== '' ? $phone : ($current['telefono'] ?? null),
                    'email'    => $email !== '' ? $email : ($current['email'] ?? null),
                    'interes'  => $bestInterest ?: 'Consulta desde Chatbot IA',
                    'ip'       => $ip ?: ($current['ip'] ?? null),
                ]),
            ]);

            return;
        }

        Lead::create([
            'tenant_id' => 1,
            'origen' => 'Chatbot IA',
            'datos_formulario' => array_filter([
                'nombre'   => $name !== '' ? $name : 'Visitante Chat IA',
                'telefono' => $phone ?: null,
                'email'    => $email ?: null,
                'interes'  => $interest !== '' ? $interest : 'Consulta desde Chatbot IA',
                'ip'       => $ip,
            ]),
        ]);
    }
}