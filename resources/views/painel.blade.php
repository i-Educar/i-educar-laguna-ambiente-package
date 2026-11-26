@extends('laguna-ambiente::layout')

@section('titulo', 'Ambiente Laguna')
@section('migalha', 'Painel')
@section('icone-tela', 'fa-heartbeat')
@section('titulo-tela', 'Ambiente Laguna')
@section('lead')
    Saúde do ensaio PE-77 neste município — clique no módulo para ver os detalhes da auditoria.
@endsection

@section('acoes-topo')
    <a class="botao" href="{{ url('/laguna-ambiente/check') }}"><i class="fa fa-stethoscope"></i> Verificar</a>
    <a class="botao claro" href="{{ url('/laguna-ambiente/setup') }}"><i class="fa fa-flask"></i> Setup</a>
    <a class="botao claro" href="{{ url('/laguna-ambiente/simulacao') }}"><i class="fa fa-users"></i> Simulação</a>
@endsection

@section('conteudo')
    @php
        $r = $relatorio['resumo'];
        $pct = $r['obrigatorios_total'] > 0
            ? (int) round(($r['obrigatorios_ok'] / $r['obrigatorios_total']) * 100)
            : 0;
        $tomSaude = $relatorio['ok'] ? 'var(--ok)' : ($pct >= 50 ? 'var(--ambar)' : 'var(--erro)');

        $modulos = collect($relatorio['modulos'] ?? [])->map(static function (array $m) use ($appsApi): array {
            $habilitado = (bool) ($m['habilitado'] ?? (
                class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class)
                    ? \iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::ativo((string) ($m['id'] ?? ''))
                    : true
            ));

            $disponivelUi = ! empty($m['admin_url']);
            if (($m['id'] ?? '') === 'apps-api') {
                $disponivelUi = ! empty($appsApi['disponivel']) || $disponivelUi;
            }

            $sit = (string) ($m['situacao'] ?? 'testar');
            $meta = $m['situacao_meta'] ?? \iEducar\Packages\BuritiLagunaAmbiente\Support\AprovacaoModulo::meta($sit);

            return array_merge($m, [
                'disponivel_ui' => $disponivelUi,
                'habilitado' => $habilitado,
                'aceito' => $sit === 'funciona' && $habilitado,
                'situacao' => $sit,
                'situacao_meta' => $meta,
            ]);
        });

        $gruposNivel = [
            'obrigatorio' => ['titulo' => 'Obrigatórios', 'lead' => 'Precisam estar aceitos para o ensaio Laguna.'],
            'recomendado' => ['titulo' => 'Recomendados', 'lead' => 'Fortalecem a PoC; avisos não bloqueiam aptidão.'],
            'opcional' => ['titulo' => 'Opcionais', 'lead' => 'Disponíveis quando o pacote está no município.'],
        ];

        $porSit = $relatorio['resumo']['por_situacao'] ?? [];
        $aceitos = (int) ($porSit['funciona'] ?? $modulos->where('situacao', 'funciona')->count());
        $ressalvas = (int) ($porSit['ressalva'] ?? 0);
        $reprovados = (int) ($porSit['reprovado'] ?? 0);
        $testar = (int) ($porSit['testar'] ?? 0);
        $comUi = $modulos->where('disponivel_ui', true)->count();
        $desligados = $modulos->where('habilitado', false)->count();
    @endphp

    <div class="bloco painel-status" style="display:flex;flex-wrap:wrap;gap:18px;align-items:center">
        <div class="saude-anel" style="--pct: {{ $pct }}; --tom: {{ $tomSaude }}">
            <span>{{ $pct }}%</span>
        </div>
        <div style="flex:1;min-width:220px">
            <h2 style="margin:0 0 6px">{{ $relatorio['ok'] ? 'Ambiente APTO' : 'Ambiente INCOMPLETO' }}</h2>
            <p style="margin:0;color:var(--suave)">
                Obrigatórios {{ $r['obrigatorios_ok'] }}/{{ $r['obrigatorios_total'] }}
                · aceitos {{ $aceitos }}/{{ $modulos->count() }}
                · com tela {{ $comUi }}
                @if($desligados > 0)· desligados {{ $desligados }}@endif
                · falhas {{ $r['falhas'] }} · avisos {{ $r['avisos'] }}
            </p>
            <div style="margin-top:10px;height:8px;border-radius:99px;background:#e2eaf0;overflow:hidden">
                <i style="display:block;height:100%;width:{{ $pct }}%;background:linear-gradient(90deg, {{ $tomSaude }}, var(--destaque));border-radius:99px"></i>
            </div>
            <p class="legenda-status" style="margin:10px 0 0;display:flex;flex-wrap:wrap;gap:8px 14px;color:var(--suave);font-size:.78rem">
                <span><i class="fa fa-check-circle" style="color:#17663f"></i> Funciona</span>
                <span><i class="fa fa-exclamation-circle" style="color:#a85f00"></i> Ressalvas</span>
                <span><i class="fa fa-times-circle" style="color:#c74634"></i> Não aprovado</span>
                <span><i class="fa fa-flask" style="color:#0369a1"></i> Precisa testar</span>
            </p>
        </div>
    </div>

    <div class="numeros">
        <article style="--tom: {{ $tomSaude }}">
            <div class="kpi-ico"><i class="fa fa-heartbeat"></i></div>
            <span>Status</span>
            <strong>{{ $relatorio['ok'] ? 'APTO' : 'PENDENTE' }}</strong>
        </article>
        <article style="--tom: #17663f">
            <div class="kpi-ico"><i class="fa fa-check-circle"></i></div>
            <span>Funciona</span>
            <strong>{{ $aceitos }}</strong>
        </article>
        <article style="--tom: #a85f00">
            <div class="kpi-ico"><i class="fa fa-exclamation-circle"></i></div>
            <span>Ressalvas</span>
            <strong>{{ $ressalvas }}</strong>
        </article>
        <article style="--tom: #c74634">
            <div class="kpi-ico"><i class="fa fa-times-circle"></i></div>
            <span>Não aprovado</span>
            <strong>{{ $reprovados }}</strong>
        </article>
        <article style="--tom: #0369a1">
            <div class="kpi-ico"><i class="fa fa-flask"></i></div>
            <span>Testar</span>
            <strong>{{ $testar }}</strong>
        </article>
        <article style="--tom: var(--destaque)">
            <div class="kpi-ico"><i class="fa fa-desktop"></i></div>
            <span>Com tela</span>
            <strong>{{ $comUi }}</strong>
        </article>
    </div>

    @if(!empty($appsApi['disponivel']))
        <div class="bloco" style="border-top-color:var(--destaque)">
            <h2><i class="fa fa-mobile" style="color:var(--destaque)"></i> APIs (snapshot)</h2>
            <p style="margin:0 0 12px;color:var(--suave)">Resumo Sanctum neste município. Governança e flags ficam nas telas de administração Laguna.</p>
            <div class="numeros" style="margin:0 0 12px">
                <article style="--tom:var(--destaque)">
                    <div class="kpi-ico"><i class="fa fa-power-off"></i></div>
                    <span>API</span>
                    <strong>{{ !empty($appsApi['enabled']) ? 'ON' : 'OFF' }}</strong>
                </article>
                <article style="--tom:var(--destaque)">
                    <div class="kpi-ico"><i class="fa fa-key"></i></div>
                    <span>Tokens</span>
                    <strong>{{ $appsApi['tokens'] ?? 0 }}</strong>
                </article>
                <article style="--tom:var(--destaque)">
                    <div class="kpi-ico"><i class="fa fa-user-times"></i></div>
                    <span>Suspensos</span>
                    <strong>{{ $appsApi['suspensos'] ?? 0 }}</strong>
                </article>
                <article style="--tom: {{ !empty($appsApi['ok']) ? 'var(--ok)' : 'var(--erro)' }}">
                    <div class="kpi-ico"><i class="fa fa-heartbeat"></i></div>
                    <span>Módulo</span>
                    <strong>{{ !empty($appsApi['ok']) ? 'OK' : 'Atenção' }}</strong>
                </article>
            </div>
        </div>
    @endif

    @foreach($gruposNivel as $nivel => $meta)
        @php $itens = $modulos->where('nivel', $nivel)->values(); @endphp
        @if($itens->isEmpty())
            @continue
        @endif
        <div class="bloco">
            <header class="grupo-mod-head">
                <div>
                    <p class="sec-kicker-laguna">{{ $meta['titulo'] }}</p>
                    <h2 style="margin:0"><i class="fa fa-th-large" style="color:var(--destaque)"></i> {{ $meta['titulo'] }}</h2>
                    <p style="margin:4px 0 0;color:var(--suave);font-size:.84rem">{{ $meta['lead'] }}</p>
                </div>
                <span class="grupo-count">{{ $itens->where('situacao', 'funciona')->count() }}/{{ $itens->count() }} funciona</span>
            </header>

            <div class="modulo-grid">
                @foreach($itens as $i => $m)
                    @php
                        $sit = (string) ($m['situacao'] ?? 'testar');
                        $meta = $m['situacao_meta'] ?? \iEducar\Packages\BuritiLagunaAmbiente\Support\AprovacaoModulo::meta($sit);
                        $badgeClass = match ($meta['tom'] ?? 'test') {
                            'ok' => 'ok',
                            'warn' => 'warn',
                            'fail' => 'fail',
                            default => 'test',
                        };
                        $nomeUp = mb_strtoupper((string) ($m['nome'] ?? ''), 'UTF-8');
                        $pctMod = (int) ($m['percentual'] ?? 0);
                        $pctTom = $pctMod >= 90 ? '#17663f' : ($pctMod >= 60 ? '#a85f00' : '#c74634');
                    @endphp
                    <button type="button"
                            id="painel-mod-{{ $m['id'] }}"
                            class="modulo-card is-clicavel sit-{{ $sit }} {{ $m['disponivel_ui'] ? 'has-ui' : 'no-ui' }}"
                            style="animation-delay: {{ $i * 0.03 }}s"
                            aria-expanded="false"
                            data-modulo-saude
                            data-modulo-id="{{ $m['id'] }}">
                        <header>
                            <span class="ico"><i class="fa {{ $m['icone'] ?? 'fa-cube' }}"></i></span>
                            <div>
                                <h3>{{ $nomeUp }}</h3>
                                <p class="tr">TR {{ $m['tr'] }} · {{ $m['nivel'] }}</p>
                            </div>
                            <span class="modulo-pct" style="--pct: {{ $pctMod }}; --tom: {{ $pctTom }}" title="{{ ($m['checks_ok'] ?? 0) }}/{{ ($m['checks_total'] ?? 0) }} checks OK">
                                <span>{{ $pctMod }}%</span>
                            </span>
                        </header>

                        <div class="modulo-badges">
                            @if(! $m['habilitado'])
                                <span class="badge ambar"><i class="fa fa-power-off"></i> Desligado</span>
                            @endif
                            <span class="badge {{ $badgeClass }}"><i class="fa {{ $meta['icone'] }}"></i> {{ $meta['rotulo'] }}</span>

                            @if($m['disponivel_ui'])
                                <span class="badge info"><i class="fa fa-desktop"></i> Disponível</span>
                            @else
                                <span class="badge muted"><i class="fa fa-cog"></i> Motor / CLI</span>
                            @endif
                        </div>

                        <p class="modulo-hint"><i class="fa fa-chevron-down"></i> Ver saúde</p>
                        <div class="modulo-saude">
                            <ul class="modulo-saude-lista">
                                @forelse(($m['checks'] ?? []) as $chk)
                                    <li>
                                        <i class="fa {{ !empty($chk['ok']) ? 'fa-check-circle ok' : 'fa-times-circle fail' }}" aria-hidden="true"></i>
                                        <span>
                                            <strong>{{ $chk['nome'] ?? 'Check' }}</strong>
                                            — {{ $chk['detalhe'] ?? '' }}
                                        </span>
                                    </li>
                                @empty
                                    <li>
                                        <i class="fa fa-info-circle" style="color:var(--suave)"></i>
                                        <span>Sem checks neste relatório — abra Verificar para nova ausculta.</span>
                                    </li>
                                @endforelse
                            </ul>
                            <p style="margin:10px 0 0">
                                <a class="botao claro" href="{{ url('/laguna-ambiente/check') }}#mod-{{ $m['id'] }}"
                                   onclick="event.stopPropagation()">
                                    <i class="fa fa-stethoscope"></i> Abrir na verificação
                                </a>
                            </p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="bloco">
        <h2><i class="fa fa-compass" style="color:var(--destaque)"></i> Ferramentas do ambiente</h2>
        <ul class="atalhos">
            <li>
                <a href="{{ url('/laguna-ambiente/check') }}">
                    <i class="fa fa-stethoscope"></i>
                    <span>Verificação<small>Ausculta módulos e schema</small></span>
                </a>
            </li>
            <li>
                <a href="{{ url('/laguna-ambiente/setup') }}">
                    <i class="fa fa-flask" style="background:#0f6b4c"></i>
                    <span>Povoar teste<small>Setup + demos PoC</small></span>
                </a>
            </li>
            <li>
                <a href="{{ url('/laguna-ambiente/simulacao') }}">
                    <i class="fa fa-users" style="background:#1a5f9e"></i>
                    <span>Simulação 20 pessoas<small>Cenários aleatórios multi-módulo</small></span>
                </a>
            </li>
            <li>
                <a href="{{ url('/laguna-ambiente/tema') }}">
                    <i class="fa fa-paint-brush" style="background:#1f4e66"></i>
                    <span>Skins da escola<small>Buriti, Ébano, Horizonte, Órbita, Litoral</small></span>
                </a>
            </li>
            <li>
                <a href="{{ url('/laguna-ambiente/usuarios') }}">
                    <i class="fa fa-user-times" style="background:#9b2c2c"></i>
                    <span>Suspender usuários<small>Multi-select + auditoria</small></span>
                </a>
            </li>
            <li>
                <a href="{{ url('/configuracoes/modulos') }}">
                    <i class="fa fa-cubes" style="background:#475569"></i>
                    <span>Configurar módulos<small>Flags e diretivas</small></span>
                </a>
            </li>
            <li>
                <a href="{{ url('/apps-api/governanca') }}">
                    <i class="fa fa-shield" style="background:#0f6b5c"></i>
                    <span>Governança apps<small>Tokens e suspensões</small></span>
                </a>
            </li>
        </ul>
        <p style="color:var(--suave);font-size:.85rem;margin:12px 0 0">CLI: <code>php artisan laguna:check</code> · <code>laguna:setup</code> · <code>laguna:simular-pessoas</code> · <code>laguna:ensaiar</code></p>
    </div>

    <script>
    (function () {
        document.querySelectorAll('[data-modulo-saude]').forEach(function (card) {
            card.addEventListener('click', function (e) {
                if (e.target.closest && e.target.closest('a')) return;
                var aberto = card.getAttribute('aria-expanded') === 'true';
                document.querySelectorAll('[data-modulo-saude][aria-expanded="true"]').forEach(function (outro) {
                    if (outro !== card) outro.setAttribute('aria-expanded', 'false');
                });
                card.setAttribute('aria-expanded', aberto ? 'false' : 'true');
            });
        });
    })();
    </script>
@endsection
