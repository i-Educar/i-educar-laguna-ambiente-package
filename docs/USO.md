# Uso — Ambiente Laguna

Pipeline completo de carga (povoar / cenários / simulação) e vínculo com as ondas:  
`packages/buriti/docs/laguna/CARGA-DADOS-AMBIENTE.md` · gate PoC: `…/GATE-100-POC.md`.  
Artisan (significado + ordem): `packages/buriti/docs/laguna/COMANDOS-ARTISAN.md` · install: `…/INSTALACAO.md` · UI: `/laguna-ambiente/setup`.

## Pré-requisitos

- i-Educar 2.12 · PHP ≥ 8.3
- Usuário **admin** para a UI
- Pacotes obrigatórios no path (ver `CatalogoModulos`)

## Fluxo recomendado (primeiro ensaio)

```bash
# 1. Liberar no tenant
# config/tenants.local.php → packages inclui buriti/i-educar-laguna-ambiente-package

composer dump-autoload
php artisan migrate --force
php artisan laguna:ensaiar
```

No browser (admin): menu **Ambiente Laguna** → Verificar → Setup teste → **Simulação**.

```bash
# Só a simulação de 20 pessoas (após migrate)
php artisan laguna:simular-pessoas
# UI: /laguna-ambiente/simulacao
```

O setup (`laguna:setup`) já inclui a simulação (use `--skip-simulacao` para pular). Dados ficam em `laguna.simulacao_lote|pessoa|evento`.

Tema visual: **Ambiente Laguna → Skins da escola** (`/laguna-ambiente/tema`), ou `BURITI_TEMA_ATIVO=true` no `.env`.

## Critérios

- Obrigatórios OK no `laguna:check`
- Rede com ≥ 1 escola e ≥ 1 turma
- Cardápio do dia presente se alimentação liberada
- Lote de simulação com 20 pessoas e eventos multi-módulo na UI
