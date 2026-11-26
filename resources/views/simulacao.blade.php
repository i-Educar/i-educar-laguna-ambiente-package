@extends('laguna-ambiente::layout')

@section('titulo', 'Simulação de pessoas')
@section('migalha', 'Simulação')
@section('icone-tela', 'fa-users')
@section('titulo-tela', 'Simulação multi-módulo')
@section('lead')
    20 pessoas percorrem cenários aleatórios. Cada módulo mostra o <strong>status real</strong> do que foi feito:
    sucesso, negativa de regra, ou <em>falta de teste</em> (pré-requisito ausente — não conta como acerto).
@endsection

@section('acoes-topo')
    <a class="botao claro" href="{{ url('/laguna-ambiente/setup') }}"><i class="fa fa-flask"></i> Setup</a>
    <a class="botao claro" href="{{ url('/laguna-ambiente/check') }}"><i class="fa fa-stethoscope"></i> Verificar</a>
@endsection

@section('conteudo')
@php
    $lote = $dados['lote'] ?? null;
    $pessoas = $dados['pessoas'] ?? [];
    $passosSetup = $passos ?? [];
    $rotulo = static function (string $st): string {
        return \iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaSimulacaoPessoasService::rotuloStatus($st);
    };
    $corStatus = static function (string $st): string {
        return match ($st) {
            'ok' => '#146c42',
            'negativa' => '#8a4b08',
            'falta_teste' => '#a85f00',
            'erro' => '#c74634',
            default => '#5c6b7a',
        };
    };
    $bgStatus = static function (string $st): string {
        return match ($st) {
            'ok' => 'rgba(20,108,66,.12)',
            'negativa' => 'rgba(138,75,8,.12)',
            'falta_teste' => 'rgba(168,95,0,.14)',
            'erro' => 'rgba(196,70,52,.12)',
            default => 'rgba(0,0,0,.06)',
        };
    };
@endphp

@if(!empty($passosSetup))
    <div class="bloco">
        <h2><i class="fa fa-list" style="color:var(--destaque)"></i> Passos do setup</h2>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:6px">
            @foreach($passosSetup as $p)
                <li style="display:flex;gap:10px;align-items:flex-start;padding:8px 10px;border-radius:8px;background:{{ $p['ok'] ? 'rgba(20,108,66,.08)' : 'rgba(196,70,52,.08)' }}">
                    <i class="fa {{ $p['ok'] ? 'fa-check-circle' : 'fa-exclamation-circle' }}" style="color:{{ $p['ok'] ? '#146c42' : '#c74634' }};margin-top:2px"></i>
                    <div>
                        <strong>{{ $p['passo'] }}</strong>
                        @if(($p['saida'] ?? '') !== '')
                            <div style="color:var(--suave);font-size:.85rem;white-space:pre-wrap">{{ \Illuminate\Support\Str::limit($p['saida'], 280) }}</div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bloco">
    <h2><i class="fa fa-play-circle" style="color:var(--destaque)"></i> Rodar simulação</h2>
    <form method="post" action="{{ url('/laguna-ambiente/simulacao') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:end">
        @csrf
        <label style="display:grid;gap:4px">
            <span style="font-size:.85rem;color:var(--suave)">Pessoas</span>
            <input type="number" name="qtd" value="20" min="1" max="40" style="width:90px;padding:8px 10px;border:1px solid var(--borda);border-radius:8px;background:var(--fundo)">
        </label>
        <label style="display:flex;gap:8px;align-items:center;padding-bottom:8px">
            <input type="checkbox" name="force_production" value="1"> Permitir em production
        </label>
        <button class="botao" type="submit"><i class="fa fa-random"></i> Simular agora</button>
    </form>
    <p style="color:var(--suave);font-size:.85rem;margin:10px 0 0">
        CLI: <code>php artisan laguna:simular-pessoas</code> · integrado em <code>laguna:setup</code>
    </p>
    <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;font-size:.8rem">
        <span style="padding:3px 10px;border-radius:999px;background:{{ $bgStatus('ok') }};color:{{ $corStatus('ok') }}">Sucesso — efeito real</span>
        <span style="padding:3px 10px;border-radius:999px;background:{{ $bgStatus('negativa') }};color:{{ $corStatus('negativa') }}">Negativa — regra de negócio</span>
        <span style="padding:3px 10px;border-radius:999px;background:{{ $bgStatus('falta_teste') }};color:{{ $corStatus('falta_teste') }}">Falta de teste — pré-requisito ausente</span>
        <span style="padding:3px 10px;border-radius:999px;background:{{ $bgStatus('erro') }};color:{{ $corStatus('erro') }}">Erro técnico</span>
    </div>
</div>

