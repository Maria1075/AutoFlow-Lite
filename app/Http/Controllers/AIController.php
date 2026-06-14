<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Services\AIService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function __construct(protected AIService $aiService) {}

    // Mostrar formulario de análisis IA
    public function showAnalyzeForm()
    {
        return view('ai.analyze');
    }

    // Analizar proceso con IA
    public function analyze(Request $request)
    {
        $request->validate([
            'process_name' => 'required|max:200',
            'process_description' => 'required|min:10',
        ]);

        $analysis = $this->aiService->analyzeProcess(
            $request->process_name,
            $request->process_description
        );

        return response()->json($analysis);
    }

    // Analizar un proceso existente
    public function analyzeExisting(Process $process)
    {
        $analysis = $this->aiService->analyzeProcess(
            $process->name,
            $process->description
        );

        return view('processes.show', compact('process', 'analysis'));
    }
}
