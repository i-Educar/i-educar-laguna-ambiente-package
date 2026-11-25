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

## Comandos

```bash
php artisan laguna:check              # audita módulos / schemas / menus / cmds
php artisan laguna:setup              # povoa setup + demos
php artisan laguna:ensaiar            # check → setup → recheck
php artisan laguna:setup --skip-municipal --skip-matriculas
```

## UI admin

`/laguna-ambiente` · `/check` · `/setup` — middleware `ExigeAdmin` (403 se não for admin).

## O que o setup faz

1. `ieducar:setup default-br` (se o setup-package estiver ativo)
2. `ieducar:matriculas --force`
3. `migrate --force`
4. Demos: alimentação, escolar, exportação, transporte (quando registrados)
5. Fallback de cardápio + resumo da rede

## Instalação no monorepo

Path repository + provider em `config/app.php` + liberar em `tenants.local.php`. Ver `docs/USO.md`.
