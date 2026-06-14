<?php

/**
 * AutoFlow Lite — API v1
 *
 * Base URL: http://localhost:8000/api/v1
 *
 * Autenticación: Bearer Token (Sanctum)
 *   1. POST /api/v1/auth/login → obtener token
 *   2. Incluir en cabecera: Authorization: Bearer {token}
 *
 * Rutas públicas (sin token):
 *   POST /api/v1/auth/login
 *   POST /api/v1/webhooks/{token}  ← webhook entrante de sistemas externos
 *
 * Rutas protegidas (requieren token):
 *   Todo lo demás
 */

use App\Http\Controllers\Api\V1\ActionController;
use App\Http\Controllers\Api\V1\AIController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProcessController;
use App\Http\Controllers\Api\V1\TriggerController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\WorkflowController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas (sin autenticación)
| Leer es público — escribir requiere token
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Webhook entrante — sistemas externos llaman sin token
    Route::post('/webhooks/{token}', [WebhookController::class, 'handle']);

    // Lectura pública de procesos y workflows (GET)
    Route::get('/processes',              [ProcessController::class, 'index']);
    Route::get('/processes/{process}',    [ProcessController::class, 'show']);
    Route::get('/workflows',              [WorkflowController::class, 'index']);
    Route::get('/workflows/{workflow}',   [WorkflowController::class, 'show']);
    Route::get('/workflows/{workflow}/logs', [WorkflowController::class, 'logs']);

});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren: Authorization: Bearer {token})
| Crear, editar, eliminar y ejecutar requieren token
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Procesos — escritura + acciones
    Route::post('/processes',                           [ProcessController::class, 'store']);
    Route::put('/processes/{process}',                  [ProcessController::class, 'update']);
    Route::patch('/processes/{process}',                [ProcessController::class, 'update']);
    Route::delete('/processes/{process}',               [ProcessController::class, 'destroy']);
    Route::post('/processes/{process}/execute',         [ProcessController::class, 'execute']);
    Route::get('/processes/{process}/executions',       [ProcessController::class, 'executions']);
    Route::post('/processes/{process}/analyze',         [ProcessController::class, 'analyze']);

    // Workflows — escritura + ejecución
    Route::post('/workflows',                           [WorkflowController::class, 'store']);
    Route::put('/workflows/{workflow}',                 [WorkflowController::class, 'update']);
    Route::patch('/workflows/{workflow}',               [WorkflowController::class, 'update']);
    Route::delete('/workflows/{workflow}',              [WorkflowController::class, 'destroy']);
    Route::post('/workflows/{workflow}/run',            [WorkflowController::class, 'run']);

    // Triggers
    Route::get('/triggers',                [TriggerController::class, 'index']);
    Route::post('/triggers',               [TriggerController::class, 'store']);
    Route::delete('/triggers/{trigger}',   [TriggerController::class, 'destroy']);

    // Actions
    Route::get('/actions',                 [ActionController::class, 'index']);
    Route::post('/actions',                [ActionController::class, 'store']);
    Route::delete('/actions/{action}',     [ActionController::class, 'destroy']);

    // IA — rate limit 10 peticiones/minuto
    Route::middleware('throttle:10,1')->prefix('ai')->group(function () {
        Route::post('/analyze', [AIController::class, 'analyze']);
        Route::post('/suggest', [AIController::class, 'suggest']);
    });

});
