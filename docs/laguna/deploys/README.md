# CI/CD · Deploy Laguna (Modelópolis)

Publicação automática dos pacotes da org [i-Educar](https://github.com/i-Educar) no servidor Laguna.

- **URL pública:** https://ieducar-laguna.librecode.coop
- **App path (secret):** `LAGUNA_APP_PATH` (ex. path no VPS — só no secret)

**Regra:** não subir código direto no VPS (rsync/scp/SSH+cp). Só GitHub Actions, salvo autorização explícita.

## Fluxo

1. Push em `main` (ou *Run workflow*) no repositório do pacote.
2. Job **Deploy Laguna** chama o reutilizável **pinado por SHA**  
   (`docs/laguna/deploys/REUSABLE_PIN` — nunca `@main`).
3. **Environment** `laguna-production` (required reviewers) — gate humano antes de `rsync`/`migrate`.
4. **Bump de versão:** incrementa `composer.json`, grava `deploy-info.json`, push com `LAGUNA_DEPLOY_TOKEN` e `[skip ci]`.
5. Testes do módulo no runner (se houver `tests/run.php`).
6. SSH com `StrictHostKeyChecking yes` + `LAGUNA_SSH_KNOWN_HOSTS` (fingerprint fixo).
7. `rsync` → pós-deploy (migrate do pacote + testes no app) → manifesto `modules-deploy.json` → tag `deploy/laguna/…`.

## Gates de segurança (obrigatórios)

| Gate | Como |
| --- | --- |
| Reusable pinado | Callers usam `@<sha40>`; atualizar com `scripts/pin-reusable-callers.sh` |
| Actions pinadas | `actions/checkout` e `setup-php` por SHA completo no reusable |
| Host key fixa | Secret `LAGUNA_SSH_KNOWN_HOSTS` (gerar com `scripts/emit-laguna-known-hosts.sh`) |
| Aprovação humana | Environment `laguna-production` com **Required reviewers** |
| Permissões mínimas | Workflow `contents: read`; bump/tag só via `LAGUNA_DEPLOY_TOKEN` |
| Sem IP/user no git | Host/usuário só em secrets — docs sem exemplos reais |
| Proteção de `main` | Branch protection na org/repos (fora do YAML) — ver checklist abaixo |

### Checklist · Environment `laguna-production`

Em cada repo (ou herdado via org rulesets), criar Environment **laguna-production**:

1. Settings → Environments → New → `laguna-production`
2. **Required reviewers:** 1+ pessoas/times (não o bot de deploy)
3. (Opcional) Wait timer; Deployment branches: só `main`
4. Secrets do Environment (preferível aos secrets de repo): SSH + known_hosts + deploy token

### Checklist · Branch protection (`main`)

Fora do YAML — sem isso o workflow não impede force-push nem bypass:

- [ ] Require pull request before merging (1+ review)
- [ ] Require status checks (CI) antes do merge
- [ ] Restrict who can push to `main` / sem force-push
- [ ] Do not allow bypassing the above settings
- [ ] Rulesets na org `i-Educar` aplicados aos pacotes Buriti

### Checklist · Token de deploy

Criar fine-grained PAT ou GitHub App **só** com:

- Contents: **Read and write** nos repositórios dos pacotes (bump `composer.json` + tags)
- Sem admin, sem workflows, sem secrets

Gravar como `LAGUNA_DEPLOY_TOKEN` no Environment (ou org secret).

### Known hosts

```bash
LAGUNA_SSH_HOST='<host-do-secret>' bash scripts/emit-laguna-known-hosts.sh
# revisar fingerprint, depois:
gh secret set LAGUNA_SSH_KNOWN_HOSTS -o i-Educar < /tmp/laguna.known_hosts
```

## Secrets

| Secret | Onde | Conteúdo |
| --- | --- | --- |
| `LAGUNA_SSH_HOST` | org / environment | hostname ou IP (não documentar no git) |
| `LAGUNA_SSH_USER` | org / environment | usuário SSH |
| `LAGUNA_SSH_KEY` | org / environment | chave privada OpenSSH |
| `LAGUNA_APP_PATH` | org / environment | path absoluto do app no VPS |
| `LAGUNA_SSH_KNOWN_HOSTS` | org / environment | linhas `known_hosts` esperadas |
| `LAGUNA_DEPLOY_TOKEN` | org / environment | PAT/App (contents:write mínimo) |

```bash
# Exemplos sem valores reais — preencha a partir do cofre da org:
gh secret set LAGUNA_SSH_HOST -o i-Educar
gh secret set LAGUNA_SSH_USER -o i-Educar
gh secret set LAGUNA_APP_PATH -o i-Educar
gh secret set LAGUNA_SSH_KEY -o i-Educar < /path/to/key
gh secret set LAGUNA_SSH_KNOWN_HOSTS -o i-Educar < /tmp/laguna.known_hosts
gh secret set LAGUNA_DEPLOY_TOKEN -o i-Educar
```

## Pin do reusable (após push do hub)

```bash
cd packages/buriti/i-educar-laguna-ambiente-package
git push origin main
SHA=$(git rev-parse HEAD)
PIN=$SHA COMMIT=1 PUSH=0 bash scripts/pin-reusable-callers.sh
# revisar diffs nos pacotes; depois PUSH=1 se quiser publicar os pins
```

## Bootstrap / pin callers

```bash
PIN=<sha40> COMMIT=1 PUSH=0 bash scripts/bootstrap-deploy-workflows.sh
```

## Acompanhar

```bash
git fetch --tags
git tag -l 'deploy/laguna/*'
gh run list --workflow "Deploy Laguna" -L 10
```

Histórico: Actions → **Deploy Laguna** em cada pacote. Painel admin `/painel` → botão **Módulos CI**.
