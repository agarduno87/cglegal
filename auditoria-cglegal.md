# Auditoría — Culebro Abogados (cglegal)

Fecha: sep 2026 · Repo: agarduno87/cglegal · Alcance: SEO · GEO · Seguridad · Tests/Funcionalidad.
Metodología igual a techStudio (Datara Hub) y Logistika.

### Cambios aplicados en la 2ª pasada
- Título de producción sin codename.
- JSON-LD (`LegalService` + `Service`/`BreadcrumbList`) en home y áreas; `WebPage` en legales.
- Open Graph + Twitter Card + `canonical` en **todas** las páginas.
- **Imagen OG dedicada** (`assets/og.png`, 1200×630, monograma sobre hueso).
- **hreflang** con espejo **`/en/`** (home + 6 áreas en inglés) + `x-default`.
- Sitemap ampliado (ES + EN + legales = 16 URLs).

## 1. Resumen ejecutivo
Sitio público (Fase 1) **sólido y ya bien optimizado**: CSP estricta, formulario con
defensa en profundidad, y SEO/GEO completos on-page (metadatos, datos estructurados,
bilingüe con hreflang). Lo que queda depende de **GoDaddy + dominio real** (SMTP,
HTTPS, Search Console) o del cliente (brochure nuevo, redes, validación legal).
El **portal (Fase 2)** se construye aparte, en rama `fase2`.

## 2. Tabla de hallazgos (estado actual)
| # | Área | Hallazgo | Sev. | Estado |
|---|------|----------|------|--------|
| 1 | SEO | Título con codename "Monograma" | 🔴 | ✅ resuelto |
| 2 | SEO | JSON-LD ausente | 🟠 | ✅ resuelto (home, áreas, legales) |
| 3 | SEO | OG/Twitter/canonical ausentes | 🟠 | ✅ resuelto (todas las páginas) |
| 4 | SEO | Metadatos por página (áreas/legales) | 🟠 | ✅ resuelto |
| 5 | SEO | Imagen OG dedicada | 🟡 | ✅ resuelto (`og.png`; mejorable con una diseñada) |
| 6 | SEO | hreflang / EN indexable | 🟡 | ✅ resuelto (espejo `/en/` home+áreas + x-default) |
| 7 | SEO | JSON-LD en áreas EN | 🔵 | ⏳ menor (pendiente, opcional) |
| 8 | SEO/Perf | Brochure PDF 13 MB (archivo viejo) | 🟡 | ⏳ cliente enviará nuevo → recomprimir si aplica |
| 9 | SEO | Search Console / Bing / dominio | 🟠 | ⏳ cliente (al migrar) |
| 10 | Seg. | `.htaccess`/CSP no aplican en GitHub Pages | 🟠 | ℹ️ Pages = staging; aplica en GoDaddy |
| 11 | Seg. | SMTP autenticado + SPF/DKIM/DMARC | 🟠 | ⏳ cliente (GoDaddy) |
| 12 | Seg. | HTTPS del dominio (HSTS/upgrade) | 🟠 | ⏳ cliente (GoDaddy) |
| 13 | Tests | Sin suite automatizada | 🟡 | ⏳ útil en Fase 2 |
| 14 | Fase 2 | Portal admin/roles/BD | 🔴 | ⏳ rama `fase2` |

## 3. SEO
**Cobertura verificada:** `index`, 6 áreas y 2 legales con `title`, `meta description`,
`canonical`, Open Graph (7), Twitter y JSON-LD; un `<h1>` por página; `lang`; viewport;
100% de imágenes con `alt`; fuentes/imágenes locales (buen LCP). Espejo `/en/`
(home + áreas) con `hreflang` es↔en y `x-default`. `sitemap.xml` con 16 URLs.
**Pendiente:** JSON-LD en áreas EN (menor); imagen OG diseñada (opcional); al migrar,
alinear dominio y dar de alta Search Console + Bing/IndexNow.

