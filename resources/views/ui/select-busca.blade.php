{{-- Seletor único com campo de busca (educação / pouco TI) --}}
@php
    $campo = $campo ?? 'id';
    $rotulo = $rotulo ?? 'Selecionar';
    $obrigatorio = ! empty($obrigatorio);
    $opcoes = $opcoes ?? [];
    $selecionado = $selecionado ?? null;
    $placeholder = $placeholder ?? 'Digite para encontrar…';
    $vazio = $vazio ?? '— escolher na lista —';
    $ajuda = $ajuda ?? null;
    $multiplo = false;
    $uid = 'sb-'.preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $campo)).'-'.substr(md5($campo.$rotulo.'1'), 0, 6);
@endphp
<label class="buriti-select {{ !empty($colWide) || ($colWide ?? true) ? 'col-wide' : '' }}">
    <span class="buriti-select-rotulo">@if(!empty($icone))<i class="fa {{ $icone }}"></i> @endif{{ $rotulo }}@if($obrigatorio) * @endif</span>
    <input type="search"
           class="buriti-select-busca"
           placeholder="{{ $placeholder }}"
           data-for="{{ $uid }}"
           autocomplete="off"
           aria-label="Buscar em {{ $rotulo }}">
    <select name="{{ $campo }}"
            id="{{ $id ?? $uid }}"
            class="buriti-select-lista"
            size="{{ (int) ($tamanho ?? 7) }}"
            @if($obrigatorio) required @endif
            @if(!empty($onchange)) onchange="{{ $onchange }}" @endif
            {!! $attrs ?? '' !!}>
        <option value="">{{ $vazio }}</option>
        @foreach($opcoes as $o)
            @php
                $oid = is_array($o) ? ($o['id'] ?? $o['value'] ?? '') : $o;
                $olabel = is_array($o) ? ($o['nome'] ?? $o['label'] ?? $o['titulo'] ?? (string) $oid) : (string) $o;
                $ometa = is_array($o) ? trim((string) ($o['meta'] ?? $o['escola'] ?? $o['login'] ?? '')) : '';
                $odataId = is_array($o) ? ($o['data_id'] ?? null) : null;
                $odataExtra = is_array($o) ? (array) ($o['data'] ?? []) : [];
                $obusca = mb_strtolower($olabel.' '.$ometa.' '.$oid);
            @endphp
            <option value="{{ $oid }}"
                    data-busca="{{ $obusca }}"
                    @if($odataId !== null) data-id="{{ $odataId }}" @endif
                    @foreach($odataExtra as $dk => $dv)
                        data-{{ $dk }}="{{ is_array($dv) || is_object($dv) ? e(json_encode($dv, JSON_UNESCAPED_UNICODE)) : e((string) $dv) }}"
                    @endforeach
                    @selected((string) $selecionado === (string) $oid)>
                {{ $olabel }}@if($ometa !== '') · {{ $ometa }}@endif
            </option>
        @endforeach
    </select>
    <small class="buriti-select-ajuda">
        {{ $ajuda ?? ('Digite para filtrar · '.count($opcoes).' opção(ões). Use o mouse ou as setas para escolher.') }}
    </small>
</label>
