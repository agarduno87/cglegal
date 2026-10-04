# Contexto del proyecto — Culebro Abogados

> Documento de contexto integral, al **30 de septiembre de 2026**.
> Sitio público + portal privado del despacho **Culebro Abogados**
> (razón social **CG Legal & Real Estate Consulting, S.C.** — "Real **Estate**", no "Real State").
> Es un **despacho legal con consultoría**, no al revés. Proyecto **para cliente, pagado**.

---

## 1. Resumen ejecutivo

Culebro Abogados es un despacho corporativo e inmobiliario con presencia en **Los Cabos y
Querétaro**. El proyecto entrega:

1. **Fase 1 — Sitio público** (terminado y en staging): home + 6 áreas de práctica +
   4 publicaciones + 2 páginas legales, bilingüe ES/EN, con SEO/GEO completo y seguridad
   endurecida. Es lo que vive en la rama `main`.
2. **Fase 2 — Portal privado** (construido y probado localmente, en rama `fase2`): login
   con roles (admin / abogado / cliente), gestión de usuarios y de asuntos, PHP + MySQL.

**Estado actual:** el sitio público está listo para revisión de contenido por el cliente
(se le entregó un PDF machote). Falta migrar a producción en GoDaddy (requiere accesos del
cliente) y completar los pendientes de Fase 2 (subida de documentos, bloqueo por intentos
de login).

- **Repo:** github.com/agarduno87/cglegal
- **Staging visual:** https://agarduno87.github.io/cglegal/ (GitHub Pages — solo visual,
  sin PHP ni `.htaccess`)
- **Producción futura:** GoDaddy (cPanel, PHP + MySQL) sobre el dominio real
  `www.cglegal.com.mx`
- **Ruta local:** `/Users/antoniogarduno/Documents/cglegal/culebroabogados`

---

## 2. Decisión de diseño

Tras un batch de 10 maquetas vanguardistas, el despacho eligió **"Monograma"**:

| Elemento | Valor |
|---|---|
| Acento (oxblood) | `#7C2E3B` |
| Fondo (hueso) | `#F2F0EA` |
| Tinta | `#14181A` |
| Serif (títulos) | **Fraunces** (auto-hospedada) |
| Sans (texto) | **Inter** (auto-hospedada) |
| Portada | Tipo Creel: intro con ícono + "Culebro" + botón Entrar |

Referencias que gustaron al cliente: Creel, Galicia, swlaw, btlaw, stblaw, perezllorca,
basham. Las maquetas históricas del batch viven en `../redesign` y `../design/rediseno`
(no son el sitio real).

**Logo:** solo ícono + palabra "Culebro" (NO "Abogados y Consultores"). Usar los PNG
entregados, nunca redibujar.

---

## 3. Hosting y arquitectura (DECIDIDO)

- **GoDaddy, cPanel, Linux — PHP + MySQL. NO corre Python.**
- El correo `@cglegal.com.mx` vive en el mismo GoDaddy (no tocar).
- **GitHub Pages = staging visual únicamente**: no ejecuta PHP, no aplica `.htaccess`/CSP
  (GitHub pone las suyas). El formulario de contacto cae a `mailto:` en Pages.
- Producción real = subir a `public_html/` de GoDaddy.

---

## 4. Ramas de git

- **`main` = producción** (sitio público). Es lo que se despliega. **No romper.**
- **`fase2` = portal** (`/portal/`, PHP + MySQL). Se fusiona a `main` cuando esté probado.
- **Regla de trabajo:** los cambios del sitio público se hacen en `main` y se **mergean a
  `fase2`** después de cada cambio, para que `fase2` no se atrase.

---

## 5. Estructura del repositorio

