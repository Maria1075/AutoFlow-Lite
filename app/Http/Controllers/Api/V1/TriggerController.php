<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TriggerResource;
use App\Models\Trigger;
use App\Models\Workflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * TriggerController (API v1) — define CUÁNDO se activa un workflow
 *
 * Tipos de trigger:
 *   manual  → el usuario llama a POST /api/v1/workflows/{id}/run
 *   webhook → el sistema externo llama a POST /api/v1/webhooks/{token}
 *   cron    → se guarda la expresión cron para futura automatización
 *
 * Rutas:
 *   GET    /api/v1/triggers
 *   POST   /api/v1/triggers
 *   DELETE /api/v1/triggers/{id}
 */
class TriggerController extends Controller
{
    /** Lista todos los triggers */
    public function index(): AnonymousResourceCollection
    {
        return TriggerResource::collection(Trigger::with('workflow')->paginate(20));
    }

    /**
     * Crea un trigger para un workflow.
     *
     * Para type=webhook, el sistema genera automáticamente un token único
     * y devuelve la URL pública en webhook_url.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'name' => 'required|string|max:200',
            'type' => 'required|in:manual,webhook,cron',
            'cron_expression' => 'required_if:type,cron|nullable|string|max:100',
        ]);

        $trigger = Trigger::create($validated);

        return response()->json([
            'message' => 'Trigger creado correctamente.',
            'data' => new TriggerResource($trigger),
        ], 201);
    }

    /** Elimina un trigger */
    public function destroy(Trigger $trigger): JsonResponse
    {
        $trigger->delete();

        return response()->json(['message' => 'Trigger eliminado correctamente.']);
    }
}
