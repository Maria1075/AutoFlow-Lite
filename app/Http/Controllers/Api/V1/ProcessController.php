<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProcessResource;
use App\Http\Resources\WorkflowExecutionResource;
use App\Models\Process;
use App\Services\AIService;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * ProcessController (API v1) — CRUD completo de procesos + ejecución + análisis IA
 *
 * Rutas:
 *   GET    /api/v1/processes
 *   POST   /api/v1/processes
 *   GET    /api/v1/processes/{id}
 *   PUT    /api/v1/processes/{id}
 *   DELETE /api/v1/processes/{id}
 *   POST   /api/v1/processes/{id}/execute   → ejecuta y lanza webhook saliente
 *   GET    /api/v1/processes/{id}/executions → historial de workflows vinculados
 *   POST   /api/v1/processes/{id}/analyze   → análisis de IA con Gemini
 */
class ProcessController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService,
        private readonly AIService $aiService,
    ) {}

    /** Lista todos los procesos con paginación */
    public function index(): AnonymousResourceCollection
    {
        $processes = Process::orderBy('created_at', 'desc')->paginate(15);

        return ProcessResource::collection($processes);
    }

    /** Crea un nuevo proceso */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200|min:3',
            'description' => 'required|string|min:10',
            'frequency' => 'required|in:hourly,daily,weekly,monthly,manual',
            'webhook_url' => 'nullable|url|max:500',
        ]);

        $process = Process::create([
            ...$validated,
            'status' => 'active',
            'executions_count' => 0,
            'success_count' => 0,
        ]);

        return response()->json([
            'message' => 'Proceso creado correctamente.',
            'data' => new ProcessResource($process),
        ], 201);
    }

    /** Devuelve un proceso por ID */
    public function show(Process $process): JsonResponse
    {
        return response()->json(['data' => new ProcessResource($process)]);
    }

    /** Actualiza un proceso existente */
    public function update(Request $request, Process $process): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:200|min:3',
            'description' => 'sometimes|string|min:10',
            'frequency' => 'sometimes|in:hourly,daily,weekly,monthly,manual',
            'status' => 'sometimes|in:active,paused,completed',
            'webhook_url' => 'nullable|url|max:500',
        ]);

        $process->update($validated);

        return response()->json([
            'message' => 'Proceso actualizado correctamente.',
            'data' => new ProcessResource($process->fresh()),
        ]);
    }

    /** Elimina un proceso */
    public function destroy(Process $process): JsonResponse
    {
        $process->delete();

        return response()->json(['message' => 'Proceso eliminado correctamente.']);
    }

    /**
     * Ejecuta el proceso (simula éxito 80%) y dispara el webhook saliente si está configurado.
     * También activa los workflows vinculados a este proceso (Idea 1 + Idea 2 combinadas).
     */
    public function execute(Process $process): JsonResponse
    {
        $process->increment('executions_count');
        $success = rand(1, 100) <= 80;

        if ($success) {
            $process->increment('success_count');
        }

        $executionData = [
            'process_id' => $process->id,
            'process_name' => $process->name,
            'success' => $success,
            'executed_at' => now()->toIso8601String(),
        ];

        $webhookFired = false;

        // Webhook saliente del proceso (Idea 1)
        if ($process->webhook_url) {
            $webhookFired = $this->webhookService->fireProcessWebhook($process->webhook_url, $executionData);
        }

        // Ejecutar workflows vinculados a este proceso (Idea 2)
        $workflowResults = [];
        foreach ($process->workflows()->where('is_active', true)->with('actions')->get() as $workflow) {
            $execution = $this->webhookService->run($workflow, 'process_execute', $executionData);
            $workflowResults[] = [
                'workflow_id' => $workflow->id,
                'workflow_name' => $workflow->name,
                'status' => $execution->status,
            ];
        }

        return response()->json([
            'message' => $success ? 'Proceso ejecutado correctamente.' : 'El proceso falló durante la ejecución.',
            'success' => $success,
            'process' => new ProcessResource($process->fresh()),
            'webhook_fired' => $webhookFired,
            'workflows_run' => $workflowResults,
        ]);
    }

    /** Historial de ejecuciones de workflows vinculados al proceso */
    public function executions(Process $process): AnonymousResourceCollection
    {
        $executions = $process->workflows()
            ->with('executions')
            ->get()
            ->pluck('executions')
            ->flatten()
            ->sortByDesc('created_at')
            ->values();

        return WorkflowExecutionResource::collection($executions);
    }

    /** Análisis de IA con Gemini para el proceso */
    public function analyze(Process $process): JsonResponse
    {
        $analysis = $this->aiService->analyzeProcess($process->name, $process->description);

        return response()->json([
            'data' => $analysis,
            'process' => new ProcessResource($process),
        ]);
    }
}
