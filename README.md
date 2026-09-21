# Culebro Abogados — Sitio (Fase 1: público)

Sitio del despacho **Culebro Abogados** (CG Legal & Real Estate Consulting, S.C.).
Diseño *Monograma*. Estático + `contact.php` (leads). Preparado para **GoDaddy
(Apache + PHP)**; también revisable en **GitHub Pages** (solo lo visual).

## Estructura
```
index.html               Home (diseño Monograma)
areas/                    6 páginas por área de práctica
assets/
  styles.css  app.js      CSS y JS externos (CSP estricta, sin inline)
  fonts.css   fonts/       Fuentes auto-hospedadas (Fraunces, Inter)
  areas/*.jpg             Imágenes de áreas (placeholder, reemplazar por propias)
  icono-*.png javier.jpg alejandro.jpg BrochureCA.pdf
contact.php               Recepción de leads -> contacto@cglegal.com.mx + autorespuesta
.htaccess                 HTTPS + cabeceras de seguridad + CSP estricta (Apache/GoDaddy)
robots.txt llms.txt sitemap.xml   SEO + GEO
```

## Probar local (con PHP, para el formulario)
```
php -S localhost:8000
```
Abrir http://localhost:8000/  · probar backend:
```
curl -X POST http://localhost:8000/contact.php -H "Content-Type: application/json" \
  -d '{"name":"Prueba","email":"a@b.com","message":"Hola","locale":"es"}'
```
(Local no envía correo real; en GoDaddy sí, con su SMTP.)

## Desplegar
- **Producción → GoDaddy (cPanel)**: subir todo a `public_html/`. Corre PHP y aplica
  `.htaccess`. Configurar por entorno: `LEAD_TO`, `LEAD_FROM`, `ALLOWED_ORIGIN`.
- **Revisión visual → GitHub Pages**: ver `PUBLICAR-PAGES.md`. OJO: Pages es estático,
  `contact.php` NO corre y `.htaccess` se ignora (es solo para revisar el diseño).

## Seguridad (hardening 1.5 hecho)
- CSP **estricta** (`default-src 'self'`), sin `unsafe-inline`, sin CDNs.
- CSS/JS externos, fuentes e imágenes **auto-hospedadas**.
- `contact.php`: honeypot + trampa de tiempo + rate-limit + validación + CORS + log anónimo.
- Cabeceras: HSTS, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy.

## Pendiente
- Cablear el formulario a `contact.php` (hoy es maqueta) — item (a).
- Reemplazar imágenes placeholder por fotografía propia/licenciada.
- Texto real: misión/visión/valores, CV de Javier y Alejandro.
- Aviso de privacidad + Términos y condiciones (los redacta el abogado).
- Fase 2: portal admin (login, roles admin/cliente/abogado, CRUD, MySQL).
