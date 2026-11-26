{{-- Corpo do modal Informes (somente leitura). --}}
@php
    $secoes = $secoes ?? [];
    $podeAlterar = (bool) ($podeAlterar ?? false);
    $urlConfig = $urlConfig ?? '#';
@endphp

<div class="informes-mod-corpo">
    @if($secoes === [])
        <p class="informes-mod-vazio">
            Nenhuma informação complementar preenchida ainda (setor responsável / rede de apoio).
            @if($podeAlterar)
                Use <strong>Alterar</strong> para completar na configuração do módulo.
            @endif
        </p>
    @else
        @foreach($secoes as $secao)
            <section class="informes-mod-secao" aria-label="{{ $secao['titulo'] }}">
                <h3>{{ $secao['titulo'] }}</h3>
                <dl>
                    @foreach($secao['itens'] as $item)
                        <div>
                            <dt>{{ $item['rotulo'] }}</dt>
                            <dd>{!! nl2br(e($item['valor'])) !!}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach
    @endif

    <div class="informes-mod-acoes">
        <button type="button" class="botao claro" data-buriti-fechar>Fechar</button>
        @if($podeAlterar)
            <a class="botao" href="{{ $urlConfig }}">
                <i class="fa fa-pencil" aria-hidden="true"></i> Alterar
            </a>
        @endif
    </div>
</div>

<style>
.informes-mod-corpo { display: grid; gap: 14px; }
.informes-mod-vazio { margin: 0; color: var(--suave, #5b6b76); line-height: 1.45; }
.informes-mod-secao h3 {
    margin: 0 0 8px;
    font-size: .78rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--suave, #5b6b76);
}
.informes-mod-secao dl {
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px 14px;
}
.informes-mod-secao dt {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--suave, #5b6b76);
}
.informes-mod-secao dd {
    margin: 2px 0 0;
    font-weight: 600;
    color: var(--tinta, #1a242b);
    white-space: pre-wrap;
    word-break: break-word;
}
.informes-mod-acoes {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 4px;
    padding-top: 12px;
    border-top: 1px solid var(--linha, #e2e8f0);
}
</style>
