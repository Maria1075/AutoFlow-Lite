<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AutoFlow Lite — Automatización de Procesos</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>

{{-- app-container: grid-template-areas cabecera / contenido / pie --}}
<div class="app-container">

    <header class="main-header">
        <div class="logo">
            <h1 class="logo__titulo">⚡ AutoFlow Lite</h1>
            <p class="logo__subtitulo">Automatización inteligente de procesos</p>
        </div>
        <nav class="main-nav" aria-label="Navegación principal">
            <a href="{{ route('dashboard') }}"       class="nav-link">Dashboard</a>
            <a href="{{ route('processes.index') }}" class="nav-link">Procesos</a>
            <a href="{{ route('ai.analyze.form') }}" class="nav-link">🤖 Analizar con IA</a>
            <a href="{{ route('api.explorer') }}"    class="nav-link">🔌 API</a>
        </nav>
    </header>

    <main class="main-content">

        @if(session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

        @if(session('execution_message'))
            <div class="alert alert-info" role="alert">{{ session('execution_message') }}</div>
        @endif

        @yield('content')

    </main>

    <footer class="main-footer">
        <span>⚡ AutoFlow Lite — Laravel {{ app()->version() }} · PHP {{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }}</span>
        <span>Análisis con Gemini 2.5 Flash</span>
    </footer>

</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
