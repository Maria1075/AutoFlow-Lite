<?php

namespace App\Http\Controllers;

use App\Models\Process;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProcesses = Process::count();
        $activeProcesses = Process::where('status', 'active')->count();
        $totalExecutions = Process::sum('executions_count');
        $totalSuccess = Process::sum('success_count');
        $successRate = $totalExecutions > 0 
            ? round(($totalSuccess / $totalExecutions) * 100, 2) 
            : 0;
        
        $recentProcesses = Process::orderBy('created_at', 'desc')->take(5)->get();
        
        // Procesos más exitosos
        $topProcesses = Process::where('executions_count', '>', 0)
            ->orderByRaw('CAST(success_count AS REAL) / executions_count DESC')
            ->take(3)
            ->get();
        
        return view('dashboard.index', compact(
            'totalProcesses',
            'activeProcesses',
            'totalExecutions',
            'successRate',
            'recentProcesses',
            'topProcesses'
        ));
    }
}