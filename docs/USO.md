# Uso — Ambiente Laguna

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

No browser (admin): menu **Ambiente Laguna** → Verificar → Setup teste.

## Critérios

- Obrigatórios OK no `laguna:check`
- Rede com ≥ 1 escola e ≥ 1 turma
- Cardápio do dia presente se alimentação liberada
