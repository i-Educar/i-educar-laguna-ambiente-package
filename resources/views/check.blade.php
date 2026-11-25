@extends('laguna-ambiente::layout')

@section('titulo', 'Verificar módulos')
@section('migalha', 'Verificar')

@section('acoes-topo')
    <a class="botao" href="{{ url('/laguna-ambiente/check') }}"><i class="fa fa-refresh"></i> Atualizar</a>
@endsection

@section('conteudo')
    <div class="cabeca-tela">
        <span class="marca-tela"><i class="fa fa-check-square-o"></i></span>
        <div>
            <h1 style="margin:0">Verificação de módulos</h1>
            <p style="margin:2px 0 0;color:#5b6b76">Pasta, provider, PackageTenant, schema, menu e comandos</p>
        </div>
    </div>

    @if(session('passos'))
        <div class="bloco">
            <h2>Último setup</h2>
            <table>
                <thead><tr><th>Passo</th><th>OK</th><th>Saída</th></tr></thead>
                <tbody>
                @foreach(session('passos') as $p)
                    <tr>
                        <td>{{ $p['passo'] }}</td>
                        <td><span class="badge {{ $p['ok'] ? 'ok' : 'fail' }}">{{ $p['ok'] ? 'OK' : 'FAIL' }}</span></td>
                        <td><small>{{ $p['saida'] }}</small></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="bloco">
        <h2>Núcleo</h2>
        <table>
            <thead><tr><th>Check</th><th>Status</th><th>Detalhe</th></tr></thead>
            <tbody>
            @foreach($relatorio['nucleo'] as $c)
                <tr>
                    <td>{{ $c['chave'] }}</td>
                    <td><span class="badge {{ $c['ok'] ? 'ok' : 'fail' }}">{{ $c['ok'] ? 'OK' : 'FAIL' }}</span></td>
                    <td>{{ $c['detalhe'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="bloco">
        <h2>Módulos Laguna</h2>
        <table>
            <thead><tr><th>TR</th><th>Módulo</th><th>Nível</th><th>Status</th><th>Falhas</th></tr></thead>
            <tbody>
            @foreach($relatorio['modulos'] as $m)
                @php
                    $falhas = array_filter($m['checks'], static fn ($c) => ! $c['ok']);
                @endphp
                <tr>
                    <td>{{ $m['tr'] }}</td>
                    <td>{{ $m['nome'] }}</td>
                    <td>{{ $m['nivel'] }}</td>
                    <td><span class="badge {{ $m['ok'] ? 'ok' : 'fail' }}">{{ $m['ok'] ? 'OK' : 'FAIL' }}</span></td>
                    <td>
                        @forelse($falhas as $f)
                            <div><small>{{ $f['nome'] }}: {{ $f['detalhe'] }}</small></div>
                        @empty
                            —
                        @endforelse
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
