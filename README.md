# AutoFlow Lite

Aplicación web full-stack para la gestión y automatización de procesos empresariales, con integración de IA (Google Gemini) y API REST versionada.

---

## Stack tecnológico

|       Capa        |      Tecnología           |      Versión      |
|-------------------|---------------------------|-------------------|
| Backend           | Laravel                   | 12.62.0           |
| Lenguaje          | PHP                       | 8.2.12            |
| Base de datos     | MySQL                     | 8.4.3             |
| Autenticación API | Laravel Sanctum           | 4.3               |
| Frontend          | Blade + CSS Grid + JS ES6+| —                 |
| IA                | Google Gemini API         | 2.5 Flash         |
| Testing           | Pest                      | —                 |
| Formateador       | Laravel Pint              | 1.0               |
---

## Instalación

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd processmind-proyect

# 2. Instalar dependencias PHP y JS
composer install
npm install

# 3. Configurar el entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=processmind_proyect
DB_USERNAME=root
DB_PASSWORD=

# 5. Obtener API key gratuita en https://aistudio.google.com/apikey
# y añadirla en .env
GEMINI_API_KEY=tu_api_key_aqui

# 6. Ejecutar migraciones
php artisan migrate

# 7. Crear usuario de prueba
php artisan db:seed

# 8. Compilar assets
npm run build

# 9. Arrancar servidor
php artisan serve
```

App disponible en `http://localhost:8000`

---

## Arquitectura

El proyecto expone **dos interfaces sobre el mismo modelo de datos**: una aplicación web con Blade y una API REST versionada. Ambas comparten modelos, servicios y base de datos.

```
┌──────────────────────────────────────────────┐
│                   CLIENTE                    │
│   Navegador (Blade/CSS)  │  Postman / App    │
└──────────┬───────────────┴──────────┬────────┘
           │ HTTP                     │ HTTP + Bearer Token
      ┌────▼──────────────────────────▼───┐
      │            LARAVEL 12             │
      │  routes/web.php                   │
      │  routes/api.php                   │
      ├───────────────────────────────────┤
      │  CONTROLLERS                      │
      │  Http/Controllers/        ← web   │
      │  Http/Controllers/Api/V1/ ← API   │
      ├───────────────────────────────────┤
      │  SERVICES (lógica de negocio)     │
      │  AIService · WebhookService       │
      ├───────────────────────────────────┤
      │  MODELS — Eloquent ORM            │
      │  Process · Workflow · Trigger     │
      │  Action  · WorkflowExecution      │
      ├───────────────────────────────────┤
      │  DATABASE — MySQL (8 tablas)      │
      └───────────────────────────────────┘
                     │
         ┌───────────▼───────────┐
         │     APIs EXTERNAS     │
         │  Gemini 2.5 Flash     │
         │  Webhooks salientes   │
         └───────────────────────┘
```

---

## Estructura de carpetas

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php      # métricas agregadas del dashboard
│   │   ├── ProcessController.php        # CRUD web (devuelve vistas Blade)
│   │   ├── AIController.php             # análisis IA desde la interfaz web
│   │   └── Api/V1/                      # controladores exclusivos de la API REST
│   │       ├── AuthController.php       # login · logout · me
│   │       ├── ProcessController.php    # CRUD + execute + analyze
│   │       ├── WorkflowController.php   # CRUD + run + logs
│   │       ├── TriggerController.php    # gestión de triggers
│   │       ├── ActionController.php     # gestión de acciones
│   │       ├── AIController.php         # analyze + suggest workflow
│   │       └── WebhookController.php    # recibe webhooks entrantes
│   └── Resources/                       # transforman Eloquent → JSON estructurado
│       ├── ProcessResource.php
│       ├── WorkflowResource.php
│       ├── TriggerResource.php
│       ├── ActionResource.php
│       └── WorkflowExecutionResource.php
├── Models/
│   ├── Process.php            # accessor success_rate, relación con Workflow
│   ├── Workflow.php           # BelongsTo Process, HasMany triggers/actions
│   ├── Trigger.php            # auto-genera webhook_token en booted()
│   ├── Action.php             # config JSON casteado a array
│   └── WorkflowExecution.php  # historial de ejecuciones
└── Services/
    ├── AIService.php          # llama a Gemini API con fallback incorporado
    └── WebhookService.php     # orquesta la ejecución de acciones de un workflow
