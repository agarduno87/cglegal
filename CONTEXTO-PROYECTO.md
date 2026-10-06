# Contexto del proyecto — Culebro Abogados

> Documento de contexto integral, al **6 de octubre de 2026**.
> Sitio público + portal privado del despacho **Culebro Abogados**
> (razón social **CG Legal & Real Estate Consulting, S.C.** — "Real **Estate**", no "Real State").
> Es un **despacho legal con consultoría**, no al revés. Proyecto **para cliente, pagado**.

---

## 1. Resumen ejecutivo

Culebro Abogados es un despacho corporativo e inmobiliario con presencia en **Los Cabos
(B.C.S.) y Querétaro**. El proyecto tiene dos fases:

1. **Fase 1 — Sitio público** (terminado, en staging): home + 6 áreas + hub de áreas +
   4 publicaciones + hub de publicaciones + FAQ + "El despacho" + 2 legales, bilingüe
   ES/EN, con SEO/GEO completo y seguridad endurecida. Vive en la rama **`main`**.
2. **Fase 2 — Portal privado** (construido y probado): login con roles
   (admin / abogado / cliente), **expediente de trabajo completo** por asunto e
   **intercambio de documentos cliente↔abogado**. Vive en la rama **`fase2`**.

**Estado actual:** listo para revisión. Falta migrar a producción en GoDaddy (requiere
accesos del cliente) y, en el portal, el **bloqueo por intentos de login** (único pendiente
técnico de Fase 2).

- **Repo:** github.com/agarduno87/cglegal
- **Staging visual:** https://agarduno87.github.io/cglegal/ (GitHub Pages — solo visual,
  sin PHP ni `.htaccess`/CSP)
- **Producción futura:** GoDaddy (cPanel, PHP + MySQL) sobre `www.cglegal.com.mx`
- **Ruta local:** `/Users/antoniogarduno/Documents/cglegal/culebroabogados`

---

## 2. Decisión de diseño

Ganador elegido por el despacho: **"Monograma"**.

| Elemento | Valor |
|---|---|
| Acento (oxblood) | `#7C2E3B` |
| Fondo (hueso) | `#F2F0EA` |
| Tinta | `#14181A` |
| Serif (títulos) | **Fraunces** (auto-hospedada) |
| Sans (texto) | **Inter** (auto-hospedada) |
| Portada | Tipo Creel: intro con ícono + "Culebro" + Entrar |

**Logo:** solo ícono + palabra "Culebro" (NO "Abogados y Consultores"). Usar los PNG
entregados, nunca redibujar. Las maquetas históricas viven en `../redesign` y
`../design/rediseno` (no son el sitio real).

---

## 3. Hosting y arquitectura (DECIDIDO)

- **GoDaddy, cPanel, Linux — PHP + MySQL. NO corre Python en el host.**
- El correo `@cglegal.com.mx` vive en el mismo GoDaddy (no tocar).
- **GitHub Pages = staging visual únicamente**: no ejecuta PHP, no aplica `.htaccess`/CSP.
  El formulario de contacto cae a `mailto:` en Pages.
- Producción real = subir a `public_html/` de GoDaddy.

---

## 4. Ramas de git

- **`main` = producción** (sitio público). Es lo que se despliega. **No romper.**
- **`fase2` = portal** (`/portal/`, PHP + MySQL) + todo lo de `main`.
- **Regla:** los cambios del sitio público se hacen en `main` y se **mergean a `fase2`**
  para que no se atrase. El portal vive solo en `fase2`.

---

## 5. Estructura del repositorio

