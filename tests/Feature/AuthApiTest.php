<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Login ──────────────────────────────────────────────────────────────

test('login con credenciales correctas devuelve token', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'message',
                 'token',
                 'user' => ['id', 'name', 'email'],
             ]);
});

test('login con credenciales incorrectas devuelve 422', function () {
    User::factory()->create(['email' => 'test@test.com']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'test@test.com',
        'password' => 'contraseña_incorrecta',
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

test('login sin datos devuelve errores de validación', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email', 'password']);
});

// ── Me ────────────────────────────────────────────────────────────────

test('me devuelve datos del usuario autenticado', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
             ->assertJsonPath('user.email', $user->email);
});

test('me sin token devuelve 401', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(401);
});

// ── Logout ────────────────────────────────────────────────────────────

test('logout revoca el token correctamente', function () {
    $user  = User::factory()->create();
    // actingAs() usa TransientToken que no tiene delete() — hay que usar un token real de Sanctum
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
             ->assertJsonPath('message', 'Sesión cerrada correctamente.');
});
