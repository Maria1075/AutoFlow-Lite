<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Las rutas /api/* no deben redirigir a 'login': devuelven null
        // para que el exception handler retorne JSON 401 en su lugar
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('api/*')) {
                return null;
            }
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 401 — No autenticado
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado. Incluye el token en la cabecera: Authorization: Bearer {token}',
                ], 401);
            }
        });

        // 405 — Método no permitido (los métodos van en el header Allow, no en getAllowedMethods)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $allow = $e->getHeaders()['Allow'] ?? 'desconocido';
                return response()->json([
                    'message' => "Método {$request->method()} no permitido. Métodos aceptados: {$allow}",
                ], 405);
            }
        });

        // 404 — Ruta no encontrada
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Ruta no encontrada.'], 404);
            }
        });
    })->create();