```
index.html                 Home (Monograma) — home de producción
areas/                     6 páginas de área de práctica
publicaciones/             4 artículos (contenido de EJEMPLO, pendiente de redacción)
legal/                     aviso-privacidad.html + terminos.html (BORRADORES, validar abogado)
en/                        Espejo en inglés (index + 6 áreas) para hreflang
assets/
  styles.css app.js areas.css fonts.css
  fonts/                   Fraunces (300/400/500/600) + Inter (400/500/600) .woff2
  areas/*.jpg              6 imágenes de área (placeholders temáticos)
  icono-color/blanco/negro.png
  javier.jpg alejandro.jpg
  og.png                   Imagen Open Graph 1200×630
  Brochure CA Esp.pdf      Brochure español (2.8 MB)
  Brochure CA Eng.pdf      Brochure inglés (2.8 MB)
  Brochure Gobierno 2024.pdf   (en disco, SIN enlazar aún — pendiente de indicaciones)
contact.php                Recepción de leads → contacto@cglegal.com.mx + autorespuesta ES/EN
.htaccess                  HTTPS + cabeceras de seguridad + CSP ESTRICTA (Apache/GoDaddy)
robots.txt llms.txt sitemap.xml   SEO + GEO
db/                        dev.sqlite (local, IGNORADO por git) + schema.mysql.sql (fase2)
portal/                    Fase 2 — portal privado (solo en rama fase2)
CLAUDE.md                  Memoria durable para el asistente
CONTEXTO-PROYECTO.md       Este documento
auditoria-cglegal.md       Auditoría SEO/GEO/seguridad/tests
CONTENIDO.md               Volcado de contenido
README.md PUBLICAR-PAGES.md
```

---

## 6. Reglas y convenciones (IMPORTANTES)

- **CERO estilos/JS inline** y **CSP estricta** (`default-src 'self'`, sin `'unsafe-inline'`,
  sin CDNs). CSS → `assets/*.css`; JS → `assets/app.js`. Fuentes e imágenes
  **auto-hospedadas**. (El JSON-LD inline sí se permite: es bloque de datos.)
- **Nada de CSP en `<meta>`** — va en `.htaccess` (cabecera).
- **i18n ES/EN:** elementos con `data-t="clave"`; diccionario `DICT.en` en `app.js`;
  `setLang()` intercambia. El espejo `/en/` declara `lang="en"` y `app.js` auto-aplica
  inglés. `hreflang` es/en/x-default en home y áreas.
- **Al agregar algo:** externalízalo (CSS/JS) o romperá la CSP.

---

## 7. Contenido (hechos confirmados)

- **6 áreas de práctica:** Corporativo general · Inmobiliario y hotelero · Auditoría legal
  (due diligence) · Comercio Exterior · Gobierno e infraestructura · Actividades filantrópicas.
- **Equipo en el sitio = solo 2 fundadores:** **Javier Culebro** y **Alejandro Culebro**
  (ambos "Fundador", ambos con modal "Ver trayectoria"). Alejandro usa provisionalmente la
  reseña de Javier hasta tener su CV. El equipo ampliado se retiró de esta versión.
- **Leads:** llegan por **correo a `contacto@cglegal.com.mx`** (+ autorespuesta). **No hay
  UI de leads** en el portal (por decisión del cliente).
- **Encabezados actuales:** equipo → "El despacho, en sus albores." (sí es "albores",
  confirmado); publicaciones → "Compromisos."
- **Misión/Visión:** textos placeholder; se les quitó la coletilla "(Texto oficial — por
  confirmar.)".
- **Valores:** Confianza · Honestidad · Compromiso · Excelencia · Trabajo en equipo.
- **Publicaciones:** 4 tarjetas (incluye "Gobierno e infraestructura"), cada una enlaza a su
  propia página. Contenido de EJEMPLO (ya sin leyenda de aviso), pendiente de redacción real.
- **Legales:** borradores; **validar con abogado** (conservan su leyenda de aviso).
- **Footer:** "© 2026 Culebro Abogados · by Datara Hub" (link a techStudio, provisional).

### Redes sociales
- **Instagram únicamente:** https://www.instagram.com/abogados_cglegal/ (usuario
  `abogados_cglegal`). Se usó la URL limpia, sin el token de rastreo `?stkn=…`.
- Se retiraron los íconos de LinkedIn y Facebook del footer.

### WhatsApp
- Botón flotante en todas las páginas. **Número de PRUEBAS `9612155515`**
  (`wa.me/529612155515`). Pendiente el número real del despacho (al cambiarlo, se reemplaza
  en los 21 archivos de una pasada). El auto-reply real lo da WhatsApp Business, no el sitio.

