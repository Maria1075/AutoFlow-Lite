<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiUrl;

    public function __construct()
    {
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent";
    }

    public function analyzeProcess(string $name, string $description): array
    {
        try {
            $response = Http::timeout(30)->post($this->apiUrl . '?key=' . config('services.gemini.key'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $this->buildPrompt($name, $description)],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                $analysis = json_decode($this->extractJson($text), true);

                if (is_array($analysis) && isset($analysis['feasibility'])) {
                    return $analysis;
                }
            }

            Log::warning('Gemini API returned unexpected response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());
        }

        return $this->fallbackAnalysis($name);
    }

    /**
     * Sugiere un workflow completo (trigger + acciones) para automatizar un proceso.
     * Devuelve la configuración lista para usar con la API.
     */
    public function suggestWorkflow(string $name, string $description): array
    {
        $prompt = <<<PROMPT
Eres un experto en automatización de procesos empresariales con Laravel.
Analiza el siguiente proceso y sugiere un workflow de automatización.

Responde ÚNICAMENTE con un JSON válido con esta estructura exacta:
{
  "workflow_name": "nombre del workflow",
  "workflow_description": "descripción breve",
  "trigger": {
    "name": "nombre del trigger",
    "type": "manual|webhook|cron",
    "cron_expression": "0 8 1 * *"
  },
  "actions": [
    {
      "name": "nombre de la acción",
      "type": "http_request|log",
      "sort_order": 1,
      "config": {
        "url": "https://ejemplo.com/notify",
        "method": "POST",
        "headers": {},
        "body": { "mensaje": "El proceso se ha ejecutado" }
      }
    }
  ],
  "explanation": "explicación breve de por qué esta es la mejor configuración"
}

Proceso: {$name}
Descripción: {$description}
PROMPT;

        try {
            $response = Http::timeout(30)->post($this->apiUrl . '?key=' . config('services.gemini.key'), [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                $suggestion = json_decode($this->extractJson($text), true);

                if (is_array($suggestion) && isset($suggestion['workflow_name'])) {
                    return $suggestion;
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini suggestWorkflow error: ' . $e->getMessage());
        }

        // Fallback con sugerencia genérica
        return [
            'workflow_name'        => "Automatización de {$name}",
            'workflow_description' => "Workflow generado para: {$description}",
            'trigger'              => ['name' => 'Activación manual', 'type' => 'manual', 'cron_expression' => null],
            'actions'              => [
                ['name' => 'Registrar ejecución', 'type' => 'log', 'sort_order' => 1, 'config' => null],
            ],
            'explanation' => 'Sugerencia de fallback — Gemini no disponible.',
        ];
    }

    private function extractJson(string $text): string
    {
        // Strip ```json ... ``` or ``` ... ``` markdown blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $text, $matches)) {
            return trim($matches[1]);
        }

        return trim($text);
    }

    private function buildPrompt(string $name, string $description): string
    {
        return <<<PROMPT
Eres un experto en automatización de procesos empresariales. Analiza el siguiente proceso y responde ÚNICAMENTE con un JSON válido con esta estructura exacta:

{
  "feasibility": "Alta|Media|Baja",
  "recommended_tech": "tecnología principal recomendada",
  "recommended_language": "lenguaje de programación recomendado",
  "estimated_time": "X horas",
  "steps": [
    "Paso 1: descripción",
    "Paso 2: descripción",
    "Paso 3: descripción",
    "Paso 4: descripción",
    "Paso 5: descripción"
  ],
  "script_preview": "código de ejemplo funcional en el lenguaje recomendado"
}

Proceso: {$name}
Descripción: {$description}
PROMPT;
    }

    private function fallbackAnalysis(string $name): array
    {
        return [
            'feasibility' => 'Media',
            'recommended_tech' => 'API REST + Base de datos',
            'recommended_language' => 'PHP/Laravel',
            'estimated_time' => '4 horas',
            'steps' => [
                'Paso 1: Analizar los requisitos actuales del proceso',
                'Paso 2: Diseñar el flujo de automatización',
                'Paso 3: Implementar la lógica principal',
                'Paso 4: Añadir manejo de errores y logs',
                'Paso 5: Probar y programar ejecución automática',
            ],
            'script_preview' => "<?php\n// Script para: {$name}\necho \"Iniciando proceso...\\n\";\n// Implementar lógica aquí\necho \"Proceso completado\\n\";",
        ];
    }
}
