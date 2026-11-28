#!/usr/bin/env bash
# Gera o conteúdo de LAGUNA_SSH_KNOWN_HOSTS (não comita o resultado).
# Uso (host/usuário só via env — nunca hardcode no repo):
#   LAGUNA_SSH_HOST=x.x.x.x bash scripts/emit-laguna-known-hosts.sh
#   # depois:
#   gh secret set LAGUNA_SSH_KNOWN_HOSTS -o i-Educar < /tmp/laguna.known_hosts
set -euo pipefail

HOST="${LAGUNA_SSH_HOST:-}"
if [[ -z "$HOST" ]]; then
  echo "Defina LAGUNA_SSH_HOST no ambiente (não versionamos IP no repositório)." >&2
  exit 1
fi

OUT="${1:-/tmp/laguna.known_hosts}"
ssh-keyscan -H "$HOST" 2>/dev/null > "$OUT"
chmod 600 "$OUT"
echo "Gravado: $OUT"
echo "Revise o fingerprint e publique só como secret/org ou no Environment laguna-production:"
echo "  gh secret set LAGUNA_SSH_KNOWN_HOSTS -o i-Educar < $OUT"
echo "  # ou: Settings → Environments → laguna-production → Secrets"
