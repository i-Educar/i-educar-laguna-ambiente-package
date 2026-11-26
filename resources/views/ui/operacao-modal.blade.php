{{--
  Modal padrão disparado por botão data-buriti-modal="ID".
  @include('buriti-ui::operacao-modal', [
    'id' => 'modal-novo',
    'titulo' => 'Novo registro',
    'lead' => 'Preencha só o essencial.',
    'corpo' => $htmlOuUseSlot,
  ])
--}}
<div class="buriti-modal-backdrop" id="{{ $id }}" hidden role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-titulo">
    <div class="buriti-modal">
        <header>
            <div>
                <h2 id="{{ $id }}-titulo">{{ $titulo ?? 'Ação' }}</h2>
                @if(!empty($lead))
                    <p>{{ $lead }}</p>
                @endif
            </div>
            <button type="button" class="buriti-modal-fechar" data-buriti-fechar aria-label="Fechar">
                <i class="fa fa-times"></i>
            </button>
        </header>
        <div class="buriti-modal-corpo">
            {!! $corpo ?? '' !!}
            {{ $slot ?? '' }}
        </div>
    </div>
</div>
