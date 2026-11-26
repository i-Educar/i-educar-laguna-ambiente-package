{{--
  Lista de PDFs de resultados por processo (sem tabela completa na interface).
  @var string $titulo
  @var string|null $lead
  @var list<array{titulo:string,meta?:string,url:string,rotulo?:string}> $itens
  @var string|null $avisoLegal
  @var string|null $vazio
--}}
@php
    $titulo = $titulo ?? 'Resultados / convocações publicadas';
    $itens = $itens ?? [];
    $avisoLegal = $avisoLegal
        ?? 'A lista completa com identificação parcial (LGPD) está no PDF de cada processo, disponibilizado após o encerramento das etapas/fases.';
    $vazio = $vazio ?? 'Nenhum processo com etapas encerradas e resultado disponível para download neste momento.';
@endphp
<section class="buriti-lista-pdf" aria-label="{{ $titulo }}">
    <header>
        <h2>{{ $titulo }}</h2>
        @if(!empty($lead))
            <p>{{ $lead }}</p>
        @endif
    </header>

    @forelse($itens as $item)
        <article class="buriti-lista-pdf__item">
            <div>
                <strong>{{ $item['titulo'] ?? 'Processo' }}</strong>
                @if(!empty($item['meta']))
                    <span>{{ $item['meta'] }}</span>
                @endif
            </div>
            <a class="buriti-lista-pdf__btn" href="{{ $item['url'] }}" rel="noopener">
                <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                {{ $item['rotulo'] ?? 'Baixar PDF' }}
            </a>
        </article>
    @empty
        <p class="buriti-lista-pdf__vazio">{{ $vazio }}</p>
    @endforelse

    <p class="aviso-legal">{{ $avisoLegal }}</p>
</section>
<style>
.buriti-lista-pdf{margin:0 0 8px;padding:18px 18px 14px;border:1px solid #dde5eb;border-radius:16px;background:#fff}
.buriti-lista-pdf > header{margin:0 0 12px;padding:0;background:transparent;border:0}
.buriti-lista-pdf h2{margin:0 0 6px;font-size:1.08rem;font-weight:700;color:#16232b}
.buriti-lista-pdf header p{margin:0;color:#445560;font-size:.92rem;line-height:1.45;max-width:52rem}
.buriti-lista-pdf__item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-top:1px solid #e6edf2}
.buriti-lista-pdf__item:first-of-type{border-top:0;padding-top:4px}
.buriti-lista-pdf__item strong{display:block;color:#1a242b;font-size:.95rem}
.buriti-lista-pdf__item span{display:block;margin-top:2px;color:#556570;font-size:.85rem}
.buriti-lista-pdf__btn{flex-shrink:0;display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:10px;background:#0a7a8a;color:#fff!important;text-decoration:none;font-weight:650;font-size:.88rem}
.buriti-lista-pdf__btn:hover{filter:brightness(1.06)}
.buriti-lista-pdf__vazio{margin:0;padding:10px 0;color:#556570;font-size:.92rem}
.buriti-lista-pdf .aviso-legal{margin:12px 0 0;font-size:.8rem;color:#556570;line-height:1.4}
@media (max-width:640px){
  .buriti-lista-pdf__item{flex-direction:column;align-items:stretch}
  .buriti-lista-pdf__btn{justify-content:center}
}
</style>
