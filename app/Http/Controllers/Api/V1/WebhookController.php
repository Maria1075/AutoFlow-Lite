<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Trigger;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WebhookController — recibe webhooks entrantes de sistemas externos (Idea 2)
 *
 * Ruta pública (sin autenticación):
 *   POST /api/v1/webhooks/{token}
 *
 * Cuando un sistema externo hace POST a esta URL:
 *   1. Se busca el trigger por su token único
 *   2. Se ejecutan todas las acciones del workflow vinculado
 *   3. Se devuelve el resultado de la ejecución
 *
 * Ejemplo de uso: Slack, GitHub, Stripe pueden llamar a esta URL
 * para disparar automatizaciones en AutoFlow Lite.
 */
class WebhookController extends Controller
{
    public function __construct(private readonly WebhookService $webhookService) {}

    public function handle(Request $request, string $token): JsonResponse
    {
        // Buscar el trigger con ese token
        $trigger = Trigger::where('webhook_token', $token)
            ->where('type', 'webhook')
            ->with('workflow.actions')
            ->first();

        if (! $trigger) {
            return response()->json(['message' => 'Token de webhook no válido.'], 404);
        }

        $workflow = $trigger->workflow;

        if (! $workflow || ! $workflow->is_active) {
            return response()->json(['message' => 'El workflow no está activo.'], 422);
        }

        // Ejecutar el workflow con el payload recibido del sistema externo
        $execution = $this->webhookService->run(
            $workflow,
            'webhook',
            $request->all()
        );

        return response()->json([
            'message' => 'Webhook recibido y procesado.',
            'workflow' => $workflow->name,
            'status' => $execution->status,
            'executed_at' => $execution->created_at?->toIso8601String(),
        ]);
    }
}