### Brochures (por idioma)
- Botón "Descargar brochure" en "La Firma": sirve **`Brochure CA Esp.pdf`** en español y
  **`Brochure CA Eng.pdf`** en inglés. Funciona tanto en el espejo `/en/` como con el toggle
  **EN** dentro de la página en español (`setLang` intercambia el `href`).
- Se eliminó el brochure viejo de 13 MB (`BrochureCA.pdf`).

---

## 8. Formulario de contacto (`contact.php`)

`app.js` envía por `fetch` a `contact.php`; si falla (p. ej. en Pages sin PHP) hace
**fallback a `mailto:`**. Defensa en profundidad:

- **Honeypot** (campo `website`) + **trampa de tiempo** (`rendered_at`)
- **Rate-limit** por archivo con `flock`
- **Validación server-side** (equivalente PHP de lo que se pensó con Pydantic)
- **CORS cerrado** (solo el origen permitido)
- **Logging anonimizado** (sin PII completa)
- **Autorespuesta** ES/EN al remitente

Configuración por entorno (SetEnv en cPanel): `LEAD_TO` (→ `contacto@cglegal.com.mx`),
`LEAD_FROM` (→ `no-reply@cglegal.com.mx`), `ALLOWED_ORIGIN`, `RATE_LIMIT_*`.

---

## 9. Seguridad

### Sitio público
- **CSP estricta**, HSTS, X-Frame-Options DENY, X-Content-Type-Options nosniff,
  Referrer-Policy, Permissions-Policy, Cross-Origin-Opener-Policy (todo en `.htaccess`).
- `.htaccess` bloquea archivos sensibles (`.md`, `.sql`, `.sample.php`, `.log`, `.json`,
  `config*`, `.env`) y desactiva el listado de directorios.
- Fuentes e imágenes auto-hospedadas → cero peticiones externas.

### Auditoría de secretos (30-sep-2026) — resultado
- **Sin** API keys, tokens, llaves privadas ni contraseñas hardcodeadas.
- **Hallazgo corregido:** `db/dev.sqlite` (usuarios demo + hashes bcrypt de contraseñas
  conocidas, 0 datos reales) estaba versionado y era descargable en Pages. Se **sacó del
  repo** (main y fase2), se **gitignoró** `db/*.sqlite|*.sqlite3|*.db` y se conservó la copia
  local. **Nota:** sigue en el historial de git; como eran credenciales demo el riesgo es
  bajo, pero **nunca** usar esas contraseñas demo en producción. Opcional: purgar historial
  con `git filter-repo`/BFG.
- Pendiente de confirmar: si el repo `agarduno87/cglegal` es público. Si lo es, al migrar el
  portal a GoDaddy conviene que el portal no quede en un repo público.

### Portal (Fase 2)
`password_hash`/`verify` (bcrypt), **CSRF**, cookies HttpOnly + Secure + SameSite +
`session_regenerate_id`, `require_role` → 403, **bitácora** `audit_log`, prepared statements
(PDO), escape con `h()`. Pendiente: bloqueo por intentos de login y endurecimiento de la
subida de documentos (LFPDPPP + secreto profesional).

---

## 10. SEO / GEO

> **Actualización oct 2026 — paridad con Datara Hub y Logistika:** se sumaron hub de áreas
> `/areas/` y de publicaciones `/publicaciones/`, FAQ (`/preguntas-frecuentes.html`) con
> `FAQPage`, página "El despacho" (`/despacho.html`, `AboutPage`+`Person`), JSON-LD en
> `@graph` (nodo `#despacho` + `WebSite` + `FAQPage`), breadcrumbs, `site.webmanifest` +
> favicons, `_headers`, IndexNow, placeholder de Search Console, `robots.txt` pro-IA
> ampliado, `llms.txt` enriquecido, carpeta `GoogleBusiness/`, sitemap a 27 URLs con
> hreflang, y los docs `plan-contenido-cglegal.md` + `SEO-GEO-SETUP.md`. Espejos EN para
> áreas-hub, FAQ y despacho. Alta real en consolas: al migrar al dominio.


- Open Graph + Twitter Card + `canonical` en todas las páginas; imagen OG dedicada
  (`og.png`).
- JSON-LD: `LegalService` + `Service`/`BreadcrumbList` en home y áreas; `Article` en
  publicaciones; `WebPage` en legales.
