<form method="post" action="{{ url('/configuracoes/modulos/'.$m['id']) }}" class="cfg-form" novalidate data-mod-form="{{ $m['id'] }}" data-was-ativo="{{ !empty($m['ativo']) ? '1' : '0' }}">
    @csrf

    @if(!empty($m['guia']))
        <div class="cfg-guia" role="note">
            <strong><i class="fa fa-compass"></i> Como preencher</strong>
            <p>{{ $m['guia'] }}</p>
        </div>
    @endif

    <label class="cfg-ativo">
        @if($m['bloqueavel'])
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $m['ativo']))
                   data-mod-ativo="{{ $m['id'] }}">
        @else
            <input type="hidden" name="ativo" value="1">
            <input type="checkbox" value="1" checked disabled data-mod-ativo="{{ $m['id'] }}">
        @endif
        <span>
            Módulo ativo para a cidade
            @if(! $m['bloqueavel'])
                <small style="display:block;font-weight:500;color:var(--suave)">(não bloqueável — infraestrutura)</small>
            @else
                <small style="display:block;font-weight:500;color:var(--suave)">Desligar oculta menus e bloqueia rotas deste módulo.</small>
            @endif
        </span>
    </label>

    @foreach($ordemGrupos as $gid)
        @if(empty($porGrupo[$gid]))
            @continue
        @endif
        <section class="cfg-secao">
            <h3>{{ $gruposRotulo[$gid] ?? ucfirst($gid) }}</h3>
            @if($gid === 'setor')
                <p class="cfg-secao-lead">Contato da SME exibido nos portais e orientações à rede. Campos com * são os mínimos recomendados.</p>
            @elseif($gid === 'rede_apoio')
                <p class="cfg-secao-lead">Instituições para encaminhamento (Conselho Tutelar, CRAS, CREAS e parceiros). Preencha o que a cidade já formalizou.</p>
            @endif
            <div class="cfg-grid">
                @foreach($porGrupo[$gid] as $d)
                    @include('laguna-ambiente::partials.modulo-diretiva-campo', ['d' => $d])
                @endforeach
            </div>
        </section>
    @endforeach

    @foreach($porGrupo as $gid => $lista)
        @if(in_array($gid, $ordemGrupos, true))
            @continue
        @endif
        <section class="cfg-secao">
            <h3>{{ $gruposRotulo[$gid] ?? 'Outras opções' }}</h3>
            <div class="cfg-grid">
                @foreach($lista as $d)
                    @include('laguna-ambiente::partials.modulo-diretiva-campo', ['d' => $d])
                @endforeach
            </div>
        </section>
    @endforeach

    <details class="cfg-risco" data-risco="{{ $m['id'] }}" @if(! $m['ativo']) open @endif>
        <summary><i class="fa fa-exclamation-triangle"></i> Riscos ao desativar</summary>
        <p style="margin:8px 0 4px;font-size:.75rem;font-weight:800;color:#9a3412;text-transform:uppercase;letter-spacing:.04em">Impactos</p>
        <ul>
            @foreach($m['impactos'] as $i)
                <li>{{ $i }}</li>
            @endforeach
        </ul>
        @if(($m['fluxos'] ?? []) !== [])
            <p style="margin:10px 0 4px;font-size:.75rem;font-weight:800;color:#9a3412;text-transform:uppercase;letter-spacing:.04em">Fluxos comprometidos</p>
            <ul>
                @foreach($m['fluxos'] as $f)
                    <li>{{ $f }}</li>
                @endforeach
            </ul>
        @endif
        <label style="display:flex;gap:8px;align-items:flex-start;margin-top:12px;font-weight:700;color:#7c2d12">
            <input type="checkbox" name="ciente_riscos" value="1" data-ciente="{{ $m['id'] }}">
            <span>Estou ciente dos riscos e impactos para a cidade ao desativar este módulo. <em style="font-weight:800">(obrigatório para desligar)</em></span>
        </label>
    </details>

    <div class="cfg-acoes">
        @if(!empty($m['admin_url']) && $m['ativo'])
            <a class="botao claro" href="{{ url($m['admin_url']) }}"><i class="fa fa-external-link"></i> Abrir módulo</a>
        @endif
        <button type="button" class="botao claro" data-buriti-fechar>Cancelar</button>
        <button class="botao" type="submit" data-mod-salvar="{{ $m['id'] }}"><i class="fa fa-save"></i> Salvar configuração</button>
    </div>
</form>
