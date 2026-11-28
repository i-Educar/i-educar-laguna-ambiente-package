# Ambiente de demonstração e administração

Ferramentas administrativas para auditar módulos instalados, povoar base de ensaio e configurar identidade/tema do ambiente.

Composer: `buriti/i-educar-laguna-ambiente-package`

## Para que serve

Destinado a administradores: checklist de saúde dos módulos, setup de dados de teste, simulações, tema visual e política de senha — sem ser módulo pedagógico da rede.

## Desenho de funções

| Função | Entrega |
| --- | --- |
| Auditoria de módulos | Check de prontidão e testes automatizados por módulo |
| Setup de ensaio | Povoamento controlado da base para demonstração |
| Simulação | Cenários de uso para validar fluxos |
| Tema / identidade | Ajustes visuais do ambiente |
| Usuários de teste | Apoio a política de senha e contas de ensaio |
| Configuração de módulos | `/configuracoes/modulos` |
| CI/CD de publicação | Workflows reutilizáveis de deploy (Actions) |

## Quem usa

- Administradores do sistema
- Equipe de implantação / QA

## Integrações

- Todos os pacotes Buriti (catálogo e checks)
- Núcleo i-Educar

## Compatibilidade

| Produto | Versão |
| --- | --- |
| **i-Educar** | **2.12.x** |
| **i-Diário** | Portabilis i-Diário (API / SSO do núcleo) |
| Pacote | `0.3.0` |

Declarado em `composer.json` → `extra.ieducar` / `extra.idiario`.

## Documentação

| Documento | Uso |
| --- | --- |
| [`EPIC.md`](./EPIC.md) | Escopo de produto, aceite e critérios |
| [`ROADMAP.md`](./ROADMAP.md) | Fases e entregas |
| [`TODO.md`](./TODO.md) | Backlog operacional |
| [`docs/`](./docs/) | Operação do ambiente e publicação contínua (GitHub Actions) |

---

Licença: **GPL-2.0-or-later** · Organização [i-Educar](https://github.com/i-Educar)
