# Gate 100% PoC Laguna — escopo Buriti (sem WhatsApp / sem apps nativos)

> Atualizado: **2026-11-28** (gate Buriti + papéis temporários)  
> Fonte: `MATRIZ-REQUISITOS-TR.md` · `ONDAS-PENDENTES-TR.md` · `STATUS-5.8-TR.md`  
> Ambiente / carga: `CARGA-DADOS-AMBIENTE.md` · índice: [`SOLUCAO-COMPLETA.md`](./SOLUCAO-COMPLETA.md)  
> Demo SME 3: [`../onda-32/ROTEIRO-DEMO.md`](../onda-32/ROTEIRO-DEMO.md) · Plano residual: [`../onda-32/PLANO-30-DIAS.md`](../onda-32/PLANO-30-DIAS.md)

## 1. O que conta como “100%” neste programa

| Inclui | Exclui (fora do gate Buriti) |
| --- | --- |
| Itens TR com evidência UI/API/`[x]` ou **N/A PoC** documentado (âmbar) | **WhatsApp** 5.8.33 / 5.8.120 |
| Exportação unificada + fixtures por onda | **Criação** de app Android/iOS (código nativo, Play/App Store) |
| Contrato **apps-api** 5.13/5.14 + OpenAPI + DOC técnico para a equipe mobile | Publicação nas lojas (`5.13-LOJA` / `5.14-LOJA`) |
| Carga de dados Laguna (`laguna:setup` / povoar / simulação / cenários) | Canais de messaging de terceiros |

**Regra de aceite PoC municipal (Buriti):**

1. Módulos obrigatórios do edital ≥ **85%** `[x]` **ou** ≤ 15% com plano ≤ 30 dias.  
2. **5.8** já fecha o gate sem WhatsApp ≈ **100%** (`STATUS-5.8-TR.md`).  
3. **5.13 / 5.14:** aceite Buriti = **API estável** + catálogo/OpenAPI + **DOC técnico** handoff. UI nativa = equipe externa.  
4. WhatsApp permanece `[ ]` explícito até SME pedir (não bloqueia 5.8 nem Onda 18).

---

## 2. Snapshot Onda 32 (2026-11-27)

| Camada | Situação |
| --- | --- |
| **Ambiente** | `laguna:check` **8/8** · falhas 0 · avisos 0 |
| **Carga / simulação** | `laguna:simular-pessoas --qtd=20` OK (lote #5 · 133 ok / 7 falta_teste) |
| **Export smoke** | `exportacao:demo` 10 formatos · `exportacao:demo-criticos` 6 arquivos |
| **Matriz bruta** | **656 `[x]` / 6 `[~]` / 2 `[ ]`** · **~98.8%** (664 itens) |
| **Módulos ≥ 85% `[x]`** | **5.6** 100% · **5.8** 98% (s/ WA) |
| **N/A / fora do gate** | WhatsApp + lojas → **Rodada 5** |
| **Residual sanável** | `BACKLOG-SANCAO-SEM-WA-APK.md` (residual WA + planos 4.9 / uniformes / LibreSign) · plano [`onda-32/PLANO-30-DIAS.md`](../onda-32/PLANO-30-DIAS.md) |

```text
Já OK / quase (matriz)
  5.6 Solicitação ……… 100%
  5.8 Fila …………… ~98% s/ WhatsApp
  apps-api ……… contrato Onda 3/5/10/17 + DOC Android
  Ambiente ……… laguna:check verde (Onda 32)

Rodada 6 — promoção de IDs (ainda planejada)
  27  5.13/5.14 na matriz (API já OK)
  28  4.x / 5.5 / 5.7 / 5.9 residual
  29  5.2 SISVAN / NF-e / financeiro
  30  5.3 Buriti (LibreSign UI + uniformes + prints)
  31  5.4 / 5.10 / RH residual
  32  Gate ambiente + snapshot + demo 3 ……… EM EVIDÊNCIA
```

**Leitura honesta do gate:** o **ambiente PoC** está apto para ensaio SME. A **cobertura TR por checkbox** ainda exige Ondas **27–31** (ou aceite do plano 30 dias). Não se declara ≥ 85% por módulo obrigatório além de 5.6/5.8.

---

## 3. Onda 17 redefinida (API + documento, sem APK)

| Passo | Escopo Buriti | Não fazer aqui |
| --- | --- | --- |
| 17.1 | Evidenciar cada ID 5.13 via `/api/apps/v1` + `GET /catalogo` | Telas Android/iOS |
| 17.2 | Idem 5.14 (professor / sync) | Empacotar APK/IPA |
| 17.3 | Perfil `equipe` na matriz (almox/biblioteca/ACT) | CameraX / FCM nativo |
| 17.4 | WhatsApp — **N/A / pulado** (só se SME priorizar) | Integração WhatsApp |
| 17.5 | Manter/atualizar `DOC-TECNICO-APP-ANDROID-UNIFICADO.md` + OpenAPI | Conta Play Console |

**Gate 17 (Buriti):** cobertura TR na API ≥ 85% **ou** plano 15%; DOC + OpenAPI revisados; lojas = handoff explícito.

Doc mobile: `packages/buriti/i-educar-apps-api-package/docs/DOC-TECNICO-APP-ANDROID-UNIFICADO.md`  
Roadmap handoff: `…/ROADMAP-ANDROID.md`

---

## 4. Dependência de carga de dados

Nenhuma demo SME fecha PoC sem rede viva. Ver **`CARGA-DADOS-AMBIENTE.md`**.

| Etapa | Precisa de |
| --- | --- |
| Prep demo | `laguna:check` verde |
| Carga | `laguna:setup` ou `laguna:ensaiar` |
| Simulação | `laguna:simular-pessoas --qtd=20` |
| Export | `exportacao:demo` + `exportacao:demo-criticos` |

---

## 5. Checklist de fechamento (Onda 32)

- [x] Matriz recalculada (headers + snapshot `ONDAS-PENDENTES`)  
- [x] WhatsApp / lojas marcados fora do gate (R5)  
- [x] `laguna:check` OK; simulação + export smoke OK  
- [x] Roteiro demo SME 3 sem WhatsApp e sem APK  
- [x] Plano 30 dias dos `[ ]` remanescentes sanáveis  
- [ ] Promoção em massa `[x]` nas Ondas 27–31 (sobe % por módulo)

---

## 6. Referências

| Doc | Papel |
| --- | --- |
| [ONDAS-PENDENTES-TR.md](./ONDAS-PENDENTES-TR.md) | Plano Ondas 12–32 |
| [CARGA-DADOS-AMBIENTE.md](./CARGA-DADOS-AMBIENTE.md) | Pipeline de carga / simulação |
| [STATUS-5.8-TR.md](../../../portabilis/pre-matricula-digital/docs/laguna/STATUS-5.8-TR.md) | Gate 5.8 sem WhatsApp |
| [ORDEM-DESENVOLVIMENTO-LAGUNA.md](../../ORDEM-DESENVOLVIMENTO-LAGUNA.md) | Grafo e checklist |
| [PE-77-2026-LAGUNA.md](../../PE-77-2026-LAGUNA.md) | Índice de pacotes |
| [../onda-32/STATUS.md](../onda-32/STATUS.md) | Gate final + evidências |
