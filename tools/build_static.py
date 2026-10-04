#!/usr/bin/env python3
"""Empaqueta el sitio para subir a GoDaddy (public_html), por WHITELIST.

Copia SOLO lo que debe servirse en producción a dist/ y hace un zip. Incluye un
audit final que ABORTA si se cuela código fuente o secretos (.py/.env/.db/.md…).
Así es imposible filtrar los generadores, docs internos o la BD al hosting.

Uso:  python3 tools/build.py && python3 tools/build_static.py
"""
import os, shutil, zipfile, sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DIST = os.path.join(ROOT, "dist")

# Qué SÍ se publica (rutas relativas al repo). Carpetas copian su contenido .html/assets.
FILES = [
    "index.html", "despacho.html", "preguntas-frecuentes.html",
    "contact.php", ".htaccess",
    "robots.txt", "sitemap.xml", "llms.txt", "site.webmanifest",
]
DIRS = ["areas", "en", "publicaciones", "legal", "assets"]
# Clave(s) IndexNow en la raíz (archivos <hex>.txt)
EXTRA_GLOBS = ["*.txt"]  # robots/llms ya listados; aquí entra la key de IndexNow

# Nunca deben terminar en dist/
FORBIDDEN_EXT = (".py", ".env", ".db", ".sqlite", ".sqlite3", ".md", ".sample.php", ".log")
# Carpetas que jamás se copian aunque alguien las liste
NEVER_DIRS = {"tools", "db", "portal", "GoogleBusiness", ".git", "dist", "__pycache__", "node_modules"}

def copy_tree(src, dst):
    for root, dirs, files in os.walk(src):
        dirs[:] = [d for d in dirs if d not in NEVER_DIRS]
        rel = os.path.relpath(root, ROOT)
        for f in files:
            if f.endswith(FORBIDDEN_EXT):   # p.ej. un .md dentro de assets
                continue
            s = os.path.join(root, f)
            d = os.path.join(DIST, rel, f)
            os.makedirs(os.path.dirname(d), exist_ok=True)
            shutil.copy2(s, d)

def main():
    if os.path.isdir(DIST):
        shutil.rmtree(DIST)
    os.makedirs(DIST)
    # archivos sueltos
    import glob
    picks = list(FILES)
    for g in EXTRA_GLOBS:
        for p in glob.glob(os.path.join(ROOT, g)):
            name = os.path.basename(p)
            if name not in picks:
                picks.append(name)
    for rel in picks:
        s = os.path.join(ROOT, rel)
        if not os.path.exists(s):
            print("  (falta, se omite)", rel); continue
        if rel.endswith(FORBIDDEN_EXT):
            continue
        d = os.path.join(DIST, rel)
        os.makedirs(os.path.dirname(d) or DIST, exist_ok=True)
        shutil.copy2(s, d)
    # carpetas
    for dname in DIRS:
        src = os.path.join(ROOT, dname)
        if os.path.isdir(src):
            copy_tree(src, dname)
    # AUDITORÍA: abortar si algo prohibido se coló
    leaked = []
    for root, _, files in os.walk(DIST):
        for f in files:
            if f.endswith(FORBIDDEN_EXT):
                leaked.append(os.path.relpath(os.path.join(root, f), DIST))
    if leaked:
        print("ABORTADO · archivos prohibidos en dist/:", leaked); sys.exit(1)
    # zip
    zpath = os.path.join(ROOT, "cglegal-public.zip")
    if os.path.exists(zpath):
        os.remove(zpath)
    with zipfile.ZipFile(zpath, "w", zipfile.ZIP_DEFLATED) as z:
        for root, _, files in os.walk(DIST):
            for f in files:
                full = os.path.join(root, f)
                z.write(full, os.path.relpath(full, DIST))
    n = sum(len(fs) for _, _, fs in os.walk(DIST))
    print(f"dist/ listo ({n} archivos) · zip: cglegal-public.zip · sin fuentes ni secretos ✓")

if __name__ == "__main__":
    main()
