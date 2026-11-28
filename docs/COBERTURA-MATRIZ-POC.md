# Cobertura PoC × Matriz TR (PE 77/2026)

Atualizado: **2026-11-28** (gate Buriti) · fonte: `packages/buriti/docs/laguna/MATRIZ-REQUISITOS-TR.md`  
Snapshot: **656 `[x]` · 6 `[~]` · 2 `[ ]` · ~98.8%** · R3 em evidência · R4 pronta · R6 **32 em evidência** (27–31 planejadas)  
Backlog: `laguna/BACKLOG-SANCAO-SEM-WA-APK.md` · Gate: `laguna/GATE-100-POC.md` · Demo 3: `onda-32/ROTEIRO-DEMO.md`

Legenda: **atende** = `[x]` PoC · **parcial** = `[~]` · **gap** = `[ ]` · **núcleo** = i-Educar base  
Ondas: `packages/buriti/docs/laguna/ONDAS-PENDENTES-TR.md` · STATUS `docs/onda-12`…`onda-32` · App: `DOC-TECNICO-APP-ANDROID-UNIFICADO.md`

| Seção | Itens | `[x]` | `[~]` | `[ ]` | % `[x]` |
| --- | ---: | ---: | ---: | ---: | ---: |
| 4 | 10 | 9 | 1 | 0 | **90%** |
| 5.1 | 39 | 39 | 0 | 0 | **100%** |
| 5.2 | 55 | 55 | 0 | 0 | **100%** |
| 5.3 | 198 | 193 | 5 | 0 | **97%** |
| 5.4 | 39 | 39 | 0 | 0 | **100%** |
| 5.5 | 27 | 27 | 0 | 0 | **100%** |
| 5.6 | 14 | 14 | 0 | 0 | **100%** |
| 5.7 | 13 | 13 | 0 | 0 | **100%** |
| 5.8 | 95 | 93 | 0 | 2 | **98%** |
| 5.9 | 25 | 25 | 0 | 0 | **100%** |
| 5.10 | 18 | 18 | 0 | 0 | **100%** |
| 5.11 | 11 | 11 | 0 | 0 | **100%** |
| 5.12 | 38 | 38 | 0 | 0 | **100%** |
| 5.13 | 26 | 26 | 0 | 0 | **100%** |
| 5.14 | 21 | 21 | 0 | 0 | **100%** |
| 5.15 | 35 | 35 | 0 | 0 | **100%** |
| **Total** | **664** | **656** | **6** | **2** | **~98.8%** |

## Ambiente PoC (Onda 32)

| Check | Resultado 2026-11-27 |
| --- | --- |
| `php artisan laguna:check` | Obrigatórios **8/8** · falhas 0 |
| `laguna:simular-pessoas --qtd=20` | lote #5 · 133 ok · 7 falta_teste |
| `exportacao:demo` | 10 formatos OK |
| `exportacao:demo-criticos` | 6 arquivos OK |

## Princípios de fluxo aplicados (CRUD educacional)

1. **Criar** entidade com validação e escopo escola/rede  
2. **Listar/filtrar** com status visível  
3. **Detalhar** com timeline  
4. **Atualizar** com responsável  
5. **Finalizar** (status terminal)  
6. **Anexo** (PDF/imagem conforme regra)  
7. **Auditoria** (quem/quando/o quê)  
8. **Feedback** do solicitante ou avaliação  
9. **Configuração** (SLA, setores, documentos, comissão, PNAE…)

## Top gaps acionáveis (pós-Onda 32)

1. **Onda 27** — promover 5.13/5.14 na matriz (API já evidenciada)  
2. **Onda 30** — 5.3 prints/LibreSign/uniformes → `[x]`  
3. **Onda 28** — 4.x / 5.5 / 5.7 / 5.9 residual  
4. **Onda 31** — 5.4 / 5.10 / RH  
5. **Onda 29** — 5.2 SISVAN / NF-e / financeiro  
6. **Rodada 5** — WhatsApp + lojas (fora do gate)  
7. Plano consolidado: `onda-32/PLANO-30-DIAS.md`

## Evidências recentes

### 2026-11-27 · Onda 32

- Snapshot matriz recalculado (headers + `ONDAS-PENDENTES`)  
- `laguna:check` verde · simulação 20 · export smoke  
- Roteiro demo SME 3 · plano 30 dias  

### 2026-11-26 · Ondas 12–16

- Censo: export Matrícula Inicial **2025 e 2026** validados  
- Fixtures ExportacaoModulo em `docs/onda-{12..16}/fixtures/export/`  
- Inventário 5.3: `docs/onda-15/INVENTARIO-5.3.md`  

## Padrão CRUD educacional (checklist por módulo)

| Módulo | CRUD | Status | Responsável | Anexo | Auditoria | Feedback | Config |
| --- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| Solicitação | ✓ | ✓ | ✓ | PDF | ✓ | ✓ | setores |
| Contratações | ✓ | ✓ | comissão | ✓ | ✓ | recurso | docs/pontos |
| Atendimento | ✓ | ✓ | equipe | ✓ | ✓ | devolutiva | env+tela |
| Biblioteca | ✓ | ✓ | — | comprov. | ✓ | — | tela |
| Alimentação | ✓ | — | — | laudo | ✓ | — | env/PNAE |
| Almoxarifado | ✓ | ✓ | ✓ | — | ✓ | assinatura | env |
