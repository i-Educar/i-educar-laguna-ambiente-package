@extends('laguna-ambiente::layout')

@section('titulo', 'Setup Laguna')
@section('migalha', 'Setup')

@section('conteudo')
    <div class="cabeca-tela">
        <span class="marca-tela"><i class="fa fa-cogs"></i></span>
        <div>
            <h1 style="margin:0">Setup / povoar teste</h1>
            <p style="margin:2px 0 0;color:#5b6b76">Roda setup municipal, matrículas, migrate e demos dos módulos</p>
        </div>
    </div>

    <div class="bloco">
        <form method="post" action="{{ url('/laguna-ambiente/setup') }}">
            @csrf
            <p><label><input type="checkbox" name="skip_municipal" value="1"> Pular <code>ieducar:setup</code></label></p>
            <p><label><input type="checkbox" name="skip_matriculas" value="1"> Pular <code>ieducar:matriculas</code></label></p>
            <p><label><input type="checkbox" name="force_production" value="1"> Permitir em production (cuidado)</label></p>
            <button class="botao" type="submit"><i class="fa fa-play"></i> Executar setup agora</button>
        </form>
    </div>

    <div class="bloco">
        <h2>O que será executado</h2>
        <ol>
            <li><code>ieducar:setup default-br</code> (se disponível)</li>
            <li><code>ieducar:matriculas --force</code> (se disponível)</li>
            <li><code>php artisan migrate --force</code></li>
            <li><code>alimentacao:demo</code>, <code>escolar:onda1-demo</code>, <code>exportacao:demo-criticos</code></li>
            <li>Fallback de cardápio + resumo da rede</li>
        </ol>
    </div>
@endsection
