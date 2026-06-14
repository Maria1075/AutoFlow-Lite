@extends('layouts.app')

@section('content')

    <div class="pagina-cabecera">
        <div>
            <h2 class="pagina-cabecera__titulo">🔌 API Explorer</h2>
            <p style="color:rgba(255,255,255,.8);font-size:.9rem;margin-top:.25rem">
                Interfaz visual de la REST API — <code style="background:rgba(255,255,255,.15);padding:.1rem .4rem;border-radius:.25rem">http://127.0.0.1:8000/api/v1</code>
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Volver al dashboard</a>
    </div>

    {{-- Rejilla principal: Auth (4 col) + Estado API (8 col) --}}
    <div class="rejilla-12" style="margin-bottom:var(--separacion)">

        {{-- Panel de autenticación --}}
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-4">
            <div class="tarjeta">
                <h3 class="tarjeta__titulo-seccion">🔑 Autenticación</h3>

                <div class="campo-grupo" style="margin-bottom:1rem">
                    <label class="campo-grupo__etiqueta" for="email">Email</label>
                    <input type="email" id="email" class="form-control"
                           value="admin@autoflow.test" placeholder="email@ejemplo.com">
                </div>
                <div class="campo-grupo" style="margin-bottom:1rem">
                    <label class="campo-grupo__etiqueta" for="password">Contraseña</label>
                    <input type="password" id="password" class="form-control" value="password123">
                </div>

                <button id="btnLogin" class="btn btn-primary u-ancho-completo">
                    Obtener Token
                </button>

                <div id="tokenBox" style="display:none;margin-top:1rem">
                    <div style="background:var(--success-light);border-radius:var(--radio);padding:.75rem;border-left:3px solid var(--success)">
                        <p style="font-size:.75rem;font-weight:600;color:#065f46;margin-bottom:.25rem">TOKEN ACTIVO</p>
                        <p id="tokenPreview" style="font-size:.72rem;color:#065f46;word-break:break-all;font-family:monospace"></p>
                    </div>
                    <button id="btnLogout" class="btn btn-secondary u-ancho-completo" style="margin-top:.5rem">
                        Cerrar sesión
                    </button>
                </div>

                <div id="authError" style="display:none;margin-top:1rem" class="alert alert-danger"></div>

                {{-- Tabla resumen de rutas --}}
                <div style="margin-top:1.5rem">
                    <h3 class="tarjeta__titulo-seccion">📋 Rutas disponibles</h3>
                    <div style="display:grid;gap:.375rem;font-size:.78rem">
                        @foreach([
                            ['GET', '/processes', 'Pública'],
                            ['POST', '/processes', 'Token'],
                            ['POST', '/processes/{id}/execute', 'Token'],
                            ['POST', '/processes/{id}/analyze', 'Token'],
                            ['GET', '/workflows', 'Pública'],
                            ['POST', '/workflows/{id}/run', 'Token'],
                            ['POST', '/ai/suggest', 'Token'],
                            ['POST', '/webhooks/{token}', 'Pública'],
                        ] as [$method, $path, $auth])
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.3rem .5rem;background:var(--gris-50);border-radius:var(--radio-sm)">
                                <span>
                                    <span style="font-weight:700;color:{{ $method === 'GET' ? '#0284c7' : '#059669' }};font-family:monospace">{{ $method }}</span>
                                    <span style="color:var(--gris-700);margin-left:.4rem">/api/v1{{ $path }}</span>
                                </span>
                                <span style="font-size:.68rem;padding:.1rem .4rem;border-radius:9999px;background:{{ $auth === 'Pública' ? 'var(--success-light)' : 'var(--warning-light)' }};color:{{ $auth === 'Pública' ? '#065f46' : '#92400e' }};font-weight:600">
                                    {{ $auth }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel derecho: procesos y workflows --}}
        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-8">

            {{-- Tabs --}}
            <div style="display:flex;gap:.5rem;margin-bottom:var(--separacion)">
                <button class="btn btn-primary tab-btn" data-tab="procesos" id="tabProcesos">
                    📋 Procesos
                </button>
                <button class="btn btn-secondary tab-btn" data-tab="workflows" id="tabWorkflows">
                    ⚙️ Workflows
                </button>
                <button class="btn btn-secondary tab-btn" data-tab="consola" id="tabConsola">
                    🖥️ Consola
                </button>
                <button id="btnRefresh" class="btn btn-secondary" style="margin-left:auto" title="Actualizar">
                    ↺ Actualizar
                </button>
            </div>

            {{-- Tab Procesos --}}
            <div id="tabProcesosPanel">
                <div class="tabla-contenedor">
                    <div style="padding:1rem 1.25rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--gris-100)">
                        <span style="font-size:.8rem;font-weight:600;color:var(--gris-500);text-transform:uppercase;letter-spacing:.06em">
                            GET /api/v1/processes
                        </span>
                        <span id="procesosCount" style="font-size:.8rem;color:var(--gris-500)"></span>
                    </div>
                    <div id="procesosBody">
                        <div style="text-align:center;padding:2rem;color:var(--gris-400)">Cargando...</div>
                    </div>
                </div>
            </div>

            {{-- Tab Workflows --}}
            <div id="tabWorkflowsPanel" style="display:none">
                <div class="tabla-contenedor">
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--gris-100)">
                        <span style="font-size:.8rem;font-weight:600;color:var(--gris-500);text-transform:uppercase;letter-spacing:.06em">
                            GET /api/v1/workflows
                        </span>
                    </div>
                    <div id="workflowsBody">
                        <div style="text-align:center;padding:2rem;color:var(--gris-400)">Cargando...</div>
                    </div>
                </div>
            </div>

            {{-- Tab Consola --}}
            <div id="tabConsolaPanel" style="display:none">
                <div class="tarjeta">
                    <h3 class="tarjeta__titulo-seccion">🖥️ Petición manual a la API</h3>

                    <div class="rejilla-12" style="margin-bottom:1rem">
                        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-3">
                            <select id="consolaMethod" class="form-control">
                                <option>GET</option>
                                <option>POST</option>
                                <option>PUT</option>
                                <option>DELETE</option>
                            </select>
                        </div>
                        <div class="rejilla-12__elemento rejilla-12__elemento--ocupa-9">
                            <input type="text" id="consolaUrl" class="form-control"
                                   value="/api/v1/processes"
                                   placeholder="/api/v1/processes">
                        </div>
                    </div>

                    <div class="campo-grupo" style="margin-bottom:1rem">
                        <label class="campo-grupo__etiqueta">Body (JSON) — opcional para GET</label>
                        <textarea id="consolaBody" rows="4" class="form-control" style="font-family:monospace;font-size:.85rem"
                                  placeholder='{ "name": "Mi proceso", "description": "Descripción del proceso...", "frequency": "daily" }'></textarea>
                    </div>

                    <button id="btnConsola" class="btn btn-primary">▶ Enviar petición</button>

                    <div id="consolaResult" style="margin-top:1rem;display:none">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
                            <span style="font-size:.8rem;font-weight:600;color:var(--gris-500)">RESPUESTA</span>
                            <span id="consolaStatus" style="font-size:.8rem;padding:.2rem .6rem;border-radius:9999px;font-weight:700"></span>
                        </div>
                        <pre id="consolaOutput" style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:var(--radio);font-size:.78rem;overflow-x:auto;max-height:400px;overflow-y:auto;line-height:1.6"></pre>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
