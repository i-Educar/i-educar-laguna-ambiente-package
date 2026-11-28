# Ondas pendentes — Anexo I TR · PE 77/2026 Laguna/SC

> Atualizado: **2026-11-28** (gate Buriti + papéis temporários) (Onda certificados 5.12) · fonte: `MATRIZ-REQUISITOS-TR.md` (este diretório)  
> **Rodada 2** = Ondas 6–11 (parcial/entregue). **Rodada 3** = Ondas **12–18** (gate PoC · em evidência).  
> **Rodada 4** = Ondas **19–24** (pronta — sanar planos 15% + 5.3 núcleo).  
> **Rodada 5** = Ondas **25–26** (opcional SME: WhatsApp / lojas — **fora do gate**).  
> **Rodada 6** = Ondas **27–32** (segunda sanção · sem WA/APK · **Onda 32 em evidência** — ambiente verde + snapshot).  
> Backlog IDs: [`BACKLOG-SANCAO-SEM-WA-APK.md`](./BACKLOG-SANCAO-SEM-WA-APK.md) (**490** abertos sanáveis).  
> Ondas 0–5: `packages/buriti/ORDEM-DESENVOLVIMENTO-LAGUNA.md`.  
> **Solução completa:** [`SOLUCAO-COMPLETA.md`](./SOLUCAO-COMPLETA.md) · install: [`INSTALACAO.md`](./INSTALACAO.md) · repos: [`REPOS-IEDUCAR-ORG.md`](./REPOS-IEDUCAR-ORG.md).  
> **Gate 100% Buriti (sem WhatsApp / sem APK):** [`GATE-100-POC.md`](./GATE-100-POC.md) · carga: [`CARGA-DADOS-AMBIENTE.md`](./CARGA-DADOS-AMBIENTE.md).  
> Espelho OCR: `tmp/edital-ocr/` (symlinks). 5.8: `STATUS-5.8-TR.md` (pré-matrícula).  
> Legenda: `[x]` PoC · `[~]` parcial · `[ ]` gap · N/A PoC = `[x]` com nota âmbar.  
> Status: `packages/buriti/docs/onda-N/STATUS.md`

## Snapshot da matriz

> Recalculado **2026-11-28** (Onda 32) a partir dos checkboxes da `MATRIZ-REQUISITOS-TR.md`.

| Módulo | Itens | `[x]` | `[~]` | `[ ]` | % `[x]` |
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

> **Ambiente (Onda 32):** `laguna:check` **8/8** · simulação 20 pessoas OK · `exportacao:demo` + `demo-criticos` OK.  
> **Cobertura TR bruta** ainda ~26% — promoção de IDs depende das Ondas **27–31** (ou plano 30 dias).  
> **5.8:** gate sem WhatsApp ≈ 100%. Residual: **5.8.33 / 5.8.120** (WhatsApp) — **Rodada 5**.  
> **5.13/5.14:** Buriti fecha com **API + DOC técnico**; lojas = **Rodada 5**.

### Meta da Rodada 3

Levar o total de **~25% → ≥ 55–60% `[x]`** (ou N/A documentado), com planos 15%/30 dias nos módulos gigantes (5.3), sem reabrir motores estáveis.  
**Meta operacional Buriti (Onda 18):** gate ≥ 85% por módulo obrigatório **ou** ≤ 15% com plano 30 dias — ver `GATE-100-POC.md` (WhatsApp e APK excluídos).

---

## Princípios

1. Promover `[~]` → `[x]` só com evidência UI/API + dados + (quando couber) export.
2. Não reabrir motor estável sem gap real.
3. Dependências: **5.1 → 5.15/5.11/5.12** · **5.3/5.8 → 5.13** · **5.2/5.4 → apps**.
4. Gate por onda: ≥ 85% dos itens **da onda** em `[x]` **ou** lista ≤ 15% com plano ≤ 30 dias.
5. Preferir **exportação unificada** (`ExportacaoModulo`) a Jasper avulso.

---

## Rodada 2 — Ondas 6–11 (resumo)

