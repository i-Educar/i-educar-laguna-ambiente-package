{{--
  Botão Informes (ao lado do Download) + modal de leitura das infos complementares.
  @include('buriti-ui::informes-modulo', ['moduloId' => 'transporte'])
  Sem moduloId: resolve pela URL atual (prefixos_rota).
--}}
@php
    $Informes = \iEducar\Packages\BuritiLagunaAmbiente\Support\InformesModulo::class;
    $moduloId = isset($moduloId) && is_string($moduloId) && $moduloId !== ''
        ? $moduloId
        : (class_exists($Informes) ? $Informes::idDaRequisicao() : null);
    $mostrar = false;
    $secoes = [];
    $nomeModulo = '';
    $podeAlterar = false;
    $urlConfig = '#';
    $modalId = '';

    if (is_string($moduloId) && $moduloId !== ''
        && class_exists($Informes)
        && $Informes::temComplementares($moduloId)
        && ! $Informes::jaRenderizado($moduloId)
    ) {
        $Informes::marcarRenderizado($moduloId);
        $mostrar = true;
        $secoes = $Informes::secoes($moduloId);
        $nomeModulo = $Informes::nomeModulo($moduloId);
        $podeAlterar = $Informes::podeAlterar();
        $urlConfig = $Informes::urlConfiguracao($moduloId);
        $modalId = 'informes-mod-'.$moduloId;
    }
@endphp

@if($mostrar)
<button type="button"
        class="botao informes"
        data-buriti-modal="{{ $modalId }}"
        title="Informações complementares do módulo"
        aria-label="Informes do módulo">
    <i class="fa fa-info" aria-hidden="true"></i>
</button>

@include('buriti-ui::operacao-modal', [
    'id' => $modalId,
    'titulo' => 'Informes — '.$nomeModulo,
    'lead' => 'Informações complementares cadastradas na configuração deste módulo.',
    'corpo' => view('buriti-ui::informes-modulo-corpo', [
        'secoes' => $secoes,
        'podeAlterar' => $podeAlterar,
        'urlConfig' => $urlConfig,
        'nomeModulo' => $nomeModulo,
    ])->render(),
])
@endif
