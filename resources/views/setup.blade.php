@extends('laguna-ambiente::layout')

@section('titulo', 'Setup Laguna')
@section('migalha', 'Setup')
@section('icone-tela', 'fa-flask')
@section('titulo-tela', 'Laboratório de povoamento')
@section('lead')
    Monta a rede viva de teste: município, matrículas, demos de todos os módulos e simulação de 20 pessoas.
@endsection

@section('acoes-topo')
    <a class="botao claro icone" href="{{ url('/laguna-ambiente/simulacao') }}" title="Simulação" aria-label="Simulação"><i aria-hidden="true" class="fa fa-users"></i></a>
    <a class="botao claro icone" href="{{ url('/laguna-ambiente/check') }}" title="Verificar depois" aria-label="Verificar depois"><i aria-hidden="true" class="fa fa-stethoscope"></i></a>
@endsection

@section('conteudo')
    <div class="bloco">
        <h2><i class="fa fa-play-circle" style="color:var(--destaque)"></i> Executar agora</h2>
        <form method="post" action="{{ url('/laguna-ambiente/setup') }}" style="display:grid;gap:10px;max-width:560px">
            @csrf
            <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="skip_municipal" value="1"> Pular <code>ieducar:setup</code></label>
            <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="skip_matriculas" value="1"> Pular <code>ieducar:matriculas</code></label>
            <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="skip_simulacao" value="1"> Pular simulação de 20 pessoas</label>
            <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="force_production" value="1"> Permitir em production (cuidado)</label>
            <button class="botao" type="submit"><i class="fa fa-flask"></i> Executar setup agora</button>
        </form>
        <p style="color:var(--suave);font-size:.85rem;margin:12px 0 0">
            CLI: <code>php artisan laguna:setup</code> · só simulação: <code>laguna:simular-pessoas</code>
            · só pré-matrícula: <code>prematricula:povoar</code>
        </p>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-road" style="color:var(--destaque)"></i> Roteiro do ensaio</h2>
        <ol class="passo-lista">
            <li><span class="n">1</span><div><strong>Base municipal</strong><br><small><code>ieducar:setup default-br</code> — estrutura da SME</small></div></li>
            <li><span class="n">2</span><div><strong>Cidade média + matrículas</strong><br><small>10+ escolas, turmas, alunos, usuários multi-ator</small></div></li>
            <li><span class="n">3</span><div><strong>Demos de todos os módulos</strong><br><small>alimentação, transporte, chamados, atendimento, biblioteca, almox, ACT, RH, eventos, horário, busca ativa, <strong>pré-matrícula</strong>…</small></div></li>
            <li><span class="n">4</span><div><strong>Simulação 20 pessoas</strong><br><small>Cenários aleatórios persistidos em <code>laguna.simulacao_*</code></small></div></li>
            <li><span class="n">5</span><div><strong>Verificação</strong><br><small>Tela Simulação + <code>laguna:check</code></small></div></li>
        </ol>
    </div>

    <div class="bloco" id="comandos-artisan">
        <h2><i class="fa fa-terminal" style="color:var(--destaque)"></i> Comandos Artisan — significado e ordem</h2>
        <p style="margin:0 0 12px;color:var(--suave);font-size:.88rem">
            Inventário dos Artisan do monorepo Laguna. O botão <strong>Executar setup agora</strong> (e <code>laguna:setup</code>)
            encadeia o pipeline principal. Catálogo completo:
            <code>packages/buriti/docs/laguna/COMANDOS-ARTISAN.md</code>
            · install: <code>…/INSTALACAO.md</code>.
        </p>

        <h3 style="margin:0 0 8px;font-size:.95rem">1. Ordem de instalação / homologação</h3>
        <ol class="passo-lista" style="margin-bottom:18px">
            <li><span class="n">0</span><div><strong><code>laguna:otimizar</code></strong><br><small>Runtime no DB Laguna + caches (<code>--sem-docker</code> se containers já estão no ar).</small></div></li>
            <li><span class="n">1</span><div><strong><code>migrate --force</code></strong><br><small>Schemas dos pacotes liberados no tenant.</small></div></li>
            <li><span class="n">2</span><div><strong><code>laguna:check</code></strong><br><small>Auditoria pré-carga.</small></div></li>
            <li><span class="n">3</span><div><strong><code>laguna:setup</code></strong> / <strong><code>laguna:ensaiar</code></strong><br><small>Carga completa (ensaiar = check → setup → recheck).</small></div></li>
            <li><span class="n">4</span><div><strong><code>laguna:idiario</code></strong><br><small>Vínculos professores/turmas (+ opcional i-Diário).</small></div></li>
            <li><span class="n">5</span><div><strong>Demos / export avulsos</strong><br><small>Só se precisar re-ensaiar um módulo (lista abaixo).</small></div></li>
        </ol>

        <h3 style="margin:0 0 8px;font-size:.95rem">2. Orquestração Laguna</h3>
        <div class="cmd-tabela">
            <article><code>laguna:otimizar</code><span>Containers + DB Laguna + caches/runtime.</span></article>
            <article><code>laguna:check</code><span>Audita pacotes, menus e demos liberados.</span></article>
            <article><code>laguna:setup</code><span>Municipal → migrate → configs → cidade → demos → pré-matrícula → simulação.</span></article>
            <article><code>laguna:ensaiar</code><span>Atalho: check → setup → recheck.</span></article>
            <article><code>laguna:povoar-cidade</code><span>Só cidade média + cenários (escolas/turmas/servidores/usuários + demos embutidos).</span></article>
            <article><code>laguna:simular-pessoas</code><span>Lote de N pessoas (padrão 20) em percursos multi-módulo.</span></article>
            <article><code>laguna:idiario</code><span>Vínculo admin/professores ↔ turmas e (opcional) sobe o i-Diário.</span></article>
        </div>

        <h3 style="margin:16px 0 8px;font-size:.95rem">3. Flags do <code>laguna:setup</code></h3>
        <div class="cmd-tabela">
            <article><code>--skip-municipal</code><span>Não chama <code>ieducar:setup</code>.</span></article>
            <article><code>--skip-povoar</code><span>Rede mínima + demos avulsos (ainda chama <code>prematricula:povoar</code>).</span></article>
            <article><code>--skip-matriculas</code><span>Não gera matrículas de teste no núcleo.</span></article>
            <article><code>--skip-simulacao</code><span>Sem lote de 20 pessoas.</span></article>
            <article><code>--por-turma=12</code><span>Alunos por turma na carga pedagógica.</span></article>
            <article><code>--skip-check</code><span>Não reaudita ao final.</span></article>
            <article><code>--force-production</code><span>Permite em <code>APP_ENV=production</code> (cuidado).</span></article>
        </div>

        <h3 style="margin:16px 0 8px;font-size:.95rem">4. Setup municipal / núcleo (<code>ieducar:*</code>)</h3>
        <div class="cmd-tabela">
            <article><code>ieducar:setup</code><span>Perfil cidade-UF (<code>default-br</code> no Laguna) — estrutura SME.</span></article>
            <article><code>ieducar:matriculas</code><span>Matrículas/notas/faltas de <strong>teste</strong> (não usar em produção real).</span></article>
            <article><code>ieducar:idades-referencia</code><span>Idade esperada nas séries + etapa Educacenso nas turmas.</span></article>
            <article><code>ieducar:cadastro-metadados</code><span>Metadados escolares / vínculos Educacenso.</span></article>
            <article><code>ieducar:sequencia-enturmacao</code><span>Sequência série→série para enturmação.</span></article>
            <article><code>ieducar:acessos</code><span>Tipos de usuário + contas SME do perfil.</span></article>
            <article><code>ieducar:ensino-medio</code> / <code>ieducar:eja</code><span>Complementos pedagógicos (BNCC / EJA).</span></article>
            <article><code>ieducar:setup-municipio-base</code><span>Setup genérico BNCC/calendário/perfis (pacote município-base).</span></article>
        </div>
        <p style="margin:6px 0 0;color:var(--suave);font-size:.8rem">Demais <code>ieducar:*</code> (Amélia, continuidade letiva, infantil-padrão…): ver <code>COMANDOS-ARTISAN.md</code> § B.</p>

        <h3 style="margin:16px 0 8px;font-size:.95rem">5. Pré-matrícula / vagas (TR 5.8)</h3>
        <div class="cmd-tabela">
            <article><code>prematricula:install</code><span>Views SQL / settings do PMD (1ª vez ou reset).</span></article>
            <article><code>prematricula:povoar</code><span>8 processos + inscrições em todos os status (protocolo <code>LP*</code>).</span></article>
            <article><code>prematricula:povoar --fresh</code><span>Apaga PoC anterior e recria.</span></article>
            <article><code>prematricula:povoar --por-status=5</code><span>Mais volume por status (padrão 3).</span></article>
            <article><code>process:recalculate-priority {id}</code><span>Recalcula prioridade das inscrições.</span></article>
            <article><code>process:recalculate-grade {id}</code><span>Recalcula série sugerida.</span></article>
            <article><code>preregistration:auto-reject-summoned</code><span>Rejeita convocados vencidos (cron).</span></article>
        </div>

        <h3 style="margin:16px 0 8px;font-size:.95rem">6. Demos por módulo (ordem sugerida no re-ensaio)</h3>
        <div class="cmd-tabela">
            <article><code>alimentacao:demo</code><span>Cardápio + restrições / distribuição.</span></article>
            <article><code>transporte:simula</code><span>Operação de transporte com alunos matriculados.</span></article>
            <article><code>transporte:passagem-empenho-demo</code><span>Passagem/empenho (evidência PoC).</span></article>
            <article><code>busca-ativa:demo</code><span>Faltas, alertas e processos multi-etapa.</span></article>
            <article><code>solicitacao:demo</code><span>Chamados em todas as etapas.</span></article>
            <article><code>atendimento:demo</code><span>Fila de atendimento multi-situação.</span></article>
            <article><code>biblioteca:demo</code><span>Acervo e empréstimos.</span></article>
            <article><code>almoxarifado:demo</code><span>Catálogo, movimentos e pedidos.</span></article>
            <article><code>horario:demo</code><span>Quadro de horários.</span></article>
            <article><code>act:demo</code><span>Processo ACT com vagas/inscrições.</span></article>
            <article><code>eventos:demo</code><span>Eventos, presença e certificados.</span></article>
            <article><code>rh:demo</code><span>Validação demo de lotação/cessão.</span></article>
            <article><code>escolar:onda1-demo</code><span>Gabarito / uniforme / marca d’água.</span></article>
            <article><code>exportacao:demo-criticos</code><span>Relatórios críticos (custo / passageiros).</span></article>
            <article><code>exportacao:demo</code><span>Um relatório em múltiplos formatos (TR 4.2/4.3).</span></article>
        </div>

        <h3 style="margin:16px 0 8px;font-size:.95rem">7. Apps API · export Educacenso · relatórios · manutenção</h3>
        <div class="cmd-tabela">
            <article><code>apps-api:suspender-usuario</code> / <code>reativar-usuario</code><span>Suspende/reativa login + revoga tokens.</span></article>
            <article><code>apps-api:bloquear-municipio</code> / <code>liberar-municipio</code><span>Desliga/religa apps-api no município.</span></article>
            <article><code>educacenso:prova-export</code><span>Prova estrutural TXT Educacenso (00–60).</span></article>
            <article><code>advanced-reports:flush-menus</code><span>Limpa cache de menus dos Relatórios Avançados.</span></article>
            <article><code>advanced-reports:deploy-check</code><span>Checks de deploy do Advanced Reports.</span></article>
            <article><code>community:reports:install</code><span>Instala pacote de relatórios community.</span></article>
            <article><code>busca-ativa:detectar</code><span>Detecta infrequência e gera alertas (operacional).</span></article>
            <article><code>chamados:menu-cache-clear</code><span>Limpa cache de menus (itens no banco que não aparecem).</span></article>
        </div>

        <p style="margin:14px 0 0;color:var(--suave);font-size:.82rem">
            Catálogo com assinaturas: <code>packages/buriti/docs/laguna/COMANDOS-ARTISAN.md</code>
            · Carga: <code>…/CARGA-DADOS-AMBIENTE.md</code>
            · Install: <code>…/INSTALACAO.md</code>
            · UI pós-povoar: <a href="{{ url('/pre-matricula') }}">/pre-matricula</a>
            · <a href="{{ url('/pre-matricula/app/processos') }}">processos</a>
            · <a href="{{ url('/pre-matricula/app/inscricoes') }}">inscrições</a>
            · <a href="{{ url('/pre-matricula/app/consultar-escola') }}">consultar escola</a>
        </p>
    </div>

    @php $lote = $simulacao['lote'] ?? null; @endphp
    @if($lote)
        <div class="bloco">
            <h2><i class="fa fa-users" style="color:var(--destaque)"></i> Última simulação</h2>
            <p style="margin:0">
                Lote <strong>#{{ $lote['id'] }}</strong> · {{ $lote['pessoas'] }} pessoas · {{ $lote['eventos'] }} eventos · {{ $lote['created_at'] }}
                <a href="{{ url('/laguna-ambiente/simulacao') }}" style="margin-left:8px">ver percurso</a>
            </p>
        </div>
    @endif
@endsection