| Onda | Foco | Status |
| ---: | --- | --- |
| 6 | Auth 4.x + 5.6/5.7/5.5/5.9 | parcial · `docs/onda-6` |
| 7 | Alimentação 5.2 + ACT 5.15 | parcial · `docs/onda-7` |
| 8 | RH 5.1 + horário 5.11 + eventos 5.12 | shell · `docs/onda-8` |
| 9 | Escolar / fila / transporte / censo | 5.8 OK · `docs/onda-9` |
| 10 | Apps 5.13/5.14 + DOC Android | entregue · `docs/onda-10` |
| 11 | Endurecimento + LibreSign + demo | documental · `docs/onda-11` |

Detalhe histórico das ondas 6–11 permanece nas seções abaixo (compacto) e nos `STATUS.md`.

### Onda 6 — Auth + ganhos rápidos (parcial)

Gaps que **migram para Onda 12:** 4.4–4.10, 5.7 restantes, 5.5/5.9 `[ ]`.

### Onda 7 — Alimentação (parcial)

Gaps que **migram para Onda 13:** 5.2.10/14/15–49/50.x/51.x; 5.15 restantes → Onda 14 junto com RH.

### Onda 8 — RH shell

Gaps que **migram para Ondas 14:** 5.1 relatórios/avaliação; 5.11; 5.12.

### Onda 9 — Volume

5.8 fechado (≥95% s/ WhatsApp). 5.3 / 5.4 / 5.10 → **Ondas 15–16**.

### Ondas 10–11

Contrato apps + DOC Android + demo/LibreSign documentados. Promoção **API** item a item 5.13/5.14 → **Onda 17** (sem build nativo).

---

## Rodada 3 — Ondas 12–18 (avançar o edital)

```
Onda 12  Fechar “rápidos” (4.x + 5.5 + 5.7 + 5.9)     ← exige laguna:setup
  → Onda 13  Alimentação 5.2 (estoque / financeiro / SISVAN)
  → Onda 14  RH 5.1 + horário 5.11 + eventos 5.12 + ACT 5.15
  → Onda 15  Escolar 5.3 — Lote A (núcleo + evidências)
  → Onda 16  Transporte 5.4 + Censo 5.10
  → Onda 17  Apps-API 5.13/5.14 evidência + DOC (sem APK; sem WhatsApp)
  → Onda 18  Hardening / N/A / demo SME + carga verde
```

---

## Onda 12 — Plataforma e módulos “quase lá”

**Status:** em evidência · `packages/buriti/docs/onda-12/STATUS.md`  
**Objetivo:** fechar leftovers da Onda 6 e subir 5.5 / 5.7 / 5.9 para ≥ 85% ou plano 15%.

| Passo | Escopo | Trabalho |
| ---: | --- | --- |
| 12.1 | **4.7 / 4.8 / 4.10** | Bloqueio grupo/unidade; vínculo usuário↔pessoa; kill sessão multi-nó (Redis) |
| 12.2 | **4.4 / 4.5 / 4.6 / 4.9** | Sessão múltipla; unificação cadastros (alerta); correio interno mínimo; troca de unidade |
| 12.3 | **5.7** | Pedidos/fatores/remessas avançadas (5.7.1, 5.7.5, 5.7.8–10, 5.7.12–14) + export |
| 12.4 | **5.5** | Relatórios e fluxos biblioteca faltantes (18 `[ ]`) |
| 12.5 | **5.9** | Relatórios + encaminhamento/alternância (15 `[ ]`) + export unificada |

**Gate 12:** 4.x ≥ 7/10 `[x]` · 5.5 e 5.9 ≥ 85% **ou** ≤ 15% com plano 30 dias · 5.7 ≥ 85% **ou** plano.

**Pacotes:** `laguna-ambiente`, `almoxarifado-escolar`, `biblioteca`, `atendimento-estudante`, core auth.

---

## Onda 13 — Alimentação: estoque, consumo, SISVAN

**Status:** em evidência · `packages/buriti/docs/onda-13/STATUS.md`  
**Objetivo:** promover `[~]` 5.2 → `[x]` e atacar estoque/financeiro/app entregas.

| Passo | Escopo | Trabalho |
| ---: | --- | --- |
| 13.1 | **5.2.10 / 5.2.14** | Seed TACO/IBGE + mapa mensal consumo/incidentes |
| 13.2 | **5.2.21–24 / 5.2.33** | UI sobras, indicadores, gráficos, entregas previstas |
| 13.3 | **5.2.15–20** | Consumo fino / transferências entre escolas |
| 13.4 | **5.2.25–49** | NF-e, financeiro, saldo, prestações (priorizar P0 SME) |
| 13.5 | **5.2.50.x / 51.x** | SISVAN + app entregas (API `equipe` / apps-api) |

