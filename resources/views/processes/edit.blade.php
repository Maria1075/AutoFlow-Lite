@extends('layouts.app')

@section('content')

    <div class="pagina-cabecera">
        <h2 class="pagina-cabecera__titulo">Editar Proceso</h2>
        <a href="{{ route('processes.show', $process) }}" class="btn btn-secondary">← Volver</a>
    </div>

    {{--
        FORMULARIO — elemento centrado de 8 columnas
        grid-column: 3 / span 8  →  2 cols vacías | 8 cols formulario | 2 cols vacías
    --}}
    <div class="rejilla-12">
        <div class="rejilla-12__elemento rejilla-12__elemento--centrado-8">
            <div class="tarjeta-formulario">
                <h2 class="tarjeta-formulario__titulo">{{ $process->name }}</h2>

                <form action="{{ route('processes.update', $process) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="campo-grupo">
                        <label class="campo-grupo__etiqueta" for="name">Nombre del proceso *</label>
                        <input type="text"
                               name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $process->name) }}"
                               required minlength="3" maxlength="200">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="campo-grupo">
                        <label class="campo-grupo__etiqueta" for="description">Descripción detallada *</label>
                        <textarea name="description" id="description"
                                  rows="5"
                                  class="form-control @error('description') is-invalid @enderror"
                                  required minlength="10">{{ old('description', $process->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="campo-grupo">
                        <label class="campo-grupo__etiqueta" for="frequency">Frecuencia de ejecución *</label>
                        <select name="frequency" id="frequency"
                                class="form-control @error('frequency') is-invalid @enderror"
                                required>
                            @foreach(['hourly' => 'Cada hora', 'daily' => 'Diario', 'weekly' => 'Semanal', 'monthly' => 'Mensual', 'manual' => 'Manual'] as $val => $label)
                                <option value="{{ $val }}" {{ old('frequency', $process->frequency) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('frequency')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="campo-grupo">
                        <label class="campo-grupo__etiqueta" for="status">Estado *</label>
                        <select name="status" id="status"
                                class="form-control @error('status') is-invalid @enderror"
                                required>
                            @foreach(['active' => 'Activo', 'paused' => 'Pausado', 'completed' => 'Completado'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $process->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="formulario-acciones">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="{{ route('processes.show', $process) }}" class="btn btn-secondary">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection
