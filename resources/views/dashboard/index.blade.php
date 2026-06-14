@extends('layouts.app')

@section('content')

    <div class="pagina-cabecera">
        <h2 class="pagina-cabecera__titulo">Panel de Control</h2>
        <a href="{{ route('processes.create') }}" class="btn btn-primary">+ Nuevo Proceso</a>
    </div>

    {{--
        ESTADÍSTICAS — rejilla-12 con 4 elementos de 3 columnas cada uno
        4 × span-3 = 12 columnas cubiertas
    --}}
    <div class="rejilla-12" style="margin-bottom: var(--separacion)">

        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-3">
            <div class="tarjeta-estadistica">
                <span class="tarjeta-estadistica__icono">📋</span>
                <span class="tarjeta-estadistica__valor">{{ $totalProcesses }}</span>
                <p class="tarjeta-estadistica__etiqueta">Total de Procesos</p>
            </div>
        </div>

        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-3">
            <div class="tarjeta-estadistica tarjeta-estadistica--exito">
                <span class="tarjeta-estadistica__icono">✅</span>
                <span class="tarjeta-estadistica__valor">{{ $activeProcesses }}</span>
                <p class="tarjeta-estadistica__etiqueta">Procesos Activos</p>
            </div>
        </div>

        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-3">
            <div class="tarjeta-estadistica tarjeta-estadistica--alerta">
                <span class="tarjeta-estadistica__icono">▶️</span>
                <span class="tarjeta-estadistica__valor">{{ $totalExecutions }}</span>
                <p class="tarjeta-estadistica__etiqueta">Ejecuciones Totales</p>
            </div>
        </div>

        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-3">
            <div class="tarjeta-estadistica tarjeta-estadistica--morado">
                <span class="tarjeta-estadistica__icono">📈</span>
                <span class="tarjeta-estadistica__valor">{{ $successRate }}%</span>
                <p class="tarjeta-estadistica__etiqueta">Tasa de Éxito Global</p>
            </div>
        </div>

    </div>

    {{--
        CUERPO — rejilla-12 con layout 8 + 4
        Columna principal (8 col): procesos recientes
        Columna lateral  (4 col): top + acciones rápidas
    --}}
    <div class="rejilla-12">

        {{-- Columna principal: procesos recientes (8 columnas) --}}
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-8">
            <div class="tarjeta">
                <h3 class="tarjeta__titulo-seccion">📋 Procesos Recientes</h3>

                @if($recentProcesses->isEmpty())
                    <p style="color:var(--gris-500);font-size:.9rem">Aún no hay procesos creados.</p>
                @else
                    <div class="lista-procesos">
                        @foreach($recentProcesses as $process)
                            <a href="{{ route('processes.show', $process) }}" class="proceso-item">
                                <div>
                                    <p class="proceso-item__nombre">{{ $process->name }}</p>
                                    <p class="proceso-item__descripcion">{{ Str::limit($process->description, 90) }}</p>
                                    <div class="proceso-item__meta">
                                        <span class="status-badge {{ $process->status }}">{{ $process->status }}</span>
                                        <span class="frequency-badge">{{ $process->frequency }}</span>
                                        @if($process->executions_count > 0)
                                            <span class="frequency-badge">{{ $process->success_rate }}% éxito</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="btn btn-sm btn-secondary">Ver →</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Columna lateral (4 columnas) --}}
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-4">

            {{-- Top procesos --}}
            @if($topProcesses->isNotEmpty())
                <div class="tarjeta" style="margin-bottom: var(--separacion)">
                    <h3 class="tarjeta__titulo-seccion">🏆 Más Exitosos</h3>
                    <ul class="lista-top u-lista-limpia">
                        @foreach($topProcesses as $i => $process)
                            <li class="lista-top__item">
                                <span class="lista-top__nombre">{{ $i + 1 }}. {{ Str::limit($process->name, 20) }}</span>
                                <span class="lista-top__tasa">{{ $process->success_rate }}%</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Acciones rápidas --}}
            <div class="tarjeta">
                <h3 class="tarjeta__titulo-seccion">⚡ Acciones Rápidas</h3>
                <div class="acciones-rapidas">
                    <a href="{{ route('processes.create') }}" class="btn btn-primary u-ancho-completo">+ Crear Proceso</a>
                    <a href="{{ route('processes.index') }}"  class="btn btn-secondary u-ancho-completo">📋 Ver todos</a>
                    <a href="{{ route('ai.analyze.form') }}"  class="btn btn-ai u-ancho-completo">🤖 Analizar con IA</a>
                </div>
            </div>

        </div>

    </div>

@endsection
