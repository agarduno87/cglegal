# Portal Culebro (Fase 2)
Portal privado con login y roles **admin / abogado / cliente**, en `/portal/`.

## Correr en local (SQLite, cero config)
```
php dev-seed.php        # crea usuarios de prueba (desde /portal)
cd .. && php -S localhost:8000
```
Abrir http://localhost:8000/portal/login.php
- admin@cglegal.com.mx / admin123
- abogado@cglegal.com.mx / abogado123
- cliente@cglegal.com.mx / cliente123

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
