{{-- Seletor múltiplo com busca e chips (escolas, disciplinas, etc.) --}}
@php
    $campo = $campo ?? 'ids[]';
    $rotulo = $rotulo ?? 'Selecionar vários';
    $opcoes = $opcoes ?? [];
    $selecionados = array_map('strval', (array) ($selecionados ?? []));
    $placeholder = $placeholder ?? 'Digite para encontrar e marcar…';
    $ajuda = $ajuda ?? null;
    $uid = 'sm-'.preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $campo)).'-'.substr(md5($campo.$rotulo.'m'), 0, 6);
@endphp
<label class="buriti-select buriti-select-multi col-wide">
    <span class="buriti-select-rotulo">@if(!empty($icone))<i class="fa {{ $icone }}"></i> @endif{{ $rotulo }}</span>
    <input type="search"
           class="buriti-select-busca"
           placeholder="{{ $placeholder }}"
           data-for="{{ $uid }}"
           autocomplete="off"
           aria-label="Buscar em {{ $rotulo }}">
    <select name="{{ $campo }}"
            id="{{ $id ?? $uid }}"
            class="buriti-select-lista"
            multiple
            size="{{ (int) ($tamanho ?? 8) }}"
            @if(!empty($obrigatorio)) required @endif>
        @foreach($opcoes as $o)
            @php
                $oid = is_array($o) ? ($o['id'] ?? $o['value'] ?? '') : $o;
                $olabel = is_array($o) ? ($o['nome'] ?? $o['label'] ?? $o['titulo'] ?? (string) $oid) : (string) $o;
                $ometa = is_array($o) ? trim((string) ($o['meta'] ?? $o['escola'] ?? $o['turmas'] ?? '')) : '';
                if ($ometa !== '' && is_numeric($ometa)) {
                    $ometa = $ometa.' turma(s)';
                }
                $obusca = mb_strtolower($olabel.' '.$ometa.' '.$oid);
            @endphp
            <option value="{{ $oid }}"
                    data-busca="{{ $obusca }}"
                    @selected(in_array((string) $oid, $selecionados, true))>
                {{ $olabel }}@if($ometa !== '') · {{ $ometa }}@endif
            </option>
        @endforeach
    </select>
    <small class="buriti-select-ajuda">
        {{ $ajuda ?? ('Segure Ctrl (ou Cmd no Mac) para marcar várias. Digite para filtrar · '.count($opcoes).' opção(ões).') }}
    </small>
</label>
