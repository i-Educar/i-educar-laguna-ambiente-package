@php
    /** @var array $d */
    $chave = $d['chave'];
    $tipo = $d['tipo'] ?? 'string';
    $nome = 'diretivas['.$chave.']';
    $oldKey = 'diretivas.'.$chave;
    $valorBruto = old($oldKey, $d['valor'] ?? '');
    if ($tipo === 'bool') {
        $valorBool = old($oldKey) !== null
            ? filter_var(old($oldKey), FILTER_VALIDATE_BOOLEAN)
            : !empty($d['valor']);
    }
    $telExibir = $valorBruto;
    if ($tipo === 'tel' && is_scalar($valorBruto) && $valorBruto !== '') {
        $dig = preg_replace('/\D+/', '', (string) $valorBruto) ?? '';
        if (strlen($dig) === 11) {
            $telExibir = sprintf('(%s) %s-%s', substr($dig, 0, 2), substr($dig, 2, 5), substr($dig, 7));
        } elseif (strlen($dig) === 10) {
            $telExibir = sprintf('(%s) %s-%s', substr($dig, 0, 2), substr($dig, 2, 4), substr($dig, 6));
        } else {
            $telExibir = $dig;
        }
    }
@endphp
@if($chave === 'ativo')
    {{-- tratado no topo do formulário --}}
@elseif($tipo === 'bool')
    <input type="hidden" name="diretivas_flags[]" value="{{ $chave }}">
    <label class="cfg-flag">
        <input type="checkbox" name="{{ $nome }}" value="1" @checked($valorBool ?? false)>
        <span>
            <strong>{{ $d['rotulo'] }}</strong>
            @if(!empty($d['ajuda']))
                <small>{{ $d['ajuda'] }}</small>
            @endif
        </span>
    </label>
@elseif(in_array($tipo, ['int', 'float'], true))
    <label class="cfg-campo">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}@if(!empty($d['sufixo'])) <em class="cfg-sufixo">({{ $d['sufixo'] }})</em>@endif</span>
        <input type="number"
               name="{{ $nome }}"
               value="{{ $valorBruto }}"
               inputmode="{{ $tipo === 'float' ? 'decimal' : 'numeric' }}"
               @if(isset($d['min'])) min="{{ $d['min'] }}" @endif
               @if(isset($d['max'])) max="{{ $d['max'] }}" @endif
               @if($tipo === 'float') step="0.1" @else step="1" @endif
               placeholder="{{ $d['placeholder'] ?? ($d['padrao'] ?? '') }}"
               @if(isset($d['min']) || isset($d['max'])) title="@if(isset($d['min']))Mín. {{ $d['min'] }}@endif @if(isset($d['max']))· Máx. {{ $d['max'] }}@endif" @endif>
        @if(!empty($d['ajuda']))
            <small>{{ $d['ajuda'] }}</small>
        @elseif(isset($d['min']) || isset($d['max']))
            <small>Intervalo permitido:@if(isset($d['min'])) {{ $d['min'] }}@endif@if(isset($d['min']) && isset($d['max'])) – @endif@if(isset($d['max'])){{ $d['max'] }}@endif</small>
        @endif
    </label>
@elseif($tipo === 'email')
    <label class="cfg-campo">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}@if(!empty($d['essencial'])) <abbr title="Recomendado para o público">*</abbr>@endif</span>
        <input type="email" name="{{ $nome }}" value="{{ $valorBruto }}"
               maxlength="{{ $d['maxlength'] ?? 120 }}" autocomplete="email"
               placeholder="{{ $d['placeholder'] ?? 'setor@municipio.gov.br' }}"
               pattern="[^@\s]+@[^@\s]+\.[^@\s]+"
               title="Informe um e-mail válido (ex.: setor@municipio.gov.br)">
        @if(!empty($d['ajuda'])) <small>{{ $d['ajuda'] }}</small> @endif
    </label>
@elseif($tipo === 'tel')
    <label class="cfg-campo">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}@if(!empty($d['essencial'])) <abbr title="Recomendado para o público">*</abbr>@endif</span>
        <input type="tel" name="{{ $nome }}" value="{{ $telExibir }}"
               maxlength="{{ $d['maxlength'] ?? 20 }}" inputmode="tel" autocomplete="tel"
               placeholder="{{ $d['placeholder'] ?? '(00) 00000-0000' }}"
               data-mascara="telefone"
               title="DDD + número. Ex.: (48) 3644-0000 ou (48) 99999-0000">
        @if(!empty($d['ajuda'])) <small>{{ $d['ajuda'] }}</small> @endif
    </label>
@elseif($tipo === 'url')
    <label class="cfg-campo">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}</span>
        <input type="url" name="{{ $nome }}" value="{{ $valorBruto }}"
               maxlength="{{ $d['maxlength'] ?? 240 }}" placeholder="{{ $d['placeholder'] ?? 'https://' }}"
               title="Inclua https://">
        @if(!empty($d['ajuda'])) <small>{{ $d['ajuda'] }}</small> @endif
    </label>
@elseif($tipo === 'password')
    <label class="cfg-campo">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}</span>
        <input type="password" name="{{ $nome }}" value=""
               maxlength="{{ $d['maxlength'] ?? 240 }}" autocomplete="new-password"
               placeholder="{{ $d['placeholder'] ?? '••••••••' }}"
               title="Deixe em branco para manter a senha atual">
        @if(!empty($d['ajuda'])) <small>{{ $d['ajuda'] }}</small> @else
            <small>Deixe em branco para manter a senha já gravada.</small>
        @endif
    </label>
@elseif($tipo === 'date')
    <label class="cfg-campo">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}</span>
        <input type="date" name="{{ $nome }}" value="{{ $valorBruto }}">
        @if(!empty($d['ajuda'])) <small>{{ $d['ajuda'] }}</small> @endif
    </label>
@elseif($tipo === 'textarea')
    <label class="cfg-campo cfg-wide">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}</span>
        <textarea name="{{ $nome }}" rows="3" maxlength="{{ $d['maxlength'] ?? 400 }}"
                  placeholder="{{ $d['placeholder'] ?? '' }}">{{ $valorBruto }}</textarea>
        @if(!empty($d['ajuda'])) <small>{{ $d['ajuda'] }}</small> @endif
    </label>
@elseif($tipo === 'select' && !empty($d['opcoes']))
    <label class="cfg-campo">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}</span>
        <select name="{{ $nome }}">
            @foreach($d['opcoes'] as $opt)
                @php
                    $ov = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                    $ol = is_array($opt) ? ($opt['label'] ?? $ov) : $opt;
                @endphp
                <option value="{{ $ov }}" @selected((string) $valorBruto === (string) $ov)>{{ $ol }}</option>
            @endforeach
        </select>
        @if(!empty($d['ajuda'])) <small>{{ $d['ajuda'] }}</small> @endif
    </label>
@else
    <label class="cfg-campo {{ ($d['wide'] ?? false) ? 'cfg-wide' : '' }}">
        <span class="cfg-rotulo">{{ $d['rotulo'] }}@if(!empty($d['essencial'])) <abbr title="Recomendado para o público">*</abbr>@endif</span>
        <input type="text" name="{{ $nome }}" value="{{ $valorBruto }}"
               maxlength="{{ $d['maxlength'] ?? 240 }}"
               placeholder="{{ $d['placeholder'] ?? '' }}"
               @if(($d['mascara'] ?? '') === 'horario') data-mascara="horario" @endif>
        @if(!empty($d['ajuda'])) <small>{{ $d['ajuda'] }}</small> @endif
    </label>
@endif
