@extends('layouts.app')

@section('content')

    {{-- Cabecera con acciones --}}
    <div class="proceso-detalle-cabecera">
        <h2 class="proceso-detalle-cabecera__titulo">{{ $process->name }}</h2>
        <div class="proceso-detalle-cabecera__acciones">
            <a href="{{ route('processes.edit', $process) }}" class="btn btn-secondary">✏️ Editar</a>
            <a href="{{ route('processes.ai-analyze', $process) }}" class="btn btn-ai">🤖 Analizar con IA</a>
            <form action="{{ route('processes.execute', $process) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">▶️ Ejecutar ahora</button>
            </form>
        </div>
    </div>

    {{--
        DETALLE — rejilla-12 con dos elementos de 6 columnas
        Info general (6 col) | Estadísticas (6 col)
        Análisis IA (12 col — ancho completo)
    --}}
    <div class="rejilla-12">

        {{-- Información general (6 columnas) --}}
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-6">
            <div class="tarjeta">
                <h3 class="tarjeta__titulo-seccion">Información General</h3>

                <div class="detalle-fila">
                    <span class="detalle-fila__etiqueta">Descripción</span>
                    <span class="detalle-fila__valor" style="max-width:60%">{{ $process->description }}</span>
                </div>
                <div class="detalle-fila">
                    <span class="detalle-fila__etiqueta">Frecuencia</span>
                    <span class="detalle-fila__valor"><span class="frequency-badge">{{ $process->frequency }}</span></span>
                </div>
                <div class="detalle-fila">
                    <span class="detalle-fila__etiqueta">Estado</span>
                    <span class="detalle-fila__valor"><span class="status-badge {{ $process->status }}">{{ $process->status }}</span></span>
                </div>
                <div class="detalle-fila">
                    <span class="detalle-fila__etiqueta">Automatizable</span>
                    <span class="detalle-fila__valor">{{ $process->isAutomatizable() ? '✅ Sí' : '⚠️ Posiblemente' }}</span>
                </div>
                <div class="detalle-fila">
                    <span class="detalle-fila__etiqueta">Creado</span>
                    <span class="detalle-fila__valor">{{ $process->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Estadísticas (6 columnas) --}}
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-6">
            <div class="tarjeta">
                <h3 class="tarjeta__titulo-seccion">Estadísticas de Ejecución</h3>

                <div class="estadistica-fila">
                    <div class="estadistica-fila__cabecera">
                        <span class="estadistica-fila__etiqueta">Ejecuciones totales</span>
                        <span class="estadistica-fila__valor">{{ $process->executions_count }}</span>
                    </div>
                </div>

                <div class="estadistica-fila">
                    <div class="estadistica-fila__cabecera">
                        <span class="estadistica-fila__etiqueta">Ejecuciones exitosas</span>
                        <span class="estadistica-fila__valor">{{ $process->success_count }}</span>
                    </div>
                </div>

                <div class="estadistica-fila">
                    <div class="estadistica-fila__cabecera">
                        <span class="estadistica-fila__etiqueta">Tasa de éxito</span>
                        <span class="estadistica-fila__valor" style="color:var(--success)">{{ $process->success_rate }}%</span>
                    </div>
                    @if($process->executions_count > 0)
                        <div class="barra-progreso">
                            <div class="barra-progreso__relleno" style="width:{{ $process->success_rate }}%"></div>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Análisis IA — ocupa las 12 columnas --}}
        @if(isset($analysis))
            <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-12">
                <div class="tarjeta-ia">
                    <h3 class="tarjeta-ia__titulo">🤖 Análisis de IA — Gemini</h3>

                    {{-- Sub-rejilla de 4 métricas (cada una ocupa 3 de 12) --}}
                    <div class="ia-metricas">
                        <div class="ia-metrica">
                            <span class="ia-metrica__etiqueta">Viabilidad</span>
                            <span class="ia-metrica__valor">{{ $analysis['feasibility'] }}</span>
                        </div>
                        <div class="ia-metrica">
                            <span class="ia-metrica__etiqueta">Tecnología</span>
                            <span class="ia-metrica__valor">{{ $analysis['recommended_tech'] }}</span>
                        </div>
                        <div class="ia-metrica">
                            <span class="ia-metrica__etiqueta">Lenguaje</span>
                            <span class="ia-metrica__valor">{{ $analysis['recommended_language'] }}</span>
                        </div>
                        <div class="ia-metrica">
                            <span class="ia-metrica__etiqueta">Tiempo estimado</span>
                            <span class="ia-metrica__valor">{{ $analysis['estimated_time'] }}</span>
                        </div>
                    </div>

                    <p class="ia-subtitulo">Pasos sugeridos</p>
                    <ol class="ia-pasos">
                        @foreach($analysis['steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>

                    <p class="ia-subtitulo">Vista previa del script</p>
                    <pre class="ia-codigo">{{ $analysis['script_preview'] }}</pre>
                </div>
            </div>
        @endif

    </div>

@endsection
