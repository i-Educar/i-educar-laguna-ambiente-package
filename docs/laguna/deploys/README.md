# CI/CD · Deploy Laguna (Modelópolis)

Publicação automática dos pacotes da org [i-Educar](https://github.com/i-Educar) no servidor:

- **URL:** https://ieducar-laguna.librecode.coop
- **App path:** `/var/www/ieducar`

## Fluxo

1. Push em `main` (ou *Run workflow*) no repositório do pacote.
2. Job **Deploy Laguna** chama o reutilizável  
   `i-Educar/i-educar-laguna-ambiente-package/.github/workflows/reusable-deploy-laguna.yml@main`.
3. `rsync` via SSH para o caminho de [`paths.map.json`](./paths.map.json).
4. Pós-deploy: `view:clear` (+ migrate opcional).
5. **Registro Git:** tag anotada no pacote `deploy/laguna/YYYYMMDD-HHMM-<sha>`.

## Secrets (por repositório ou org)

| Secret | Exemplo |
| --- | --- |
| `LAGUNA_SSH_HOST` | `177.39.18.192` |
| `LAGUNA_SSH_USER` | `debian` |
| `LAGUNA_SSH_KEY` | chave privada OpenSSH |
| `LAGUNA_APP_PATH` | `/var/www/ieducar` |

```bash
gh secret set LAGUNA_SSH_HOST -R i-Educar/<pacote> --body '177.39.18.192'
gh secret set LAGUNA_SSH_USER -R i-Educar/<pacote> --body 'debian'
gh secret set LAGUNA_APP_PATH -R i-Educar/<pacote> --body '/var/www/ieducar'
gh secret set LAGUNA_SSH_KEY -R i-Educar/<pacote> < ~/.ssh/id_ieducar_laguna
```

Preferir **Organization secrets** (requer admin da org).

## Bootstrap

```bash
COMMIT=1 PUSH=1 bash packages/buriti/i-educar-laguna-ambiente-package/scripts/bootstrap-deploy-workflows.sh
```

## Acompanhar

```bash
git fetch --tags
git tag -l 'deploy/laguna/*'
gh run list --workflow "Deploy Laguna" -L 10
```

Histórico: Actions → **Deploy Laguna** em cada pacote.