- `hreflang` es/en/x-default con espejo `/en/` (home + 6 áreas). `sitemap.xml` con ~20 URLs.
- `robots.txt` **pro-IA** (GPTBot, OAI-SearchBot, ChatGPT-User, PerplexityBot, ClaudeBot,
  Google-Extended) + `llms.txt` fiel para que las IA citen el sitio.
- **Siembra long-tail sugerida:** "abogado inmobiliario Los Cabos fideicomiso zona
  restringida", "due diligence legal Querétaro", "constitución de empresa extranjera México",
  "comercio exterior IMMEX asesoría legal".
- Pendiente (al migrar): alinear dominio real, alta en Google Search Console + Bing/IndexNow.

---

## 11. Fase 2 — Portal privado (rama `fase2`, en `/portal/`)

Login con roles **admin / abogado / cliente**.

- **`db.php`** (PDO): **SQLite en dev** (auto-DDL, cero config) / **MySQL en prod**
  (`db/schema.mysql.sql`). **`models.php`** = capa de datos.
- **Usuarios (admin):** crear/editar rol + activo + nombre, reset de contraseña, eliminar.
  Guardas: no auto-borrado, no quitar al último admin, no auto-degradarse.
- **Asuntos:** admin crea/edita/asigna cliente + abogado/estado/elimina + notas; abogado ve
  solo los asignados (guard por-asunto = 403), cambia estado y agrega notas; cliente ve sus
  asuntos + seguimiento (solo lectura).

### Correr el portal en local
```bash
cd portal && php dev-seed.php      # siembra usuarios/datos demo
cd .. && php -S localhost:8000     # servidor
# abrir http://localhost:8000/portal/login.php
```
Usuarios de prueba: `admin@` / `abogado@` / `cliente@cglegal.com.mx` (contraseña = rol+123,
p. ej. `admin123`). **Solo para desarrollo local.**

---

## 12. Cómo correr y desplegar

- **Local con PHP (prueba el form):** `php -S localhost:8000` en la raíz → abrir `/`.
- **Staging visual:** GitHub Pages (ver `PUBLICAR-PAGES.md`) — sin PHP; el form cae a mailto.
- **Producción:** subir a `public_html/` de GoDaddy (corre PHP + `.htaccess`).

---

## 13. Entregables generados para el cliente

- **PDF machote de contenido** (`machote-contenido-culebro.pdf`): todo el contenido público
  (home + subpáginas; el portal NO entra) para la ronda de adecuación de contenido. Ya sin la
  coletilla "(Texto oficial — por confirmar.)".
- **PDF consolidado** (`cglegal-contenido-completo.pdf`): unión de los 13 archivos del cliente
  (cg1…cg13) en orden ascendente, 26 páginas.

---

## 14. Pendientes

### De nuestro lado
- Reemplazar el **número de WhatsApp** por el real cuando lo entreguen (21 archivos).
- Definir dónde va **`Brochure Gobierno 2024.pdf`** (esperando indicaciones del cliente).
- Fase 2: bloqueo por intentos de login; subida de documentos endurecida (lo más sensible).
- (Opcional) JSON-LD en áreas EN; imagen OG diseñada; espejo EN de legales.
- (Opcional) Purgar `db/dev.sqlite` del historial de git.

### Del cliente / al migrar
- Accesos GoDaddy (SMTP / MySQL / cPanel) → migración + correo real.
- Dominio + HTTPS activo; SPF/DKIM/DMARC.
- Alta en Google Search Console y Bing (enviar `sitemap.xml`).
- **Validación legal** del aviso de privacidad y los términos (abogado).
- Contenido real de **publicaciones**, **CVs** reales (Javier y Alejandro), misión/visión/
  valores definitivos.
- Confirmar visibilidad del repo (público/privado).

---

## 15. Reciclaje / proyectos hermanos

Código reutilizable (misma arquitectura): **`~/Documents/logistika`** y
**`~/Documents/techStudio/techStudio`** (= **Datara Hub**, nombre comercial; el repo sigue
llamándose techStudio).

---

*Mantener este documento y `CLAUDE.md` al día tras cambios grandes. Re-correr
`auditoria-cglegal.md` cuando aplique.*
