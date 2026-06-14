<?php

use App\Models\Process;
use App\Models\Trigger;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── GET /workflows — pública ──────────────────────────────────────────

test('listar workflows es público', function () {
    Workflow::factory()->count(2)->create();

    $response = $this->getJson('/api/v1/workflows');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'name', 'is_active']],
        ]);
});

// ── POST /workflows ───────────────────────────────────────────────────

test('crear workflow requiere autenticación', function () {
    $response = $this->postJson('/api/v1/workflows', [
        'name' => 'Test workflow',
    ]);

    $response->assertStatus(401);
});

test('crear workflow con token crea el registro', function () {
    $user = User::factory()->create();
    $process = Process::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/workflows', [
        'name' => 'Notificar Slack',
        'description' => 'Envía mensaje cuando el proceso termina',
        'is_active' => true,
        'process_id' => $process->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Notificar Slack')
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.process_id', $process->id);

    $this->assertDatabaseHas('workflows', ['name' => 'Notificar Slack']);
});

// ── POST /workflows/{id}/run ──────────────────────────────────────────

test('ejecutar workflow desactivado devuelve 422', function () {
    $user = User::factory()->create();
    $workflow = Workflow::factory()->create(['is_active' => false]);

    $response = $this->actingAs($user)->postJson("/api/v1/workflows/{$workflow->id}/run");

    $response->assertStatus(422)
        ->assertJsonPath('message', 'El workflow está desactivado.');
});

test('ejecutar workflow activo crea registro de ejecución', function () {
    $user = User::factory()->create();
    $workflow = Workflow::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->postJson("/api/v1/workflows/{$workflow->id}/run", [
        'payload' => ['origen' => 'test_ci'],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Workflow ejecutado.')
        ->assertJsonStructure(['execution' => ['status', 'triggered_by']]);

    $this->assertDatabaseHas('workflow_executions', [
        'workflow_id' => $workflow->id,
        'triggered_by' => 'manual',
        'status' => 'success',
    ]);
});

// ── GET /workflows/{id}/logs — pública ───────────────────────────────

test('ver logs de workflow es público', function () {
    $workflow = Workflow::factory()->create();

    $response = $this->getJson("/api/v1/workflows/{$workflow->id}/logs");

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

// ── DELETE /workflows/{id} ────────────────────────────────────────────

test('eliminar workflow elimina triggers y acciones en cascada', function () {
    $user = User::factory()->create();
    $workflow = Workflow::factory()->create();
    $trigger = Trigger::factory()->create(['workflow_id' => $workflow->id]);

    $this->actingAs($user)->deleteJson("/api/v1/workflows/{$workflow->id}");

    $this->assertDatabaseMissing('workflows', ['id' => $workflow->id]);
    $this->assertDatabaseMissing('triggers', ['id' => $trigger->id]);
});
