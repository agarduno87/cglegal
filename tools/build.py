#!/usr/bin/env python3
"""Generador de HTML — Culebro Abogados.

Fuente de verdad para las familias de páginas repetitivas. Edita los datos en
tools/data/*.json y las plantillas aquí; luego corre:  python3 tools/build.py

Produce (sobre el árbol del repo): las 6 áreas ES + 6 EN, los hubs (áreas ES/EN,
publicaciones ES), FAQ (ES/EN), "El despacho" (ES/EN) y sitemap.xml.
NO toca: home, legales, artículos de publicaciones, assets, contact.php, .htaccess
(son contenido/archivos autor-mantenidos; se listan para sitemap y empaquetado).

Python corre SOLO en local (build). GoDaddy nunca ejecuta Python: se sube el HTML
generado + contact.php + .htaccess (ver tools/build_static.py).
"""
import os
from render import SITE, BASE, wa, jsonld, write, load

AREAS = load("areas.json")
ORDER = SITE["areas_order"]
NAME = SITE["areas_name"]
SHORT = SITE["areas_short"]
FOOT = SITE["foot_copy"]

# ---------------------------------------------------------------- ÁREAS (ES/EN)
def area_jsonld_es(slug, d):
    url = f"{BASE}/areas/{slug}.html"
    return jsonld({"@context":"https://schema.org","@graph":[
        {"@type":"Service","name":d["h1"],"description":d["ogdesc"],"serviceType":d["h1"],
         "areaServed":SITE["area_served"],"url":url,
         "provider":{"@type":"LegalService","name":"Culebro Abogados","url":f"{BASE}/"}},
        {"@type":"BreadcrumbList","itemListElement":[
            {"@type":"ListItem","position":1,"name":"Inicio","item":f"{BASE}/"},
            {"@type":"ListItem","position":2,"name":"Áreas","item":f"{BASE}/#areas"},
            {"@type":"ListItem","position":3,"name":d["h1"],"item":url}]}]})

def area_page(slug, lang):
    d = AREAS[slug][lang]
    lis = "\n".join(f"        <li>{x}</li>" for x in d["inc"])
    if lang == "es":
        pre, assets, site_mf = "../", "../assets", "../site.webmanifest"
        canon = f"{BASE}/areas/{slug}.html"
        twitter = (f'<meta name="twitter:card" content="summary">\n'
                   f'<meta name="twitter:title" content="{d["title"]}">\n'
                   f'<meta name="twitter:description" content="{d["ogdesc"]}">\n'
                   f'<meta name="twitter:image" content="{BASE}/assets/og.png">\n')
        ld = f'<script type="application/ld+json">\n{area_jsonld_es(slug, d)}\n</script>\n'
        locale = "es_MX"; cover = f"../assets/areas/{slug}.jpg"
    else:
        pre, assets, site_mf = "../", "../../assets", "../../site.webmanifest"
        canon = f"{BASE}/en/areas/{slug}.html"
        twitter = ""; ld = ""; locale = "en_US"; cover = f"../../assets/areas/{slug}.jpg"
    icon_pre = assets
    html = f"""<!doctype html>
<html lang="{lang}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{d["title"]}</title>
<meta name="description" content="{d["desc"]}">
<meta name="color-scheme" content="light">
<meta name="theme-color" content="{SITE["theme_color"]}">
<meta name="robots" content="index,follow,max-image-preview:large">
<link rel="icon" type="image/png" href="{icon_pre}/icono-color.png">
<link rel="icon" type="image/svg+xml" href="{icon_pre}/favicon.svg">
<link rel="apple-touch-icon" href="{icon_pre}/apple-touch-icon.png">
<link rel="manifest" href="{site_mf}">
<link rel="canonical" href="{canon}">
<link rel="alternate" hreflang="es" href="{BASE}/areas/{slug}.html">
<link rel="alternate" hreflang="en" href="{BASE}/en/areas/{slug}.html">
<link rel="alternate" hreflang="x-default" href="{BASE}/areas/{slug}.html">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Culebro Abogados">
<meta property="og:locale" content="{locale}">
<meta property="og:title" content="{d["title"]}">
<meta property="og:description" content="{d["ogdesc"]}">
<meta property="og:url" content="{canon}">
<meta property="og:image" content="{BASE}/assets/og.png">
{twitter}{ld}<link rel="stylesheet" href="{icon_pre}/fonts.css">
<link rel="stylesheet" href="{icon_pre}/areas.css">
</head>
<body>
<header><div class="wrap bar">
  <a class="lock" href="{pre}index.html"><img src="{icon_pre}/icono-color.png" alt=""><span class="nm">Culebro</span></a>
  <a class="back" href="{pre}index.html#areas">{d["back"]}</a>
</div></header>
<main>
  <section class="hero"><div class="wrap">
    <p class="eyebrow">{d["eyebrow"]}</p>
    <h1>{d["h1"]}</h1>
    <p class="lead">{d["lead"]}</p>
    <img class="cover" src="{cover}" alt="{d["cover_alt"]}">
  </div></section>
  <section><div class="wrap body">
    <div><h2>{d["inc_h"]}</h2><ul class="inc">
{lis}
    </ul></div>
    <div class="aside"><h2>{d["aside_h"]}</h2>
      <p>{d["aside_p"]}</p>
    </div>
  </div></section>
  <section><div class="wrap"><div class="cta">
    <h3>{d["cta_h"]}</h3>
    <a class="btn" href="{pre}index.html#contacto">{d["cta_btn"]}</a>
  </div></div></section>
</main>
<footer class="foot"><div class="wrap">
  <span>{FOOT}</span>
  <span><a href="{pre}index.html">{d["foot_home"]}</a></span>
</div></footer>
{wa()}
</body>
</html>
"""
    rel = f"areas/{slug}.html" if lang == "es" else f"en/areas/{slug}.html"
    return write(rel, html)