```
index.html                 Home (Monograma) — home de producción
despacho.html              "El despacho" (About, AboutPage+Person) + espejo en/
preguntas-frecuentes.html  FAQ (FAQPage) + espejo en/
areas/                     index.html (hub) + 6 páginas de área de práctica
publicaciones/             index.html (hub) + 4 artículos (EJEMPLO, pendiente de redacción)
legal/                     aviso-privacidad.html + terminos.html (BORRADORES, validar abogado)
en/                        Espejo en inglés (index, areas+hub, despacho, FAQ) para hreflang
assets/
  styles.css app.js areas.css fonts.css
  fonts/                   Fraunces (300/400/500/600) + Inter (400/500/600) .woff2
  areas/*.jpg              6 imágenes de área
  icono-*.png, favicon.svg, apple-touch-icon.png, icon-192/512.png
  javier.jpg alejandro.jpg, og.png (1200×630)
  Brochure CA Esp.pdf / Brochure CA Eng.pdf   (brochure por idioma)
  Brochure Gobierno 2024.pdf   (en disco, SIN enlazar — pendiente de indicaciones)
contact.php                Leads → contacto@cglegal.com.mx + autorespuesta ES/EN
.htaccess / _headers       Seguridad + CSP estricta (Apache / hosting estático)
robots.txt llms.txt sitemap.xml   SEO + GEO (sitemap: 27 URLs)
site.webmanifest           PWA manifest
<hex>.txt                  Key de IndexNow
GoogleBusiness/            NAP + checklist para Google Business Profile
tools/                     Generador de HTML en Python (build local) — ver §11
plan-contenido-cglegal.md  SEO-GEO-SETUP.md   Plan de contenido + alta en consolas
portal/  db/               Fase 2 — portal (solo en rama fase2) — ver §12
CLAUDE.md / CONTEXTO-PROYECTO.md / auditoria-cglegal.md
```

---

## 6. Reglas y convenciones (IMPORTANTES)

- **CERO estilos/JS inline** y **CSP estricta** (`default-src 'self'`, sin `'unsafe-inline'`,
  sin CDNs). CSS → `assets/*.css`; JS → `assets/*.js`. Fuentes e imágenes auto-hospedadas.
  (JSON-LD inline sí: es bloque de datos.)
- **Nada de CSP en `<meta>`** — va en `.htaccess`/`_headers` (cabecera).
- **i18n ES/EN:** `data-t="clave"` + `DICT.en` en `app.js`; `setLang()` intercambia; espejo
  `/en/` con `lang="en"`. hreflang es/en/x-default.
- **Al agregar algo:** externalízalo o rompe la CSP.

---

## 7. Contenido (hechos confirmados)

- **6 áreas:** Corporativo general · Inmobiliario y hotelero · Auditoría legal (due
  diligence) · Comercio Exterior · Gobierno e infraestructura · Actividades filantrópicas.
- **Equipo en el sitio = solo 2 fundadores:** **Javier Culebro** y **Alejandro Culebro**
  (ambos "Fundador"). Javier tiene modal "Ver trayectoria"; **el de Alejandro está oculto**
  hasta tener su información real (su tarjeta hoy duplicaría la de Javier).
- **Leads:** llegan por **correo a `contacto@cglegal.com.mx`** (+ autorespuesta). No hay UI
  de leads en el portal (decisión del cliente).
- **Sin placeholders visibles:** se quitaron todas las leyendas tipo "ilustrativo /
  pendiente de validación / por confirmar" del sitio.
- **Encabezados:** equipo "El despacho, en sus albores." (sí es "albores"); publicaciones
  "Compromisos.".
- **Redes:** solo **Instagram** (`@abogados_cglegal`) en footer y `sameAs`.
- **WhatsApp:** botón flotante, **número de PRUEBAS `9612155515`** (`wa.me/529612155515`),
  repetido en 21 archivos. Pendiente el número real.
- **Brochure por idioma:** botón sirve `Brochure CA Esp.pdf` en ES y `Brochure CA Eng.pdf`
  en EN (también cambia con el toggle de idioma). Se eliminó el brochure viejo de 13 MB.
- **Footer:** "© 2026 Culebro Abogados · by Datara Hub" (link a techStudio con opacidad .25).
- **Publicaciones y legales:** contenido de EJEMPLO/BORRADOR; pendiente redacción y
  validación legal del despacho.

---

## 8. Formulario de contacto (`contact.php`)

`app.js` envía por `fetch` a `contact.php`; si falla (p. ej. en Pages) hace fallback a
`mailto:`. Defensa en profundidad: honeypot + trampa de tiempo (`rendered_at`) + rate-limit
(archivo + flock) + validación server-side + CORS cerrado + logging anonimizado +
autorespuesta ES/EN. Config por entorno (SetEnv): `LEAD_TO`, `LEAD_FROM`, `ALLOWED_ORIGIN`.

---

## 9. SEO / GEO (paridad con Datara Hub y Logistika)

- **On-page:** title, description, canonical, Open Graph, Twitter, `color-scheme`,
  `theme-color`, `robots max-image-preview:large` en todas las páginas; favicon set +
  `site.webmanifest`.
- **Datos estructurados:** home en `@graph` (nodo de negocio `#despacho` + `WebSite` +
  `FAQPage`); áreas con `Service`+`BreadcrumbList`; hubs con `CollectionPage`/`ItemList`;
  FAQ con `FAQPage`; despacho con `AboutPage`+`Person`; breadcrumbs en subpáginas.