```

---

## Base de datos

**8 tablas** gestionadas mediante migraciones de Laravel:

```
users                       autenticación de usuarios
personal_access_tokens      tokens Bearer de la API (Sanctum)
processes                   entidad principal + webhook_url para notificaciones
workflows                   automatizaciones vinculables a un proceso
triggers                    cuándo se activa un workflow (manual / webhook / cron)
actions                     qué hace el workflow (http_request / log)
workflow_executions         historial y auditoría de ejecuciones
cache / jobs                tablas internas del framework
```

**Relaciones:**

```
Process   ──< Workflow ──< Trigger
                       ──< Action
                       ──< WorkflowExecution
```

---

## Interfaz web

URL base: `http://localhost:8000`

| Ruta                          | Descripción                                           |
|-------------------------------|-------------------------------------------------------|
| `/`                           | Dashboard con métricas y procesos recientes           |
| `/processes`                  | Listado paginado de procesos                          |
| `/processes/create`           | Formulario de creación                                |
| `/processes/{id}`             | Detalle con estadísticas y barra de progreso          |
| `/processes/{id}/edit`        | Edición de proceso                                    |
| `/processes/{id}/execute`     | Simula ejecución y dispara webhooks                   |
| `/processes/{id}/ai-analyze`  | Análisis con Gemini integrado en la vista             |
| `/ai/analyze`                 | Analizador libre (formul + resultado en dos columnas) |
| `/api-explorer`               | Demo visual interactiva de la API REST                |

**Frontend sin frameworks CSS externos.** Implementación propia de un sistema de rejilla de 12 columnas con CSS Grid siguiendo convención BEM:

```css
/* Contenedor */
.rejilla-12 { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); }

/* Modificadores de span */
.rejilla-12__elemento--ocupa-3  { grid-column: span 3;  }
.rejilla-12__elemento--ocupa-6  { grid-column: span 6;  }
.rejilla-12__elemento--ocupa-8  { grid-column: span 8;  }
.rejilla-12__elemento--centrado-8 { grid-column: 3 / span 8; }

/* Responsive: todo colapsa a 12 columnas en ≤768px */
```

El layout principal usa `grid-template-areas` (cabecera / contenido / pie).

---

## API REST — v1

URL base: `http://localhost:8000/api/v1`

### Autenticación

Token Bearer con Laravel Sanctum. Todas las rutas de escritura requieren token.

```bash
# Obtener token
POST /api/v1/auth/login
{ "email": "admin@autoflow.test", "password": "password123" }

# Usar token en cabeceras
Authorization: Bearer {token}
```

### Rutas

**Autenticación**

```
POST   /auth/login      Obtener token (pública)
GET    /auth/me         Datos del usuario autenticado
POST   /auth/logout     Revocar token
```

**Procesos** — Idea 1: Process Automation API

```
GET    /processes               Lista paginada (pública)
POST   /processes               Crear proceso
GET    /processes/{id}          Ver detalle (pública)
PUT    /processes/{id}          Editar
DELETE /processes/{id}          Eliminar
POST   /processes/{id}/execute  Ejecutar + disparar webhook saliente + activar workflows
GET    /processes/{id}/executions  Historial de ejecuciones asociadas
POST   /processes/{id}/analyze  Análisis con Gemini
```

**Workflows** — Idea 2: Integration Hub API

```
GET    /workflows               Lista (pública)
POST   /workflows               Crear workflow (vinculable a proceso)
GET    /workflows/{id}          Ver con triggers y acciones (pública)
PUT    /workflows/{id}          Editar / activar / desactivar
DELETE /workflows/{id}          Eliminar en cascada
POST   /workflows/{id}/run      Ejecutar manualmente con payload opcional
GET    /workflows/{id}/logs     Historial de ejecuciones (pública)
```

**Triggers** — define *cuándo* se activa un workflow

```
GET    /triggers                Lista
POST   /triggers                Crear (tipos: manual / webhook / cron)
DELETE /triggers/{id}           Eliminar
```

> Al crear un trigger de tipo `webhook`, el sistema genera automáticamente un `webhook_token` único y devuelve la URL pública en el campo `webhook_url`.

**Actions** — define *qué* hace el workflow

```
GET    /actions                 Lista
POST   /actions                 Crear (tipos: http_request / log)
DELETE /actions/{id}            Eliminar
```

**IA** — con rate limiting de 10 peticiones/minuto

```
POST   /ai/analyze              Analiza un proceso y da recomendaciones técnicas
POST   /ai/suggest              Gemini devuelve un workflow completo listo para crear
```

