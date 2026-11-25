#!/usr/bin/env bash
# Povoa a base Laguna com carga rica (rede + processos multi-etapa).
# Uso (após composer new-install + db:seed):
#   bash packages/buriti/i-educar-laguna-ambiente-package/bin/povoar-demo.sh
#   bash packages/buriti/i-educar-laguna-ambiente-package/bin/povoar-demo.sh --force
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../../.." && pwd)"
cd "$ROOT"

FORCE=0
POR_TURMA=12
for arg in "$@"; do
  case "$arg" in
    --force) FORCE=1 ;;
    --por-turma=*) POR_TURMA="${arg#*=}" ;;
  esac
done

echo "==> Laguna · povoamento demo (PE-77)"
echo "    root=$ROOT  por-turma=$POR_TURMA  force=$FORCE"

if [[ ! -f artisan ]]; then
  echo "artisan não encontrado em $ROOT" >&2
  exit 1
fi

php artisan laguna:setup --por-turma="$POR_TURMA"

if [[ "$FORCE" -eq 1 ]]; then
  php artisan busca-ativa:demo --force --faltas=28
  php artisan solicitacao:demo --force
  php artisan atendimento:demo --force
fi

echo "==> Concluído. Logins PoC: CPF = senha (ex. 42091020001 admin, 42091020014 busca ativa)."
