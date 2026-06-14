<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProcessController extends Controller
{
    // Listar todos los procesos
    public function index()
    {
        $processes = Process::orderBy('created_at', 'desc')->paginate(10);
        return view('processes.index', compact('processes'));
    }
    
    // Mostrar formulario de creación
    public function create()
    {
        return view('processes.create');
    }
    
    // Guardar nuevo proceso
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:200|min:3',
            'description' => 'required|min:10',
            'frequency' => 'required|in:hourly,daily,weekly,monthly,manual'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('processes.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $process = Process::create([
            'name' => $request->name,
            'description' => $request->description,
            'frequency' => $request->frequency,
            'status' => 'active'
        ]);
        
        return redirect()->route('processes.show', $process)
            ->with('success', 'Proceso creado correctamente');
    }
    
    // Ver detalle de un proceso
    public function show(Process $process)
    {
        return view('processes.show', compact('process'));
    }
    
    // Formulario de edición
    public function edit(Process $process)
    {
        return view('processes.edit', compact('process'));
    }
    
    // Actualizar proceso
    public function update(Request $request, Process $process)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:200|min:3',
            'description' => 'required|min:10',
            'frequency' => 'required|in:hourly,daily,weekly,monthly,manual',
            'status' => 'required|in:active,paused,completed'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('processes.edit', $process)
                ->withErrors($validator)
                ->withInput();
        }
        
        $process->update($request->only(['name', 'description', 'frequency', 'status']));
        
        return redirect()->route('processes.show', $process)
            ->with('success', 'Proceso actualizado correctamente');
    }
    
    // Eliminar proceso
    public function destroy(Process $process)
    {
        $process->delete();
        
        return redirect()->route('processes.index')
            ->with('success', 'Proceso eliminado correctamente');
    }
    
    // Simular ejecución
    public function execute(Process $process)
    {
        $process->increment('executions_count');
        
        // Simular éxito aleatorio (80% éxito)
        $success = rand(1, 100) <= 80;
        
        if ($success) {
            $process->increment('success_count');
            $message = "✅ Proceso '{$process->name}' ejecutado correctamente";
        } else {
            $message = "❌ Error en la ejecución de '{$process->name}'. Revisa los logs.";
        }
        
        return redirect()->route('processes.show', $process)
            ->with('execution_message', $message);
    }
}