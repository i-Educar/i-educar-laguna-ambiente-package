@extends('laguna-ambiente::layout')

@section('titulo', 'Setup Laguna')
@section('migalha', 'Setup')

@section('conteudo')
    <div class="cabeca-tela">
        <span class="marca-tela"><i class="fa fa-flask"></i></span>
        <div>
            <h1 style="margin:0">Laboratório de povoamento</h1>
            <p style="margin:2px 0 0;color:#5b6b76">Monta a “rede viva” de teste: município, matrículas, demos e identidade Laguna</p>
        </div>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-play-circle" style="color:var(--destaque)"></i> Executar agora</h2>
        <form method="post" action="{{ url('/laguna-ambiente/setup') }}" style="display:grid;gap:10px;max-width:520px">
            @csrf
            <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="skip_municipal" value="1"> Pular <code>ieducar:setup</code></label>
            <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="skip_matriculas" value="1"> Pular <code>ieducar:matriculas</code></label>
            <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="force_production" value="1"> Permitir em production (cuidado)</label>
            <button class="botao" type="submit"><i class="fa fa-flask"></i> Executar setup agora</button>
        </form>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-road" style="color:var(--destaque)"></i> Roteiro do ensaio</h2>
        <ol class="passo-lista">
            <li><span class="n">1</span><div><strong>Base municipal</strong><br><small><code>ieducar:setup default-br</code> — estrutura da SME</small></div></li>
            <li><span class="n">2</span><div><strong>Matrículas</strong><br><small><code>ieducar:matriculas --force</code> — alunos na rede</small></div></li>
            <li><span class="n">3</span><div><strong>Migrations</strong><br><small><code>php artisan migrate --force</code> — schemas dos pacotes</small></div></li>
            <li><span class="n">4</span><div><strong>Demos de domínio</strong><br><small>alimentação, escolar onda 1, exportação crítica</small></div></li>
            <li><span class="n">5</span><div><strong>Identidade Laguna</strong><br><small>Prefeitura, footer, logo e resumo da rede</small></div></li>
        </ol>
    </div>
@endsection
