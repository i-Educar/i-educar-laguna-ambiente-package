#!/usr/bin/env bash
# Gera callers Deploy Laguna a partir do template + paths.map + REUSABLE_PIN.
# Uso:
#   COMMIT=0 PUSH=0 bash scripts/bootstrap-deploy-workflows.sh
#   PIN=<sha> COMMIT=1 PUSH=1 bash scripts/bootstrap-deploy-workflows.sh
set -euo pipefail
HUB="$(cd "$(dirname "$0")/.." && pwd)"
exec bash "$HUB/scripts/pin-reusable-callers.sh"