**Gate 13:** 5.2 ≥ 85% `[x]` **ou** ≤ 15% com plano 30 dias assinado com SME.

**Pacote:** `i-educar-alimentacao-escolar-package` (+ apps-api para 51.x).

---

## Onda 14 — RH, horário, eventos e ACT

**Status:** em evidência · `packages/buriti/docs/onda-14/STATUS.md`  
**Objetivo:** sair do “shell” da Onda 8 para PoC demonstrável nos quatro módulos.

| Passo | Escopo | Trabalho |
| ---: | --- | --- |
| 14.1 | **5.1** | Relatórios TR 5.1.10–25; licenças; adicionais; avaliação 5.1.33–39; write-back lotação |
| 14.2 | **5.11** | Promover 5.11.1/4/5 → `[x]`; restrições 5.11.6–16; publicação grade |
| 14.3 | **5.12** | Hot site, QR frequência, inscrição/presença + **onda de produto Certificados** (abaixo) |
| 14.4 | **5.15** | Gaps ACT: e-mail massa, consulta pública resultado, recursos/fases restantes |

**Gate 14:** cada um de 5.1 / 5.11 / 5.12 / 5.15 ≥ 85% **ou** plano 15% por módulo.

**Pacotes:** `rh-educacional`, `horario-automatico`, `eventos-formacao`, `contratacoes-temporarias` · certificados: `assinatura-digital` + `exportacao-unificada`.

### Onda de produto — Certificados (5.12) · fatia LibreSign

> Fecha o escopo TR de **assinatura eletrônica + validação + liberação condicionada** **e** extras que estavam fora: feira/banca, gráficos anuais e leitor de barras.  
> IDs-alvo: **5.12.5 · 5.12.7 · 5.12.13 · 5.12.17 · 5.12.20–24 · 5.12.33–35** (+ 5.12.15/16/26).

#### Já existia (PoC base)

| Capacidade | Evidência |
| --- | --- |
| Mínimo de frequência (padrão **75%**) | `CertificadoPresenca::elegivel` · `EVENTOS_FREQ_MIN_CERT` |
| Emissão SME / portal + código `CERT-…` | controllers + `/eventos-formacao/certificados` |

#### Pipeline (ordem de execução)

```
C1 Modelos + tags → C2 PDF exportação → C3 LibreSign (unitário+lote) → C4 Validador
→ C5 Liberação (evento concluído + questionário) → C6 Frequência UI → C7 Meus certificados + notif
→ C8 Feira/banca (5.12.22–23) → C9 Gráficos anuais (5.12.24) → C10 Leitor barras (5.12.17)
```

| Passo | Escopo TR | Trabalho |
| ---: | --- | --- |
| C1 | **5.12.5** / 5.12.13 | Modelos + tags (programa, ministrante(s), evento, pessoa, doc, via, URL) |
| C2 | **5.12.13** / 5.12.15–16 | PDF via **exportação unificada**; declaração de presença |
| C3 | **5.12.13** | LibreSign unitário + **lote** |
| C4 | **5.12.13** | Validador público (código CERT + UUID LibreSign) |
| C5 | **5.12.7** / 5.12.20–21 / 5.12.34 | Evento concluído **e** questionário (participante / palestrante) |
| C6 | **5.12.35** | Gate frequência configurável + evidência UI |
| C7 | **5.12.26** / 5.12.33 | «Meus certificados» + notificações |
| C8 | **5.12.22** / 5.12.23 | Feira: trabalhos + banca avaliadora + alunos/orientadores |
| C9 | **5.12.24** | Gráficos / resumo anual de certificações |
| C10 | **5.12.17** | Leitura código de barras entrada/saída |

**Dependências:** `assinatura-digital` · `exportacao-unificada` · ACL eventos.  
**Gate:** IDs acima em `[x]`/`[~]` com evidência + demo: emitir → assinar lote → validar → Meus certificados · leitor · feira · gráfico.

---

## Onda 15 — Escolar (5.3) Lote A

**Status:** inventário entregue · `packages/buriti/docs/onda-15/STATUS.md` + `INVENTARIO-5.3.md`  
**Objetivo:** atacar o maior volume (198 itens). Estratégia: **evidenciar núcleo i-Educar + i-Diário** e fechar gaps Buriti/assinatura.

