# Auditoría — Culebro Abogados (cglegal)

Fecha: sep 2026 · Sitio: `index.html` + `areas/` + `legal/` · Repo: agarduno87/cglegal
Alcance: SEO · GEO · Seguridad · Tests/Funcionalidad. Metodología igual a
techStudio (Datara Hub) y Logistika.

## 1. Resumen ejecutivo
El sitio público (Fase 1) está **sólido**: diseño terminado, CSP estricta,
formulario con defensa en profundidad, SEO base y archivos GEO presentes.
En esta pasada se **corrigieron** los 3 huecos SEO principales del home
(título con codename, falta de JSON-LD/Open Graph/canonical). Quedan pendientes
la réplica de metadatos por página, el tema EN-indexable, y todo lo que depende
de **GoDaddy + dominio real** (SMTP, HTTPS, Search Console). El **portal (Fase 2)**
aún no existe: su seguridad se aborda en su propia construcción.

## 2. Tabla de hallazgos
| # | Área | Hallazgo | Sev. | Estado |
|---|------|----------|------|--------|
| 1 | SEO | `<title>` traía el codename "Monograma (oxblood)" | 🔴 | ✅ CORREGIDO |
| 2 | SEO | Sin JSON-LD (datos estructurados) | 🟠 | ✅ CORREGIDO (LegalService en home) |
| 3 | SEO | Sin Open Graph / Twitter / canonical | 🟠 | ✅ CORREGIDO (home) |
| 4 | SEO | OG/canonical/JSON-LD faltan en áreas y legales | 🟠 | ⏳ pendiente (replicar) |
| 5 | SEO | Sin imagen OG dedicada (1200×630); usa el ícono | 🟡 | ⏳ pendiente |
| 6 | SEO | EN es toggle JS, sin URL propia → no indexable aparte | 🟡 | ⏳ decisión (hreflang o /en/) |
| 7 | SEO/Perf | Brochure PDF de 13 MB | 🟡 | ⏳ comprimir |
| 8 | SEO | Search Console / Bing / dominio real | 🟠 | ⏳ cliente (al migrar) |
| 9 | Seguridad | `.htaccess`/CSP NO aplican en GitHub Pages (solo GoDaddy) | 🟠 | ℹ️ Pages = staging |
| 10 | Seguridad | SMTP autenticado + SPF/DKIM/DMARC del dominio | 🟠 | ⏳ cliente (GoDaddy) |
| 11 | Seguridad | HTTPS del dominio (para HSTS/upgrade) | 🟠 | ⏳ cliente (GoDaddy) |
| 12 | Tests | No hay suite automatizada (los otros sí) | 🟡 | ⏳ opcional (útil en Fase 2) |
| 13 | Fase 2 | Portal admin/roles/BD no construido | 🔴 | ⏳ Fase 2 |

## 3. SEO
**Bien:** un `<h1>` por página, `meta description` en todas, `lang="es"`,
viewport, 100% de imágenes con `alt`, `sitemap.xml` y `robots.txt` presentes,
rutas limpias, fuentes/imágenes locales (buen LCP).
**Corregido ahora (home):** título sin codename; `canonical`; Open Graph +
Twitter; JSON-LD `LegalService` con nombre legal, oficinas (2 direcciones),
teléfonos, correo, idiomas y área servida.
**Pendiente:** replicar OG/canonical/JSON-LD (`BreadcrumbList` + `Service`) en las
6 áreas y 2 legales; imagen OG dedicada; decidir EN indexable (hreflang o `/en/`);
al migrar, alinear dominio y dar de alta Search Console + Bing/IndexNow.

## 4. GEO (que las IA citen el sitio)
**Bien:** `llms.txt` fiel al sitio; `robots.txt` **pro-IA** (GPTBot, OAI-SearchBot,
ChatGPT-User, PerplexityBot, ClaudeBot, Google-Extended permitidos).
**Refuerzo:** el JSON-LD y las páginas por área dan contenido "citable".
**Pendiente/siembra (long-tail):** "abogado inmobiliario Los Cabos fideicomiso
zona restringida", "due diligence legal Querétaro", "constitución de empresa
extranjera México", "comercio exterior IMMEX asesoría legal". Evitar genéricas
gigantes ("abogado", "corporativo").

