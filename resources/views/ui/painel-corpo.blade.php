{{--
  Corpo padrão de painel analítico Buriti.
  Variáveis:
    $kpis       — mapa rotulo=>valor OU lista [{rotulo,valor,icone?,tom?}]
    $atalhos    — lista [{url,icone,rotulo,meta?}]  (opcional; senão use slot)
    $destaques  — lista [{titulo,meta?,corpo?,icone?,tom?}]
    $analise    — lista [{titulo,meta?,badge?,badge_tom?,valor?,url?}]  tabela analítica
    $analiseTitulo — título do bloco análise (default: Análise)
    $resumo     — string|html contexto do módulo
    $fluxoTitulo — título dos atalhos (default: Fluxo)
--}}
@php
    $kpiLista = [];
    foreach (($kpis ?? []) as $k => $v) {
        if (is_array($v) && array_key_exists('valor', $v)) {
            $kpiLista[] = [
                'rotulo' => (string) ($v['rotulo'] ?? $k),
                'valor' => $v['valor'],
                'icone' => (string) ($v['icone'] ?? 'fa-circle-o'),
                'tom' => (string) ($v['tom'] ?? 'var(--cor)'),
            ];
        } else {
            $kpiLista[] = [
                'rotulo' => (string) $k,
                'valor' => $v,
                'icone' => 'fa-circle-o',
                'tom' => 'var(--cor)',
            ];
        }
    }
    $fluxoTitulo = $fluxoTitulo ?? 'Fluxo do módulo';
    $analiseTitulo = $analiseTitulo ?? 'Análise';
@endphp

<style>
    .painel-grade { display:grid; grid-template-columns: 1.4fr 1fr; gap:12px; align-items:start; }
    .painel-analise { width:100%; border-collapse:collapse; font-size:.86rem; }
    .painel-analise th, .painel-analise td { text-align:left; padding:9px 10px; border-bottom:1px solid var(--linha); vertical-align:top; }
    .painel-analise th { background:var(--clara); color:var(--destaque, var(--cor)); font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
    .painel-analise tr:hover td { background: color-mix(in srgb, var(--clara) 55%, var(--mix-branco, #fff)); }
    .painel-analise a { color:var(--link, var(--destaque, var(--cor))); font-weight:700; text-decoration:none; }
    html[data-buriti-modo="escuro"] .painel-analise th {
        background: var(--clara) !important;
        color: var(--destaque, var(--acento)) !important;
        border-color: var(--linha) !important;
    }
    html[data-buriti-modo="escuro"] .painel-analise td { color: var(--tinta); border-bottom-color: var(--linha); }
    html[data-buriti-modo="escuro"] .painel-analise tr:hover td {
        background: color-mix(in srgb, var(--clara) 75%, transparent) !important;
    }
    html[data-buriti-modo="escuro"] .painel-resumo { color: var(--suave) !important; }
    html[data-buriti-modo="escuro"] .destaque-item,
    html[data-buriti-modo="escuro"] .painel-grade .bloco {
        background: var(--superficie) !important;
        border-color: var(--linha) !important;
        color: var(--tinta) !important;
    }
    .painel-resumo { margin:0; color:var(--suave); font-size:.9rem; line-height:1.45; }
    @media (max-width: 960px) { .painel-grade { grid-template-columns: 1fr; } }
</style>

@if($kpiLista !== [])
    <div class="numeros">
        @foreach($kpiLista as $i => $kpi)
            <article style="--tom: {{ $kpi['tom'] }}; animation-delay: {{ $i * 40 }}ms">
                <div class="kpi-ico"><i class="fa {{ $kpi['icone'] }}"></i></div>
                <span>{{ $kpi['rotulo'] }}</span>
                <strong>{{ is_bool($kpi['valor']) ? ($kpi['valor'] ? 'Sim' : 'Não') : $kpi['valor'] }}</strong>
            </article>
        @endforeach
    </div>
@endif

<div class="painel-grade">
    <div>
        @if(!empty($atalhos))
            <div class="bloco">
                <h2><i class="fa fa-compass" style="color:var(--destaque)"></i> {{ $fluxoTitulo }}</h2>
                <ul class="atalhos">
                    @foreach($atalhos as $a)
                        <li>
                            <a href="{{ url($a['url']) }}">
                                <i class="fa {{ $a['icone'] ?? 'fa-arrow-right' }}"></i>
                                <span>{{ $a['rotulo'] }}@if(!empty($a['meta']))<small>{{ $a['meta'] }}</small>@endif</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($analise))
            <div class="bloco">
                <h2><i class="fa fa-bar-chart" style="color:var(--destaque)"></i> {{ $analiseTitulo }}</h2>
                <table class="painel-analise">
                    <thead>
                        <tr>
                            <th>Indicador</th>
                            <th>Detalhe</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analise as $row)
                            <tr>
                                <td>
                                    @if(!empty($row['url']))
                                        <a href="{{ url($row['url']) }}">{{ $row['titulo'] }}</a>
                                    @else
                                        <strong>{{ $row['titulo'] }}</strong>
                                    @endif
                                    @if(isset($row['valor']) && $row['valor'] !== '')
                                        <div style="font-size:1.1rem;font-weight:800;letter-spacing:-.02em;margin-top:2px">{{ $row['valor'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $row['meta'] ?? '' }}</div>
                                    @if(!empty($row['corpo']))
                                        <div style="color:var(--suave);font-size:.8rem;margin-top:2px">{{ $row['corpo'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($row['badge']))
                                        <span class="badge {{ $row['badge_tom'] ?? 'ok' }}">{{ $row['badge'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>
        @if(!empty($destaques))
            <div class="bloco">
                <h2><i class="fa fa-lightbulb-o" style="color:var(--ambar)"></i> Situação</h2>
                <div class="card-grid" style="grid-template-columns:1fr">
                    @foreach($destaques as $d)
                        <article class="card" style="--tom: {{ $d['tom'] ?? 'var(--cor)' }}">
                            <header>
                                <span class="ico"><i class="fa {{ $d['icone'] ?? 'fa-info' }}"></i></span>
                                <div>
                                    <h3>{{ $d['titulo'] }}</h3>
                                    <p class="meta">{{ $d['meta'] ?? '' }}</p>
                                </div>
                            </header>
                            {{ $d['corpo'] ?? '' }}
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!empty($resumo))
            <div class="bloco">
                <h2><i class="fa fa-info-circle"></i> Contexto</h2>
                <p class="painel-resumo">{!! $resumo !!}</p>
            </div>
        @endif
    </div>
</div>