- **i18n/hreflang:** espejo `/en/` (home, áreas+hub, FAQ, despacho). `sitemap.xml` con
  **27 URLs** (lastmod/changefreq/priority + alternates hreflang).
- **GEO:** `robots.txt` pro-IA (permite GPTBot, OAI-SearchBot, ChatGPT-User, PerplexityBot,
  ClaudeBot, Claude-Web, Google-Extended; bloquea CCBot/Bytespider/Applebot-Extended/
  Amazonbot) + `llms.txt` enriquecido + key de **IndexNow**.
- **Consolas (pendiente, requieren dominio real + cuenta del cliente):** Google Search
  Console (placeholder meta ya puesto + método DNS TXT), Bing Webmaster, Google Business
  Profile (ver `GoogleBusiness/README.md` y `SEO-GEO-SETUP.md`). Sin analítica aún (opción
  Plausible/GA4, requiere ajustar CSP).
- Plan de crecimiento en `plan-contenido-cglegal.md` (keywords long-tail + backlog de
  artículos).

---

## 10. (reservado)

---

## 11. Generador de HTML — `tools/` (build local)

Las **familias repetitivas** se generan con Python (como los proyectos hermanos):
`python3 tools/build.py` → 6 áreas ES + 6 EN + hubs (áreas ES/EN, publicaciones ES) +
`sitemap.xml`, desde `tools/data/*.json`. `python3 tools/build_static.py` empaqueta
`dist/` + `cglegal-public.zip` por whitelist (con audit anti-fugas) para subir a GoDaddy.

- **Python SOLO en local (build), NUNCA en GoDaddy** (runtime = PHP). Es un paso de build.
- **Editar datos/plantillas en `tools/`, no el HTML generado** de áreas/hubs.
- NO se generan (contenido editorial, a mano): home, legales, artículos, FAQ, despacho.
- `dist/`, `cglegal-public.zip`, `__pycache__/` están en `.gitignore`. Ver `tools/README.md`.

---

## 12. Fase 2 — Portal privado (rama `fase2`, en `/portal/`)

Login con roles **admin / abogado / cliente**. `db.php` (PDO): **SQLite en dev** (auto-DDL
+ auto-migración de columnas) / **MySQL en prod** (`db/schema.mysql.sql`). `models.php` =
capa de datos; `matter.php` = expediente compartido (admin+abogado); `layout.php` = shell.

**Gestión base**
- **Usuarios (admin):** crear/editar rol+activo+nombre, reset pass, eliminar. Guardas: no
  auto-borrado, no quitar último admin, no auto-degradarse.
- **Asuntos:** admin crea/edita/asigna cliente+abogado/estado/elimina; abogado ve solo los
  asignados (guard por-asunto = 403); cliente ve los suyos (lectura).

**Expediente de trabajo (Fase 2.1 — A/B/C/D), en `portal/lib/matter.php`**
- **A. Llevar el asunto:** plazos/vencimientos (con alertas de vencido), tareas
  (responsable+fecha), partes/contactos, meta del caso (no. expediente, autoridad/foro,
  cuantía+moneda, riesgo).
- **B. Investigación/DD:** checklist con plantillas por tipo, requerimientos al cliente
  (request list), hallazgos con severidad + recomendación.
- **C. Negocio:** tiempo y honorarios (horas/tarifa/facturable/totales) + KPIs en los
  dashboards (asuntos por estado/área/abogado, plazos próximos/vencidos, honorarios, leads).
- **D. Cumplimiento/ético:** conflicto de interés (registro + verificador + aviso al crear),
  bitácora por asunto, acceso por asunto (403).

**Intercambio de documentos cliente↔abogado**
- Subida endurecida (whitelist ext + verificación `finfo`, 20 MB, nombre aleatorio),
  almacenada **fuera de la raíz** en `db/uploads/` (`Require all denied`), servida solo por
  `portal/download.php` con control de acceso. `db/uploads/` gitignorado.
- **Abogado/admin → cliente:** casilla "Compartir con el cliente" (`visible_to_client`) +
  compartir/ocultar. Solo lo compartido llega al cliente.
- **Cliente → abogado:** el cliente sube a SUS asuntos (verificado por propiedad), contra
  un requerimiento (lo marca "recibido") o en general; genera **nota automática** de aviso
  y aparece en el panel del abogado ("Documentos recientes del cliente"). El cliente puede
  **borrar** lo que él subió. Helper reutilizable `doc_store()`.