def build_areas():
    out = []
    for slug in ORDER:
        out.append(area_page(slug, "es"))
        out.append(area_page(slug, "en"))
    return out


# ------------------------------------------------------------- HUB DE ÁREAS
HUB_T = {
    "es": {"lang":"es","title":"Áreas de práctica — Culebro Abogados",
      "desc":"Las seis áreas de práctica de Culebro Abogados: corporativo, inmobiliario y hotelero, auditoría legal, comercio exterior, gobierno e infraestructura y actividades filantrópicas.",
      "ogdesc":"Seis áreas de práctica para acompañar la inversión y la operación de la empresa en México.",
      "crumb_home":"Inicio","crumb":"Áreas de práctica","eyebrow":"Áreas de práctica",
      "h1":"Seis áreas, un mismo criterio",
      "lead":"Entendemos primero el negocio y luego el problema legal. Estas son las materias en las que acompañamos a quien invierte y opera en México.",
      "go":"Conocer el área →","rel_h":"Recursos relacionados",
      "rel":[("../publicaciones/","Publicaciones y guías →"),("../preguntas-frecuentes.html","Preguntas frecuentes →")],
      "cta_h":"¿Tienes un asunto en alguna de estas áreas?","cta_btn":"Solicitar información →",
      "back":"← Inicio","foot_home":"Inicio","locale":"es_MX"},
    "en": {"lang":"en","title":"Practice areas — Culebro Abogados",
      "desc":"The six practice areas of Culebro Abogados: corporate, real estate & hospitality, legal audit (due diligence), foreign trade, government & infrastructure, and philanthropic work.",
      "ogdesc":"Six practice areas to support investment and business operations in Mexico.",
      "crumb_home":"Home","crumb":"Practice areas","eyebrow":"Practice areas",
      "h1":"Six areas, one standard",
      "lead":"We understand the business first and the legal problem second. These are the areas in which we support those who invest and operate in Mexico.",
      "go":"Explore area →","rel_h":"Related",
      "rel":[("../preguntas-frecuentes.html","Frequently asked questions →"),("../despacho.html","The firm →")],
      "cta_h":"Do you have a matter in any of these areas?","cta_btn":"Request information →",
      "back":"← Home","foot_home":"Home","locale":"en_US"},
}

