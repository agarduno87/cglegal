#!/usr/bin/env bash
# Lanzador local del Portal Culebro (dev).
# - Sirve SIEMPRE desde la raíz del repo (culebroabogados/) para que /portal/ resuelva.
# - Usa un solo host (127.0.0.1) para no mezclar cookies localhost vs 127.0.0.1.
# - Si el puerto está ocupado, toma otro libre al azar.
# Uso:  bash portal/serve.sh            (puerto automático)
#       bash portal/serve.sh 8080       (intenta 8080; si está ocupado, elige otro)
set -euo pipefail
HOST=127.0.0.1
ROOT="$(cd "$(dirname "$0")/.." && pwd)"   # .../culebroabogados

port_in_use() { # 0 = en uso, 1 = libre
  (exec 3<>"/dev/tcp/$HOST/$1") 2>/dev/null && { exec 3>&- 3<&-; return 0; } || return 1
}
pick_free_port() {
  for _ in $(seq 1 100); do
    local p=$(( (RANDOM % 20000) + 20000 ))   # 20000–39999
    port_in_use "$p" || { echo "$p"; return; }
  done
  echo "8111"
}

WANT="${1:-}"
if [[ -n "$WANT" ]] && ! port_in_use "$WANT"; then
  PORT="$WANT"
elif [[ -n "$WANT" ]]; then
  PORT="$(pick_free_port)"
  echo "⚠  El puerto $WANT está ocupado; usando $PORT."
else
  PORT="$(pick_free_port)"
fi

echo "──────────────────────────────────────────────"
echo "  Portal Culebro (dev)  ·  raíz: $ROOT"
echo "  Abre:  http://$HOST:$PORT/portal/login.php"
echo "  Usa EXACTAMENTE ese host ($HOST), no 'localhost'."
echo "  admin@cglegal.com.mx / admin123  (abogado / cliente = rol+123)"
echo "  Ctrl+C para detener."
echo "──────────────────────────────────────────────"
cd "$ROOT"
exec php -S "$HOST:$PORT"
