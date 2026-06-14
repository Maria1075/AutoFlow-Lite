<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WebhookService — ejecuta las acciones de un workflow
 *
 * Se usa en dos escenarios:
 *   1. Cuando se activa un workflow manualmente (POST /api/v1/workflows/{id}/run)
 *   2. Cuando llega un webhook entrante (POST /api/v1/webhooks/{token})
 *   3. Cuando se ejecuta un proceso vinculado a un workflow
 */
class WebhookService
{
    /**
     * Ejecuta todas las acciones de un workflow en orden y guarda el resultado.
     *
     * @param  Workflow  $workflow    El workflow a ejecutar
     * @param  string    $triggeredBy Quién lo activó (manual, webhook, process_execute)
     * @param  array     $payload     Datos recibidos del trigger (body del webhook, etc.)
     */
    public function run(Workflow $workflow, string $triggeredBy = 'manual', array $payload = []): WorkflowExecution
    {
        $results = [];
        $hasError = false;

        // Cargar acciones ordenadas por sort_order
        $actions = $workflow->actions()->orderBy('sort_order')->get();

        foreach ($actions as $action) {
            $result = $this->executeAction($action, $payload);
            $results[] = $result;

            if (! $result['ok']) {
                $hasError = true;
            }
        }

        // Determinar estado global de la ejecución
        $status = match (true) {
            ! $hasError               => 'success',
            count($results) === 0     => 'success',
            default                   => count(array_filter($results, fn ($r) => $r['ok'])) > 0 ? 'partial' : 'failed',
        };

        // Guardar en el historial de ejecuciones
        return WorkflowExecution::create([
            'workflow_id'      => $workflow->id,
            'triggered_by'     => $triggeredBy,
            'status'           => $status,
            'request_payload'  => $payload,
            'response_payload' => $results,
        ]);
    }

    /**
     * Lanza el webhook saliente de un proceso (outgoing webhook — Idea 1).
     * Se llama cuando se ejecuta un proceso que tiene webhook_url configurada.
     *
     * @param  string  $url     URL externa a notificar
     * @param  array   $payload Datos de la ejecución del proceso
     */
    public function fireProcessWebhook(string $url, array $payload): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            Log::info("Webhook saliente enviado a {$url}", [
                'status' => $response->status(),
                'ok'     => $response->successful(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Error al enviar webhook a {$url}: " . $e->getMessage());

            return false;
        }
    }

    /** Ejecuta una acción individual y devuelve el resultado */
    private function executeAction(Action $action, array $payload): array
    {
        return match ($action->type) {
            'http_request' => $this->executeHttpRequest($action, $payload),
            'log'          => $this->executeLog($action, $payload),
            default        => ['action' => $action->name, 'type' => $action->type, 'ok' => false, 'error' => 'Tipo desconocido'],
        };
    }

    /** Hace un POST a la URL configurada en la acción */
    private function executeHttpRequest(Action $action, array $payload): array
    {
        $config = $action->config ?? [];
        $url = $config['url'] ?? null;

        if (! $url) {
            return ['action' => $action->name, 'type' => 'http_request', 'ok' => false, 'error' => 'URL no configurada'];
        }

        try {
            $body = array_merge($config['body'] ?? [], ['event_payload' => $payload]);

            $response = Http::timeout(10)
                ->withHeaders($config['headers'] ?? [])
                ->post($url, $body);

            return [
                'action'      => $action->name,
                'type'        => 'http_request',
                'url'         => $url,
                'http_status' => $response->status(),
                'ok'          => $response->successful(),
            ];
        } catch (\Exception $e) {
            Log::error("Acción http_request '{$action->name}' falló: " . $e->getMessage());

            return ['action' => $action->name, 'type' => 'http_request', 'ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** Registra el evento en el log (sin acción externa) */
    private function executeLog(Action $action, array $payload): array
    {
        Log::info("Workflow action log: {$action->name}", ['payload' => $payload]);

        return ['action' => $action->name, 'type' => 'log', 'ok' => true, 'message' => 'Registrado correctamente'];
    }
}
