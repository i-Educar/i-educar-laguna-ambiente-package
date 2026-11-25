{{-- Área de anexo com cara de “colar o papel no envelope” --}}
@php
    $campo = $campo ?? 'anexo';
    $rotulo = $rotulo ?? 'Anexar arquivo';
    $ajuda = $ajuda ?? 'Arraste o arquivo para cá ou toque para escolher no celular/computador.';
    $accept = $accept ?? '.pdf,application/pdf';
    $obrigatorio = ! empty($obrigatorio);
    $icone = $icone ?? 'fa-paperclip';
    $tituloAcao = $tituloAcao ?? 'Soltar o arquivo aqui';
    $uid = 'ax-'.preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $campo)).'-'.substr(md5($campo.$rotulo), 0, 6);
@endphp
<div class="buriti-anexo col-wide">
    <span class="buriti-anexo-rotulo"><i class="fa {{ $icone }}"></i> {{ $rotulo }}@if($obrigatorio) * @endif</span>
    <label class="buriti-anexo-drop" for="{{ $uid }}">
        <input type="file"
               name="{{ $campo }}"
               id="{{ $uid }}"
               accept="{{ $accept }}"
               @if($obrigatorio) required @endif
               @if(!empty($multiple)) multiple @endif>
        <span>
            <i class="fa {{ $icone }} ico" aria-hidden="true"></i>
            <strong>{{ $tituloAcao }}</strong>
            <span>{{ $ajuda }}</span>
        </span>
    </label>
    <div class="buriti-anexo-nome" aria-live="polite">
        <i class="fa fa-check-circle"></i>
        <span class="buriti-anexo-nome-txt">Arquivo escolhido</span>
    </div>
</div>
