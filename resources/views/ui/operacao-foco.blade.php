{{--
  Card de foco: o que importa agora.
  Uso:
    @include('buriti-ui::operacao-foco', [
      'kicker' => 'Agora',
      'titulo' => '3 pedidos aguardam separação',
      'texto' => 'Priorize os do setor Pedagógico.',
    ])
--}}
<section class="buriti-foco" @if(!empty($id)) id="{{ $id }}" @endif>
    @if(!empty($kicker))
        <p class="buriti-foco-kicker">{{ $kicker }}</p>
    @endif
    @if(!empty($titulo))
        <h2 class="buriti-foco-titulo">{{ $titulo }}</h2>
    @endif
    @if(!empty($texto))
        <p class="buriti-foco-texto">{{ $texto }}</p>
    @endif
    @if(!empty($slot) || isset($slot))
        <div class="buriti-foco-meta">{{ $slot ?? '' }}</div>
    @endif
    {{ $meta ?? '' }}
</section>
