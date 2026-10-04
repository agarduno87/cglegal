# Plan de contenido SEO/GEO — Culebro Abogados

Metodología replicada de **Datara Hub** y **Logistika** (arquitectura pilar/cluster +
GEO). Objetivo: que CG Legal tenga desde el día 1 todas las páginas que va a necesitar
una vez live, y un plan de crecimiento de contenido.

## 1. Arquitectura de contenido (pilar → cluster)

- **Pilares comerciales = las 6 áreas de práctica** (`/areas/<slug>.html`). Cada una es
  la página que queremos posicionar para su tema.
- **Hub de áreas** (`/areas/`) = índice navegable + `CollectionPage`/`ItemList`.
- **Cluster informacional = Publicaciones** (`/publicaciones/`): artículos que responden
  dudas y **enlazan al pilar (área) de su tema**. Hoy hay 4; el plan es 1–2/mes.
- **Soporte de conversión/confianza:** `/despacho.html` (quiénes somos),
  `/preguntas-frecuentes.html` (FAQ con `FAQPage`), legales.
- **Enlazado interno:** hub→pilares, pilar→publicaciones relacionadas, publicaciones→pilar,
  FAQ→áreas/publicaciones, breadcrumbs (`BreadcrumbList`) en todas las subpáginas.

## 2. Mapa de keywords por cluster (siembra long-tail, no genéricas gigantes)

| Cluster | Keyword objetivo (ejemplos) | Página destino |
|---|---|---|
| Inmobiliario (local) | "abogado inmobiliario Los Cabos", "fideicomiso zona restringida Los Cabos", "comprar casa en México siendo extranjero" | área inmobiliario-hotelero + guía fideicomiso |
| Corporativo | "constituir empresa extranjera en México", "gobierno corporativo socios" | área corporativo + guía gobierno-corporativo |
| Due diligence | "due diligence legal Querétaro", "auditoría legal antes de comprar empresa" | área auditoria-legal |
| Comercio exterior | "cumplimiento aduanero importador", "asesoría legal comercio exterior IMMEX" | área comercio-exterior + guía aduanero |
| Gobierno | "licitaciones públicas asesoría legal", "contratación pública permisos" | área gobierno + guía licitaciones |
| Filantrópico | "constituir donataria autorizada", "asociación civil sin fines de lucro" | área actividades-filantropicas |

## 3. Páginas que YA existen (estructura completa al día de hoy)

- Home `/` + espejo `/en/`
- Hub áreas `/areas/` + `/en/areas/` · 6 áreas ES + 6 EN
- Hub publicaciones `/publicaciones/` + 4 artículos
- `/despacho.html` + `/en/despacho.html`
- `/preguntas-frecuentes.html` + `/en/preguntas-frecuentes.html`
- Legales `/legal/aviso-privacidad.html` + `/legal/terminos.html`

## 4. Backlog de contenido (siguientes publicaciones a redactar — el despacho valida)

1. "Guía del inversionista extranjero para comprar en Los Cabos" (pilar inmobiliario).
2. "Constituir una empresa en México siendo extranjero: pasos y vehículos".
3. "Qué revisa un due diligence legal antes de comprar un negocio".
4. "IMMEX y comercio exterior: obligaciones legales del importador".
5. "Donatarias autorizadas: cómo se constituye y qué obligaciones tiene".
6. Versiones EN de las publicaciones de mayor intención (nearshoring / foreign buyers).

**Formato citable por IA (GEO):** definición directa en 40–60 palabras al inicio, subtítulos
claros, tablas donde aplique, FAQ con `FAQPage`, y declarar las fronteras (p. ej. "no somos
agente aduanal"). Cada artículo enlaza a su área pilar.

## 5. Pendiente del cliente (contenido real)
- Redacción/validación legal de publicaciones y textos (áreas marcadas "ilustrativo").
- CVs reales de Javier y Alejandro para `/despacho.html`.
- Fotos reales de oficinas/equipo (para el sitio y Google Business Profile).

> Ver `SEO-GEO-SETUP.md` para el alta en Search Console, Bing, IndexNow y Google Business
> Profile una vez que el sitio esté en el dominio real.