## 4. GEO (que las IA citen el sitio)
`llms.txt` fiel + `robots.txt` **pro-IA** (GPTBot, OAI-SearchBot, ChatGPT-User,
PerplexityBot, ClaudeBot, Google-Extended). JSON-LD `LegalService`/`Service` y páginas
por área dan contenido citable, en ES y EN.
**Siembra (long-tail):** "abogado inmobiliario Los Cabos fideicomiso zona restringida",
"due diligence legal Querétaro", "constitución de empresa extranjera México",
"comercio exterior IMMEX asesoría legal". Evitar genéricas gigantes.

## 5. Seguridad
CSP **estricta** (`default-src 'self'`), HSTS, X-Frame-Options DENY, nosniff,
Referrer-Policy, Permissions-Policy (`.htaccess`). `contact.php`: honeypot + trampa de
tiempo + rate-limit + validación server-side + CORS cerrado + logging anónimo. Sin
secretos en git; `.gitignore` correcto.
**Ojo:** en GitHub Pages el `.htaccess`/CSP no aplican (Pages = staging); protegen en
GoDaddy. **Pendiente GoDaddy:** SMTP autenticado + SPF/DKIM/DMARC; HTTPS activo;
`LEAD_TO`/`LEAD_FROM`/`ALLOWED_ORIGIN` por entorno.
**Fase 2 (portal):** `password_hash` (bcrypt), CSRF, cookies HttpOnly+Secure+SameSite +
regeneración de sesión, bloqueo por intentos, bitácora de auditoría, usuario MySQL de
mínimos privilegios, separación de documentos con secreto profesional (LFPDPPP).

## 6. Tests / Funcionalidad (verificado en vivo — todo PASA ✅)
Intro/Entrar · ES/EN (toggle y espejo `/en/`) · acordeón · modales CV (Javier/Alejandro) ·
WhatsApp · brochure · 6 áreas + páginas de área (ES/EN) · "Solicitar información" →
salta la intro y baja al formulario · formulario (fetch→`contact.php` + fallback mailto,
con estado) · legales · favicon · responsive.
**Pendiente:** suite automatizada (recomendable con el backend de Fase 2).

## 7. Wizard de ejecución
**Nuestro lado:**
1. ✅ Título, JSON-LD, OG/Twitter, canonical (todas las páginas).
2. ✅ Imagen OG dedicada (`og.png`).
3. ✅ hreflang + espejo `/en/` (home + áreas) + x-default + sitemap.
4. ⏳ (menor) JSON-LD en áreas EN.
5. ⏳ Recibir brochure nuevo → reemplazar, **re-auditar** y comprimir si el peso lo pide.
6. ⏳ (opcional) Imagen OG diseñada (con texto/marca) en vez del monograma.
7. ⏳ (opcional) Espejo EN de las páginas legales.

**Del cliente (requiere cuentas/datos):**
1. Accesos GoDaddy (SMTP/MySQL/cPanel) → migración + correo real.
2. Dominio + HTTPS activo.
3. Alta en Google Search Console y Bing (enviar `sitemap.xml`).
4. Validación de aviso de privacidad y términos (abogado).
5. URLs reales de redes sociales.
6. Brochure actualizado.

## 8. Lo que ya está bien (no tocar)
CSP y cabeceras; `contact.php`; rutas limpias; fuentes/imágenes auto-hospedadas;
`llms.txt` + `robots.txt` pro-IA; i18n ES/EN + hreflang; accesibilidad básica.

## 9. Comandos de verificación
```bash
# Cobertura SEO por página
for f in index.html areas/*.html legal/*.html en/index.html en/areas/*.html; do
  printf "%-40s og:%s canon:%s jsonld:%s hreflang:%s\n" "$f" \
  "$(grep -c 'property=\"og:' $f)" "$(grep -c 'rel=\"canonical\"' $f)" \
  "$(grep -c 'application/ld+json' $f)" "$(grep -c 'hreflang=' $f)"; done
# Cabeceras de seguridad (solo contra el dominio en GoDaddy, no Pages)
curl -sI https://www.cglegal.com.mx/ | grep -i 'content-security-policy\|strict-transport\|x-frame'
# Backend de leads (local con php -S)
curl -s -X POST http://localhost:8000/contact.php -H "Content-Type: application/json" \
  -d '{"name":"T","email":"a@b.com","message":"hola","locale":"es"}'
```
