@extends('laguna-ambiente::layout')

@section('titulo', 'Verificar módulos')
@section('migalha', 'Verificar')

@section('acoes-topo')
    <a class="botao" href="{{ url('/laguna-ambiente/check') }}"><i class="fa fa-refresh"></i> Atualizar</a>
@endsection

@section('conteudo')
    @php
        $r = $relatorio['resumo'] ?? ['obrigatorios_ok' => 0, 'obrigatorios_total' => 0, 'falhas' => 0, 'avisos' => 0];
        $pct = ($r['obrigatorios_total'] ?? 0) > 0
            ? (int) round(($r['obrigatorios_ok'] / $r['obrigatorios_total']) * 100)
            : 0;
    @endphp

    <div class="cabeca-tela">
        <span class="marca-tela"><i class="fa fa-stethoscope"></i></span>
        <div>
            <h1 style="margin:0">Ausculta dos módulos</h1>
            <p style="margin:2px 0 0;color:#5b6b76">Pasta, provider, PackageTenant, schema, menu e comandos — como um exame de rotina</p>
        </div>
    </div>

    <div class="numeros">
        <article style="--tom: {{ ($relatorio['ok'] ?? false) ? 'var(--ok)' : 'var(--erro)' }}">
            <div class="kpi-ico"><i class="fa fa-heartbeat"></i></div>
            <span>Resultado</span>
            <strong>{{ ($relatorio['ok'] ?? false) ? 'APTO' : 'FAIL' }}</strong>
        </article>
        <article style="--tom: var(--destaque)">
            <div class="kpi-ico"><i class="fa fa-tachometer"></i></div>
            <span>Cobertura</span>
            <strong>{{ $pct }}%</strong>
        </article>
        <article style="--tom: var(--ambar)">
            <div class="kpi-ico"><i class="fa fa-bell"></i></div>
            <span>Avisos</span>
            <strong>{{ $r['avisos'] ?? 0 }}</strong>
        </article>
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
                <article class="modulo-card" style="--tom: {{ $c['ok'] ? 'var(--ok)' : 'var(--erro)' }}">
                    <header>
                        <span class="ico"><i class="fa {{ $c['ok'] ? 'fa-check' : 'fa-times' }}"></i></span>
                        <div>
                            <h3>{{ $c['chave'] }}</h3>
                            <p class="tr">{{ $c['detalhe'] }}</p>
                        </div>
                    </header>
                    <span class="badge {{ $c['ok'] ? 'ok' : 'fail' }}">{{ $c['ok'] ? 'OK' : 'FAIL' }}</span>
                </article>
            @endforeach
        </div>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-cubes" style="color:var(--destaque)"></i> Módulos disponíveis</h2>
        <div class="modulo-grid">
            @foreach($relatorio['modulos'] as $i => $m)
                @php
                    $falhas = array_filter($m['checks'], static fn ($c) => ! $c['ok']);
                    $tom = $m['ok'] ? ($m['cor'] ?? 'var(--ok)') : 'var(--erro)';
                    $icone = $m['icone'] ?? ($m['ok'] ? 'fa-check' : 'fa-exclamation');
                    $nomeUp = mb_strtoupper((string) ($m['nome'] ?? ''), 'UTF-8');
                @endphp
                <article id="mod-{{ $m['id'] }}" class="modulo-card" style="--tom: {{ $tom }}; animation-delay: {{ $i * 0.04 }}s">
                    <header>
                        <span class="ico"><i class="fa {{ $icone }}"></i></span>
                        <div>
                            <h3>{{ $nomeUp }}</h3>
                            <p class="tr">TR {{ $m['tr'] }} · {{ $m['nivel'] }}</p>
                        </div>
                    </header>
                    <div class="modulo-meta">
                        <span class="badge {{ $m['ok'] ? 'ok' : 'fail' }}">{{ $m['ok'] ? 'Aceito' : 'Atenção' }}</span>
                        @if(!empty($m['admin_url']))
                            <span class="badge info"><i class="fa fa-desktop"></i> Disponível</span>
                            <a class="modulo-link" href="{{ url($m['admin_url']) }}">Abrir <i class="fa fa-arrow-right"></i></a>
                        @else
                            <span class="badge muted"><i class="fa fa-cog"></i> Motor / CLI</span>
                        @endif
                    </div>
                    @if($falhas !== [])
                        <div style="margin-top:10px">
                            @foreach($falhas as $f)
                                <div style="font-size:.8rem;color:var(--erro);margin-top:4px"><i class="fa fa-warning"></i> {{ $f['nome'] }}: {{ $f['detalhe'] }}</div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
@endsection
