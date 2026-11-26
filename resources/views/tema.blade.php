@extends('laguna-ambiente::layout')

@section('titulo', 'Skins da escola')
@section('migalha', 'Tema')

@section('icone-tela', 'fa-paint-brush')
@section('titulo-tela', 'Skins da escola')
@section('lead')
    Escolha a identidade visual aplicada no i-Educar e no i-Diário. Cada card mostra uma prévia do chrome (topo + lateral + miolo).
@endsection

@section('conteudo')
    @php
        $skins = $skins ?? \iEducar\Packages\BuritiLagunaAmbiente\Support\TemaBuriti::catalogo();
        $atual = $skinAtual ?? \iEducar\Packages\BuritiLagunaAmbiente\Support\TemaBuriti::skinId();
    @endphp

    <style>
        .skin-grade {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
        }
        .skin-card {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--linha);
            border-radius: 14px;
            background: var(--superficie, #fff);
            box-shadow: var(--sombra-leve, 0 1px 2px rgba(0,0,0,.05));
            cursor: pointer;
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }
        .skin-card:hover {
            border-color: color-mix(in srgb, var(--destaque) 40%, var(--linha));
            box-shadow: var(--sombra, 0 10px 24px rgba(0,0,0,.08));
            transform: translateY(-2px);
        }
        .skin-card.ativa {
            border-color: color-mix(in srgb, var(--acento, var(--destaque)) 55%, var(--linha));
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--acento, var(--destaque)) 35%, transparent);
        }
        .skin-card input { position: absolute; opacity: 0; pointer-events: none; }
        .skin-previa {
            --p-fundo: #eee;
            --p-topo: #333;
            --p-lat: #444;
            --p-acento: #5bc0de;
            --p-texto: #fff;
            position: relative;
            height: 132px;
            border-radius: 10px;
            overflow: hidden;
            background: var(--p-fundo);
            border: 1px solid color-mix(in srgb, var(--p-lat) 25%, transparent);
        }
        .skin-previa .pv-topo {
            height: 28px;
            background: linear-gradient(105deg, var(--p-topo), color-mix(in srgb, var(--p-acento) 35%, var(--p-topo)));
            display: flex; align-items: center; gap: 6px; padding: 0 8px;
        }
        .skin-previa .pv-topo i {
            width: 10px; height: 10px; border-radius: 50%;
            background: color-mix(in srgb, var(--p-texto) 35%, transparent);
            display: inline-block;
        }
        .skin-previa .pv-topo span {
            margin-left: auto; font-size: 9px; font-weight: 800; letter-spacing: .06em;
            text-transform: uppercase; color: var(--p-texto); opacity: .85;
        }
        .skin-previa .pv-corpo { display: grid; grid-template-columns: 54px 1fr; height: calc(100% - 28px); }
        .skin-previa .pv-lat {
            background: var(--p-lat);
            padding: 8px 6px;
            display: grid; gap: 5px; align-content: start;
        }
        .skin-previa .pv-lat b {
            display: block; height: 7px; border-radius: 4px;
            background: color-mix(in srgb, var(--p-texto) 22%, transparent);
        }
        .skin-previa .pv-lat b.on {
            background: var(--p-acento);
            box-shadow: inset 2px 0 0 var(--p-acento);
        }
        .skin-previa .pv-miolo { padding: 8px; display: grid; gap: 6px; align-content: start; }
        .skin-previa .pv-card {
            height: 34px; border-radius: 7px;
            background: color-mix(in srgb, #fff 88%, var(--p-fundo));
            border: 1px solid color-mix(in srgb, var(--p-lat) 18%, transparent);
            box-shadow: 0 4px 10px rgba(0,0,0,.05);
        }
        .skin-previa .pv-card.kpi {
            display: grid; grid-template-columns: 1fr 1fr; gap: 4px; background: transparent; border: 0; box-shadow: none; height: auto;
        }
        .skin-previa .pv-card.kpi i {
            display: block; height: 22px; border-radius: 6px;
            background: color-mix(in srgb, #fff 90%, var(--p-fundo));
            border-top: 2px solid var(--p-acento);
            border: 1px solid color-mix(in srgb, var(--p-lat) 14%, transparent);
            border-top-color: var(--p-acento);
        }
        .skin-meta h3 { margin: 0; font-size: .95rem; font-weight: 800; letter-spacing: -.02em; }
        .skin-meta .conceito {
            display: inline-flex; align-items: center; gap: 5px;
            margin: 4px 0 0; padding: 2px 8px; border-radius: 999px;
            font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            background: color-mix(in srgb, var(--clara) 70%, transparent);
            color: var(--destaque); border: 1px solid var(--linha);
        }
        .skin-meta p { margin: 6px 0 0; color: var(--suave); font-size: .82rem; line-height: 1.4; }
        .skin-selo {
            position: absolute; top: 18px; right: 18px; z-index: 2;
            background: var(--ok, #146c42); color: #fff;
            font-size: .65rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
            padding: 3px 8px; border-radius: 999px;
            box-shadow: 0 4px 12px rgba(0,0,0,.18);
        }
        .skin-acoes { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 4px; }
    </style>

    <div class="bloco">
        <h2><i class="fa fa-desktop" style="color:var(--destaque)"></i> Prévia e aplicação</h2>
        <p style="margin:0 0 14px;color:var(--suave);line-height:1.5">
            Skin atual:
            <strong>{{ $skins[$atual]['nome'] ?? $atual }}</strong>
            · Setting <code>laguna-ambiente.tema.skin</code>
            · Propagada ao i-Diário via <code>.laguna-tema-skin</code>
        </p>

        <form method="post" action="{{ url('/laguna-ambiente/tema') }}">
            @csrf
            <div class="skin-grade">
                @foreach($skins as $id => $skin)
                    @php $pv = $skin['preview']; @endphp
                    <label class="skin-card {{ $atual === $id ? 'ativa' : '' }}">
                        <input type="radio" name="skin" value="{{ $id }}" @checked($atual === $id)>
                        @if($atual === $id)
                            <span class="skin-selo"><i class="fa fa-check"></i> Ativa</span>
                        @endif
                        <div class="skin-previa" style="--p-fundo:{{ $pv['fundo'] }};--p-topo:{{ $pv['topo'] }};--p-lat:{{ $pv['lateral'] }};--p-acento:{{ $pv['acento'] }};--p-texto:{{ $pv['texto'] }}">
                            <div class="pv-topo">
                                <i></i><i></i><i></i>
                                <span>{{ $skin['conceito'] }}</span>
                            </div>
                            <div class="pv-corpo">
                                <div class="pv-lat">
                                    <b class="on"></b><b></b><b></b><b></b>
                                </div>
                                <div class="pv-miolo">
                                    <div class="pv-card kpi"><i></i><i></i></div>
                                    <div class="pv-card"></div>
                                </div>
                            </div>
                        </div>
                        <div class="skin-meta">
                            <h3>{{ $skin['nome'] }}</h3>
                            <span class="conceito">{{ $skin['conceito'] }}</span>
                            <p>{{ $skin['descricao'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
            <div class="skin-acoes" style="margin-top:16px">
                <button class="botao" type="submit"><i class="fa fa-paint-brush"></i> Aplicar skin selecionada</button>
                @if(!empty(config('services.idiario.url')))
                    <a class="botao claro" href="{{ url('/idiario/entrar') }}" target="_blank" rel="noopener">
                        <i class="fa fa-book"></i> Abrir i-Diário (SSO)
                    </a>
                @endif
                <a class="botao claro" href="{{ url('/') }}" target="_blank" rel="noopener">
                    <i class="fa fa-external-link"></i> Abrir intranet
                </a>
            </div>
        </form>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-adjust" style="color:var(--acento, var(--destaque))"></i> Modo claro / escuro</h2>
        <p style="margin:0 0 12px;color:var(--suave);line-height:1.5">
            Independente da skin: o botão de lua/sol no topo alterna o modo nos shells Buriti.
            Preferência neste navegador (<code>localStorage</code>).
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
            <button type="button" class="botao claro" data-buriti-modo-toggle data-tema-page-toggle>
                <i class="fa fa-moon-o" data-buriti-modo-icone></i> Alternar modo
            </button>
            <span style="font-size:.85rem;color:var(--suave)">
                Atual: <strong data-tema-modo-rotulo>claro</strong>
            </span>
        </div>
    </div>

    <script>
    (function () {
        function sync() {
            var m = document.documentElement.getAttribute('data-buriti-modo') || 'claro';
            var el = document.querySelector('[data-tema-modo-rotulo]');
            if (el) el.textContent = m;
        }
        sync();
        document.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('[data-tema-page-toggle]')) {
                setTimeout(sync, 0);
            }
            var card = e.target.closest && e.target.closest('.skin-card');
            if (card) {
                document.querySelectorAll('.skin-card').forEach(function (c) { c.classList.remove('ativa'); });
                card.classList.add('ativa');
            }
        });
        new MutationObserver(sync).observe(document.documentElement, { attributes: true, attributeFilter: ['data-buriti-modo'] });
    })();
    </script>
@endsection
