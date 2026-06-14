@extends('layouts.app')

@section('content')

    <div class="ia-cabecera">
        <h2 class="ia-cabecera__titulo">🤖 Analizador de Procesos con IA</h2>
        <p class="ia-cabecera__subtitulo">Describe el proceso y Gemini te dará recomendaciones de automatización personalizadas.</p>
    </div>

    {{--
        ANALIZADOR IA — rejilla-12 con dos elementos de 6 columnas
        Formulario (6 col) | Resultado (6 col, oculto hasta recibir respuesta)
        En móvil ambos colapsan a 12 columnas y se apilan.
    --}}
    <div class="rejilla-12">

        {{-- Columna izquierda: formulario (6 columnas) --}}
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-6">
            <div class="tarjeta-ia-formulario">
                <h3 class="tarjeta-ia-formulario__titulo">Descripción del proceso</h3>

                <div class="campo-grupo">
                    <label class="campo-grupo__etiqueta" for="process_name">Nombre del proceso</label>
                    <input type="text"
                           id="process_name"
                           class="form-control"
                           placeholder="Ej: Envío automático de informes mensuales">
                </div>

                <div class="campo-grupo">
                    <label class="campo-grupo__etiqueta" for="process_description">Descripción detallada</label>
                    <textarea id="process_description"
                              rows="8"
                              class="form-control"
                              placeholder="Describe qué hace el proceso actualmente, qué pasos sigue, qué herramientas usa y qué te gustaría automatizar..."></textarea>
                    <small class="campo-grupo__ayuda">Cuanta más información, mejor será el análisis.</small>
                </div>

                <div style="margin-top: 1.25rem">
                    <button id="analyzeBtn" class="btn btn-ai u-ancho-completo">
                        🔍 Analizar con IA
                    </button>
                </div>
            </div>
        </div>

        {{--
            Columna derecha: resultado (6 columnas).
            Oculto con display:none hasta que llega la respuesta.
            Al mostrarse ocupa su posición en la rejilla (col 7-12).
        --}}
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-6"
             id="resultadoElemento" style="display:none">
            <div class="tarjeta-ia-resultado">
                <h3 class="tarjeta-ia-resultado__titulo">Resultado del Análisis</h3>
                <div id="resultadoContenido"></div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    const btnAnalizar     = document.getElementById('analyzeBtn');
    const elementoResultado = document.getElementById('resultadoElemento');
    const contenidoResultado = document.getElementById('resultadoContenido');

    btnAnalizar.addEventListener('click', async function () {
        const nombre      = document.getElementById('process_name').value.trim();
        const descripcion = document.getElementById('process_description').value.trim();

        if (!nombre || !descripcion) {
            alert('Por favor, completa el nombre y la descripción.');
            return;
        }

        /* Estado de carga */
        btnAnalizar.disabled     = true;
        btnAnalizar.textContent  = 'Analizando…';
        elementoResultado.style.display = 'block';
        contenidoResultado.innerHTML = `
            <div class="estado-cargando">
                <div class="spinner"></div>
                <p>Gemini está analizando el proceso…</p>
            </div>`;

        try {
            const respuesta = await fetch('{{ route("ai.analyze") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    process_name:        nombre,
                    process_description: descripcion,
                }),
            });

            if (!respuesta.ok) {
                throw new Error(`Error del servidor: ${respuesta.status}`);
            }

            const datos = await respuesta.json();

            /*
             * Renderizar resultado usando las clases del sistema rejilla-12:
             * - resultado-metricas: sub-rejilla de 2 × 6 columnas (4 métricas)
             * - resultado-metrica: cada métrica span 6 (2 por fila)
             */
            contenidoResultado.innerHTML = `
                <div class="resultado-metricas">
                    <div class="resultado-metrica">
                        <span class="resultado-metrica__etiqueta">🎯 Viabilidad</span>
                        <span class="resultado-metrica__valor">${datos.feasibility}</span>
                    </div>
                    <div class="resultado-metrica">
                        <span class="resultado-metrica__etiqueta">⏱️ Tiempo estimado</span>
                        <span class="resultado-metrica__valor">${datos.estimated_time}</span>
                    </div>
                    <div class="resultado-metrica">
                        <span class="resultado-metrica__etiqueta">💻 Tecnología</span>
                        <span class="resultado-metrica__valor">${datos.recommended_tech}</span>
                    </div>
                    <div class="resultado-metrica">
                        <span class="resultado-metrica__etiqueta">📝 Lenguaje</span>
                        <span class="resultado-metrica__valor">${datos.recommended_language}</span>
                    </div>
                </div>

                <p class="resultado-subtitulo">📋 Pasos a seguir</p>
                <div class="resultado-pasos">
                    <ol>${datos.steps.map(paso => `<li>${paso}</li>`).join('')}</ol>
                </div>

                <p class="resultado-subtitulo">🔧 Vista previa del script</p>
                <div class="resultado-codigo">
                    <pre><code>${escaparHtml(datos.script_preview)}</code></pre>
                </div>
            `;

        } catch (error) {
            contenidoResultado.innerHTML = `
                <div class="alert alert-danger">
                    No se pudo obtener el análisis: ${error.message}
                </div>`;
        } finally {
            btnAnalizar.disabled    = false;
            btnAnalizar.textContent = '🔍 Analizar con IA';
        }
    });

    function escaparHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }
</script>
@endpush