<script>
const BASE = '/api/v1';
let token = localStorage.getItem('api_token') || null;

/* ── Helpers ── */
async function apiFetch(path, options = {}) {
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
    if (token) { headers['Authorization'] = 'Bearer ' + token; }
    const res = await fetch(BASE + path, { ...options, headers });
    const data = await res.json().catch(() => ({}));
    return { status: res.status, ok: res.ok, data };
}

function badge(text, color) {
    const colors = {
        active:    'background:var(--primary-light);color:#1e40af',
        paused:    'background:var(--warning-light);color:#92400e',
        completed: 'background:var(--success-light);color:#065f46',
        true:      'background:var(--success-light);color:#065f46',
        false:     'background:var(--gris-100);color:var(--gris-500)',
    };
    return `<span style="display:inline-flex;align-items:center;padding:.15rem .5rem;border-radius:9999px;font-size:.68rem;font-weight:600;${colors[text] || 'background:var(--gris-100);color:var(--gris-700)'}">${text}</span>`;
}

/* ── Auth ── */
function updateAuthUI() {
    if (token) {
        document.getElementById('tokenBox').style.display = 'block';
        document.getElementById('tokenPreview').textContent = token.substring(0, 40) + '...';
        document.getElementById('btnLogin').style.display = 'none';
    } else {
        document.getElementById('tokenBox').style.display = 'none';
        document.getElementById('btnLogin').style.display = 'block';
    }
}

document.getElementById('btnLogin').addEventListener('click', async () => {
    const btn = document.getElementById('btnLogin');
    btn.disabled = true; btn.textContent = 'Conectando...';
    const { ok, data } = await apiFetch('/auth/login', {
        method: 'POST',
        body: JSON.stringify({
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
        })
    });
    if (ok && data.token) {
        token = data.token;
        localStorage.setItem('api_token', token);
        document.getElementById('authError').style.display = 'none';
        updateAuthUI();
        loadProcesos();
    } else {
        document.getElementById('authError').style.display = 'block';
        document.getElementById('authError').textContent = data.message || 'Credenciales incorrectas';
    }
    btn.disabled = false; btn.textContent = 'Obtener Token';
});

document.getElementById('btnLogout').addEventListener('click', async () => {
    if (token) { await apiFetch('/auth/logout', { method: 'POST' }); }
    token = null;
    localStorage.removeItem('api_token');
    updateAuthUI();
});

