# tools/ — Generador de HTML (build local)

Fuente de verdad para las **familias de páginas repetitivas** de Culebro Abogados.
Mismo patrón que Datara Hub y Logistika: se edita el **dato/plantilla**, no el HTML.

> **Python corre SOLO en local (build).** GoDaddy nunca ejecuta Python: se sube el
> HTML ya generado + `contact.php` + `.htaccess`. Es un paso de *build*, no de *runtime*.

## Uso
```bash
python3 tools/build.py          # genera áreas, hubs y sitemap.xml
python3 tools/build_static.py   # empaqueta dist/ + cglegal-public.zip para subir a GoDaddy
```
No requiere dependencias (solo la stdlib de Python 3).

## Qué genera `build.py` (desde datos)
- **6 áreas ES + 6 EN** (`areas/*.html`, `en/areas/*.html`) desde `data/areas.json`.
- **Hub de áreas** ES/EN y **hub de publicaciones** ES (reusan `data/site.json` y
  `data/pubs.json` → cero duplicación de nombres/descripciones).
- **`sitemap.xml`** (27 URLs con `hreflang`/`lastmod`/`changefreq`) desde el registro
  central en `build.py`.

## Qué NO genera (páginas de contenido, se editan a mano)
`index.html` (home), `legal/*`, los 4 artículos de `publicaciones/*.html`,
`preguntas-frecuentes.html` y `despacho.html`. Son contenido editorial; el "chrome"
compartido (botón de WhatsApp, cabeceras) está disponible en `render.py` para cuando
se quieran migrar. **Están en el registro del sitemap y en el empaquetado.**

## Estructura
```
tools/
  data/
    site.json     # marca, NAP, orden/nombres/descripciones de áreas, colores
    areas.json    # contenido único de cada área (ES/EN) — extraído del sitio
    pubs.json     # metadatos de publicaciones (para el hub y el sitemap)
  render.py       # helpers compartidos: wa(), jsonld(), write() (sin newline final)
  build.py        # plantillas + registro de páginas → genera HTML + sitemap
  build_static.py # empaqueta por whitelist a dist/ (+ audit anti-fugas) y zip
```

## Reglas
- **Editar datos/plantillas, no el HTML de salida** de las familias generadas.
- `write()` escribe **sin salto de línea final** (igual que las páginas a mano) para
  que el `diff` quede limpio.
- El JSON-LD se serializa con `json.dumps` (espaciado); es válido e idéntico en datos.
- `dist/` y `cglegal-public.zip` NO se versionan (ver `.gitignore`).

## Verificación rápida
```bash
python3 tools/build.py && git diff --stat   # ver qué cambió al regenerar
grep -c '<loc>' sitemap.xml                  # 27
```
