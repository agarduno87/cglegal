# Publicar en GitHub Pages (revisión visual)

1. Crea el repo (privado o público) y sube TODO el contenido de esta carpeta a la raíz.
2. En GitHub: **Settings → Pages → Build and deployment → Source: Deploy from a branch**.
3. Branch: `main`, carpeta `/ (root)` → **Save**.
4. En 1-2 min queda en `https://<usuario>.github.io/<repo>/`.

Notas:
- Las rutas son **relativas**, así que funciona en subcarpeta sin cambios.
- `contact.php` **no** funciona en Pages (es estático). El formulario es maqueta por ahora.
- `.htaccess`/CSP **se ignoran** en Pages (GitHub pone sus propias cabeceras). La CSP
  estricta aplica en **GoDaddy**, que es producción.
- Para producción real, el destino es GoDaddy (Apache + PHP), no Pages.
