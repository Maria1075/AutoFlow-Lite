<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkflowExecutionResource;
use App\Http\Resources\WorkflowResource;
use App\Models\Workflow;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * WorkflowController (API v1) — Integration Hub (Idea 2)
 *
 * Un workflow combina triggers (cuándo) + actions (qué hacer).
 *
 * Rutas:
 *   GET    /api/v1/workflows
 *   POST   /api/v1/workflows
 *   GET    /api/v1/workflows/{id}
 *   PUT    /api/v1/workflows/{id}
 *   DELETE /api/v1/workflows/{id}
 *   POST   /api/v1/workflows/{id}/run    → activa el workflow manualmente
 *   GET    /api/v1/workflows/{id}/logs   → historial de ejecuciones
 */
class WorkflowController extends Controller
{
    public function __construct(private readonly WebhookService $webhookService) {}

    /** Lista todos los workflows con sus triggers y acciones */
    public function index(): AnonymousResourceCollection
    {
        $workflows = Workflow::with(['triggers', 'actions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return WorkflowResource::collection($workflows);
    }

    /** Crea un workflow (triggers y actions se añaden por separado) */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'process_id'  => 'nullable|exists:processes,id',
        ]);

        $workflow = Workflow::create($validated);
        $workflow->load(['triggers', 'actions']);

        return response()->json([
            'message' => 'Workflow creado correctamente.',
            'data'    => new WorkflowResource($workflow),
        ], 201);
    }

    /** Devuelve un workflow con sus triggers y acciones */
    public function show(Workflow $workflow): JsonResponse
    {
        $workflow->load(['triggers', 'actions', 'process']);

        return response()->json(['data' => new WorkflowResource($workflow)]);
    }

    /** Actualiza nombre, descripción, estado o proceso vinculado */
    public function update(Request $request, Workflow $workflow): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'process_id'  => 'nullable|exists:processes,id',
        ]);

        $workflow->update($validated);
        $workflow->load(['triggers', 'actions']);

        return response()->json([
            'message' => 'Workflow actualizado correctamente.',
            'data'    => new WorkflowResource($workflow->fresh(['triggers', 'actions'])),
        ]);
    }

    /** Elimina el workflow y en cascada sus triggers, acciones y logs */
    public function destroy(Workflow $workflow): JsonResponse
    {
        $workflow->delete();

        return response()->json(['message' => 'Workflow eliminado correctamente.']);
    }

    /**
     * Activa el workflow manualmente ejecutando todas sus acciones en orden.
     * Se puede pasar un payload personalizado en el body de la petición.
     */
    public function run(Request $request, Workflow $workflow): JsonResponse
    {
        if (! $workflow->is_active) {
            return response()->json(['message' => 'El workflow está desactivado.'], 422);
        }

        $payload = $request->input('payload', []);

        $execution = $this->webhookService->run($workflow, 'manual', $payload);

        return response()->json([
            'message'   => 'Workflow ejecutado.',
            'execution' => new WorkflowExecutionResource($execution),
        ]);
    }

    /** Historial de ejecuciones del workflow (últimas 50) */
    public function logs(Workflow $workflow): AnonymousResourceCollection
    {
        $logs = $workflow->executions()->limit(50)->get();

        return WorkflowExecutionResource::collection($logs);
    }
}
