# SEO/GEO — alta en consolas (checklist para cuando el sitio esté live)

Estas herramientas NO se pueden automatizar desde el sitio: requieren la cuenta del
cliente y el **dominio real activo** (`www.cglegal.com.mx`). Aquí está todo listo en el
código; esto es la operación de alta.

## 0. Pre-requisito
- Sitio migrado a GoDaddy en `https://www.cglegal.com.mx/` con HTTPS activo.
- `sitemap.xml`, `robots.txt`, `llms.txt` y la key de IndexNow accesibles en la raíz.

## 1. Google Search Console  (https://search.google.com/search-console)
Dos métodos (elige uno):
- **DNS TXT (recomendado, sobrevive a rediseños):** agrega el registro TXT que te dé GSC
  en la zona DNS del dominio (en GoDaddy). Verifica el dominio completo.
- **Meta tag:** descomenta en `index.html` (y `en/index.html`) la línea
  `<meta name="google-site-verification" content="PEGA-AQUI-TU-TOKEN">` con tu token.
Luego: **Sitemaps → enviar** `https://www.cglegal.com.mx/sitemap.xml`.

## 2. Bing Webmaster Tools  (https://www.bing.com/webmasters)
- Opción simple: **importar la propiedad desde Google Search Console** (un clic).
- Enviar el mismo `sitemap.xml`.
- Bing también se alimenta de **IndexNow** (ver abajo).

## 3. IndexNow  (Bing / Yandex — avisa cambios al instante)
- La key ya está publicada como archivo en la raíz:
  `9ef28b807e9569a0ae51194b41a124e2.txt` (su contenido es la propia key).
- Al publicar o actualizar páginas, hacer un ping (manual o con script) a:
  `https://api.indexnow.org/indexnow?url=<URL>&key=9ef28b807e9569a0ae51194b41a124e2`
  o POST con la lista de URLs del sitemap. (En Logistika/Datara esto se hace con un
  pequeño script tras cada deploy.)

## 4. Google Business Profile  (https://business.google.com)
- **Una ficha por oficina** (Los Cabos y Querétaro). Ver `GoogleBusiness/README.md` con
  el NAP exacto y el checklist.
- Mantener el **NAP idéntico** al del sitio (ya está en el JSON-LD de la home y en
  `llms.txt`). Al tener los CID de Maps, agregarlos a `sameAs` del JSON-LD de la home.

## 5. Analítica (decisión del cliente)
- Hoy el sitio NO tiene analytics/pixel (CSP estricta `connect-src 'self'`, consola limpia,
  privacidad). Si se quiere medir, opciones: **Plausible** (privacy-first) o **GA4**.
  Cualquiera requiere ajustar la CSP (`connect-src`/`script-src`) y declararlo en el aviso
  de privacidad. Recomendado: Plausible por el menor impacto en CSP y privacidad.

## 6. Redes
- Instagram ya vinculado (`@abogados_cglegal`) en footer, `llms.txt` y `sameAs`.
- Si abren LinkedIn/Facebook, agregarlos a `sameAs` del JSON-LD y al footer.

## Verificación rápida (tras migrar)
```bash
curl -sI https://www.cglegal.com.mx/ | grep -i 'content-security-policy\|strict-transport'
curl -s https://www.cglegal.com.mx/robots.txt | head
curl -s https://www.cglegal.com.mx/sitemap.xml | grep -c '<loc>'
curl -s https://www.cglegal.com.mx/9ef28b807e9569a0ae51194b41a124e2.txt
```
