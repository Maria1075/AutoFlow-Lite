<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\AIController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Procesos
Route::resource('processes', ProcessController::class);
Route::post('/processes/{process}/execute', [ProcessController::class, 'execute'])->name('processes.execute');

// IA
Route::get('/ai/analyze', [AIController::class, 'showAnalyzeForm'])->name('ai.analyze.form');
Route::post('/ai/analyze', [AIController::class, 'analyze'])->name('ai.analyze');
Route::get('/processes/{process}/ai-analyze', [AIController::class, 'analyzeExisting'])->name('processes.ai-analyze');

// API Explorer — demo visual de la REST API
Route::get('/api-explorer', fn() => view('api.explorer'))->name('api.explorer');