# CLAUDE.md — Culebro Abogados (repo `cglegal`)

Memoria durable del proyecto. Sitio del despacho **Culebro Abogados** (razón social
**CG Legal & Real Estate Consulting, S.C.** — "Real Estate", no "Real State").
Es un despacho legal con consultoría, no al revés. Trabajo **para cliente**, pagado.
Repo: github.com/agarduno87/cglegal · Pages (staging visual): agarduno87.github.io/cglegal/
Última actualización: sep 2026.

## Cambios recientes (sep 2026)
- Textos: equipo "El despacho, en sus albores."; Publicaciones "Compromisos.".
- Publicaciones: 4 tarjetas (agregada Gobierno e infraestructura) que enlazan a
  páginas propias en `/publicaciones/`. **Se quitó** la leyenda "Artículo de ejemplo…".
- Footer: crédito ahora "© 2026 Culebro Abogados · by Datara Hub" (se quitó el 2º 2026).
- **Entregado al cliente un PDF machote** con TODO el contenido público (home +
  subpáginas) para su **ronda de adecuación de contenido**. El portal NO entra en ese
  ejercicio.
- Fase 2 (portal) construida y probada: usuarios/roles + asuntos (ver abajo).

## Ramas
- **`main` = producción** (sitio público). Es lo que se despliega. NO romper.
- **`fase2` = portal** (`/portal/`, PHP+MySQL). Se fusiona a `main` cuando esté probado.
  Regla: los cambios del sitio público se hacen en `main` y se mergean a `fase2`
  para que no se atrase.

## Diseño y decisión
Ganador elegido por el despacho: **Monograma** (acento *oxblood* `#7C2E3B` sobre
hueso `#F2F0EA`, tinta `#14181A`; serif **Fraunces**, sans **Inter**). Portada tipo
Creel (intro con ícono + "Culebro" + Entrar). Referencias que gustaron: Creel,
Galicia, swlaw, btlaw, stblaw, perezllorca, basham. Las 9 maquetas del batch previo
viven en `../rediseno`/`../landing` (histórico); el sitio real es este repo.

## Hosting (DECIDIDO)
**GoDaddy, cPanel, Linux — PHP + MySQL. NO corre Python.** El correo
`@cglegal.com.mx` vive en el mismo GoDaddy (no tocarlo). **GitHub Pages es solo
staging visual**: NO ejecuta PHP y NO aplica `.htaccess`/CSP (GitHub pone las suyas).
Producción real = GoDaddy.

## Estructura
```
index.html            Home (Monograma). Home de producción = index.html
areas/                6 páginas de área de práctica
publicaciones/        4 artículos (contenido de EJEMPLO, pendiente de redacción)
legal/                aviso-privacidad.html + terminos.html (BORRADORES, validar abogado)
en/                   Espejo en inglés (index + areas) para hreflang
assets/               styles.css, app.js, areas.css, fonts.css + fonts/ (Fraunces,Inter),
                      areas/*.jpg (placeholders), icono-*.png, javier.jpg, alejandro.jpg,
                      og.png (1200x630), BrochureCA.pdf (13MB, archivo VIEJO pendiente)
contact.php           Recepción de leads -> contacto@cglegal.com.mx + autorespuesta ES/EN
.htaccess             HTTPS + cabeceras seguridad + CSP ESTRICTA (Apache/GoDaddy)
robots.txt llms.txt sitemap.xml   SEO + GEO
portal/  db/          Fase 2 (ver abajo) — presentes en rama fase2
```

## Reglas / convenciones (IMPORTANTES)
- **CERO estilos/JS inline** y **CSP estricta** (`default-src 'self'`, sin
  'unsafe-inline', sin CDNs). CSS→`assets/*.css`, JS→`assets/app.js`. Fuentes e
  imágenes **auto-hospedadas**. (JSON-LD inline sí se permite: es bloque de datos.)
- **NADA de CSP en `<meta>`** — va en `.htaccess` (cabecera).
- **i18n ES/EN**: elementos con `data-t="clave"`; diccionario `DICT.en` en `app.js`;
  `setLang()` intercambia. El espejo `/en/` declara `lang="en"` y `app.js` auto-aplica
  inglés. hreflang es/en/x-default en home y áreas.
- **Logo**: solo ícono + "Culebro" (NO "Abogados y Consultores"). Usar los PNG
  entregados, nunca redibujar.
- **WhatsApp**: botón flotante, número de PRUEBAS `9612155515` (`wa.me/529612155515`).
  Auto-reply real lo da WhatsApp Business (app o API), no el sitio.

## Contenido (hechos)
- **6 áreas**: Corporativo general · Inmobiliario y hotelero · Auditoría legal (due
  diligence) · Comercio Exterior · Gobierno e infraestructura · Actividades filantrópicas.
- **Equipo en el sitio = solo 2 fundadores**: **Javier Culebro** y **Alejandro
  Culebro** (ambos "Fundador", ambos con modal "Ver trayectoria"; Alejandro usa
  provisionalmente la reseña de Javier hasta tener su CV). El equipo ampliado
  (Adriana, Ricardo, Yubuali, Efraín, Jairo, Patricio, Fátima) se retiró de esta
  versión del diseño.
