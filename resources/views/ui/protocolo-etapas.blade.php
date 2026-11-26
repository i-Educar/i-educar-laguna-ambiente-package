{{--
  Linha do tempo institucional de protocolo (sem metáfora de chat).
  @var list<array{chave:string,rotulo:string,feito?:bool,atual?:bool,detalhe?:string}> $etapas
  @var string|null $titulo
--}}
@php
    $etapas = $etapas ?? [];
@endphp
@if($etapas !== [])
<section class="buriti-protocolo-etapas" aria-label="Andamento do protocolo">
    @if(!empty($titulo))
        <h2>{{ $titulo }}</h2>
    @endif
    <ol>
        @foreach($etapas as $i => $e)
            <li class="@if(!empty($e['atual'])) atual @endif @if(!empty($e['feito'])) feito @endif">
                <span class="num" aria-hidden="true">{{ $i + 1 }}</span>
                <div>
                    <strong>{{ $e['rotulo'] }}</strong>
                    @if(!empty($e['detalhe']))
                        <small>{{ $e['detalhe'] }}</small>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</section>
<style>
.buriti-protocolo-etapas{margin:14px 0 18px}
.buriti-protocolo-etapas h2{margin:0 0 10px;font-size:1rem}
.buriti-protocolo-etapas ol{list-style:none;margin:0;padding:0;display:grid;gap:0;border:1px solid var(--linha,#cfd8df);border-radius:12px;overflow:hidden;background:#fff}
.buriti-protocolo-etapas li{display:grid;grid-template-columns:40px 1fr;gap:10px;align-items:start;padding:12px 14px;border-bottom:1px solid var(--linha,#cfd8df);background:#fff}
.buriti-protocolo-etapas li:last-child{border-bottom:0}
.buriti-protocolo-etapas .num{width:28px;height:28px;border-radius:999px;display:grid;place-items:center;font-size:.75rem;font-weight:800;background:var(--clara,#e8eef8);color:var(--suave,#5b6b76);margin-top:1px}
.buriti-protocolo-etapas li.feito .num{background:color-mix(in srgb,var(--ok,#17663f) 18%,#fff);color:var(--ok,#17663f)}
.buriti-protocolo-etapas li.atual{background:color-mix(in srgb,var(--cor-tela,#1a5f9e) 8%,#fff)}
.buriti-protocolo-etapas li.atual .num{background:var(--cor-tela,#1a5f9e);color:#fff}
.buriti-protocolo-etapas strong{display:block;font-size:.92rem}
.buriti-protocolo-etapas small{display:block;margin-top:2px;color:var(--suave,#5b6b76);font-size:.82rem;line-height:1.4}
</style>
@endif
