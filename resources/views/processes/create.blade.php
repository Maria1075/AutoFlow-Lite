@extends('layouts.app')

@section('content')

    <div class="pagina-cabecera">
        <h2 class="pagina-cabecera__titulo">Nuevo Proceso</h2>
        <a href="{{ route('processes.index') }}" class="btn btn-secondary">← Volver</a>
    </div>

    {{--
        FORMULARIO — elemento centrado de 8 columnas
        grid-column: 3 / span 8  →  2 cols vacías | 8 cols formulario | 2 cols vacías
    --}}
    <div class="rejilla-12">
        <div class="rejilla-12__elemento rejilla-12__elemento--centrado-8">
            <div class="tarjeta-formulario">
                <h2 class="tarjeta-formulario__titulo">Crear Proceso</h2>

                <form action="{{ route('processes.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="campo-grupo">
                        <label class="campo-grupo__etiqueta" for="name">Nombre del proceso *</label>
                        <input type="text"
                               name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Ej: Envío automático de informes mensuales"
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
                                  placeholder="Describe qué hace el proceso, qué pasos sigue y qué te gustaría automatizar..."
                                  required minlength="10">{{ old('description') }}</textarea>
                        <small class="campo-grupo__ayuda">Cuanto más detallada, mejor será el análisis de IA.</small>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="campo-grupo">
                        <label class="campo-grupo__etiqueta" for="frequency">Frecuencia de ejecución *</label>
                        <select name="frequency" id="frequency"
                                class="form-control @error('frequency') is-invalid @enderror"
                                required>
                            <option value="" disabled {{ old('frequency') ? '' : 'selected' }}>Selecciona una frecuencia…</option>
                            @foreach(['hourly' => 'Cada hora', 'daily' => 'Diario', 'weekly' => 'Semanal', 'monthly' => 'Mensual', 'manual' => 'Manual'] as $val => $label)
                                <option value="{{ $val }}" {{ old('frequency') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('frequency')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="formulario-acciones">
                        <button type="submit" class="btn btn-primary">Crear Proceso</button>
                        <a href="{{ route('processes.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection
