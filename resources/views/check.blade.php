@extends('laguna-ambiente::layout')

@section('titulo', 'Verificar módulos')
@section('migalha', 'Verificar')
@section('icone-tela', 'fa-stethoscope')
@section('titulo-tela', 'Verificação e aprovação PoC')
@section('lead')
    Saúde técnica + decisão de aprovação por módulo — cores: funciona, ressalvas, não aprovado, precisa testar.
@endsection

@section('acoes-topo')
    <form method="post" action="{{ url('/laguna-ambiente/check/testar') }}" style="display:inline" data-confirm-testes>
        @csrf
        <button class="botao" type="submit" title="Roda tests/run.php de cada módulo e atualiza pendências">
            <i class="fa fa-play"></i> Rodar testes
        </button>
    </form>
    <a class="botao claro" href="{{ url('/laguna-ambiente/check') }}"><i class="fa fa-refresh"></i> Atualizar</a>
    <a class="botao claro" href="{{ url('/laguna-ambiente/setup') }}"><i class="fa fa-flask"></i> Setup</a>
    <a class="botao claro" href="{{ url('/laguna-ambiente') }}"><i class="fa fa-heartbeat"></i> Painel</a>
@endsection

@section('conteudo')
@php
    use iEducar\Packages\BuritiLagunaAmbiente\Support\AprovacaoModulo;
    use iEducar\Packages\BuritiLagunaAmbiente\Support\DataHora;
    $r = $relatorio['resumo'] ?? ['obrigatorios_ok' => 0, 'obrigatorios_total' => 0, 'falhas' => 0, 'avisos' => 0, 'por_situacao' => []];
    $porSit = $r['por_situacao'] ?? [];
    $pct = ($r['obrigatorios_total'] ?? 0) > 0
        ? (int) round(($r['obrigatorios_ok'] / $r['obrigatorios_total']) * 100)
        : 0;
    $foco = (string) request()->query('mod', '');
    $pendencias = $pendencias ?? [];
    $ultimoTeste = $ultimoTeste ?? [];
    $testePorId = $testePorId ?? [];
    $legenda = $relatorio['legenda'] ?? [
        AprovacaoModulo::meta(AprovacaoModulo::STATUS_FUNCIONA),
        AprovacaoModulo::meta(AprovacaoModulo::STATUS_RESSALVA),
        AprovacaoModulo::meta(AprovacaoModulo::STATUS_REPROVADO),
        AprovacaoModulo::meta(AprovacaoModulo::STATUS_TESTAR),
    ];
@endphp

    <div class="numeros">
        <article style="--tom: {{ ($relatorio['ok'] ?? false) ? 'var(--ok)' : 'var(--erro)' }}">
            <div class="kpi-ico"><i class="fa fa-heartbeat"></i></div>
            <span>Ambiente</span>
            <strong>{{ ($relatorio['ok'] ?? false) ? 'APTO' : 'PENDENTE' }}</strong>
        </article>
        <article style="--tom: #17663f">
            <div class="kpi-ico"><i class="fa fa-check-circle"></i></div>
            <span>Funciona</span>
            <strong>{{ $porSit['funciona'] ?? 0 }}</strong>
        </article>
        <article style="--tom: #a85f00">
            <div class="kpi-ico"><i class="fa fa-exclamation-circle"></i></div>
            <span>Com ressalvas</span>
            <strong>{{ $porSit['ressalva'] ?? 0 }}</strong>
        </article>
        <article style="--tom: #c74634">
            <div class="kpi-ico"><i class="fa fa-times-circle"></i></div>
            <span>Não aprovado</span>
            <strong>{{ $porSit['reprovado'] ?? 0 }}</strong>
        </article>
        <article style="--tom: #0369a1">
            <div class="kpi-ico"><i class="fa fa-flask"></i></div>
            <span>Precisa testar</span>
            <strong>{{ $porSit['testar'] ?? 0 }}</strong>
        </article>
        <article style="--tom: var(--destaque)">
            <div class="kpi-ico"><i class="fa fa-tachometer"></i></div>
            <span>Obrigatórios OK</span>
            <strong>{{ $pct }}%</strong>
        </article>
    </div>

    <div class="bloco" id="pendencias">
        <header class="grupo-mod-head" style="margin-bottom:12px">
            <div>
                <p class="sec-kicker-laguna">Diagnóstico</p>
                <h2 style="margin:0"><i class="fa fa-exclamation-triangle" style="color:{{ count($pendencias) ? '#c74634' : '#17663f' }}"></i> O que não está pronto</h2>
                <p style="margin:4px 0 0;color:var(--suave);font-size:.84rem">
                    Checks técnicos falhos, config incompleta e falhas do último <code>tests/run.php</code>.
                </p>
            </div>
            <span class="grupo-count">{{ count($pendencias) }} pendente{{ count($pendencias) === 1 ? '' : 's' }}</span>
        </header>

        @if($ultimoTeste !== [])
            <p class="teste-resumo-linha">
                Última bateria
                @if(!empty($ultimoTeste['em']))
                    · {{ DataHora::formatar($ultimoTeste['em']) }}
                @endif
                ·
                <span class="badge ok">{{ $ultimoTeste['resumo']['ok'] ?? 0 }} ok</span>
                <span class="badge {{ ($ultimoTeste['resumo']['falha'] ?? 0) > 0 ? 'fail' : 'muted' }}">{{ $ultimoTeste['resumo']['falha'] ?? 0 }} falha(s)</span>
                <span class="badge muted">{{ $ultimoTeste['resumo']['skip'] ?? 0 }} sem script</span>
            </p>
        @else
            <p class="teste-resumo-linha" style="color:var(--suave)">
                Ainda não há bateria de testes nesta sessão — use <strong>Rodar testes</strong> no topo.
            </p>
        @endif

        @forelse($pendencias as $p)
            <article class="pendencia-item grav-{{ $p['gravidade'] }}">
                <header>
                    <strong>
                        <a href="#mod-{{ $p['id'] }}" data-abrir-mod="{{ $p['id'] }}">{{ $p['nome'] }}</a>
                        <span class="badge muted">TR {{ $p['tr'] }}</span>
                    </strong>
                    <form method="post" action="{{ url('/laguna-ambiente/check/testar') }}" style="display:inline" data-confirm-testes>
                        @csrf
                        <input type="hidden" name="modulo_id" value="{{ $p['id'] }}">
                        <button class="botao claro" type="submit" style="min-height:32px;padding:4px 10px;font-size:.78rem">
                            <i class="fa fa-play"></i> Testar
                        </button>
                    </form>
                </header>
                <ul>
                    @foreach($p['motivos'] as $motivo)
                        <li>{{ $motivo }}</li>
                    @endforeach
                </ul>
            </article>
        @empty
            <p class="pendencia-vazia"><i class="fa fa-check-circle" style="color:#17663f"></i> Nenhum módulo com pendência detectada agora.</p>
        @endforelse
    </div>

    <div class="bloco">
        <h2><i class="fa fa-map-signs" style="color:var(--destaque)"></i> Legenda de aprovação</h2>
        <div class="legenda-aprovacao">
            @foreach($legenda as $L)
                <article>
                    <span class="dot {{ $L['tom'] }}"><i class="fa {{ $L['icone'] }}"></i></span>
                    <div>
                        <strong>{{ $L['rotulo'] }}</strong>
                        <span>{{ $L['ajuda'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>
        <p style="margin:0;color:var(--suave);font-size:.84rem">
            A cor do card combina a <strong>saúde técnica</strong> (pasta, provider, schema…) com a
            <strong>decisão de aprovação</strong> registrada abaixo. Expandir o card → ver checks e gravar o status PoC.
        </p>
    </div>

    @if(session('passos'))
        <div class="bloco">
            <h2><i class="fa fa-list-ol" style="color:var(--destaque)"></i> Último setup</h2>
            <table>
                <thead><tr><th>Passo</th><th>OK</th><th>Saída</th></tr></thead>
                <tbody>
                @foreach(session('passos') as $p)
                    <tr>
                        <td>{{ $p['passo'] }}</td>
                        <td><span class="badge {{ $p['ok'] ? 'ok' : 'fail' }}"><i class="fa {{ $p['ok'] ? 'fa-check' : 'fa-times' }}"></i> {{ $p['ok'] ? 'OK' : 'FAIL' }}</span></td>
                        <td><small>{{ $p['saida'] }}</small></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="bloco">
        <h2><i class="fa fa-server" style="color:var(--destaque)"></i> Núcleo</h2>
        <div class="modulo-grid">
            @foreach($relatorio['nucleo'] as $c)
                <article class="modulo-card" style="--tom: {{ $c['ok'] ? '#17663f' : '#c74634' }}">
                    <header>
                        <span class="ico"><i class="fa {{ $c['ok'] ? 'fa-check' : 'fa-times' }}"></i></span>
                        <div>
                            <h3>{{ mb_strtoupper((string) $c['chave'], 'UTF-8') }}</h3>
                            <p class="tr">{{ $c['detalhe'] }}</p>
                        </div>
                    </header>
                    <span class="badge {{ $c['ok'] ? 'ok' : 'fail' }}">{{ $c['ok'] ? 'OK' : 'FAIL' }}</span>
                </article>
            @endforeach
        </div>
    </div>

    <div class="bloco">
        <header class="grupo-mod-head">
            <div>
                <p class="sec-kicker-laguna">PoC Laguna</p>
                <h2 style="margin:0"><i class="fa fa-cubes" style="color:var(--destaque)"></i> Módulos</h2>
                <p style="margin:4px 0 0;color:var(--suave);font-size:.84rem">
                    Clique no card para saúde detalhada e formulário de aprovação.
                </p>
            </div>
            <span class="grupo-count">{{ count($relatorio['modulos'] ?? []) }} módulos</span>
        </header>
        <div class="modulo-grid">
            @foreach($relatorio['modulos'] as $i => $m)
                @php
                    $sit = (string) ($m['situacao'] ?? 'testar');
                    $meta = $m['situacao_meta'] ?? AprovacaoModulo::meta($sit);
                    $aprov = $meta['aprovacao'] ?? AprovacaoModulo::de((string) $m['id']);
                    $aberto = $foco !== '' && $foco === (string) ($m['id'] ?? '');
                    $badgeClass = match ($meta['tom'] ?? 'test') {
                        'ok' => 'ok',
                        'warn' => 'warn',
                        'fail' => 'fail',
                        default => 'test',
                    };
                    $pctMod = (int) ($m['percentual'] ?? 0);
                    $pctTom = $pctMod >= 90 ? '#17663f' : ($pctMod >= 60 ? '#a85f00' : '#c74634');
                    $testeMod = $testePorId[(string) $m['id']] ?? null;
                    $checksFail = array_values(array_filter($m['checks'] ?? [], static fn ($c) => empty($c['ok'])));
                @endphp
                <div id="mod-{{ $m['id'] }}"
                     class="modulo-card is-clicavel sit-{{ $sit }}{{ $checksFail !== [] || (($testeMod['status'] ?? '') === 'falha') ? ' is-atencao' : '' }}"
                     style="animation-delay: {{ $i * 0.04 }}s"
                     aria-expanded="{{ $aberto ? 'true' : 'false' }}"
                     data-modulo-saude
                     data-modulo-id="{{ $m['id'] }}"
                     role="button"
                     tabindex="0">
                    <header>
                        <span class="ico"><i class="fa {{ $m['icone'] ?? 'fa-cube' }}"></i></span>
                        <div>
                            <h3>{{ mb_strtoupper((string) ($m['nome'] ?? ''), 'UTF-8') }}</h3>
                            <p class="tr">TR {{ $m['tr'] }} · {{ $m['nivel'] }}
                                @if(empty($m['habilitado'])) · flag desligada @endif
                            </p>
                        </div>
                        <span class="modulo-pct" style="--pct: {{ $pctMod }}; --tom: {{ $pctTom }}" title="{{ ($m['checks_ok'] ?? 0) }}/{{ ($m['checks_total'] ?? 0) }} checks OK">
                            <span>{{ $pctMod }}%</span>
                        </span>
                    </header>
                    <div class="modulo-meta">
                        <span class="badge {{ $badgeClass }}"><i class="fa {{ $meta['icone'] }}"></i> {{ $meta['rotulo'] }}</span>
                        <span class="badge muted"><i class="fa fa-stethoscope"></i> {{ ($m['checks_ok'] ?? 0) }}/{{ ($m['checks_total'] ?? count($m['checks'] ?? [])) }}</span>
                        @if($testeMod)
                            <span class="badge {{ ($testeMod['status'] ?? '') === 'ok' ? 'ok' : (($testeMod['status'] ?? '') === 'falha' ? 'fail' : 'muted') }}">
                                <i class="fa fa-flask"></i>
                                @if(($testeMod['status'] ?? '') === 'ok') teste OK
                                @elseif(($testeMod['status'] ?? '') === 'falha') teste falhou
                                @else sem script
                                @endif
                            </span>
                        @endif
                        @if($checksFail !== [])
                            <span class="badge fail">{{ count($checksFail) }} check{{ count($checksFail) === 1 ? '' : 's' }}</span>
                        @endif
                    </div>
                    <p class="modulo-hint"><i class="fa fa-chevron-down"></i> Saúde, testes e aprovação</p>

                    <div class="modulo-saude" onclick="event.stopPropagation()">
                        <p style="margin:0 0 8px;font-size:.78rem;color:var(--suave)">
                            Técnico: <strong>{{ AprovacaoModulo::meta($m['tecnico'] ?? 'testar')['rotulo'] }}</strong>
                            @if(!empty($aprov['em']))
                                · Decisão {{ DataHora::formatar($aprov['em']) }}@if(!empty($aprov['por'])) por {{ $aprov['por'] }}@endif
                            @endif
                        </p>

                        @if($checksFail !== [])
                            <div class="mod-nao-pronto">
                                <strong><i class="fa fa-times-circle" style="color:#c74634"></i> Não pronto</strong>
                                <ul>
                                    @foreach($checksFail as $chk)
                                        <li>
                                            {{ $chk['nome'] ?? 'Check' }}
                                            @if(!empty($chk['critico'])) <span class="badge fail">crítico</span> @endif
                                            — {{ $chk['detalhe'] ?? '' }}
                                        </li>
                                    @endforeach
                                    @if($testeMod && ($testeMod['status'] ?? '') === 'falha')
                                        <li>Teste: {{ mb_substr((string) ($testeMod['saida'] ?? 'falhou'), 0, 180) }}</li>
                                    @endif
                                </ul>
                            </div>
                        @elseif($testeMod && ($testeMod['status'] ?? '') === 'falha')
                            <div class="mod-nao-pronto">
                                <strong><i class="fa fa-times-circle" style="color:#c74634"></i> Não pronto</strong>
                                <ul><li>Teste: {{ mb_substr((string) ($testeMod['saida'] ?? 'falhou'), 0, 180) }}</li></ul>
                            </div>
                        @endif

                        <ul class="modulo-saude-lista">
                            @forelse(($m['checks'] ?? []) as $chk)
                                <li>
                                    <i class="fa {{ $chk['ok'] ? 'fa-check-circle ok' : 'fa-times-circle fail' }}" aria-hidden="true"></i>
                                    <span>
                                        <strong>{{ $chk['nome'] ?? 'Check' }}</strong>
                                        @if(!empty($chk['critico']) && empty($chk['ok']))
                                            <span class="badge fail" style="margin-left:4px">crítico</span>
                                        @endif
                                        — {{ $chk['detalhe'] ?? '' }}
                                    </span>
                                </li>
                            @empty
                                <li><i class="fa fa-info-circle" style="color:var(--suave)"></i> <span>Sem checks registrados.</span></li>
                            @endforelse
                        </ul>

                        @if($testeMod)
                            <p class="teste-mod-linha">
                                Último teste:
                                <span class="badge {{ ($testeMod['status'] ?? '') === 'ok' ? 'ok' : (($testeMod['status'] ?? '') === 'falha' ? 'fail' : 'muted') }}">
                                    {{ $testeMod['status'] ?? 'skip' }}
                                </span>
                                @if(($testeMod['ok'] ?? 0) || ($testeMod['falha'] ?? 0))
                                    · {{ $testeMod['ok'] ?? 0 }} ok / {{ $testeMod['falha'] ?? 0 }} falha(s)
                                @endif
                                @if(!empty($testeMod['script']))
                                    · <code>{{ $testeMod['script'] }}</code>
                                @endif
                            </p>
                        @endif

                        <div class="mod-acoes-rapidas">
                            <form method="post" action="{{ url('/laguna-ambiente/check/testar') }}" data-confirm-testes>
                                @csrf
                                <input type="hidden" name="modulo_id" value="{{ $m['id'] }}">
                                <button class="botao" type="submit"><i class="fa fa-play"></i> Rodar teste deste módulo</button>
                            </form>
                            @if(!empty($m['admin_url']))
                                <a class="modulo-link" href="{{ url($m['admin_url']) }}" target="_blank" rel="noopener">
                                    <i class="fa fa-external-link"></i> Abrir módulo
                                </a>
                            @endif
                        </div>

                        <form class="aprovacao-form form-grid" method="post" action="{{ url('/laguna-ambiente/check/aprovacao') }}">
                            @csrf
                            <input type="hidden" name="modulo_id" value="{{ $m['id'] }}">
                            <label>Decisão PoC
                                <select name="status" required>
                                    @foreach([
                                        AprovacaoModulo::STATUS_FUNCIONA => 'Aprovado · funciona',
                                        AprovacaoModulo::STATUS_RESSALVA => 'Aprovado · com ressalvas',
                                        AprovacaoModulo::STATUS_REPROVADO => 'Não aprovado',
                                        AprovacaoModulo::STATUS_TESTAR => 'Precisa ser testado',
                                    ] as $val => $lab)
                                        <option value="{{ $val }}" @selected(($aprov['status'] ?? 'testar') === $val)>{{ $lab }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="col-wide">Nota / ressalva
                                <input name="nota" value="{{ $aprov['nota'] ?? '' }}" placeholder="Ex.: schema OK, falta ensaio UI na escola X">
                            </label>
                            <div class="acoes">
                                <button class="botao" type="submit"><i class="fa fa-save"></i> Salvar aprovação</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
    (function () {
        function toggleCard(card, forcarAberto) {
            var aberto = card.getAttribute('aria-expanded') === 'true';
            var novo = forcarAberto === true ? true : (forcarAberto === false ? false : !aberto);
            document.querySelectorAll('[data-modulo-saude][aria-expanded="true"]').forEach(function (outro) {
                if (outro !== card) outro.setAttribute('aria-expanded', 'false');
            });
            card.setAttribute('aria-expanded', novo ? 'true' : 'false');
            if (novo) {
                try { history.replaceState(null, '', '#' + card.id); } catch (e) {}
            }
        }
        document.querySelectorAll('[data-modulo-saude]').forEach(function (card) {
            card.addEventListener('click', function (ev) {
                if (ev.target.closest('.modulo-saude')) return;
                toggleCard(card);
            });
            card.addEventListener('keydown', function (ev) {
                if (ev.key === 'Enter' || ev.key === ' ') {
                    ev.preventDefault();
                    toggleCard(card);
                }
            });
        });
        document.querySelectorAll('[data-abrir-mod]').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                var id = a.getAttribute('data-abrir-mod');
                var card = document.getElementById('mod-' + id);
                if (!card) return;
                ev.preventDefault();
                toggleCard(card, true);
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
        document.querySelectorAll('form[data-confirm-testes]').forEach(function (form) {
            form.addEventListener('submit', function (ev) {
                var unico = form.querySelector('input[name="modulo_id"]');
                var msg = unico && unico.value
                    ? 'Rodar tests/run.php deste módulo? Pode levar até ~90s.'
                    : 'Rodar tests/run.php de todos os módulos? Pode levar vários minutos.';
                if (!window.confirm(msg)) {
                    ev.preventDefault();
                    return;
                }
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Rodando…';
                }
            });
        });
        var hash = (location.hash || '').replace(/^#/, '');
        var queryMod = new URLSearchParams(location.search).get('mod');
        var alvoId = (hash && hash.indexOf('mod-') === 0) ? hash : (queryMod ? 'mod-' + queryMod : '');
        if (alvoId) {
            var alvo = document.getElementById(alvoId);
            if (alvo && alvo.hasAttribute('data-modulo-saude')) {
                alvo.setAttribute('aria-expanded', 'true');
                alvo.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    })();
    </script>
@endsection
