@extends('laguna-ambiente::layout')

@section('titulo', 'Configuração de módulos')
@section('migalha', 'Módulos')
@section('icone-tela', 'fa-sliders')
@section('titulo-tela', 'Configuração dos módulos')
@section('lead')
    Clique em um cartão para configurar. Ativação, setor da SME, regras e rede de apoio ficam neste painel — sem telas soltas dentro de cada módulo.
@endsection

@section('conteudo')
@php
    $gruposRotulo = $gruposRotulo ?? [];
    $abrir = $moduloAbrir ?? '';
    $destaque = $moduloDestaque ?? $abrir;
@endphp

@if($errors->any())
    <div class="flash erro" style="margin-bottom:14px">
        <strong>Revise os campos:</strong>
        <ul style="margin:6px 0 0;padding-left:18px">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(!empty(session('avisos_modulo')))
    <div class="bloco" style="border-color:#c2410c;background:#fff7ed">
        <h2><i class="fa fa-exclamation-triangle" style="color:#c2410c"></i> Atenção</h2>
        <ul style="margin:0;padding-left:18px;line-height:1.55;color:#9a3412">
            @foreach(session('avisos_modulo') as $aviso)
                <li>{{ $aviso }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="cfg-intro bloco">
    <h2><i class="fa fa-info-circle"></i> Como usar</h2>
    <p style="margin:0;color:var(--suave);line-height:1.55">
        Conexão: <code>{{ $tenant }}</code>.
        Abra o cartão do módulo, siga o roteiro “Como preencher”, salve e feche.
        Módulos com atendimento à família pedem <strong>setor da SME</strong> (nome, telefone, e-mail).
        Atendimento e Busca Ativa também pedem a <strong>rede de apoio</strong> (Conselho Tutelar, CRAS/CREAS).
        Desativar exige ciência dos riscos. Pacotes de infraestrutura não podem ser desligados.
    </p>
</section>

<div class="cfg-grade" role="list">
@foreach($modulos as $m)
    @php
        $portal = collect($m['diretivas'])->firstWhere('chave', 'portal_externo');
        $portalOn = $portal ? !empty($portal['valor']) : null;
        $qtd = collect($m['diretivas'])->where('chave', '!=', 'ativo')->count();
        $pctCfg = (int) ($m['percentual'] ?? 0);
        $pctTom = $pctCfg >= 90 ? '#17663f' : ($pctCfg >= 50 ? '#a85f00' : '#c74634');
    @endphp
    <button type="button"
            class="cfg-card {{ $destaque === $m['id'] ? 'is-foco' : '' }} {{ $m['ativo'] ? '' : 'is-off' }}"
            role="listitem"
            data-buriti-modal="cfg-{{ $m['id'] }}"
            data-mod-id="{{ $m['id'] }}"
            style="--mod-cor: {{ $m['cor'] }}">
        <span class="cfg-card-ico" aria-hidden="true"><i class="fa {{ $m['icone'] }}"></i></span>
        <span class="cfg-card-body">
            <strong>{{ $m['nome'] }}</strong>
            <small>TR {{ $m['tr'] }} · {{ $m['nivel'] }}</small>
            <span class="cfg-card-meta">
                <span class="badge {{ $m['ativo'] ? 'ok' : 'fail' }}">{{ $m['ativo'] ? 'Ativo' : 'Off' }}</span>
                <span class="badge muted" title="{{ ($m['campos_ok'] ?? 0) }}/{{ ($m['campos_total'] ?? $qtd) }} campos preenchidos" style="color:{{ $pctTom }}">{{ $pctCfg }}%</span>
                @if($portalOn === true)
                    <span class="badge ok">Portal externo</span>
                @elseif($portalOn === false)
                    <span class="badge warn">Sem portal</span>
                @endif
                @if(!$m['tenant_ok'] && $m['ativo'])
                    <span class="badge warn">Tenant</span>
                @endif
                @if($m['ativo'] && empty($m['contato_ok']))
                    <span class="badge warn">Contato incompleto</span>
                @elseif(!empty($m['ja_configurado']))
                    <span class="badge ok">Salvo</span>
                @endif
                <span class="cfg-card-qtd">{{ $qtd }} opção(ões)</span>
            </span>
        </span>
        <span class="cfg-card-pct" style="--pct: {{ $pctCfg }}; --tom: {{ $pctTom }}" aria-label="{{ $pctCfg }}% configurado">
            <span>{{ $pctCfg }}%</span>
        </span>
        <span class="cfg-card-acao"><i class="fa fa-cog"></i> Configurar</span>
    </button>
@endforeach
</div>

@foreach($modulos as $m)
    @php
        $porGrupo = [];
        foreach ($m['diretivas'] as $d) {
            if (($d['chave'] ?? '') === 'ativo') continue;
            $g = $d['grupo'] ?? (str_starts_with((string) ($d['chave'] ?? ''), 'setor_') ? 'setor' : (str_starts_with((string) ($d['chave'] ?? ''), 'rede_') ? 'rede_apoio' : (in_array($d['chave'] ?? '', ['portal_externo', 'aparecer_na_home'], true) ? 'acesso' : 'regras')));
            $porGrupo[$g][] = $d;
        }
        $ordemGrupos = ['acesso', 'regras', 'setor', 'rede_apoio'];
    @endphp
    @include('buriti-ui::operacao-modal', [
        'id' => 'cfg-'.$m['id'],
        'titulo' => $m['nome'],
        'lead' => 'TR '.$m['tr'].' · '.$m['composer'],
        'corpo' => view('laguna-ambiente::partials.modulo-config-form', [
            'm' => $m,
            'porGrupo' => $porGrupo,
            'ordemGrupos' => $ordemGrupos,
            'gruposRotulo' => $gruposRotulo,
        ])->render(),
    ])
@endforeach

<style>
    .cfg-grade {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 12px;
        margin: 0 0 20px;
    }
    .cfg-card {
        --mod-cor: var(--cor-tela, #0a7a8a);
        display: grid;
        grid-template-columns: 44px 1fr 46px;
        gap: 12px;
        align-items: start;
        text-align: left;
        width: 100%;
        padding: 14px;
        border: 1px solid var(--linha);
        border-radius: 12px;
        border-left: 3px solid var(--mod-cor);
        background: var(--superficie, #fff);
        box-shadow: none;
        cursor: pointer;
        font: inherit;
        color: var(--tinta);
        transition: border-color .15s ease, background .15s ease;
    }
    .cfg-card-pct {
        --pct: 0; --tom: var(--ok, #17663f);
        width: 46px; height: 46px; border-radius: 50%; display: grid; place-items: center; flex: none;
        background: conic-gradient(var(--tom) calc(var(--pct) * 1%), color-mix(in srgb, var(--linha) 70%, var(--clara, #eef2f7)) 0);
        box-shadow: inset 0 0 0 6px var(--superficie, #fff);
    }
    .cfg-card-pct span {
        width: 32px; height: 32px; border-radius: 50%; background: var(--superficie, #fff);
        color: var(--tom); display: grid; place-items: center;
        font-size: .68rem; font-weight: 800; letter-spacing: -.02em;
    }
    .cfg-card:hover, .cfg-card:focus-visible {
        background: color-mix(in srgb, var(--clara, #eef2f7) 50%, var(--superficie, #fff));
        border-color: color-mix(in srgb, var(--mod-cor) 28%, var(--linha));
        outline: none;
    }
    .cfg-card.is-foco {
        border-color: color-mix(in srgb, var(--mod-cor) 40%, var(--linha));
        background: color-mix(in srgb, var(--mod-cor) 5%, var(--superficie, #fff));
    }
    .cfg-card.is-off { opacity: .82; }
    .cfg-card-ico {
        width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center;
        color: #fff; background: var(--mod-cor);
        font-size: 1rem;
    }
    .cfg-card-body { display: grid; gap: 4px; min-width: 0; }
    .cfg-card-body strong { font-size: .98rem; line-height: 1.25; }
    .cfg-card-body > small { color: var(--suave); font-size: .72rem; }
    .cfg-card-meta { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-top: 4px; }
    .cfg-card-qtd { font-size: .72rem; color: var(--suave); font-weight: 600; }
    .cfg-card-acao {
        grid-column: 1 / -1; display: inline-flex; align-items: center; gap: 6px;
        margin-top: 4px; font-size: .8rem; font-weight: 800; color: var(--mod-cor);
        letter-spacing: .02em;
    }
    .cfg-card .cfg-card-pct { align-self: center; }
    .cfg-form { display: grid; gap: 14px; }
    .cfg-guia {
        border: 1px solid color-mix(in srgb, var(--cor-tela, #0a7a8a) 28%, var(--linha));
        background: color-mix(in srgb, var(--cor-tela, #0a7a8a) 8%, #fff);
        border-radius: 12px; padding: 10px 12px;
    }
    .cfg-guia strong { display: block; margin-bottom: 4px; font-size: .82rem; }
    .cfg-guia p { margin: 0; color: var(--suave); font-size: .86rem; line-height: 1.45; font-weight: 500; }
    .cfg-secao {
        border: 1px solid var(--linha); border-radius: 12px; padding: 12px;
        background: color-mix(in srgb, var(--clara, #eef4f5) 45%, #fff);
    }
    .cfg-secao h3 {
        margin: 0 0 6px; font-size: .72rem; letter-spacing: .08em; text-transform: uppercase; color: var(--suave);
    }
    .cfg-secao-lead { margin: 0 0 10px; font-size: .8rem; color: var(--suave); line-height: 1.4; font-weight: 500; }
    .cfg-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
    .cfg-campo { display: grid; gap: 4px; font-size: .8rem; font-weight: 700; color: var(--suave); }
    .cfg-campo.cfg-wide { grid-column: 1 / -1; }
    .cfg-rotulo { color: var(--tinta); }
    .cfg-sufixo { font-style: normal; font-weight: 600; color: var(--suave); }
    .cfg-campo input, .cfg-campo select, .cfg-campo textarea {
        min-height: 38px; padding: 8px 10px; border: 1px solid var(--linha); border-radius: 10px;
        font: inherit; font-weight: 500; color: var(--tinta); background: #fff; width: 100%;
    }
    .cfg-campo textarea { min-height: 72px; resize: vertical; }
    .cfg-campo small, .cfg-flag small { display: block; font-weight: 500; color: var(--suave); margin-top: 2px; line-height: 1.35; }
    .cfg-flag {
        display: flex; gap: 10px; align-items: flex-start; padding: 10px 12px;
        border: 1px solid var(--linha); border-radius: 12px; background: #fff;
        font-weight: 700; color: var(--tinta); grid-column: 1 / -1;
    }
    .cfg-flag input { margin-top: 3px; }
    .cfg-flag strong { display: block; }
    .cfg-ativo {
        display: flex; gap: 10px; align-items: flex-start; padding: 12px;
        border-radius: 12px; border: 1px solid color-mix(in srgb, var(--cor-tela) 25%, var(--linha));
        background: #fff; font-weight: 800;
    }
    .cfg-risco {
        border: 1px solid #fed7aa; border-radius: 12px; background: #fffbeb; padding: 10px 12px;
    }
    .cfg-risco summary { cursor: pointer; font-weight: 800; color: #9a3412; }
    .cfg-risco ul { margin: 8px 0 0; padding-left: 18px; color: #7c2d12; font-size: .86rem; line-height: 1.45; }
    .cfg-acoes { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
    .buriti-modal { width: min(760px, 96vw); }
    @media (max-width: 640px) {
        .cfg-grade { grid-template-columns: 1fr; }
    }
</style>

<script>
(function () {
    var abrir = @json($abrir);
    if (abrir) {
        var btn = document.querySelector('[data-buriti-modal="cfg-' + abrir + '"]');
        if (btn) {
            setTimeout(function () { btn.click(); }, 120);
        }
        // Limpa ?modulo= da barra para não reabrir em F5 sem flash.
        if (window.history && window.history.replaceState) {
            try {
                var u = new URL(window.location.href);
                u.searchParams.delete('modulo');
                u.searchParams.delete('abrir');
                window.history.replaceState({}, '', u.pathname + (u.search ? u.search : '') + u.hash);
            } catch (e) {}
        }
    }

    document.querySelectorAll('[data-mod-ativo]').forEach(function (cb) {
        var id = cb.getAttribute('data-mod-ativo');
        var risco = document.querySelector('[data-risco="' + id + '"]');
        var ciente = document.querySelector('[data-ciente="' + id + '"]');
        var form = cb.closest('form');
        function sync() {
            if (!risco) return;
            if (!cb.checked) {
                risco.open = true;
                if (ciente) ciente.required = true;
            } else if (ciente) {
                ciente.required = false;
                ciente.checked = false;
            }
        }
        cb.addEventListener('change', sync);
        sync();

        if (form) {
            form.addEventListener('submit', function (ev) {
                // Ciência só na transição ativo → off.
                if (cb.disabled) return;
                var estavaAtivo = form.getAttribute('data-was-ativo') === '1';
                if (estavaAtivo && !cb.checked) {
                    if (risco) risco.open = true;
                    if (!ciente || !ciente.checked) {
                        ev.preventDefault();
                        if (ciente) {
                            ciente.required = true;
                            ciente.focus();
                            ciente.setCustomValidity('Marque que está ciente dos riscos para desativar o módulo.');
                            ciente.reportValidity();
                            ciente.addEventListener('change', function () {
                                ciente.setCustomValidity('');
                            }, { once: true });
                        } else {
                            alert('Marque que está ciente dos riscos para desativar o módulo.');
                        }
                    }
                }
            });
        }
    });

    function soDigitos(v) { return String(v || '').replace(/\D+/g, ''); }

    function mascaraTelefone(el) {
        var d = soDigitos(el.value).slice(0, 11);
        if (d.length === 0) { el.value = ''; return; }
        if (d.length <= 2) { el.value = '(' + d; return; }
        if (d.length <= 6) { el.value = '(' + d.slice(0, 2) + ') ' + d.slice(2); return; }
        if (d.length <= 10) {
            el.value = '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
            return;
        }
        el.value = '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
    }

    document.querySelectorAll('[data-mascara="telefone"]').forEach(function (el) {
        el.addEventListener('input', function () { mascaraTelefone(el); });
        el.addEventListener('blur', function () { mascaraTelefone(el); });
        if (el.value) mascaraTelefone(el);
    });
})();
</script>
@endsection