def areas_hub(lang):
    t = HUB_T[lang]
    url = f"{BASE}/areas/" if lang=="es" else f"{BASE}/en/areas/"
    icon = "../assets" if lang=="es" else "../../assets"
    mf = "../site.webmanifest" if lang=="es" else "../../site.webmanifest"
    items = [{"@type":"ListItem","position":i+1,"name":NAME[lang][s],
              "url":f"{BASE}/{'' if lang=='es' else 'en/'}areas/{s}.html"} for i,s in enumerate(ORDER)]
    ld = jsonld({"@context":"https://schema.org","@graph":[
        {"@type":"CollectionPage","name":t["title"],"url":url,"inLanguage":"es-MX" if lang=="es" else "en",
         "about":{"@type":"LegalService","name":"Culebro Abogados","url":f"{BASE}/"}},
        {"@type":"ItemList","itemListElement":items},
        {"@type":"BreadcrumbList","itemListElement":[
            {"@type":"ListItem","position":1,"name":t["crumb_home"],"item":f"{BASE}/" if lang=="es" else f"{BASE}/en/"},
            {"@type":"ListItem","position":2,"name":t["crumb"],"item":url}]}]})
    cards = "\n".join(
        f'      <a class="hubcard" href="{s}.html"><span class="k">{i+1:02d}</span>'
        f'<h2>{NAME[lang][s].replace("&","&amp;")}</h2><p>{SHORT[lang][s]}</p>'
        f'<span class="go">{t["go"]}</span></a>' for i,s in enumerate(ORDER))
    rel = "\n".join(f'        <li><a href="{h}">{txt}</a></li>' for h,txt in t["rel"])
    og_alt = "" if lang=="es" else '\n<meta property="og:locale:alternate" content="es_MX">'
    tw = (f'<meta name="twitter:card" content="summary">\n'
          f'<meta name="twitter:title" content="{t["title"]}">\n'
          f'<meta name="twitter:description" content="{t["ogdesc"]}">\n'
          f'<meta name="twitter:image" content="{BASE}/assets/og.png">\n')
    html = f"""<!doctype html>
<html lang="{lang}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{t["title"]}</title>
<meta name="description" content="{t["desc"]}">
<meta name="color-scheme" content="light">
<meta name="theme-color" content="{SITE["theme_color"]}">
<meta name="robots" content="index,follow,max-image-preview:large">
<link rel="icon" type="image/png" href="{icon}/icono-color.png">
<link rel="icon" type="image/svg+xml" href="{icon}/favicon.svg">
<link rel="apple-touch-icon" href="{icon}/apple-touch-icon.png">
<link rel="manifest" href="{mf}">
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="es" href="{BASE}/areas/">
<link rel="alternate" hreflang="en" href="{BASE}/en/areas/">
<link rel="alternate" hreflang="x-default" href="{BASE}/areas/">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Culebro Abogados">
<meta property="og:locale" content="{t["locale"]}">{og_alt}
<meta property="og:title" content="{t["title"]}">
<meta property="og:description" content="{t["ogdesc"]}">
<meta property="og:url" content="{url}">
<meta property="og:image" content="{BASE}/assets/og.png">
{tw}<script type="application/ld+json">
{ld}
</script>
<link rel="stylesheet" href="{icon}/fonts.css">
<link rel="stylesheet" href="{icon}/areas.css">
</head>
<body>
<header><div class="wrap bar">
  <a class="lock" href="{'../' if lang=='es' else '../'}index.html"><img src="{icon}/icono-color.png" alt=""><span class="nm">Culebro</span></a>
  <a class="back" href="{'../' if lang=='es' else '../'}index.html">{t["back"]}</a>
</div></header>
<main>
  <section class="hero"><div class="wrap">
    <nav class="crumbs" aria-label="{'Ruta' if lang=='es' else 'Breadcrumb'}"><a href="{'../' if lang=='es' else '../'}index.html">{t["crumb_home"]}</a> <span>/</span> <span>{t["crumb"]}</span></nav>
    <p class="eyebrow">{t["eyebrow"]}</p>
    <h1>{t["h1"]}</h1>
    <p class="lead">{t["lead"]}</p>
  </div></section>
  <section><div class="wrap">
    <div class="hub">
{cards}
    </div>
  </div></section>
  <section><div class="wrap">
    <div class="rel"><h2>{t["rel_h"]}</h2>
      <ul class="rellist">
{rel}
      </ul>
    </div>
  </div></section>
  <section><div class="wrap"><div class="cta">
    <h3>{t["cta_h"]}</h3>
    <a class="btn" href="{'../' if lang=='es' else '../'}index.html#contacto">{t["cta_btn"]}</a>
  </div></div></section>
</main>
<footer class="foot"><div class="wrap">
  <span>{FOOT}</span>
  <span><a href="{'../' if lang=='es' else '../'}index.html">{t["foot_home"]}</a></span>
</div></footer>
{wa()}
</body>
</html>
"""
    rel_path = "areas/index.html" if lang=="es" else "en/areas/index.html"
    return write(rel_path, html)


