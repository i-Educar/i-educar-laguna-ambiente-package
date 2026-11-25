@extends('laguna-ambiente::layout')

@section('titulo', 'Configuração de módulos')
@section('migalha', 'Módulos')
@section('icone-tela', 'fa-cubes')
@section('titulo-tela', 'Módulos da cidade')
@section('lead')
    Flags, diretivas e ativação por módulo. Desativar exige ciência dos riscos e impactos na rede.
@endsection

@section('conteudo')
@if(!empty(session('avisos_modulo')))
    <div class="bloco" style="border-color:#c2410c;background:#fff7ed">
        <h2><i class="fa fa-exclamation-triangle" style="color:#c2410c"></i> Riscos / impactos</h2>
        <ul style="margin:0;padding-left:18px;line-height:1.55;color:#9a3412">
            @foreach(session('avisos_modulo') as $aviso)
                <li>{{ $aviso }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bloco">
    <h2><i class="fa fa-info-circle"></i> Como funciona</h2>
    <p style="margin:0;color:var(--suave);line-height:1.5">
        Conexão atual: <code>{{ $tenant }}</code>.
        Desligar um módulo oculta menus, bloqueia rotas e registra notificação aos administradores.
        Módulos <strong>obrigatórios</strong> no ensaio Laguna exigem confirmação explícita.
        O pacote de infraestrutura (setup) não pode ser desligado por aqui.
    </p>
</div>

@foreach($modulos as $m)
    @php
        $foco = session('modulo_foco') === $m['id'];
    @endphp
    <div class="bloco modulo-cfg" id="modulo-{{ $m['id'] }}" style="border-top-color:{{ $m['cor'] }};{{ $foco ? 'box-shadow:0 0 0 2px color-mix(in srgb, '.$m['cor'].' 35%, transparent);' : '' }}">
        <header style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin:0 0 12px">
            <span style="width:42px;height:42px;border-radius:12px;display:grid;place-items:center;color:#fff;background:linear-gradient(145deg,{{ $m['cor'] }},color-mix(in srgb,{{ $m['cor'] }} 70%,#000))">
                <i class="fa {{ $m['icone'] }}"></i>
            </span>
            <div style="flex:1;min-width:180px">
                <h2 style="margin:0;font-size:1.05rem">{{ $m['nome'] }}</h2>
                <p style="margin:2px 0 0;color:var(--suave);font-size:.78rem">
                    TR {{ $m['tr'] }} · {{ $m['nivel'] }} · <code style="font-size:.72rem">{{ $m['composer'] }}</code>
                </p>
            </div>
            <span class="badge {{ $m['ativo'] ? 'ok' : 'fail' }}">
                <i class="fa fa-{{ $m['ativo'] ? 'check' : 'ban' }}"></i>
                {{ $m['ativo'] ? 'Ativo' : 'Desativado' }}
            </span>
            @if(!$m['tenant_ok'] && $m['ativo'])
                <span class="badge warn"><i class="fa fa-warning"></i> Fora do tenant</span>
            @endif
            @if(!empty($m['admin_url']))
                <a class="botao claro" href="{{ url($m['admin_url']) }}" @if(!$m['ativo']) tabindex="-1" aria-disabled="true" style="opacity:.45;pointer-events:none" @endif>
                    <i class="fa fa-external-link"></i> Abrir
                </a>
            @endif
        </header>

        <form method="post" action="{{ url('/configuracoes/modulos/'.$m['id']) }}" class="form-mod-cfg">
            @csrf
            <div class="form-grid" style="margin-bottom:12px">
                <label style="display:flex;gap:8px;align-items:center;font-weight:700;color:var(--tinta)">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" @checked($m['ativo'])
                           @disabled(! $m['bloqueavel']) data-mod-ativo="{{ $m['id'] }}">
                    Módulo ativo para a cidade
                    @if(! $m['bloqueavel'])
                        <small style="color:var(--suave);font-weight:500">(não bloqueável)</small>
                    @endif
                </label>
            </div>

            @if(count($m['diretivas']) > 1)
                <h3 style="margin:0 0 8px;font-size:.85rem;letter-spacing:.06em;text-transform:uppercase;color:var(--suave)">Diretivas</h3>
                <div class="form-grid">
                    @foreach($m['diretivas'] as $d)
                        @if($d['chave'] === 'ativo')
                            @continue
                        @endif
                        @if($d['tipo'] === 'bool')
                            <input type="hidden" name="diretivas_flags[]" value="{{ $d['chave'] }}">
                            <label style="display:flex;gap:8px;align-items:center">
                                <input type="checkbox" name="diretivas[{{ $d['chave'] }}]" value="1" @checked($d['valor'])>
                                <span>
                                    {{ $d['rotulo'] }}
                                    @if(!empty($d['ajuda']))
                                        <small style="display:block;color:var(--suave);font-weight:500">{{ $d['ajuda'] }}</small>
                                    @endif
                                </span>
                            </label>
                        @else
                            <label>{{ $d['rotulo'] }}
                                <input type="{{ $d['tipo'] === 'float' ? 'number' : 'number' }}"
                                       name="diretivas[{{ $d['chave'] }}]"
                                       value="{{ $d['valor'] }}"
                                       @if(isset($d['min'])) min="{{ $d['min'] }}" @endif
                                       @if(isset($d['max'])) max="{{ $d['max'] }}" @endif
                                       @if($d['tipo'] === 'float') step="0.1" @else step="1" @endif>
                                @if(!empty($d['ajuda']))
                                    <small style="color:var(--suave);font-weight:500">{{ $d['ajuda'] }}</small>
                                @endif
                            </label>
                        @endif
                    @endforeach
                </div>
            @endif

            <details class="risco-box" style="margin:14px 0;border:1px solid #fed7aa;border-radius:12px;background:#fffbeb;padding:10px 12px" data-risco="{{ $m['id'] }}" @if(!$m['ativo'] || $foco) open @endif>
                <summary style="cursor:pointer;font-weight:800;color:#9a3412">
                    <i class="fa fa-exclamation-triangle"></i> Riscos ao desativar · impactos na cidade
                </summary>
                <div style="margin-top:8px">
                    <p style="margin:0 0 6px;font-size:.78rem;font-weight:700;color:#9a3412;text-transform:uppercase;letter-spacing:.04em">Impactos</p>
                    <ul style="margin:0 0 10px;padding-left:18px;color:#7c2d12;line-height:1.45;font-size:.86rem">
                        @foreach($m['impactos'] as $i)
                            <li>{{ $i }}</li>
                        @endforeach
                    </ul>
                    @if($m['fluxos'] !== [])
                        <p style="margin:0 0 6px;font-size:.78rem;font-weight:700;color:#9a3412;text-transform:uppercase;letter-spacing:.04em">Fluxos comprometidos</p>
                        <ul style="margin:0;padding-left:18px;color:#7c2d12;line-height:1.45;font-size:.86rem">
                            @foreach($m['fluxos'] as $f)
                                <li>{{ $f }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <label style="display:flex;gap:8px;align-items:flex-start;margin-top:12px;font-weight:700;color:#7c2d12" data-ciente-wrap="{{ $m['id'] }}">
                        <input type="checkbox" name="ciente_riscos" value="1" data-ciente="{{ $m['id'] }}">
                        <span>Estou ciente dos riscos e impactos para a cidade ao desativar este módulo.</span>
                    </label>
                </div>
            </details>

            <button class="botao" type="submit"><i class="fa fa-save"></i> Salvar «{{ $m['nome'] }}»</button>
        </form>
    </div>
@endforeach

<script>
(function () {
    document.querySelectorAll('[data-mod-ativo]').forEach(function (cb) {
        var id = cb.getAttribute('data-mod-ativo');
        var risco = document.querySelector('[data-risco="' + id + '"]');
        var ciente = document.querySelector('[data-ciente="' + id + '"]');
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
    });
})();
</script>
@endsection
