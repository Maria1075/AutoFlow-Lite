<?php

use App\Models\Process;

// ── Accessor success_rate ────────────────────────────────────────────

test('success_rate devuelve 0 cuando no hay ejecuciones', function () {
    $process = new Process(['executions_count' => 0, 'success_count' => 0]);

    expect($process->success_rate)->toBe(0.0);
});

test('success_rate calcula el porcentaje correctamente', function () {
    $process = new Process(['executions_count' => 10, 'success_count' => 8]);

    expect($process->success_rate)->toBe(80.0);
});

test('success_rate con string "0" de la BD no lanza división por cero', function () {
    $process = new Process(['executions_count' => '0', 'success_count' => '0']);

    expect($process->success_rate)->toBe(0.0);
});

// ── isAutomatizable ───────────────────────────────────────────────────

test('isAutomatizable devuelve true si la descripción contiene keyword', function () {
    $process = new Process([
        'description' => 'Enviar un informe diario por email a los gerentes',
    ]);

    expect($process->isAutomatizable())->toBeTrue();
});

test('isAutomatizable devuelve false si no hay keywords relevantes', function () {
    $process = new Process([
        'description' => 'Revisar manualmente el estado de los proyectos activos',
    ]);

    expect($process->isAutomatizable())->toBeFalse();
});