| Passo | Escopo | Trabalho |
| ---: | --- | --- |
| 15.1 | **Inventário 5.3** | Classificar cada `[ ]` em: núcleo OK / Buriti gap / N/A / app |
| 15.2 | **Pedagógico** | Frequência, notas, boletins, atas — evidência URL/print + export |
| 15.3 | **Matrícula / turmas** | Fluxos matrícula, enturmação, remanejamento (cruzar estudo em `ESTUDO-FLUXO-…`) |
| 15.4 | **5.3.87** | Assinatura digital LibreSign — PoC UI (além do doc Onda 11) |
| 15.5 | **Plano 15%** | Lista ≤ 30 dias dos gaps reais pós-inventário |

**Gate 15:** ≥ 50% do 5.3 em `[x]`/`[~]` com evidência **e** plano 30 dias para o restante · inventário 100% classificado.

**Pacotes:** núcleo i-Educar · `escolar-aderencia` · `assinatura-digital` · i-Diário.

---

## Onda 16 — Transporte (5.4) + Censo (5.10)

**Status:** censo export 2025/2026 **validado** · transporte em evidência · `packages/buriti/docs/onda-16/STATUS.md`

| Passo | Escopo | Trabalho |
| ---: | --- | --- |
| 16.1 | **5.4** | Promover `[~]` → `[x]`; frota, rotas, frequência, avisos, empenho; app `/familia/transporte` |
| 16.2 | **5.4 export** | Relatórios TR via `ExportacaoModulo` + CSV nativo · fixtures Onda 16 |
| 16.3 | **5.10** | **OK prova:** layouts 2025/2026; `EducacensoExportValidator`; `tests/export-run.php` 23 ok; doc `EXPORT-LAYOUTS-2025-2026.md`; UI checklist residual (5.10.13–17/80) |
| 16.4 | **Integração** | Transporte × aluno/escola estável para demo |

**Gate 16:** 5.4 e 5.10 ≥ 85% **ou** plano 15% cada.

**Pacotes:** `transporte-escolar-v2`, `educacenso-import`.

---

## Onda 17 — Apps-API (5.13/5.14) + DOC técnico — **sem build nativo**

**Status:** **em evidência** · `packages/buriti/docs/onda-17/STATUS.md` + `EVIDENCIA-API.md` (`coberturaTr` **100%**)  
**Objetivo:** transformar contrato Onda 10 em `[x]` na matriz por **evidência de API**; entregar/atualizar DOC para a equipe mobile.  
**Fora de escopo Buriti nesta onda:** criar app Android/iOS, publicar lojas, WhatsApp.

| Passo | Escopo | Trabalho |
| ---: | --- | --- |
| 17.1 | **5.13** | Evidência por ID via `/api/apps/v1/familia/*` + `GET /catalogo` |
| 17.2 | **5.14** | Idem professor: chamada, notas, sync, horário, eventos, histórico |
| 17.3 | **Equipe** | Rotas Sanctum `equipe` (almox/biblioteca/ACT) na matriz |
| 17.4 | **5.8.33 / 5.8.120** | WhatsApp — **N/A / pulado** (não bloqueia gate) |
| 17.5 | **DOC + OpenAPI** | Revisar `DOC-TECNICO-APP-ANDROID-UNIFICADO.md` + `openapi-apps-v1.yaml` para handoff |

**Gate 17 (Buriti):** 5.13 e 5.14 ≥ 85% por **contrato API** **ou** plano 15% · DOC/OpenAPI revisados · WhatsApp explícito como N/A.  
**Pré-requisito:** carga Laguna (`CARGA-DADOS-AMBIENTE.md`) com pessoas para Sanctum.

**Pacote:** `apps-api` · DOC em `i-educar-apps-api-package/docs/`.

---

## Onda 18 — Hardening, N/A e demo final

**Status:** **em evidência** · `packages/buriti/docs/onda-18/STATUS.md` (`laguna:check` 8/8)  
**Objetivo:** fechar a PoC municipal Buriti (`GATE-100-POC.md`) com gaps residuais controlados.

