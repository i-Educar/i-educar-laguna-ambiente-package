@extends('laguna-ambiente::layout')

@section('titulo', 'Ambiente Laguna')
@section('migalha', 'Painel')

@section('acoes-topo')
    <a class="botao" href="{{ url('/laguna-ambiente/check') }}"><i class="fa fa-stethoscope"></i> Verificar</a>
    <a class="botao claro" href="{{ url('/laguna-ambiente/setup') }}"><i class="fa fa-flask"></i> Setup</a>
@endsection

@section('icone-tela', 'fa-heartbeat')
@section('titulo-tela', 'Ambiente Laguna')
@section('lead')
    O que está aceito no ensaio PE-77 e o que já tem tela disponível neste município.
@endsection

@section('conteudo')
    @php
        $r = $relatorio['resumo'];
        $pct = $r['obrigatorios_total'] > 0
            ? (int) round(($r['obrigatorios_ok'] / $r['obrigatorios_total']) * 100)
            : 0;
        $tomSaude = $relatorio['ok'] ? 'var(--ok)' : ($pct >= 50 ? 'var(--ambar)' : 'var(--erro)');

        $modulos = collect($relatorio['modulos'] ?? [])->map(static function (array $m) use ($appsApi): array {
            $adminUrl = $m['admin_url'] ?? null;
            if (($m['id'] ?? '') === 'apps-api') {
                $adminUrl = $appsApi['url'] ?? url('/apps-api/configuracao');
            } elseif (is_string($adminUrl) && $adminUrl !== '') {
                $adminUrl = url($adminUrl);
            } else {
                $adminUrl = null;
            }

            $habilitado = class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class)
                ? \iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::ativo((string) ($m['id'] ?? ''))
                : true;

            return array_merge($m, [
                'admin_url' => $adminUrl,
                'disponivel_ui' => $adminUrl !== null,
                'habilitado' => $habilitado,
                'aceito' => (bool) ($m['ok'] ?? false) && $habilitado,
            ]);
        });

        $gruposNivel = [
            'obrigatorio' => ['titulo' => 'Obrigatórios', 'lead' => 'Precisam estar aceitos para o ensaio Laguna.'],
            'recomendado' => ['titulo' => 'Recomendados', 'lead' => 'Fortalecem a PoC; avisos não bloqueiam aptidão.'],
            'opcional' => ['titulo' => 'Opcionais', 'lead' => 'Disponíveis quando o pacote está no município.'],
        ];

        $aceitos = $modulos->where('aceito', true)->count();
        $comUi = $modulos->where('disponivel_ui', true)->count();
        $desligados = $modulos->where('habilitado', false)->count();
        $atencao = $modulos->where('aceito', false)->count();
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
                <span><i class="fa fa-check-circle" style="color:var(--ok)"></i> Aceito = auditoria OK e flag ativa</span>
                <span><i class="fa fa-external-link" style="color:var(--destaque)"></i> Disponível = tem tela admin neste município</span>
                <span><i class="fa fa-power-off" style="color:var(--ambar)"></i> Desligado = flag em Configuração de módulos</span>
            </p>
        </div>
    </div>

    <div class="numeros">
        <article style="--tom: {{ $tomSaude }}">
            <div class="kpi-ico"><i class="fa fa-heartbeat"></i></div>
            <span>Status</span>
            <strong>{{ $relatorio['ok'] ? 'APTO' : 'PENDENTE' }}</strong>
        </article>
        <article style="--tom: var(--ok)">
            <div class="kpi-ico"><i class="fa fa-check-square-o"></i></div>
            <span>Aceitos</span>
            <strong>{{ $aceitos }}/{{ $modulos->count() }}</strong>
        </article>
        <article style="--tom: var(--destaque)">
            <div class="kpi-ico"><i class="fa fa-desktop"></i></div>
            <span>Com tela</span>
            <strong>{{ $comUi }}</strong>
        </article>
        <article style="--tom: {{ $atencao ? 'var(--erro)' : 'var(--ok)' }}">
            <div class="kpi-ico"><i class="fa fa-exclamation-circle"></i></div>
            <span>Atenção</span>
            <strong>{{ $atencao }}</strong>
        </article>
    </div>

    @if(!empty($appsApi['disponivel']))
        <div class="bloco" style="border-top-color:#5c4d7a">
            <h2><i class="fa fa-mobile" style="color:#5c4d7a"></i> Apps API</h2>
            <p style="margin:0 0 12px;color:var(--suave)">Configuração Sanctum, funções família/professor e governança de tokens.</p>
            <div class="numeros" style="margin:0 0 12px">
                <article style="--tom:#5c4d7a">
                    <div class="kpi-ico"><i class="fa fa-power-off"></i></div>
                    <span>API</span>
                    <strong>{{ !empty($appsApi['enabled']) ? 'ON' : 'OFF' }}</strong>
                </article>
                <article style="--tom:#5c4d7a">
                    <div class="kpi-ico"><i class="fa fa-key"></i></div>
                    <span>Tokens</span>
                    <strong>{{ $appsApi['tokens'] ?? 0 }}</strong>
                </article>
                <article style="--tom:#5c4d7a">
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
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                <a class="botao" href="{{ $appsApi['url'] }}"><i class="fa fa-cog"></i> Configuração</a>
                <a class="botao claro" href="{{ $appsApi['governanca_url'] }}"><i class="fa fa-shield"></i> Governança</a>
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
                <span class="grupo-count">{{ $itens->where('aceito', true)->count() }}/{{ $itens->count() }} aceitos</span>
            </header>

            <div class="modulo-grid">
                @foreach($itens as $i => $m)
                    @php
                        $tom = ! $m['habilitado']
                            ? 'var(--ambar)'
                            : (($m['ok'] ?? false) ? (string) ($m['cor'] ?? 'var(--ok)') : 'var(--erro)');
                        $nomeUp = mb_strtoupper((string) ($m['nome'] ?? ''), 'UTF-8');
                    @endphp
                    <article class="modulo-card {{ $m['aceito'] ? 'is-aceito' : 'is-atencao' }} {{ $m['disponivel_ui'] ? 'has-ui' : 'no-ui' }}"
                             style="--tom: {{ $tom }}; animation-delay: {{ $i * 0.03 }}s">
                        <header>
                            <span class="ico"><i class="fa {{ $m['icone'] ?? 'fa-cube' }}"></i></span>
                            <div>
                                <h3>{{ $nomeUp }}</h3>
                                <p class="tr">TR {{ $m['tr'] }} · {{ $m['nivel'] }}</p>
                            </div>
                        </header>

                        <div class="modulo-badges">
                            @if(! $m['habilitado'])
                                <span class="badge ambar"><i class="fa fa-power-off"></i> Desligado</span>
                            @elseif($m['aceito'])
                                <span class="badge ok"><i class="fa fa-check"></i> Aceito</span>
                            @else
                                <span class="badge fail"><i class="fa fa-times"></i> Atenção</span>
                            @endif

                            @if($m['disponivel_ui'])
                                <span class="badge info"><i class="fa fa-desktop"></i> Disponível</span>
                            @else
                                <span class="badge muted"><i class="fa fa-cog"></i> Motor / CLI</span>
                            @endif
                        </div>

                        <div class="modulo-meta">
                            @if($m['disponivel_ui'])
                                <a class="modulo-link" href="{{ $m['admin_url'] }}">Abrir <i class="fa fa-arrow-right"></i></a>
                            @else
                                <span class="modulo-link muted">Sem tela admin</span>
                            @endif
                            <a class="modulo-link suave" href="{{ url('/laguna-ambiente/check') }}#mod-{{ $m['id'] }}">Detalhes</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="bloco">
        <h2><i class="fa fa-compass" style="color:var(--destaque)"></i> Atalhos do ensaio</h2>
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
                <a href="{{ url('/laguna-ambiente/tema') }}">
                    <i class="fa fa-paint-brush" style="background:#1f4e66"></i>
                    <span>Tema visual<small>Skin Buriti no core</small></span>
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
                <a href="{{ url('/apps-api/configuracao') }}">
                    <i class="fa fa-mobile" style="background:#5c4d7a"></i>
                    <span>Apps API<small>Config + governança</small></span>
                </a>
            </li>
            @foreach(($atalhosModulos ?? []) as $atalho)
                @if(!str_contains($atalho['url'], '/apps-api'))
                    <li>
                        <a href="{{ $atalho['url'] }}">
                            <i class="fa {{ $atalho['icone'] }}"></i>
                            <span>{{ $atalho['nome'] }}<small>{{ $atalho['meta'] }}</small></span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
        <p style="color:#5b6b76;font-size:.85rem;margin:12px 0 0">CLI: <code>php artisan laguna:check</code> · <code>laguna:setup</code> · <code>laguna:ensaiar</code></p>
    </div>
@endsection