- **Leads**: llegan por **correo a contacto@cglegal.com.mx** (+ autorespuesta). **No
  hay UI de leads** en el portal (por decisión del cliente).
- **Misión/visión/valores**: textos placeholder marcados "por confirmar".
- **Publicaciones**: contenido de EJEMPLO (ya sin la leyenda de aviso). Pendiente
  **ronda de adecuación de contenido** con el cliente (se le entregó PDF machote).
  **Legales**: borradores, validar con abogado (esos SÍ conservan su aviso).
- **Footer**: "© 2026 Culebro Abogados · by Datara Hub" (link provisional a techStudio).
- Encabezados actuales: equipo "El despacho, en sus albores." (SÍ es "albores");
  publicaciones "Compromisos."

## Formulario de contacto
`app.js` envía por `fetch` a `contact.php`; si falla (p.ej. en Pages sin PHP) hace
**fallback a `mailto:`**. Trae honeypot (`website`) + `rendered_at` (trampa de tiempo).
`contact.php`: honeypot + trampa de tiempo + rate-limit (archivo) + validación
server-side + CORS cerrado + logging anonimizado + autorespuesta. Config por entorno:
`LEAD_TO`, `LEAD_FROM`, `ALLOWED_ORIGIN` (SetEnv en cPanel).

## Fase 2 — Portal (rama `fase2`, en `/portal/`)
Login + roles **admin / abogado / cliente**. `db.php` (PDO): **SQLite en dev**
(auto-DDL, cero config) / **MySQL en prod** (`db/schema.mysql.sql`). `models.php` =
capa de datos. Seguridad: sesión HttpOnly+SameSite+Secure, `session_regenerate_id`,
**CSRF**, `password_hash`/`verify`, `require_role` (403), **bitácora** `audit_log`,
prepared statements, escape con `h()`.
- **Usuarios (admin)**: crear/editar rol+activo+nombre/reset pass/eliminar. Guardas:
  no auto-borrado, no quitar último admin, no auto-degradarse.
- **Asuntos**: admin crea/edita/asigna cliente+abogado/estado/elimina + notas;
  abogado ve solo asignados (guard por-asunto=403), cambia estado, agrega notas;
  cliente ve sus asuntos + seguimiento (solo lectura).
- **Correr local**: `cd portal && php dev-seed.php` → `cd .. && php -S localhost:8000`
  → `/portal/login.php`. Usuarios prueba: admin@/abogado@/cliente@cglegal.com.mx
  (pass = rol+123).
- **Pendiente Fase 2**: subida de **documentos** endurecida (LFPDPPP + secreto
  profesional — lo más sensible, va al final), bloqueo por intentos de login, y
  migración/staging en GoDaddy (MySQL).

## Correr y desplegar
- Local (con PHP, prueba el form): `php -S localhost:8000` en la raíz → abrir `/`.
- Producción: subir a `public_html/` de GoDaddy (corre PHP + `.htaccess`).
- Revisión visual: GitHub Pages (ver `PUBLICAR-PAGES.md`) — ojo: sin PHP, `.htaccess`
  ignorado; el form cae a mailto.

## Trampas / lecciones registradas
- **CSP estricta ⇒ nada inline.** Si agregas algo, externalízalo o rompe la CSP.
- **`.htaccess`/CSP no aplican en Pages** (solo GoDaddy). Pages = staging.
- **`post_redirect`** debe usar `&` si la URL ya trae `?` (bug corregido: daba `?id=1?ok=`).
- **sitemap/canonical usan `https://www.cglegal.com.mx`** (dominio de prod). En Pages
  eso no coincide con la URL real; se alinea al migrar al dominio.
- **Sin secretos en git**: `.gitignore` excluye `.env`, `portal/lib/config.php`,
  `db/*.sqlite`. Nunca versionar credenciales (lección del `.env` de logistika).
- **Brochure**: `assets/BrochureCA.pdf` es archivo VIEJO y pesa 13MB. Cuando el cliente
  mande el nuevo: reemplazar, re-auditar y comprimir si el peso lo pide.

## Pendientes (del cliente / al migrar)
Accesos GoDaddy (SMTP/MySQL/cPanel) · dominio + HTTPS · SPF/DKIM/DMARC ·
Search Console + Bing · validación legal (aviso+términos) · URLs reales de redes
sociales (hoy `#` en el footer) · brochure nuevo · CVs y textos reales.

## Auditoría y reciclaje
`auditoria-cglegal.md` (SEO/GEO/seguridad/tests) — re-correr tras cambios grandes.
Código reutilizable de otros proyectos (misma arquitectura): **`~/Documents/logistika`**
y **`~/Documents/techStudio/techStudio`** (= **DataraHub**, el nombre comercial que
resolvió al provisional "techStudio"; el repo sigue llamándose techStudio).