- **Acceso:** el cliente solo descarga lo compartido o lo que él subió; el trabajo interno
  del despacho nunca es visible para el cliente.

**Diseño del portal**
- Carga fuentes self-hosted (Fraunces/Inter); inputs/selects/botones estilizados (focus
  ring oxblood, `::file-selector-button`, `portal.js` para "quitar" selección de archivo),
  tarjetas con sombra. Respeta la paleta del sitio.

**Seguridad del portal**
- Sesión HttpOnly+SameSite+Secure, `session_regenerate_id`, **CSRF** por formulario,
  `password_hash`/`verify`, control por rol y **por asunto** (403), prepared statements,
  **bitácora** `audit_log`, subida de documentos endurecida.

**Correr el portal en local**
```bash
php portal/dev-seed.php          # 1ª vez: siembra usuarios + asunto demo
bash portal/serve.sh             # elige puerto libre, fija 127.0.0.1, imprime la URL
```
Usuarios de prueba: `admin@` / `abogado@` / `cliente@cglegal.com.mx` (contraseña = rol+123).
**Ojo:** usa un solo host (`127.0.0.1`, no `localhost`) o la sesión/cookie no viaja.
**Pendiente Fase 2:** bloqueo por intentos de login; migración/staging en GoDaddy (MySQL;
`db/schema.mysql.sql` ya trae todas las tablas).

---

## 13. Seguridad (resumen)

- **Sitio:** CSP estricta + HSTS + nosniff + Referrer-Policy + Permissions-Policy +
  X-Frame-Options + COOP (`.htaccess` y `_headers`). Fuentes/imágenes auto-hospedadas.
- **Auditoría de secretos (sep 2026):** sin API keys/tokens/llaves. Se **sacó `db/dev.sqlite`**
  del repo (quedaba descargable en Pages) y se gitignoró `db/*.sqlite` + `db/uploads/`.
  Nota: nunca usar las contraseñas demo del portal en producción.
- Confirmar si el repo `agarduno87/cglegal` es público; si lo es, el portal debería no
  servirse desde un repo público al migrar.

---

## 14. Cómo correr y desplegar

- **Sitio local (con PHP, prueba el form):** `php -S localhost:8000` en la raíz → `/`.
- **Portal local:** ver §12 (`serve.sh`).
- **Build de familias + paquete:** `python3 tools/build.py && python3 tools/build_static.py`.
- **Staging visual:** GitHub Pages (sin PHP; form cae a mailto).
- **Producción:** subir a `public_html/` de GoDaddy (PHP + `.htaccess`).

---

## 15. Entregables al cliente

- **PDF machote de contenido** (`machote-contenido-culebro.pdf`): todo el contenido público
  para la ronda de adecuación (el portal no entra).
- **PDF consolidado** (`cglegal-contenido-completo.pdf`): unión de los 13 archivos del
  cliente (cg1…cg13), 26 páginas.

---

## 16. Pendientes

### De nuestro lado
- Portal: **bloqueo por intentos de login**.
- Reemplazar el **número de WhatsApp** por el real (21 archivos).
- Definir dónde va **`Brochure Gobierno 2024.pdf`** (esperando indicaciones).
- (Opcional) migrar FAQ/despacho al generador; JSON-LD en áreas EN; OG diseñada.

### Del cliente / al migrar
- Accesos GoDaddy (SMTP/MySQL/cPanel) → migración + correo real; dominio + HTTPS;
  SPF/DKIM/DMARC.
- Alta en Google Search Console, Bing y Google Business Profile (ver `SEO-GEO-SETUP.md`).
- **Validación legal** (aviso de privacidad + términos) y **redacción real** (publicaciones,
  CVs de los fundadores, misión/visión/valores definitivos).
- Confirmar visibilidad del repo (público/privado).

---

## 17. Reciclaje / proyectos hermanos

Misma arquitectura y metodología SEO/GEO:
- **`~/Documents/logistika`** (Logistika — comercio exterior, Neubox).
- **`~/Documents/techStudio/techStudio`** (**Datara Hub** — el repo sigue llamándose
  techStudio).
- **`~/Documents/lista_n`** (referencia de diseño de portal — Next.js).

---

*Mantener este documento, `CLAUDE.md` y `auditoria-cglegal.md` al día tras cambios grandes.*
