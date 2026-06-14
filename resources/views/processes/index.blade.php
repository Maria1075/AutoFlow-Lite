@extends('layouts.app')

@section('content')

    <div class="pagina-cabecera">
        <h2 class="pagina-cabecera__titulo">Gestión de Procesos</h2>
        <a href="{{ route('processes.create') }}" class="btn btn-primary">+ Nuevo Proceso</a>
    </div>

    {{-- Tabla — elemento único de 12 columnas --}}
    <div class="rejilla-12">
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-12">
            <div class="tabla-contenedor">

                @if($processes->isEmpty())
                    <div class="estado-vacio">
                        <p>No hay procesos creados aún.</p>
                        <a href="{{ route('processes.create') }}" class="btn btn-primary">Crear tu primer proceso</a>
                    </div>
                @else
                    <table class="tabla-procesos">
                        <thead>
                            <tr>
                                <th>Proceso</th>
                                <th>Frecuencia</th>
                                <th>Estado</th>
                                <th>Ejecuciones</th>
                                <th>Éxito</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processes as $process)
                                <tr>
                                    <td>
                                        <span class="tabla-procesos__nombre">{{ $process->name }}</span>
                                        <span class="tabla-procesos__descripcion">{{ Str::limit($process->description, 60) }}</span>
                                    </td>
                                    <td><span class="frequency-badge">{{ $process->frequency }}</span></td>
                                    <td><span class="status-badge {{ $process->status }}">{{ $process->status }}</span></td>
                                    <td>{{ $process->executions_count }}</td>
                                    <td>
                                        @if($process->executions_count > 0)
                                            {{ $process->success_rate }}%
                                        @else
                                            <span style="color:var(--gris-200)">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="tabla-procesos__acciones">
                                            <a href="{{ route('processes.show', $process) }}"
                                               class="btn-icon" title="Ver detalle">👁️</a>
                                            <a href="{{ route('processes.edit', $process) }}"
                                               class="btn-icon" title="Editar">✏️</a>
                                            <form action="{{ route('processes.destroy', $process) }}"
                                                  method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn-icon btn-icon-danger"
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Eliminar «{{ $process->name }}»? No se puede deshacer.')">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($processes->hasPages())
                        <div class="tabla-paginacion">
                            {{ $processes->links() }}
                        </div>
                    @endif
                @endif

            </div>
        </div>
    </div>

@endsection
