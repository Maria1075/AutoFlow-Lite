# AutoFlow Lite — Documentación de la API

**Versión:** 1.0  
**Base URL:** `http://localhost:8000/api/v1`  
**Formato:** JSON  
**Autenticación:** Bearer Token (Laravel Sanctum)

---

## Índice

1. [Introducción](#1-introducción)
2. [Autenticación](#2-autenticación)
3. [Módulo 1 — Procesos](#3-módulo-1--procesos)
4. [Módulo 2 — Workflows](#4-módulo-2--workflows)
5. [Módulo 3 — Triggers](#5-módulo-3--triggers)
6. [Módulo 4 — Actions](#6-módulo-4--actions)
7. [Módulo 5 — Webhooks entrantes](#7-módulo-5--webhooks-entrantes)
8. [Módulo 6 — Inteligencia Artificial](#8-módulo-6--inteligencia-artificial)
9. [Códigos de respuesta](#9-códigos-de-respuesta)
10. [Tabla resumen de rutas](#10-tabla-resumen-de-rutas)

---

## 1. Introducción

AutoFlow Lite es una plataforma de **automatización de procesos empresariales** que expone una REST API para que sistemas externos puedan:

- Consultar y gestionar procesos
- Definir workflows de automatización (trigger + acciones)
- Recibir y enviar webhooks hacia herramientas externas
- Obtener análisis y sugerencias de automatización mediante IA (Google Gemini)

### Cabeceras requeridas en todas las peticiones

```
Accept: application/json
Content-Type: application/json
```

### Cabecera de autenticación (rutas protegidas)

```
Authorization: Bearer {token}
```

### Formato de respuesta estándar

```json
{
  "message": "Descripción de la operación",
  "data": { }
}
```

Las listas paginadas devuelven además `links` y `meta` con información de paginación.

---

## 2. Autenticación

### POST `/auth/login`

Valida las credenciales del usuario y devuelve un token de acceso personal. El token no caduca por defecto y debe incluirse en todas las rutas protegidas.

**Acceso:** Público

**Body:**

```json
{
  "email": "admin@autoflow.test",
  "password": "password123"
}
```

**Respuesta `200`:**

```json
{
  "message": "Login correcto.",
  "token": "1|xK9mP2...",
  "user": {
    "id": 1,
    "name": "Admin API",
    "email": "admin@autoflow.test"
  }
}
```

**Respuesta `422` — credenciales incorrectas:**

```json
{
  "message": "Las credenciales no son correctas.",
  "errors": {
    "email": ["Las credenciales no son correctas."]
  }
}
```

---

### GET `/auth/me`

Devuelve los datos del usuario al que pertenece el token activo.

**Acceso:** Token requerido

**Respuesta `200`:**

```json
{
  "user": {
    "id": 1,
    "name": "Admin API",
    "email": "admin@autoflow.test"
  }
}
```

---

### POST `/auth/logout`

Revoca el token actual. Las peticiones posteriores con ese token recibirán `401`.

**Acceso:** Token requerido

**Respuesta `200`:**

```json
{
  "message": "Sesión cerrada correctamente."
}
```

---

## 3. Módulo 1 — Procesos

Un **proceso** representa una tarea empresarial que puede ejecutarse, medirse y automatizarse. Cada proceso registra sus ejecuciones y tasa de éxito. Si tiene `webhook_url` configurada, notifica automáticamente a un sistema externo cuando se ejecuta.

### GET `/processes`

Devuelve la lista de procesos ordenada por fecha de creación descendente, paginada a 15 por página.

**Acceso:** Público

**Respuesta `200`:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Generación de informes mensuales",
      "description": "Cada primer día del mes consulta la BD y genera un PDF.",
      "frequency": "monthly",
      "status": "active",
      "webhook_url": "https://hooks.slack.com/services/...",
      "executions_count": 12,
      "success_count": 10,
      "success_rate": 83.33,
      "is_automatizable": true,
      "created_at": "2026-06-01T10:00:00+00:00",
      "updated_at": "2026-06-13T18:00:00+00:00"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/processes?page=1",
    "last":  "http://localhost:8000/api/v1/processes?page=1",
    "prev":  null,
    "next":  null
  },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 4
  }
}
```

---

### POST `/processes`

Crea un nuevo proceso.

**Acceso:** Token requerido

**Body:**

```json
{
  "name": "Generación de informes mensuales",
  "description": "Cada primer día del mes consulta la BD, genera un PDF con gráficos y lo envía por correo.",
  "frequency": "monthly",
  "webhook_url": "https://hooks.slack.com/services/TU/SLACK/WEBHOOK"
}
```

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `name` | string | Sí | Mínimo 3 caracteres, máximo 200 |
| `description` | string | Sí | Mínimo 10 caracteres |
| `frequency` | string | Sí | `hourly` `daily` `weekly` `monthly` `manual` |
| `webhook_url` | string | No | URL válida a la que notificar al ejecutar |

**Respuesta `201`:**

```json
{
  "message": "Proceso creado correctamente.",
  "data": {
    "id": 2,
    "name": "Generación de informes mensuales",
    "frequency": "monthly",
    "status": "active",
    "webhook_url": "https://hooks.slack.com/...",
    "executions_count": 0,
    "success_count": 0,
    "success_rate": 0,
    "is_automatizable": true,
    "created_at": "2026-06-13T20:00:00+00:00"
  }
}
```

---

### GET `/processes/{id}`

Devuelve el detalle de un proceso.

**Acceso:** Público

**Respuesta `200`:** mismo objeto que en el listado.

**Respuesta `404`:**

```json
{ "message": "Ruta no encontrada." }
```

---

### PUT `/processes/{id}`

Actualiza uno o varios campos de un proceso. Acepta también `PATCH` para actualizaciones parciales.

**Acceso:** Token requerido

**Body (todos los campos son opcionales):**

```json
{
  "name": "Informes mensuales v2",
  "status": "paused",
  "webhook_url": "https://nueva-url.com/webhook"
}
```

| Campo `status` | Descripción |
|---|---|
| `active` | Proceso activo |
| `paused` | Temporalmente detenido |
| `completed` | Proceso finalizado |

**Respuesta `200`:**

```json
{
  "message": "Proceso actualizado correctamente.",
  "data": { }
}
```

---

### DELETE `/processes/{id}`

Elimina el proceso y sus datos asociados.

**Acceso:** Token requerido

**Respuesta `200`:**

```json
{ "message": "Proceso eliminado correctamente." }
```

---

### POST `/processes/{id}/execute`

Simula la ejecución del proceso (80% de probabilidad de éxito). Al ejecutarse:

1. Incrementa `executions_count`
2. Si tiene éxito, incrementa `success_count`
3. Si tiene `webhook_url`, envía un `POST` automático a esa URL
4. Activa todos los workflows activos vinculados al proceso

**Acceso:** Token requerido

**Respuesta `200`:**

```json
{
  "message": "Proceso ejecutado correctamente.",
  "success": true,
  "process": {
    "id": 1,
    "executions_count": 13,
    "success_count": 11,
    "success_rate": 84.62
  },
  "webhook_fired": true,
  "workflows_run": [
    {
      "workflow_id": 1,
      "workflow_name": "Notificar Slack",
      "status": "success"
    }
  ]
}
```

**Payload enviado al `webhook_url` del proceso:**

```json
{
  "process_id": 1,
  "process_name": "Generación de informes mensuales",
  "success": true,
  "executed_at": "2026-06-13T21:00:00+00:00"
}
```

---

### GET `/processes/{id}/executions`

Devuelve el historial de ejecuciones de los workflows vinculados al proceso.

**Acceso:** Token requerido

**Respuesta `200`:**

```json
{
  "data": [
    {
      "id": 5,
      "workflow_id": 1,
      "triggered_by": "process_execute",
      "status": "success",
      "request_payload": { "process_id": 1, "success": true },
      "response_payload": [
        { "action": "Notificar Slack", "type": "http_request", "http_status": 200, "ok": true }
      ],
      "error_message": null,
      "created_at": "2026-06-13T21:00:00+00:00"
    }
  ]
}
```

---

### POST `/processes/{id}/analyze`

Envía el proceso a Gemini y devuelve recomendaciones de automatización.

**Acceso:** Token requerido  
**Rate limit:** 10 peticiones/minuto

**Respuesta `200`:**

```json
{
  "data": {
    "feasibility": "Alta",
    "recommended_tech": "Python con pandas, matplotlib y fpdf2",
    "recommended_language": "Python",
    "estimated_time": "20 horas",
    "steps": [
      "Paso 1: Conectar a la base de datos y extraer datos del mes anterior.",
      "Paso 2: Generar gráficos con matplotlib.",
      "Paso 3: Ensamblar el PDF con fpdf2.",
      "Paso 4: Enviar el correo con smtplib.",
      "Paso 5: Programar con cron."
    ],
    "script_preview": "import pandas as pd\nimport matplotlib.pyplot as plt\n..."
  },
  "process": { }
}
```

---

## 4. Módulo 2 — Workflows

Un **workflow** es el contenedor de una automatización. Agrupa un trigger (cuándo actuar) y una o varias acciones (qué hacer). Puede vincularse a un proceso para ejecutarse automáticamente cuando éste se lance.

### GET `/workflows`

Lista todos los workflows con sus triggers y acciones.

**Acceso:** Público

**Respuesta `200`:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Notificar Slack al ejecutar informes",
      "description": "Envía mensaje a Slack cuando el proceso de informes termina.",
      "is_active": true,
      "process_id": 1,
      "triggers": [ ],
      "actions": [ ],
      "created_at": "2026-06-13T20:00:00+00:00"
    }
  ]
}
```

---

### POST `/workflows`

Crea un nuevo workflow. Los triggers y acciones se añaden en llamadas separadas.

**Acceso:** Token requerido

**Body:**

```json
{
  "name": "Notificar Slack al ejecutar informes",
  "description": "Envía mensaje a Slack cuando el proceso de informes termina.",
  "is_active": true,
  "process_id": 1
}
```

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `name` | string | Sí | Nombre del workflow |
| `description` | string | No | Descripción opcional |
| `is_active` | boolean | No | `true` por defecto |
| `process_id` | integer | No | ID de proceso al que vincular |

**Respuesta `201`:**

```json
{
  "message": "Workflow creado correctamente.",
  "data": {
    "id": 1,
    "name": "Notificar Slack al ejecutar informes",
    "is_active": true,
    "process_id": 1,
    "triggers": [],
    "actions": []
  }
}
```

---

### GET `/workflows/{id}`

Devuelve el detalle del workflow con sus triggers, acciones y proceso vinculado.

**Acceso:** Público

---

### PUT `/workflows/{id}`

Edita el workflow. Útil para activar o desactivar sin borrar.

**Acceso:** Token requerido

**Body:**

```json
{ "is_active": false }
```

---

### DELETE `/workflows/{id}`

Elimina el workflow y en cascada sus triggers, acciones y registros de ejecución.

**Acceso:** Token requerido

**Respuesta `200`:**

```json
{ "message": "Workflow eliminado correctamente." }
```

---

### POST `/workflows/{id}/run`

Ejecuta el workflow manualmente, lanzando todas sus acciones en orden. Se puede pasar un payload libre que llegará a las acciones.

**Acceso:** Token requerido

**Body (opcional):**

```json
{
  "payload": {
    "origen": "prueba_manual",
    "dato_extra": "valor"
  }
}
```

**Respuesta `200`:**

```json
{
  "message": "Workflow ejecutado.",
  "execution": {
    "id": 10,
    "workflow_id": 1,
    "triggered_by": "manual",
    "status": "success",
    "response_payload": [
      {
        "action": "Notificar Slack",
        "type": "http_request",
        "url": "https://hooks.slack.com/...",
        "http_status": 200,
        "ok": true
      },
      {
        "action": "Registrar en historial",
        "type": "log",
        "ok": true,
        "message": "Registrado correctamente"
      }
    ],
    "created_at": "2026-06-13T21:00:00+00:00"
  }
}
```

**Respuesta `422` — workflow desactivado:**

```json
{ "message": "El workflow está desactivado." }
```

---

### GET `/workflows/{id}/logs`

Historial de las últimas 50 ejecuciones del workflow.

**Acceso:** Público

**Respuesta `200`:**

```json
{
  "data": [
    {
      "id": 10,
      "triggered_by": "manual",
      "status": "success",
      "request_payload": { "origen": "prueba_manual" },
      "response_payload": [ ],
      "error_message": null,
      "created_at": "2026-06-13T21:00:00+00:00"
    }
  ]
}
```

---

## 5. Módulo 3 — Triggers

Un **trigger** define *cuándo* se activa un workflow.

| Tipo | Cómo se activa |
|---|---|
| `manual` | Llamando a `POST /workflows/{id}/run` |
| `webhook` | Sistema externo hace POST a la URL pública única del trigger |
| `cron` | Expresión cron almacenada para ejecución programada futura |

### GET `/triggers`

Lista todos los triggers con el workflow al que pertenecen.

**Acceso:** Token requerido

---

### POST `/triggers`

Crea un trigger para un workflow. Si el tipo es `webhook`, el sistema genera automáticamente un token único y devuelve la URL pública lista para usar.

**Acceso:** Token requerido

**Body — tipo `manual`:**

```json
{
  "workflow_id": 1,
  "name": "Activación manual desde consola",
  "type": "manual"
}
```

**Body — tipo `webhook`:**

```json
{
  "workflow_id": 1,
  "name": "Webhook desde GitHub Actions",
  "type": "webhook"
}
```

**Body — tipo `cron`:**

```json
{
  "workflow_id": 1,
  "name": "Primer día de cada mes a las 8:00",
  "type": "cron",
  "cron_expression": "0 8 1 * *"
}
```

**Respuesta `201` para tipo `webhook`:**

```json
{
  "message": "Trigger creado correctamente.",
  "data": {
    "id": 3,
    "workflow_id": 1,
    "name": "Webhook desde GitHub Actions",
    "type": "webhook",
    "cron_expression": null,
    "webhook_url": "http://localhost:8000/api/v1/webhooks/6hcnbZxreIi6jcfnTTo2SIGGNWvVZ8zeE7iY7Wjm",
    "created_at": "2026-06-13T21:00:00+00:00"
  }
}
```

---

### DELETE `/triggers/{id}`

Elimina el trigger. Si era de tipo `webhook`, su URL deja de funcionar.

**Acceso:** Token requerido

---

## 6. Módulo 4 — Actions

Una **action** define *qué hace* el workflow cuando se activa. Las acciones de un mismo workflow se ejecutan en orden según `sort_order`.

| Tipo | Descripción |
|---|---|
| `http_request` | Hace POST a una URL externa con los datos del evento |
| `log` | Registra el evento en el historial sin acción externa |

### GET `/actions`

Lista todas las acciones.

**Acceso:** Token requerido

---

### POST `/actions`

Crea una acción para un workflow.

**Acceso:** Token requerido

**Body — tipo `http_request`:**

```json
{
  "workflow_id": 1,
  "name": "Notificar a Slack",
  "type": "http_request",
  "sort_order": 1,
  "config": {
    "url": "https://hooks.slack.com/services/TU/SLACK/WEBHOOK",
    "method": "POST",
    "headers": {
      "Content-Type": "application/json"
    },
    "body": {
      "text": "Proceso ejecutado correctamente en AutoFlow Lite"
    }
  }
}
```

**Body — tipo `log`:**

```json
{
  "workflow_id": 1,
  "name": "Registrar ejecución en historial",
  "type": "log",
  "sort_order": 2
}
```

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `workflow_id` | integer | Sí | ID del workflow al que pertenece |
| `name` | string | Sí | Nombre descriptivo de la acción |
| `type` | string | Sí | `http_request` o `log` |
| `sort_order` | integer | No | Orden de ejecución. Por defecto `0` |
| `config` | object | Si `type=http_request` | URL, headers y body de la petición |
| `config.url` | string | Si `type=http_request` | URL externa válida |

**Payload que recibe la URL externa cuando se ejecuta la acción:**

```json
{
  "event_payload": {
    "process_id": 1,
    "process_name": "Generación de informes",
    "success": true,
    "executed_at": "2026-06-13T21:00:00+00:00"
  },
  "texto_personalizado": "Proceso ejecutado correctamente en AutoFlow Lite"
}
```

**Respuesta `201`:**

```json
{
  "message": "Acción creada correctamente.",
  "data": {
    "id": 2,
    "workflow_id": 1,
    "name": "Notificar a Slack",
    "type": "http_request",
    "config": { "url": "https://...", "method": "POST" },
    "sort_order": 1,
    "created_at": "2026-06-13T21:00:00+00:00"
  }
}
```

---

### DELETE `/actions/{id}`

Elimina la acción.

**Acceso:** Token requerido

---

## 7. Módulo 5 — Webhooks entrantes

Ruta **pública sin autenticación** para que sistemas externos activen workflows.

### POST `/webhooks/{token}`

Cuando un servicio externo (GitHub, Stripe, Slack, etc.) hace POST a esta URL:

1. El sistema localiza el trigger por su `webhook_token` único
2. Verifica que el workflow vinculado esté activo
3. Ejecuta todas las acciones en orden
4. Guarda el resultado en `workflow_executions`
5. Devuelve el estado de la ejecución

**Acceso:** Público

**URL:** `http://localhost:8000/api/v1/webhooks/{token}`

> El `token` se obtiene al crear un trigger de tipo `webhook` (ver Módulo 3).

**Body (libre — cualquier dato del sistema externo):**

```json
{
  "source": "github",
  "event": "push",
  "repository": "mi-proyecto",
  "branch": "main",
  "author": "developer@empresa.com"
}
```

**Respuesta `200`:**

```json
{
  "message": "Webhook recibido y procesado.",
  "workflow": "Notificar Slack al ejecutar informes",
  "status": "success",
  "executed_at": "2026-06-13T21:00:00+00:00"
}
```

**Respuesta `404` — token no válido:**

```json
{ "message": "Token de webhook no válido." }
```

**Respuesta `422` — workflow desactivado:**

```json
{ "message": "El workflow no está activo." }
```

---

## 8. Módulo 6 — Inteligencia Artificial

Integración con Google Gemini 2.5 Flash para análisis y sugerencias de automatización.

**Rate limiting:** 10 peticiones por minuto por usuario para proteger la cuota de la API key.  
**Fallback:** Si Gemini no está disponible, ambos endpoints devuelven una respuesta genérica en lugar de un error.

### POST `/ai/analyze`

Analiza la descripción de un proceso y devuelve recomendaciones técnicas de automatización.

**Acceso:** Token requerido

**Body:**

```json
{
  "process_name": "Backup nocturno de base de datos",
  "process_description": "Cada noche a las 2:00 AM se hace un mysqldump de la base de datos de producción y se sube el archivo comprimido a un bucket de S3. Si falla, se notifica al equipo de DevOps."
}
```

**Respuesta `200`:**

```json
{
  "data": {
    "feasibility": "Alta",
    "recommended_tech": "Bash + AWS CLI + cron",
    "recommended_language": "Bash",
    "estimated_time": "8 horas",
    "steps": [
      "Paso 1: Configurar credenciales de AWS CLI en el servidor.",
      "Paso 2: Escribir script Bash con mysqldump y compresión gzip.",
      "Paso 3: Añadir subida a S3 con aws s3 cp.",
      "Paso 4: Implementar manejo de errores con notificación por email o Slack.",
      "Paso 5: Programar con cron: 0 2 * * *"
    ],
    "script_preview": "#!/bin/bash\nDATE=$(date +%Y%m%d)\nDUMP_FILE=\"backup_$DATE.sql.gz\"\nmysqldump -u root -p mydb | gzip > /tmp/$DUMP_FILE\naws s3 cp /tmp/$DUMP_FILE s3://mi-bucket/backups/\nrm /tmp/$DUMP_FILE\necho \"Backup completado: $DUMP_FILE\""
  }
}
```

---

### POST `/ai/suggest`

Gemini analiza el proceso y devuelve la configuración completa de un workflow (trigger + acciones) lista para crear con `POST /api/v1/workflows`.

**Acceso:** Token requerido

**Body:**

```json
{
  "process_name": "Backup nocturno de base de datos",
  "process_description": "Cada noche a las 2:00 AM se hace un mysqldump y se sube a S3. Si falla, se notifica al equipo de DevOps."
}
```

**Respuesta `200`:**

```json
{
  "message": "Sugerencia de workflow generada con IA.",
  "data": {
    "workflow_name": "Automatización de backup nocturno",
    "workflow_description": "Ejecuta el backup a las 2:00 AM y notifica al equipo de DevOps en caso de fallo.",
    "trigger": {
      "name": "Cron diario 2:00 AM",
      "type": "cron",
      "cron_expression": "0 2 * * *"
    },
    "actions": [
      {
        "name": "Notificar éxito a DevOps en Slack",
        "type": "http_request",
        "sort_order": 1,
        "config": {
          "url": "https://hooks.slack.com/services/TU/SLACK/WEBHOOK",
          "method": "POST",
          "headers": {},
          "body": { "text": "Backup nocturno completado correctamente." }
        }
      },
      {
        "name": "Registrar resultado en historial",
        "type": "log",
        "sort_order": 2,
        "config": null
      }
    ],
    "explanation": "El cron nocturno a las 2:00 AM minimiza el impacto en producción. La acción de Slack notifica en tiempo real al equipo de DevOps para que puedan actuar si algo falla."
  }
}
```

---

## 9. Códigos de respuesta

| Código | Significado | Cuándo ocurre |
|---|---|---|
| `200` | OK | Petición correcta |
| `201` | Created | Recurso creado correctamente |
| `401` | Unauthorized | Token ausente, inválido o revocado |
| `404` | Not Found | Recurso o ruta no encontrada |
| `405` | Method Not Allowed | Método HTTP no soportado por la ruta |
| `422` | Unprocessable Entity | Error de validación o regla de negocio |
| `429` | Too Many Requests | Rate limit de IA superado (10 req/min) |

**Formato de error de validación `422`:**

```json
{
  "message": "El campo name es obligatorio.",
  "errors": {
    "name": ["El campo name es obligatorio."],
    "frequency": ["El campo frequency debe ser uno de: hourly, daily, weekly, monthly, manual."]
  }
}
```

---

## 10. Tabla resumen de rutas

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/auth/login` | Público | Obtener token |
| `GET` | `/auth/me` | Token | Datos del usuario |
| `POST` | `/auth/logout` | Token | Revocar token |
| `GET` | `/processes` | Público | Listar procesos |
| `POST` | `/processes` | Token | Crear proceso |
| `GET` | `/processes/{id}` | Público | Ver proceso |
| `PUT` | `/processes/{id}` | Token | Editar proceso |
| `DELETE` | `/processes/{id}` | Token | Eliminar proceso |
| `POST` | `/processes/{id}/execute` | Token | Ejecutar + webhook saliente |
| `GET` | `/processes/{id}/executions` | Token | Historial de ejecuciones |
| `POST` | `/processes/{id}/analyze` | Token | Análisis IA |
| `GET` | `/workflows` | Público | Listar workflows |
| `POST` | `/workflows` | Token | Crear workflow |
| `GET` | `/workflows/{id}` | Público | Ver workflow |
| `PUT` | `/workflows/{id}` | Token | Editar workflow |
| `DELETE` | `/workflows/{id}` | Token | Eliminar en cascada |
| `POST` | `/workflows/{id}/run` | Token | Ejecutar manualmente |
| `GET` | `/workflows/{id}/logs` | Público | Historial de ejecuciones |
| `GET` | `/triggers` | Token | Listar triggers |
| `POST` | `/triggers` | Token | Crear trigger |
| `DELETE` | `/triggers/{id}` | Token | Eliminar trigger |
| `GET` | `/actions` | Token | Listar acciones |
| `POST` | `/actions` | Token | Crear acción |
| `DELETE` | `/actions/{id}` | Token | Eliminar acción |
| `POST` | `/webhooks/{token}` | Público | Webhook entrante de sistema externo |
| `POST` | `/ai/analyze` | Token | Análisis con Gemini |
| `POST` | `/ai/suggest` | Token | Sugerir workflow con Gemini |

---

*Documentación generada para AutoFlow Lite v1.0 — Laravel 12 · PHP 8.2 · Google Gemini 2.5 Flash*
