<?php

use App\Models\Process;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── GET /processes — pública ──────────────────────────────────────────

test('listar procesos es público y devuelve estructura paginada', function () {
    Process::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/processes');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'name', 'description', 'frequency', 'status', 'success_rate']],
            'links',
            'meta' => ['current_page', 'total', 'per_page'],
        ]);
});

test('listar procesos sin datos devuelve array vacío', function () {
    $response = $this->getJson('/api/v1/processes');

    $response->assertStatus(200)
        ->assertJsonPath('meta.total', 0);
});

// ── GET /processes/{id} — pública ────────────────────────────────────

test('ver proceso individual es público', function () {
    $process = Process::factory()->create();

    $response = $this->getJson("/api/v1/processes/{$process->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $process->id)
        ->assertJsonPath('data.name', $process->name);
});

test('ver proceso inexistente devuelve 404', function () {
    $response = $this->getJson('/api/v1/processes/9999');

    $response->assertStatus(404);
});

// ── POST /processes — requiere token ─────────────────────────────────

test('crear proceso sin token devuelve 401', function () {
    $response = $this->postJson('/api/v1/processes', [
        'name' => 'Proceso de prueba',
        'description' => 'Descripción del proceso de prueba para CI',
        'frequency' => 'daily',
    ]);

    $response->assertStatus(401);
});

test('crear proceso con token válido devuelve 201', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/processes', [
        'name' => 'Backup diario',
        'description' => 'Backup de base de datos cada noche a las 2:00 AM',
        'frequency' => 'daily',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Backup diario')
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.executions_count', 0);

    $this->assertDatabaseHas('processes', ['name' => 'Backup diario']);
});

test('crear proceso con datos inválidos devuelve 422', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/processes', [
        'name' => 'AB',          // mínimo 3 caracteres
        'description' => 'Corta',       // mínimo 10 caracteres
        'frequency' => 'invalid',     // no está en el enum
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'description', 'frequency']);
});

// ── PUT /processes/{id} ───────────────────────────────────────────────

test('actualizar proceso cambia los campos correctamente', function () {
    $user = User::factory()->create();
    $process = Process::factory()->create(['status' => 'active']);

    $response = $this->actingAs($user)->putJson("/api/v1/processes/{$process->id}", [
        'status' => 'paused',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('processes', ['id' => $process->id, 'status' => 'paused']);
});

// ── DELETE /processes/{id} ────────────────────────────────────────────

test('eliminar proceso borra el registro de la BD', function () {
    $user = User::factory()->create();
    $process = Process::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/api/v1/processes/{$process->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('processes', ['id' => $process->id]);
});

// ── POST /processes/{id}/execute ─────────────────────────────────────

test('ejecutar proceso incrementa executions_count', function () {
    $user = User::factory()->create();
    $process = Process::factory()->create(['executions_count' => 0]);

    $response = $this->actingAs($user)->postJson("/api/v1/processes/{$process->id}/execute");

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'process', 'webhook_fired', 'workflows_run']);

    $this->assertDatabaseHas('processes', [
        'id' => $process->id,
        'executions_count' => 1,
    ]);
});
