# CI/CD · Deploy Laguna (Modelópolis)

Publicação automática dos pacotes da org [i-Educar](https://github.com/i-Educar) no servidor:

- **URL:** https://ieducar-laguna.librecode.coop  
- **Host:** VPS (`LAGUNA_SSH_*`)  
- **App path:** `/var/www/ieducar`

## Como funciona

1. Push em `main` (ou `workflow_dispatch`) no repositório do pacote.
2. O workflow **Deploy Laguna** chama o reutilizável em  
   [`i-educar-laguna-ambiente-package`](https://github.com/i-Educar/i-educar-laguna-ambiente-package) → `.github/workflows/reusable-deploy-laguna.yml`.
3. O conteúdo é sincronizado via **rsync/SSH** para o caminho mapeado em [`paths.map.json`](./paths.map.json).
4. Pós-deploy: `view:clear` + `package:discover` (migrate opcional).
5. **Registro Git**
   - Tag anotada no pacote: `deploy/laguna/YYYYMMDD-HHMM-<sha>`
   - Linha em [`REGISTRY.jsonl`](./REGISTRY.jsonl) neste hub (se `IEDUCAR_DEPLOY_TOKEN` estiver configurado)
   - Environment GitHub `laguna-production` (histórico de deployments)

## Secrets (org `i-Educar` ou por repositório)

| Secret | Exemplo | Uso |
| --- | --- | --- |
| `LAGUNA_SSH_HOST` | `177.39.18.192` | Host da VPS |
| `LAGUNA_SSH_USER` | `debian` | Usuário SSH |
| `LAGUNA_SSH_KEY` | *(chave privada)* | OpenSSH ed25519/rsa |
| `LAGUNA_APP_PATH` | `/var/www/ieducar` | Raiz do i-Educar no servidor |
| `IEDUCAR_DEPLOY_TOKEN` | PAT `contents:write` | Commit no `REGISTRY.jsonl` (opcional) |

Preferir **Organization secrets** com acesso aos repositórios Buriti.

```bash
# Exemplo (admin da org)
gh secret set LAGUNA_SSH_HOST --org i-Educar --body '177.39.18.192'
gh secret set LAGUNA_SSH_USER --org i-Educar --body 'debian'
gh secret set LAGUNA_SSH_KEY --org i-Educar < ~/.ssh/id_ieducar_laguna
gh secret set LAGUNA_APP_PATH --org i-Educar --body '/var/www/ieducar'
```

## Bootstrap nos pacotes

No monorepo local:

```bash
bash packages/buriti/i-educar-laguna-ambiente-package/scripts/bootstrap-deploy-workflows.sh
```

Isso copia `.github/workflows/deploy-laguna.yml` para cada pacote mapeado e faz commit local (push manual ou via script).

## Deploy manual

Actions → **Deploy Laguna** → Run workflow.

## Acompanhar

```bash
# Tags de deploy no pacote
git fetch --tags
git tag -l 'deploy/laguna/*'

# Registry central
git -C packages/buriti/i-educar-laguna-ambiente-package log --oneline -- docs/laguna/deploys/REGISTRY.jsonl
```