# --------------------------------------------------------- HUB PUBLICACIONES
def pubs_hub():
    P = load("pubs.json"); order = P["order"]; items = P["items"]
    url = f"{BASE}/publicaciones/"
    il = [{"@type":"ListItem","position":i+1,"name":items[s]["title"],
           "url":f"{BASE}/publicaciones/{s}.html"} for i,s in enumerate(order)]
    ld = jsonld({"@context":"https://schema.org","@graph":[
        {"@type":"CollectionPage","name":"Publicaciones — Culebro Abogados","url":url,"inLanguage":"es-MX",
         "about":{"@type":"LegalService","name":"Culebro Abogados","url":f"{BASE}/"}},
        {"@type":"ItemList","itemListElement":il},
        {"@type":"BreadcrumbList","itemListElement":[
            {"@type":"ListItem","position":1,"name":"Inicio","item":f"{BASE}/"},
            {"@type":"ListItem","position":2,"name":"Publicaciones","item":url}]}]})
    cards = "\n".join(
        f'      <a class="hubcard" href="{s}.html"><span class="k">{items[s]["kicker"]}</span>'
        f'<h2>{items[s]["title"]}</h2><p>{items[s]["card"]}</p>'
        f'<span class="go">Leer →</span></a>' for s in order)
    html = f"""<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Publicaciones — Culebro Abogados</title>
<meta name="description" content="Guías y publicaciones de Culebro Abogados sobre fideicomiso en zona restringida, cumplimiento aduanero, gobierno corporativo y licitaciones públicas.">
<meta name="color-scheme" content="light">
<meta name="theme-color" content="{SITE["theme_color"]}">
<meta name="robots" content="index,follow,max-image-preview:large">
<link rel="icon" type="image/png" href="../assets/icono-color.png">
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="apple-touch-icon" href="../assets/apple-touch-icon.png">
<link rel="manifest" href="../site.webmanifest">
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="es" href="{url}">
<link rel="alternate" hreflang="x-default" href="{url}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Culebro Abogados">
<meta property="og:locale" content="es_MX">
<meta property="og:title" content="Publicaciones — Culebro Abogados">
<meta property="og:description" content="Guías prácticas para invertir y operar en México con certeza jurídica.">
<meta property="og:url" content="{url}">
<meta property="og:image" content="{BASE}/assets/og.png">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Publicaciones — Culebro Abogados">
<meta name="twitter:description" content="Guías prácticas para invertir y operar en México con certeza jurídica.">
<meta name="twitter:image" content="{BASE}/assets/og.png">
<script type="application/ld+json">
{ld}
</script>
<link rel="stylesheet" href="../assets/fonts.css">
<link rel="stylesheet" href="../assets/areas.css">
</head>
<body>
<header><div class="wrap bar">
  <a class="lock" href="../index.html"><img src="../assets/icono-color.png" alt=""><span class="nm">Culebro</span></a>
  <a class="back" href="../index.html">← Inicio</a>
</div></header>
<main>
  <section class="hero"><div class="wrap">
    <nav class="crumbs" aria-label="Ruta"><a href="../index.html">Inicio</a> <span>/</span> <span>Publicaciones</span></nav>
    <p class="eyebrow">Publicaciones</p>
    <h1>Compromisos.</h1>
    <p class="lead">Notas y guías prácticas sobre los temas por los que más nos consultan. Información para decidir mejor, no asesoría para un caso concreto.</p>
  </div></section>
  <section><div class="wrap">
    <div class="hub">
{cards}
    </div>
  </div></section>
  <section><div class="wrap">
    <div class="rel"><h2>Explora también</h2>
      <ul class="rellist">
        <li><a href="../areas/">Áreas de práctica →</a></li>
        <li><a href="../preguntas-frecuentes.html">Preguntas frecuentes →</a></li>
      </ul>
    </div>
  </div></section>
  <section><div class="wrap"><div class="cta">
    <h3>¿Quieres hablar de tu caso?</h3>
    <a class="btn" href="../index.html#contacto">Solicitar información →</a>
  </div></div></section>
</main>
<footer class="foot"><div class="wrap">
  <span>{FOOT}</span>
  <span><a href="../index.html">Inicio</a></span>
</div></footer>
{wa()}
</body>
</html>
"""
    return write("publicaciones/index.html", html)


