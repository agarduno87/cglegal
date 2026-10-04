# Portal Culebro (Fase 2)
Portal privado con login y roles **admin / abogado / cliente**, en `/portal/`.

## Correr en local (SQLite, cero config)
Forma recomendada (elige puerto libre solo, host fijo, imprime la URL):
```
bash portal/serve.sh            # desde la raíz del repo (culebroabogados/)
bash portal/serve.sh 8080       # intenta 8080; si está ocupado toma otro al azar
```
La primera vez, siembra los usuarios de prueba:
```
php portal/dev-seed.php
```
Abrir la URL que imprime `serve.sh`, p. ej. http://127.0.0.1:22245/portal/login.php
- admin@cglegal.com.mx / admin123
- abogado@cglegal.com.mx / abogado123
- cliente@cglegal.com.mx / cliente123

> **Usa un solo host.** `localhost` y `127.0.0.1` son orígenes DISTINTOS para las
> cookies: si inicias sesión en uno y abres el otro, la sesión no viaja y parece que
> "no deja entrar". `serve.sh` fija `127.0.0.1` para evitarlo.

Forma manual equivalente: `cd` a `culebroabogados/` y `php -S 127.0.0.1:<puerto>`.

## Producción (GoDaddy / MySQL)
1. Importar `db/schema.mysql.sql`.
2. Copiar `portal/lib/config.sample.php` → `portal/lib/config.php` con `driver=mysql` y credenciales (por env o directo). `config.php` NO se versiona.
3. Crear el primer admin (script protegido) y borrar `dev-seed.php`.

## Expediente de trabajo (Fase 2.1 — A/B/C/D)
Cada asunto (admin y abogado) es ahora un expediente completo, en `portal/lib/matter.php`
(manejador de sub-acciones `m_action` + render de secciones, reutilizado por ambos):
- **A. Llevar el asunto:** plazos y vencimientos (con alertas de vencido), tareas con
  responsable y fecha, partes y contactos (contraparte, notario, autoridad, co-counsel…),
  y meta del caso (no. de expediente, autoridad/foro, cuantía+moneda, riesgo).
- **B. Investigación / due diligence:** checklist con plantillas por tipo de asunto,
  requerimientos al cliente (request list), y hallazgos con severidad + recomendación.
- **C. Negocio:** tiempo y honorarios (horas, tarifa, facturable, totales) + KPIs en los
  dashboards (asuntos por estado/área/abogado, plazos próximos/vencidos, honorarios, leads).
- **D. Cumplimiento/ético:** verificación de **conflicto de interés** (registro + chequeo,
  y aviso al crear asunto), **bitácora por asunto** visible, y acceso por asunto (403).

### Documentos (endurecido, LFPDPPP + secreto profesional)
Subida validada (whitelist de extensión + verificación `finfo` de contenido, límite 20 MB,
nombre aleatorio), almacenada **fuera de la raíz pública** en `db/uploads/` (con
`Require all denied`) y servida solo por `portal/download.php` con control de acceso por
rol/asignación. `db/uploads/` está en `.gitignore` (nunca se versionan archivos de clientes).

## Seguridad
Sesión HttpOnly+SameSite+Secure, `session_regenerate_id`, CSRF por formulario,
`password_hash`/`password_verify`, control por rol y **por asunto** (403), prepared
statements, bitácora (`audit_log`), subida de documentos endurecida.
Pendiente: bloqueo por intentos de login, y migración/staging en GoDaddy (MySQL).
