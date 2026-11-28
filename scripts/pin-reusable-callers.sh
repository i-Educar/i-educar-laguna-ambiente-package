#!/usr/bin/env bash
# Atualiza callers Deploy Laguna para pinarem o reusable em um SHA imutável.
# Uso:
#   PIN=<40-hex> COMMIT=1 PUSH=0 bash scripts/pin-reusable-callers.sh
#   # ou lê docs/laguna/deploys/REUSABLE_PIN (linha sha=...)
set -euo pipefail

HUB="$(cd "$(dirname "$0")/.." && pwd)"
MAP="$HUB/docs/laguna/deploys/paths.map.json"
TEMPLATE="$HUB/scripts/deploy-laguna-caller.workflow.yml"
PIN_FILE="$HUB/docs/laguna/deploys/REUSABLE_PIN"
BURITI_ROOT="$(cd "$HUB/.." && pwd)"
MONOREPO="$(cd "$BURITI_ROOT/../.." && pwd)"

PIN="${PIN:-}"
if [[ -z "$PIN" && -f "$PIN_FILE" ]]; then
  PIN="$(awk -F= '/^sha=/{print $2; exit}' "$PIN_FILE" | tr -d '[:space:]')"
fi
if [[ ! "$PIN" =~ ^[0-9a-f]{40}$ ]]; then
  echo "PIN inválido (precisa SHA completo 40 hex). Atualize REUSABLE_PIN ou passe PIN=..." >&2
  echo "Atual: ${PIN:-<vazio>}" >&2
  exit 1
fi

COMMIT="${COMMIT:-0}"
PUSH="${PUSH:-0}"

echo "Pin reusable @ $PIN"
echo "$PIN" | awk '{print "sha="$0}' > "$PIN_FILE.tmp"
{
  echo "# Pin imutável do reusable Deploy Laguna."
  echo "# Callers NÃO devem usar @main — só SHA completo (40 hex)."
  echo "# Atualizado por scripts/pin-reusable-callers.sh"
  echo "sha=$PIN"
} > "$PIN_FILE"

python3 - <<PY
import json, os, re, subprocess
from pathlib import Path

pin = "${PIN}"
map_path = Path("${MAP}")
template = Path("${TEMPLATE}").read_text(encoding="utf-8")
monorepo = Path("${MONOREPO}")
do_commit = "${COMMIT}" == "1"
do_push = "${PUSH}" == "1"
mapping = json.loads(map_path.read_text(encoding="utf-8"))

if "__REUSABLE_SHA__" not in template:
    raise SystemExit("template sem __REUSABLE_SHA__")

for repo, rel in sorted(mapping.items()):
    if repo == "i-educar-laguna-ambiente-package":
        print(f"skip (hub): {repo}")
        continue
    local = monorepo / rel
    if not local.is_dir() or not (local / ".git").exists():
        print(f"skip (sem .git): {repo} → {rel}")
        continue
    body = (
        template
        .replace("__REUSABLE_SHA__", pin)
        .replace("__PACKAGE_REPO__", repo)
        .replace("__SERVER_PATH__", rel)
    )
    wf_dir = local / ".github" / "workflows"
    wf_dir.mkdir(parents=True, exist_ok=True)
    dest = wf_dir / "deploy-laguna.yml"
    dest.write_text(body, encoding="utf-8")
    print(f"ok: {repo} → {dest.relative_to(monorepo)}")
    if not do_commit:
        continue
    subprocess.run(["git", "add", ".github/workflows/deploy-laguna.yml"], cwd=local, check=True)
    st = subprocess.run(["git", "diff", "--cached", "--quiet"], cwd=local)
    if st.returncode == 0:
        print("  (sem mudanças)")
        continue
    subprocess.run(
        ["git", "commit", "-m", f"ci: pin Deploy Laguna reusable @{pin[:12]}"],
        cwd=local,
        check=True,
    )
    if do_push:
        p = subprocess.run(["git", "push", "origin", "HEAD"], cwd=local)
        print("  pushed" if p.returncode == 0 else f"  push FALHOU rc={p.returncode}")
PY