# ---------------------------------------------------------------- SITEMAP
LASTMOD = SITE.get("lastmod", "2026-10-04")

def _alt(es, en):
    return (f'<xhtml:link rel="alternate" hreflang="es" href="{BASE}{es}"/>'
            f'<xhtml:link rel="alternate" hreflang="en" href="{BASE}{en}"/>'
            f'<xhtml:link rel="alternate" hreflang="x-default" href="{BASE}{es}"/>')

def build_sitemap():
    rows = []
    def u(loc, prio, freq, alts=""):
        rows.append(f'  <url><loc>{BASE}{loc}</loc><lastmod>{LASTMOD}</lastmod>'
                    f'<changefreq>{freq}</changefreq><priority>{prio}</priority>{alts}</url>')
    u("/", "1.0", "monthly", _alt("/", "/en/"))
    u("/en/", "0.9", "monthly", _alt("/", "/en/"))
    u("/areas/", "0.9", "monthly", _alt("/areas/", "/en/areas/"))
    u("/en/areas/", "0.8", "monthly", _alt("/areas/", "/en/areas/"))
    for s in ORDER:
        u(f"/areas/{s}.html", "0.8", "monthly", _alt(f"/areas/{s}.html", f"/en/areas/{s}.html"))
    for s in ORDER:
        u(f"/en/areas/{s}.html", "0.7", "monthly", _alt(f"/areas/{s}.html", f"/en/areas/{s}.html"))
    u("/despacho.html", "0.7", "yearly", _alt("/despacho.html", "/en/despacho.html"))
    u("/en/despacho.html", "0.6", "yearly", _alt("/despacho.html", "/en/despacho.html"))
    u("/preguntas-frecuentes.html", "0.7", "monthly", _alt("/preguntas-frecuentes.html", "/en/preguntas-frecuentes.html"))
    u("/en/preguntas-frecuentes.html", "0.6", "monthly", _alt("/preguntas-frecuentes.html", "/en/preguntas-frecuentes.html"))
    u("/publicaciones/", "0.7", "monthly")
    for s in load("pubs.json")["order"]:
        u(f"/publicaciones/{s}.html", "0.6", "monthly")
    u("/legal/aviso-privacidad.html", "0.3", "yearly")
    u("/legal/terminos.html", "0.3", "yearly")
    out = ('<?xml version="1.0" encoding="UTF-8"?>\n'
           '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
           'xmlns:xhtml="http://www.w3.org/1999/xhtml">\n' + "\n".join(rows) + "\n</urlset>\n")
    path = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "sitemap.xml")
    open(path, "w", encoding="utf-8").write(out)
    return f"sitemap.xml ({len(rows)} URLs)"


if __name__ == "__main__":
    written = []
    written += build_areas()
    written.append(areas_hub("es"))
    written.append(areas_hub("en"))
    written.append(pubs_hub())
    written.append(build_sitemap())
    print(f"Generadas {len(written)} salidas:")
    for w in written:
        print("  ", w)
