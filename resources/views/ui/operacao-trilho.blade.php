{{--
  Trilho de ações com ordem fixa entre módulos.
  Slots (Blade components via sections locais ou HTML):
    $primaria   — botão sólido (criar/salvar/confirmar)
    $consulta   — filtrar/buscar
    $secundaria — navegação / claro
    $mais       — abre modal/painel
    $perigo     — destrutivo (direita)
  Permissão: omitir o botão (oculto) OU passar classe buriti-acao-bloqueada (visível desabilitado).
--}}
<nav class="buriti-trilho" aria-label="{{ $rotulo ?? 'Ações da tela' }}">
    <div class="buriti-trilho-grupo">
        {!! $primaria ?? '' !!}
        {!! $consulta ?? '' !!}
        {!! $secundaria ?? '' !!}
        {!! $mais ?? '' !!}
    </div>
    @if(!empty($perigo))
        <div class="buriti-trilho-grupo fim">
            {!! $perigo !!}
        </div>
    @endif
</nav>