@if($lote)
    @php $contagem = $lote['resumo']['contagem_status'] ?? []; @endphp
    <div class="bloco">
        <h2><i class="fa fa-database" style="color:var(--destaque)"></i> Último lote #{{ $lote['id'] }}</h2>
        <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:14px">
            <div><span style="color:var(--suave);font-size:.8rem">Pessoas</span><div style="font-size:1.4rem;font-weight:700">{{ $lote['pessoas'] }}</div></div>
            <div><span style="color:var(--suave);font-size:.8rem">Eventos</span><div style="font-size:1.4rem;font-weight:700">{{ $lote['eventos'] }}</div></div>
            <div><span style="color:var(--suave);font-size:.8rem">Módulos</span><div style="font-size:1.4rem;font-weight:700">{{ $lote['modulos'] }}</div></div>
            <div><span style="color:var(--suave);font-size:.8rem">Quando</span><div>{{ $lote['created_at'] }}</div></div>
            <div><span style="color:var(--suave);font-size:.8rem">Por</span><div>{{ $lote['usuario'] ?? '—' }}</div></div>
        </div>
        @if($contagem !== [])
            <p style="margin:0 0 12px;font-size:.9rem">
                Resultado do lote:
                @foreach($contagem as $st => $n)
                    <span style="display:inline-block;padding:2px 8px;margin:2px;border-radius:999px;background:{{ $bgStatus($st) }};color:{{ $corStatus($st) }};font-size:.8rem;font-weight:600">
                        {{ $rotulo($st) }}: {{ $n }}
                    </span>
                @endforeach
            </p>
        @endif
        @if(!empty($lote['resumo']['modulos']))
            <p style="margin:0 0 12px;font-size:.9rem;color:var(--suave)">
                Módulos exercitados (tocar ≠ sucesso — veja status por evento):
                @foreach($lote['resumo']['modulos'] as $m)
                    <span style="display:inline-block;padding:2px 8px;margin:2px;border-radius:999px;background:rgba(26,95,158,.12);font-size:.8rem">{{ $m }}</span>
                @endforeach
            </p>
        @endif
    </div>

    <div class="bloco">
        <h2><i class="fa fa-sitemap" style="color:var(--destaque)"></i> Percurso das {{ count($pessoas) }} pessoas</h2>
        <div style="display:grid;gap:14px">
            @foreach($pessoas as $i => $p)
                @php
                    $evs = $p['eventos'] ?? [];
                    $porStatus = [];
                    foreach ($evs as $ev) {
                        $s = $ev['status'] ?? 'skip';
                        $porStatus[$s] = ($porStatus[$s] ?? 0) + 1;
                    }
                @endphp
                <details style="border:1px solid var(--borda);border-radius:10px;padding:10px 14px;background:var(--fundo)" @if($i < 3) open @endif>
                    <summary style="cursor:pointer;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                        <strong style="min-width:2rem">{{ $i + 1 }}.</strong>
                        <span>{{ $p['nome'] }}</span>
                        <span style="font-size:.8rem;padding:2px 8px;border-radius:999px;background:rgba(0,0,0,.06)">{{ $p['perfil'] }}</span>
                        <span style="color:var(--suave);font-size:.85rem">CPF {{ $p['cpf'] }}</span>
                        <span style="color:var(--suave);font-size:.85rem">{{ $p['escola'] }}</span>
                        <span style="margin-left:auto;font-size:.8rem;display:flex;flex-wrap:wrap;gap:4px">
                            @foreach($porStatus as $st => $n)
                                <span style="padding:2px 7px;border-radius:999px;background:{{ $bgStatus($st) }};color:{{ $corStatus($st) }}">{{ $rotulo($st) }} {{ $n }}</span>
                            @endforeach
                        </span>
                    </summary>
                    <ol style="margin:12px 0 0;padding-left:0;list-style:none;display:grid;gap:8px">
                        @foreach($evs as $ev)
                            @php $st = $ev['status'] ?? 'skip'; @endphp
                            <li style="font-size:.9rem;padding:8px 10px;border-radius:8px;background:{{ $bgStatus($st) }};border-left:3px solid {{ $corStatus($st) }}">
                                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:baseline">
                                    <strong style="min-width:7rem;color:{{ $corStatus($st) }}">{{ $ev['modulo'] }}</strong>
                                    <span style="font-size:.75rem;font-weight:700;letter-spacing:.02em;padding:2px 8px;border-radius:999px;background:#fff;color:{{ $corStatus($st) }};border:1px solid {{ $corStatus($st) }}">
                                        {{ $rotulo($st) }}
                                    </span>
                                    <code style="font-size:.8rem">{{ $ev['cenario'] }}</code>
                                    @if(!empty($ev['url']))
                                        <a href="{{ url($ev['url']) }}" style="margin-left:auto;font-size:.8rem">abrir</a>
                                    @endif
                                </div>
                                <div style="margin-top:4px;color:var(--texto, #222)">{{ $ev['detalhe'] }}</div>
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endforeach
        </div>
    </div>
@else
    <div class="bloco">
        <p style="color:var(--suave);margin:0">Nenhuma simulação gravada ainda. Execute acima ou rode o setup com a opção de simulação ligada.</p>
    </div>
@endif
@endsection