| Passo | Trabalho |
| ---: | --- |
| 18.1 | Recalcular matriz; WhatsApp e lojas como N/A/handoff; demais N/A com ata SME |
| 18.2 | Roteiro demo SME (sem WhatsApp / sem APK; módulos ≥ 85% + gaps confessados) |
| 18.3 | `laguna:check` verde; **carga + simulação**; skins; export smoke |
| 18.4 | Plano 30 dias único dos `[ ]` remanescentes (≤ 15% do total ou justificado) |
| 18.5 | Pacote evidências (prints/URLs/API) por módulo em `docs/onda-N/` |

**Gate 18 (PoC municipal Buriti):** gaps totais ≤ 15% com plano ≤ 30 dias **ou** ≥ 85% `[x]` nos módulos obrigatórios — **excluindo** WhatsApp e build de lojas.

---

## Prioridade sugerida (se SME cortar escopo)

| Prioridade | Ondas | Por quê |
| --- | --- | --- |
| P0 sanção | **19 → 20 → 22** | Plataforma + merenda + transporte (demo SME) |
| P1 volume | **21 → 23** | Escolar núcleo + RH/horário/eventos/ACT |
| P2 fechar R4 | **24** | Recalcular matriz + demo 2 |
| P3 matriz apps | **27** | Promover 5.13/5.14 `[x]` (API já OK · sem APK) |
| P4 R6 | **28–32** | Residuais P1 + gate final |
| — | WhatsApp / APK | **Fora do gate** · Rodada 5 se SME pedir |

---

## Rodada 4 — Ondas 19–24 (sanar planos 15% + 5.3 núcleo)

> **Estado:** **pronta para execução** · STATUS detalhados em `docs/onda-19`…`24`.  
> **Premissa:** R3 deixou hubs/exports em evidência; R4 **executa** os planos 30 dias e promove o volume 5.3.  
> **Volume:** ~**447** IDs sanáveis (excl. 5.13/5.14 → Onda 27; excl. WA). Ver `BACKLOG-SANCAO-SEM-WA-APK.md`.  
> **Meta R4:** total matriz **≥ 75–80% `[x]`** (ou N/A) · módulos obrigatórios ≥ 90% · 5.8 fechado s/ WhatsApp.  
> **Fora:** WhatsApp · build Android/iOS (Rodada 5).

```
Onda 19  4.x + 5.5 + 5.7 + 5.9          (~48 IDs)
  → Onda 20  Alimentação 5.2 Lote B       (~40)
  → Onda 21  Escolar 5.3 Lote B           (~198 · evidência núcleo + Buriti)
  → Onda 22  Transporte 5.4 + Censo 5.10  (~56)
  → Onda 23  RH 5.1 + 5.11 + 5.12 + 5.15  (~105)
  → Onda 24  Recalcular · demo SME 2 · residual → R6
```

**Ordem prática R4:** **19 → 20 → 22 → 21 → 23 → 24** (P0 demo: plataforma, merenda, transporte, depois volume escolar/RH).

### Onda 19 — Fechar leftovers “rápidos”

**Status:** pronta · `docs/onda-19/STATUS.md`  
**P0:** 4.9/4.10 · 5.7.8/10 · 5.5.13/15 · 5.9.20/25  
**Gate 19:** 4.x ≥ 9/10 · 5.5 / 5.7 / 5.9 ≥ 90% **ou** N/A.

### Onda 20 — Alimentação Lote B

**Status:** pronta · `docs/onda-20/STATUS.md`  
**P0:** 5.2.14/24/33 · transferências 5.2.25 · API entregas  
**Gate 20:** 5.2 ≥ 90% **ou** ≤ 10% ata SME.

### Onda 21 — Escolar 5.3 Lote B

**Status:** pronta · `docs/onda-21/STATUS.md`  
**P0:** checklist demo núcleo → `[x]` em lote · LibreSign 5.3.85–87 · uniformes  
**Gate 21:** 5.3 ≥ 85% evidenciado · Buriti ≤ 15% com plano.

### Onda 22 — Transporte fino + Censo UI

**Status:** pronta · `docs/onda-22/STATUS.md`  
**P0:** promover censo `[~]` com UI · frota/rotas/frequência  
**Gate 22:** 5.4 e 5.10 ≥ 90% **ou** plano ≤ 10%.

### Onda 23 — RH / horário / eventos / ACT

**Status:** pronta · `docs/onda-23/STATUS.md`  
**P0:** publicar grade · ACT público/e-mail · **onda Certificados C1–C7** (modelos→PDF→LibreSign→validador→Meus certificados) · lote relatórios RH  
**Gate 23:** cada módulo ≥ 90% **ou** plano ≤ 10%.

