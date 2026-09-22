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

## Seguridad
Sesión HttpOnly+SameSite+Secure, `session_regenerate_id`, CSRF por formulario,
`password_hash`/`password_verify`, control por rol, bitácora (`audit_log`).
Pendiente (siguiente iteración): CRUD (usuarios, leads, asuntos, documentos),
bloqueo por intentos, y subida de documentos endurecida (LFPDPPP).
