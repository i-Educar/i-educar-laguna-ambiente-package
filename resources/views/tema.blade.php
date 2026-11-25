@extends('laguna-ambiente::layout')

@section('titulo', 'Tema visual Buriti')
@section('migalha', 'Tema')

@section('icone-tela', 'fa-paint-brush')
@section('titulo-tela', 'Configuração visual')
@section('lead')
    Ativa o skin Buriti Laguna no i-Educar e no i-Diário (header, lateral e tipografia).
@endsection

@section('conteudo')
    <div class="bloco">
        <h2><i class="fa fa-toggle-{{ $ativo ? 'on' : 'off' }}" style="color:var(--destaque)"></i> Estado atual</h2>
        <p style="margin:0 0 12px">
            Tema Buriti:
            @if($ativo)
                <span class="badge ok"><i class="fa fa-check"></i> Ativo</span>
            @else
                <span class="badge fail"><i class="fa fa-times"></i> Inativo</span>
            @endif
        </p>
        <p style="color:var(--suave);margin:0 0 14px;font-size:.9rem">
            Flag env: <code>BURITI_TEMA_ATIVO</code> · Setting: <code>laguna-ambiente.tema.ativo</code>
            (o valor salvo aqui prevalece sobre o .env).
        </p>
        <form method="post" action="{{ url('/laguna-ambiente/tema') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
            @csrf
            @if($ativo)
                <input type="hidden" name="ativo" value="0">
                <button class="botao claro" type="submit"><i class="fa fa-times"></i> Desativar tema</button>
            @else
                <input type="hidden" name="ativo" value="1">
                <button class="botao" type="submit"><i class="fa fa-paint-brush"></i> Ativar tema Buriti</button>
            @endif
        </form>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-info-circle" style="color:var(--destaque)"></i> O que muda (onda 1)</h2>
        <ul style="margin:0;padding-left:18px;color:var(--suave);line-height:1.6">
            <li>Tokens de cor compartilhados com os módulos municipais</li>
            <li>Header translúcido, lateral em gradiente e tipografia Manrope / Segoe UI</li>
            <li>Botões e cabeçalhos de tabela alinhados à identidade Laguna</li>
            <li>Mesmo skin propagado ao i-Diário (flag <code>.laguna-tema-buriti</code>)</li>
        </ul>
        <p style="margin:12px 0 0;display:flex;flex-wrap:wrap;gap:8px">
            <a class="botao claro" href="{{ url('/') }}" target="_blank" rel="noopener">
                <i class="fa fa-external-link"></i> Abrir intranet
            </a>
            @if(!empty(config('services.idiario.url')))
                <a class="botao claro" href="{{ url('/idiario/entrar') }}" target="_blank" rel="noopener">
                    <i class="fa fa-book"></i> Abrir i-Diário (SSO)
                </a>
            @endif
        </p>
    </div>
@endsection
