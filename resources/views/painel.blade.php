@extends('laguna-ambiente::layout')

@section('titulo', 'Ambiente Laguna')
@section('migalha', 'Painel')

@section('acoes-topo')
    <a class="botao" href="{{ url('/laguna-ambiente/check') }}"><i class="fa fa-check"></i> Verificar</a>
    <a class="botao claro" href="{{ url('/laguna-ambiente/setup') }}"><i class="fa fa-cogs"></i> Setup</a>
@endsection

@section('conteudo')
    <div class="cabeca-tela">
        <span class="marca-tela"><i class="fa fa-heartbeat"></i></span>
        <div>
            <h1 style="margin:0">Painel do ambiente Laguna</h1>
            <p style="margin:2px 0 0;color:#5b6b76">Auditoria dos módulos PE-77 + setup de base de teste</p>
        </div>
    </div>

    @php $r = $relatorio['resumo']; @endphp
    <div class="numeros">
        <article><span>Status</span><strong>{{ $relatorio['ok'] ? 'APTO' : 'INCOMPLETO' }}</strong></article>
        <article><span>Obrigatórios</span><strong>{{ $r['obrigatorios_ok'] }}/{{ $r['obrigatorios_total'] }}</strong></article>
        <article><span>Falhas</span><strong>{{ $r['falhas'] }}</strong></article>
        <article><span>Avisos</span><strong>{{ $r['avisos'] }}</strong></article>
    </div>

    <div class="bloco">
        <h2>Atalhos</h2>
        <p>
            <a class="botao" href="{{ url('/laguna-ambiente/check') }}"><i class="fa fa-search"></i> Rodar verificação</a>
            <a class="botao claro" href="{{ url('/laguna-ambiente/setup') }}"><i class="fa fa-database"></i> Povoar base de teste</a>
            <a class="botao claro" href="{{ url('/alimentacao-escolar') }}"><i class="fa fa-cutlery"></i> Alimentação</a>
            <a class="botao claro" href="{{ url('/transporte-escolar') }}"><i class="fa fa-bus"></i> Transporte</a>
        </p>
        <p style="color:#5b6b76;font-size:.85rem">CLI: <code>php artisan laguna:check</code> · <code>php artisan laguna:setup</code> · <code>php artisan laguna:ensaiar</code></p>
    </div>
@endsection
