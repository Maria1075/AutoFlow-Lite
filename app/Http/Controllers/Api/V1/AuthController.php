<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthController — gestiona la autenticación por token con Sanctum
 *
 * Flujo:
 *   1. POST /api/v1/auth/login  → devuelve un token Bearer
 *   2. Todas las rutas protegidas requieren: Authorization: Bearer {token}
 *   3. POST /api/v1/auth/logout → revoca el token actual
 */
class AuthController extends Controller
{
    /**
     * Login: valida credenciales y genera un token de acceso personal.
     *
     * Cuerpo esperado:
     *   { "email": "test@example.com", "password": "password" }
     *
     * Respuesta:
     *   { "token": "...", "user": { "id": 1, "name": "...", "email": "..." } }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Verificar que el usuario existe y la contraseña es correcta
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        // Revocar tokens anteriores para que solo exista uno activo
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login correcto.',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /** Devuelve los datos del usuario autenticado */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => [
                'id'    => $request->user()->id,
                'name'  => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    /** Logout: revoca el token actual */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }
}
