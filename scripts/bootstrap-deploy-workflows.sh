#!/usr/bin/env bash
# Copia o caller Deploy Laguna para os pacotes mapeados e faz commit local.
set -euo pipefail

HUB="$(cd "$(dirname "$0")/.." && pwd)"
MAP="$HUB/docs/laguna/deploys/paths.map.json"
TEMPLATE="$HUB/scripts/deploy-laguna-caller.workflow.yml"
BURITI_ROOT="$(cd "$HUB/.." && pwd)"
MONOREPO="$(cd "$BURITI_ROOT/../.." && pwd)"

if [[ ! -f "$MAP" || ! -f "$TEMPLATE" ]]; then
  echo "Arquivos do hub ausentes." >&2
  exit 1
fi

PUSH="${PUSH:-0}"
COMMIT="${COMMIT:-1}"

python3 - <<'PY' "$MAP" "$TEMPLATE" "$BURITI_ROOT" "$MONOREPO" "$COMMIT" "$PUSH"
import json, os, sys, subprocess, shutil
from pathlib import Path

map_path, template, buriti, monorepo, do_commit, do_push = sys.argv[1:7]
do_commit = do_commit == "1"
do_push = do_push == "1"
mapping = json.loads(Path(map_path).read_text())
tpl = Path(template).read_text()

# repo → pasta local relativa ao monorepo
for repo, rel in sorted(mapping.items()):
    local = Path(monorepo) / rel
    if not local.is_dir() or not (local / ".git").exists():
        print(f"skip (sem .git): {repo} → {rel}")
        continue
    wf_dir = local / ".github" / "workflows"
    wf_dir.mkdir(parents=True, exist_ok=True)
    dest = wf_dir / "deploy-laguna.yml"
    # Caller aponta para o hub; package_repo vem do github.event.repository.name
    dest.write_text(tpl)
    print(f"ok: {repo} → {dest.relative_to(monorepo)}")
    if not do_commit:
        continue
    subprocess.run(["git", "add", ".github/workflows/deploy-laguna.yml"], cwd=local, check=True)
    st = subprocess.run(["git", "diff", "--cached", "--quiet"], cwd=local)
    if st.returncode == 0:
        print(f"  (sem mudanças)")
        continue
    subprocess.run(
        ["git", "commit", "-m", "ci: deploy automático Laguna (GitHub Actions)"],
        cwd=local,
        check=True,
    )
    if do_push:
        subprocess.run(["git", "push", "origin", "HEAD"], cwd=local, check=True)
        print(f"  pushed")
PY