**Webhooks entrantes** — sistemas externos activan workflows sin autenticación

```
POST   /webhooks/{token}        Activa el workflow vinculado al token (pública)
```

---

## Integración con IA (Gemini)

El servicio `AIService` conecta con la API de Google Gemini 2.5 Flash. Implementa dos funciones principales:

**`analyzeProcess()`** — analiza un proceso y devuelve:
- Viabilidad de automatización (Alta / Media / Baja)
- Tecnología y lenguaje recomendados
- Tiempo estimado de implementación
- Pasos sugeridos
- Vista previa del script generado por IA

**`suggestWorkflow()`** — Gemini devuelve la configuración completa de un workflow (trigger + acciones) lista para enviar a `POST /api/v1/workflows`.

Ambos métodos incluyen **fallback**: si la API de Gemini falla (timeout, cuota agotada), devuelven una respuesta genérica en lugar de un error 500.

---

## Sistema de webhooks

### Saliente (outgoing)

Cuando se ejecuta un proceso que tiene `webhook_url` configurada, `WebhookService::fireProcessWebhook()` hace un `POST` automático a esa URL con los datos de la ejecución. Permite notificar a Slack, ERPs, sistemas de monitoreo u otras herramientas.

### Entrante (incoming)

Cada trigger de tipo `webhook` recibe una URL pública única:

```
POST http://localhost:8000/api/v1/webhooks/{token}
```

Cuando un sistema externo (GitHub, Stripe, Slack, etc.) hace `POST` a esa URL, `WebhookController` localiza el workflow vinculado y ejecuta todas sus acciones en orden. El resultado queda registrado en `workflow_executions`.

### Flujo completo de ejecución

```
POST /api/v1/processes/{id}/execute
        │
        ├── increment executions_count
        ├── calcular éxito (80% de probabilidad)
        │
        ├── Si process.webhook_url existe:
        │       WebhookService::fireProcessWebhook(url, datos)
        │
        └── Por cada Workflow activo vinculado al proceso:
                WebhookService::run(workflow, 'process_execute', datos)
                        │
                        └── Por cada Action (ordenada por sort_order):
                                ├── http_request → Http::post(config.url, payload)
                                └── log          → Log::info(...)
                        │
                        └── WorkflowExecution::create(status, payloads)
```

---

## Decisiones de diseño

**Separación web / API en controllers distintos**
`ProcessController` (web) trabaja con sesiones y redirecciones. `Api\V1\ProcessController` devuelve JSON a través de API Resources. Comparten el mismo modelo `Process` y el mismo `AIService`.

**API Resources como contrato de salida**
Los modelos Eloquent no se exponen directamente. Cada Resource controla exactamente qué campos ve el cliente y en qué formato, desacoplando la estructura interna de la interfaz pública.

**Versionado desde el primer día**
El prefijo `/api/v1/` permite añadir `/api/v2/` en el futuro sin romper integraciones existentes.

**WebhookService como orquestador único**
Tres flujos distintos (ejecución manual, webhook entrante, proceso vinculado) reutilizan el mismo `WebhookService::run()`. La lógica de negocio no se duplica en los controllers.

**Token auto-generado en el modelo**
`Trigger::booted()` genera el `webhook_token` con `Str::random(40)` automáticamente al crear un trigger de tipo `webhook`. El controller no necesita conocer esta lógica.

**Rate limiting en IA**
`throttle:10,1` en las rutas `/ai/*` limita las peticiones a Gemini a 10 por minuto por usuario para proteger la cuota de la API key.

**Gestión de errores HTTP en la API**
`bootstrap/app.php` intercepta `AuthenticationException`, `MethodNotAllowedHttpException` y `NotFoundHttpException` para devolver siempre JSON en rutas `/api/*`, evitando que Laravel intente redirigir a rutas web que no existen en el contexto de la API.

---

## Usuario de prueba

```
Email:      admin@autoflow.test
Contraseña: password123
```

---

## Colección Postman

Importar el archivo `AutoFlow-API.postman_collection.json` incluido en el repositorio. Contiene todas las rutas con ejemplos de body y un script que guarda el token automáticamente tras hacer login.

---

## Posibles mejoras futuras

- Ejecución real de scripts mediante cron (actualmente simulada)
- Tests de feature con Pest para todos los endpoints de la API
- Documentación OpenAPI / Swagger generada desde las rutas
- Autenticación con roles (admin / usuario)
- Notificaciones por email al ejecutar un proceso
- Conexión a OpenAI como alternativa a Gemini