## 5. Seguridad
**Bien:** CSP **estricta** (`default-src 'self'`, sin unsafe-inline/CDN), HSTS,
X-Frame-Options DENY, X-Content-Type-Options, Referrer-Policy, Permissions-Policy
(en `.htaccess`); `contact.php` con honeypot + trampa de tiempo + rate-limit +
validación server-side + CORS cerrado + logging anonimizado; **sin secretos en
git** y `.gitignore` correcto.
**Ojo:** en **GitHub Pages** el `.htaccess`/CSP **no aplican** (GitHub impone las
suyas). La CSP estricta protege en **GoDaddy** (producción). Pages es staging.
**Pendiente (GoDaddy):** SMTP autenticado + SPF/DKIM/DMARC del dominio (correo y
autorespuesta sin spam); certificado HTTPS activo (para HSTS/upgrade);
`LEAD_TO`/`LEAD_FROM`/`ALLOWED_ORIGIN` por entorno.
**Fase 2 (portal):** `password_hash` (bcrypt), CSRF por formulario, cookies
HttpOnly+Secure+SameSite + regeneración de sesión, bloqueo por intentos fallidos,
bitácora de auditoría (quién ve qué), usuario MySQL de privilegios mínimos, y
separación fuerte de documentos con secreto profesional (LFPDPPP).

## 6. Tests / Funcionalidad (verificado en vivo — todo PASA ✅)
Intro + Entrar · toggle ES/EN (ambos sentidos) · acordeón de áreas (clic) ·
modales CV (Javier y Alejandro) · botón WhatsApp (número de pruebas) ·
descarga de brochure · 6 enlaces a páginas de área · páginas de área ·
"Solicitar información" → salta la intro y baja al formulario ·
formulario de contacto (fetch→`contact.php` con estado "¡Gracias!" y fallback
mailto) · enlaces legales → sus páginas · favicon · responsive.
**Pendiente:** no hay suite automatizada (los otros proyectos usan pytest/jsdom);
recomendable al construir el backend de la Fase 2.

## 7. Wizard de ejecución
**Nuestro lado (ejecutable ya):**
1. ✅ Título, JSON-LD, OG/Twitter, canonical (home).
2. ⏳ Replicar metadatos por página (áreas + legales) + `BreadcrumbList`.
3. ⏳ Imagen OG 1200×630 dedicada.
4. ⏳ Comprimir el brochure (o servir una versión ligera).
5. ⏳ Decidir EN indexable (hreflang o `/en/`).

**Del cliente (requiere cuentas/datos):**
1. Accesos GoDaddy (SMTP/MySQL/cPanel) → migración + correo real.
2. Dominio apuntando + HTTPS activo.
3. Alta en Google Search Console y Bing (enviar `sitemap.xml`).
4. Validación del aviso de privacidad y términos (abogado).
5. URLs reales de redes sociales.

## 8. Lo que ya está bien (no tocar)
CSP estricta y cabeceras; `contact.php` (defensa en profundidad); estructura
de rutas; fuentes/imágenes auto-hospedadas; `llms.txt` + `robots.txt` pro-IA;
i18n ES/EN; accesibilidad básica (alt, lang, aria en botones).

## 9. Comandos de verificación
```bash
# SEO: metadatos por página
for f in index.html areas/*.html legal/*.html; do echo "== $f =="; \
  grep -o '<title>[^<]*' "$f"; grep -c 'application/ld+json\|og:\|canonical' "$f"; done
# Seguridad: cabeceras (SOLO sirve contra el dominio en GoDaddy, no Pages)
curl -sI https://www.cglegal.com.mx/ | grep -i 'content-security-policy\|strict-transport\|x-frame'
# Backend de leads (local con php -S)
curl -s -X POST http://localhost:8000/contact.php -H "Content-Type: application/json" \
  -d '{"name":"T","email":"a@b.com","message":"hola","locale":"es"}'
```
