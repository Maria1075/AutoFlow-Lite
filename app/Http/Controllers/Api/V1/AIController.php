<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AIController (API v1) — integración con Gemini para sugerir workflows
 *
 * Rutas (con rate limiting de 10 peticiones/minuto):
 *   POST /api/v1/ai/analyze   → analiza un proceso y da recomendaciones
 *   POST /api/v1/ai/suggest   → sugiere un workflow completo (trigger + acciones)
 */
class AIController extends Controller
{
    public function __construct(private readonly AIService $aiService) {}

    /**
     * Analiza un proceso y devuelve recomendaciones de automatización.
     * Igual que la ruta web pero consumible desde cualquier cliente.
     */
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'process_name'        => 'required|string|max:200',
            'process_description' => 'required|string|min:10',
        ]);

        $analysis = $this->aiService->analyzeProcess(
            $request->process_name,
            $request->process_description
        );

        return response()->json(['data' => $analysis]);
    }

    /**
     * Sugiere un workflow completo (trigger + acciones) para un proceso dado.
     * Gemini devuelve la configuración lista para crear con POST /api/v1/workflows.
     */
    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            'process_name'        => 'required|string|max:200',
            'process_description' => 'required|string|min:10',
        ]);

        $suggestion = $this->aiService->suggestWorkflow(
            $request->process_name,
            $request->process_description
        );

        return response()->json([
            'message' => 'Sugerencia de workflow generada con IA.',
            'data'    => $suggestion,
        ]);
    }
}