### Onda 24 — Recalcular, N/A e demo 2

**Status:** pronta · `docs/onda-24/STATUS.md`  
**Gate 24:** `GATE-100-POC.md` revalidado · residual sanável → **Rodada 6**.

---

## Rodada 5 — Opcional SME (fora do gate Buriti)

```
Onda 25  WhatsApp 5.8.33 / 5.8.120 (só se SME priorizar)
Onda 26  Handoff lojas / acompanhamento equipe mobile (APK/IPA externos)
```

**Status:** planejada · `docs/onda-25/STATUS.md` · `docs/onda-26/STATUS.md`  
**Não bloqueia** PoC municipal nem 5.8 (já ≈ 100% s/ WhatsApp — `STATUS-5.8-TR.md`).  
**Não entra** na sequência de sanção R4/R6.

---

## Rodada 6 — Ondas 27–32 (segunda sanção · sem WA / sem APK)

> **Para quê:** fechar o que a R4 não zerar (promoção API na matriz, leftovers P1, Buriti fino, gate final).  
> **Meta R6:** módulos obrigatórios ≥ **95%** `[x]`/N/A · gaps confessados ≤ **5–10%** · demo SME 3.  
> **Fora:** WhatsApp · APK/IPA/lojas.

```
Onda 27  Promover 5.13/5.14 na matriz (API Onda 17 já OK)   (~43 IDs)
  → Onda 28  Auth/plataforma residual pós-19
  → Onda 29  Alimentação SISVAN / NF-e / financeiro residual
  → Onda 30  Escolar Buriti (LibreSign UI + uniformes + prints)
  → Onda 31  Transporte / Censo / RH residual
  → Onda 32  Gate final Buriti + demo SME 3
```

| Onda | Foco | STATUS |
| ---: | --- | --- |
| 27 | Matriz apps-API (sem build nativo) | `docs/onda-27/STATUS.md` |
| 28 | 4.x / 5.5 / 5.7 / 5.9 residual | `docs/onda-28/STATUS.md` |
| 29 | 5.2 SISVAN / NF-e / financeiro | `docs/onda-29/STATUS.md` |
| 30 | 5.3 Buriti + evidências núcleo | `docs/onda-30/STATUS.md` |
| 31 | 5.4 / 5.10 / 5.1 / 5.11 / 5.12 / 5.15 | `docs/onda-31/STATUS.md` |
| 32 | Recalcular + `laguna:check` + demo 3 | `docs/onda-32/STATUS.md` · **em evidência** |

**Ordem prática R6:** **27 → 30 → 28 → 31 → 29** (promover IDs) · **32** fechou ambiente/snapshot/demo (2026-11-27).

---

## Sequência executiva (visão única)

| Rodada | Ondas | Objetivo | Estado |
| --- | --- | --- | --- |
| 0–1 | 0–5 | Prova → apps contrato → governança | entregue |
| 2 | 6–11 | Motores / shells / DOC | parcial/entregue |
| **3** | **12–18** | Gate PoC ≥ 85% / módulo | **em evidência** |
| **4** | **19–24** | Sanar planos 15% + 5.3 núcleo → `[x]` | **pronta** |
| 5 | 25–26 | WhatsApp / lojas (opcional) | fora do gate |
| **6** | **27–32** | Segunda sanção + gate final (s/ WA/APK) | **32 em evidência** · 27–31 planejadas |

**Ordem prática agora:** promover IDs nas Ondas **27→30→28→31→29** (subir % `[x]`); residual só **R5** (WA/lojas) ou [`onda-32/PLANO-30-DIAS.md`](../onda-32/PLANO-30-DIAS.md).

---

## Como atualizar

1. Ao fechar passo: `[~]`/`[ ]` → `[x]` na `MATRIZ-REQUISITOS-TR.md` (N/A com nota âmbar).
2. Recalcular snapshot desta página + headers de seção da matriz.
3. Preencher `packages/buriti/docs/onda-N/STATUS.md` com URL/arquivo/export.
4. Atualizar `ORDEM-DESENVOLVIMENTO-LAGUNA.md` se o grafo mudar.
5. Manter `tmp/edital-ocr/*.md` como symlinks para estes arquivos.
