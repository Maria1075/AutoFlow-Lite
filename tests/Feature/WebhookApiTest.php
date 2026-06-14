<?php

use App\Models\Trigger;
use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('webhook con token inválido devuelve 404', function () {
    $response = $this->postJson('/api/v1/webhooks/token-que-no-existe', [
        'source' => 'test',
    ]);

    $response->assertStatus(404)
             ->assertJsonPath('message', 'Token de webhook no válido.');
});

test('webhook con token válido ejecuta el workflow', function () {
    $workflow = Workflow::factory()->create(['is_active' => true]);

    $trigger = Trigger::factory()->create([
        'workflow_id'   => $workflow->id,
        'type'          => 'webhook',
        'webhook_token' => 'token-de-prueba-ci',
    ]);

    $response = $this->postJson('/api/v1/webhooks/token-de-prueba-ci', [
        'source' => 'github',
        'event'  => 'push',
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('workflow', $workflow->name)
             ->assertJsonPath('status', 'success');

    $this->assertDatabaseHas('workflow_executions', [
        'workflow_id'  => $workflow->id,
        'triggered_by' => 'webhook',
    ]);
});

test('webhook con workflow desactivado devuelve 422', function () {
    $workflow = Workflow::factory()->create(['is_active' => false]);

    Trigger::factory()->create([
        'workflow_id'   => $workflow->id,
        'type'          => 'webhook',
        'webhook_token' => 'token-workflow-inactivo',
    ]);

    $response = $this->postJson('/api/v1/webhooks/token-workflow-inactivo');

    $response->assertStatus(422)
             ->assertJsonPath('message', 'El workflow no está activo.');
});
