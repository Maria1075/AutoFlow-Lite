<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActionResource;
use App\Models\Action;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * ActionController (API v1) — define QUÉ hace el workflow cuando se activa
 *
 * Tipos de acción:
 *   http_request → hace POST a una URL externa con datos del evento (webhook saliente)
 *   log          → registra el evento en la tabla workflow_executions
 *
 * Rutas:
 *   GET    /api/v1/actions
 *   POST   /api/v1/actions
 *   DELETE /api/v1/actions/{id}
 */
class ActionController extends Controller
{
    /** Lista todas las acciones */
    public function index(): AnonymousResourceCollection
    {
        return ActionResource::collection(Action::with('workflow')->paginate(20));
    }

    /**
     * Crea una acción para un workflow.
     *
     * Para type=http_request, el campo config debe incluir:
     *   { "url": "https://...", "method": "POST", "headers": {}, "body": {} }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'name'        => 'required|string|max:200',
            'type'        => 'required|in:http_request,log',
            'config'      => 'required_if:type,http_request|nullable|array',
            'config.url'  => 'required_if:type,http_request|url',
            'sort_order'  => 'integer|min:0',
        ]);

        $action = Action::create($validated);

        return response()->json([
            'message' => 'Acción creada correctamente.',
            'data'    => new ActionResource($action),
        ], 201);
    }

    /** Elimina una acción */
    public function destroy(Action $action): JsonResponse
    {
        $action->delete();

        return response()->json(['message' => 'Acción eliminada correctamente.']);
    }
}