/* ── Tabs ── */
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.className = 'btn btn-secondary tab-btn';
        });
        btn.className = 'btn btn-primary tab-btn';
        ['Procesos','Workflows','Consola'].forEach(t => {
            document.getElementById('tab' + t + 'Panel').style.display = 'none';
        });
        const tab = btn.dataset.tab.charAt(0).toUpperCase() + btn.dataset.tab.slice(1);
        document.getElementById('tab' + tab + 'Panel').style.display = 'block';
        if (btn.dataset.tab === 'procesos') { loadProcesos(); }
        if (btn.dataset.tab === 'workflows') { loadWorkflows(); }
    });
});

document.getElementById('btnRefresh').addEventListener('click', () => {
    loadProcesos(); loadWorkflows();
});

/* ── Procesos ── */
async function loadProcesos() {
    const body = document.getElementById('procesosBody');
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gris-400)">Cargando...</div>';
    const { ok, data } = await apiFetch('/processes');
    if (!ok || !data.data) {
        body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--danger)">Error al cargar los procesos.</div>';
        return;
    }
    const procesos = data.data;
    document.getElementById('procesosCount').textContent = procesos.length + ' proceso(s)';
    if (!procesos.length) {
        body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gris-400)">No hay procesos todavía.</div>';
        return;
    }
    body.innerHTML = `
        <table class="tabla-procesos">
            <thead>
                <tr>
                    <th>Proceso</th>
                    <th>Frecuencia</th>
                    <th>Estado</th>
                    <th>Éxito</th>
                    <th>Webhook</th>
                </tr>
            </thead>
            <tbody>
                ${procesos.map(p => `
                    <tr>
                        <td>
                            <span class="tabla-procesos__nombre">${p.name}</span>
                            <span class="tabla-procesos__descripcion">${p.description?.substring(0,60) ?? ''}${p.description?.length > 60 ? '…' : ''}</span>
                        </td>
                        <td><span class="frequency-badge">${p.frequency}</span></td>
                        <td>${badge(p.status)}</td>
                        <td>${p.executions_count > 0 ? p.success_rate + '%' : '<span style="color:var(--gris-300)">—</span>'}</td>
                        <td>${p.webhook_url ? '<span style="color:var(--success);font-size:.75rem">✅ configurada</span>' : '<span style="color:var(--gris-300);font-size:.75rem">—</span>'}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

/* ── Workflows ── */
async function loadWorkflows() {
    const body = document.getElementById('workflowsBody');
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gris-400)">Cargando...</div>';
    const { ok, data } = await apiFetch('/workflows');
    if (!ok || !data.data) {
        body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--gris-400)">No hay workflows creados todavía.</div>';
        return;
    }
    const workflows = data.data;
    if (!workflows.length) {
        body.innerHTML = `
            <div style="text-align:center;padding:2rem;color:var(--gris-500)">
                <p style="margin-bottom:.5rem">No hay workflows todavía.</p>
                <p style="font-size:.8rem">Crea uno en Postman con <code>POST /api/v1/workflows</code></p>
            </div>`;
        return;
    }
    body.innerHTML = `
        <table class="tabla-procesos">
            <thead>
                <tr><th>Workflow</th><th>Proceso vinculado</th><th>Activo</th><th>Triggers</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                ${workflows.map(w => `
                    <tr>
                        <td>
                            <span class="tabla-procesos__nombre">${w.name}</span>
                            <span class="tabla-procesos__descripcion">${w.description?.substring(0,50) ?? ''}</span>
                        </td>
                        <td style="font-size:.8rem;color:var(--gris-500)">${w.process_id ? 'Proceso #' + w.process_id : '—'}</td>
                        <td>${badge(String(w.is_active))}</td>
                        <td style="font-size:.8rem">${w.triggers?.length ?? 0} trigger(s)</td>
                        <td style="font-size:.8rem">${w.actions?.length ?? 0} acción(es)</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>`;
}

/* ── Consola ── */
document.getElementById('btnConsola').addEventListener('click', async () => {
    const btn = document.getElementById('btnConsola');
    btn.disabled = true; btn.textContent = 'Enviando...';

    const method = document.getElementById('consolaMethod').value;
    const url    = document.getElementById('consolaUrl').value;
    const rawBody = document.getElementById('consolaBody').value.trim();

    const options = { method };
    if (rawBody && method !== 'GET') {
        try { options.body = JSON.stringify(JSON.parse(rawBody)); }
        catch { options.body = rawBody; }
    }

    const { status, ok, data } = await apiFetch(url.replace('/api/v1', ''), options);

    const statusEl = document.getElementById('consolaStatus');
    statusEl.textContent = status;
    statusEl.style.cssText = ok
        ? 'background:var(--success-light);color:#065f46;font-size:.8rem;padding:.2rem .6rem;border-radius:9999px;font-weight:700'
        : 'background:var(--danger-light);color:#991b1b;font-size:.8rem;padding:.2rem .6rem;border-radius:9999px;font-weight:700';

    document.getElementById('consolaOutput').textContent = JSON.stringify(data, null, 2);
    document.getElementById('consolaResult').style.display = 'block';

    btn.disabled = false; btn.textContent = '▶ Enviar petición';
});

/* ── Init ── */
updateAuthUI();
loadProcesos();
</script>
@endpush
