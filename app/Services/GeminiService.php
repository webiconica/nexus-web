<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService
{
    protected string $apiKey;
    protected float $exchangeRate;

    public function __construct()
    {
        $dbKey = Setting::where('key', 'gemini_api_key')->value('value');
        $this->apiKey = !empty(trim((string) $dbKey)) ? trim($dbKey) : (string) env('GEMINI_API_KEY', '');

        $rate = Setting::where('key', 'ai_usd_to_mxn')->value('value');
        $this->exchangeRate = is_numeric($rate) && (float) $rate > 0 ? (float) $rate : 20.00;
    }

    /**
     * Catálogo central de modelos disponibles y sus tarifas aproximadas por 1M de tokens (USD)
     */
    public static function availableModels(): array
    {
        return [
            'gemini-3.8-flash' => [
                'label' => 'Gemini 3.8 Flash (Recomendado - Rápido y Económico)',
                'input_per_1m' => 0.15,
                'output_per_1m' => 0.60,
            ],
            'gemini-2.5-flash-lite' => [
                'label' => 'Gemini 2.5 Flash-Lite (Ultra Económico - Ideal Chatbot)',
                'input_per_1m' => 0.075,
                'output_per_1m' => 0.30,
            ],
            'gemini-3.1-pro-preview' => [
                'label' => 'Gemini 3.1 Pro (Máxima Calidad y Razonamiento)',
                'input_per_1m' => 1.25,
                'output_per_1m' => 10.00,
            ],
        ];
    }

    /**
     * Verifica si la IA está activa globalmente y tiene API Key configurada
     */
    public function isEnabled(): bool
    {
        $enabled = Setting::where('key', 'ai_enabled')->value('value');
        return $enabled === '1' && !empty($this->apiKey);
    }

    /**
     * Prueba la API Key consultando la lista de modelos (no consume tokens ni saldo)
     */
    public function testConnection(?string $customKey = null): array
    {
        $keyToTest = !empty(trim((string) $customKey)) ? trim($customKey) : $this->apiKey;

        if (empty($keyToTest)) {
            return ['ok' => false, 'message' => 'No hay ninguna API Key ingresada para probar.'];
        }

        $response = Http::timeout(15)
            ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$keyToTest}");

        if ($response->successful()) {
            return ['ok' => true, 'message' => 'Conexión exitosa con Google Gemini API. La llave es válida.'];
        }

        $errorMsg = $response->json('error.message') ?? 'Error desconocido al conectar con Google.';
        return ['ok' => false, 'message' => 'Error de API: ' . $errorMsg];
    }

    /**
     * Genera contenido de texto o JSON estructurado y registra automáticamente el consumo
     */
    public function generate(
        string $prompt,
        string $module = 'general',
        ?string $model = null,
        ?array $responseSchema = null,
        float $temperature = 0.7
    ): array {
        if (empty($this->apiKey)) {
            throw new Exception('La API Key de Gemini no está configurada.');
        }

        $selectedModel = $model
            ?: Setting::where('key', 'ai_default_model')->value('value')
            ?: 'gemini-3.8-flash';

        // Reemplazo automático si la BD aún tiene guardado el modelo retirado
        if ($selectedModel === 'gemini-2.5-flash') {
            $selectedModel = 'gemini-3.8-flash';
        }

        $generationConfig = [
            'temperature' => $temperature,
            'maxOutputTokens' => 16384,
        ];

        if ($responseSchema !== null) {
            $generationConfig['responseMimeType'] = 'application/json';
            $generationConfig['responseSchema'] = $responseSchema;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$selectedModel}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(90)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => $generationConfig,
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new Exception("Error Gemini ({$selectedModel}): {$error}");
        }

        $json = $response->json();
        $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Registrar consumo de tokens y costo
        $usage = $json['usageMetadata'] ?? [];
        $promptTokens = (int) ($usage['promptTokenCount'] ?? 0);
        $completionTokens = (int) ($usage['candidatesTokenCount'] ?? 0);
        $totalTokens = (int) ($usage['totalTokenCount'] ?? ($promptTokens + $completionTokens));

        $costUsd = $this->calculateCostUsd($selectedModel, $promptTokens, $completionTokens);
        $costMxn = $costUsd * $this->exchangeRate;

        AiUsageLog::create([
            'module' => $module,
            'model' => $selectedModel,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'cost_usd' => $costUsd,
            'cost_mxn' => $costMxn,
        ]);

        if ($responseSchema !== null) {
            $decoded = json_decode($rawText, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('La IA devolvió un JSON inválido.');
            }
            return $decoded;
        }

        return ['text' => $rawText];
    }

    /**
     * Calcula el costo en USD según tarifas del modelo
     */
    protected function calculateCostUsd(string $model, int $promptTokens, int $completionTokens): float
    {
        $catalog = self::availableModels();
        $rates = $catalog[$model] ?? ['input_per_1m' => 0.15, 'output_per_1m' => 0.60];

        $inputCost = ($promptTokens / 1_000_000) * $rates['input_per_1m'];
        $outputCost = ($completionTokens / 1_000_000) * $rates['output_per_1m'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Devuelve el resumen financiero para mostrar en Configuración Global
     */
    public static function getUsageSummary(): array
    {
        $budgetMxn = (float) (Setting::where('key', 'ai_budget_mxn')->value('value') ?? 100.00);
        $spentMxn = (float) AiUsageLog::sum('cost_mxn');
        $spentUsd = (float) AiUsageLog::sum('cost_usd');
        $totalRequests = AiUsageLog::count();
        $totalTokens = (int) AiUsageLog::sum('total_tokens');

        $remainingMxn = max(0, $budgetMxn - $spentMxn);
        $percentUsed = $budgetMxn > 0 ? min(100, round(($spentMxn / $budgetMxn) * 100, 1)) : 0;

        return [
            'budget_mxn' => $budgetMxn,
            'spent_mxn' => round($spentMxn, 2),
            'spent_usd' => round($spentUsd, 4),
            'remaining_mxn' => round($remainingMxn, 2),
            'percent_used' => $percentUsed,
            'low_balance' => $budgetMxn > 0 && ($remainingMxn / $budgetMxn) <= 0.15,
            'total_requests' => $totalRequests,
            'total_tokens' => $totalTokens,
        ];
    }
}