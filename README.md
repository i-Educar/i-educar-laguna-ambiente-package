# Ambiente Laguna (admin)

Pacote Buriti para o i-Educar **2.12**: auditoria dos módulos PE-77/2026 e setup que povoa a base para teste completo.

Composer: `buriti/i-educar-laguna-ambiente-package`

| Token | Valor |
| --- | --- |
| Cor | `#16384a` |
| Ícone | `fa-heartbeat` |
| Processos | `8899xx` |
| Menu `order` | `99` (ferramenta admin) |
| Acesso web | **somente** `User::isAdmin()` |

## Compatibilidade

| Produto | Versão |
| --- | --- |
| **i-Educar** | **2.12.x** (min. 2.12.0) |
| **i-Diário** | Portabilis i-Diário (espelho Laguna) via API/SSO do core |
| Pacote | `0.2.0` |

Declarado em `composer.json` → `extra.ieducar` e `extra.idiario`.

## Comandos

```bash
php artisan laguna:check              # audita módulos / schemas / menus / cmds
php artisan laguna:setup              # povoa setup + demos
php artisan laguna:ensaiar            # check → setup → recheck
php artisan laguna:setup --skip-municipal --skip-matriculas
```

## UI admin

`/laguna-ambiente` · `/check` · `/setup` · `/tema` · `/usuarios` — middleware `ExigeAdmin` (403 se não for admin).

### Suspender usuários

**Ambiente Laguna → Suspender usuários** (`/laguna-ambiente/usuarios`): select múltiplo + desativar.
Revoga tokens Sanctum, bloqueia login web/apps e registra ator na auditoria (`apps_api.evento_governanca`).

### Tema visual Buriti (onda 1)

Caminho na interface: **Ambiente Laguna → Tema visual** (`/laguna-ambiente/tema`).

Alternativa: `BURITI_TEMA_ATIVO=true` no `.env` (o toggle da UI grava `laguna-ambiente.tema.ativo` em settings e prevalece).

## O que o setup faz

1. `ieducar:setup default-br` (se o setup-package estiver ativo)
2. `ieducar:matriculas --force`
3. `migrate --force`
4. Demos: alimentação, escolar, exportação, transporte (quando registrados)
5. Fallback de cardápio + resumo da rede

## Instalação no monorepo

Path repository + provider em `config/app.php` + liberar em `tenants.local.php`. Ver `docs/USO.md`.
