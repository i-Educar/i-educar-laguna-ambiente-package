{{--
  Bloco institucional do setor responsável (portais externos).
  Dados vêm das diretivas do módulo (Ambiente Laguna) — sem HTML inventado.
  @var array{nome?:string,endereco?:string,horario?:string,telefone?:string,email?:string,observacao?:string,publicado?:bool}|string|null $setor
  @var string|null $titulo
  @var bool|null $compacto
--}}
@php
    $setor = $setor ?? null;
    $compacto = (bool) ($compacto ?? false);
    if (is_string($setor) && class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\SetorResponsavel::class)) {
        $setor = \iEducar\Packages\BuritiLagunaAmbiente\Support\SetorResponsavel::de($setor);
    }

    $temMapa = is_array($setor) && ($setor['endereco'] ?? '') !== '';
    $mapaQuery = $temMapa
        ? rawurlencode(trim(($setor['endereco'] ?? '').', Brasil'))
        : '';
    $mapaEmbed = $temMapa
        ? 'https://maps.google.com/maps?q='.$mapaQuery.'&hl=pt-BR&z=16&output=embed'
        : '';
    $mapaLink = $temMapa
        ? 'https://www.openstreetmap.org/search?query='.$mapaQuery
        : '';

    $telHref = '';
    if (is_array($setor) && ($setor['telefone'] ?? '') !== '') {
        $digitos = preg_replace('/\D+/', '', (string) $setor['telefone']);
        $telHref = $digitos !== '' ? 'tel:+55'.$digitos : '';
    }
@endphp
@if(is_array($setor) && !empty($setor['publicado']))
<section class="buriti-setor-contato{{ $compacto ? ' is-compacto' : '' }}{{ $temMapa ? ' has-mapa' : '' }}" aria-label="Contato SME — setor responsável">
    <div class="setor-shell">
        <div class="setor-info">
            <header class="setor-cab">
                <span class="setor-marca" aria-hidden="true"><i class="fa fa-university"></i></span>
                <div class="setor-cab-txt">
                    <span class="kicker">Contato SME</span>
                    <h2>{{ $titulo ?? (($setor['nome'] ?? '') !== '' ? $setor['nome'] : 'Setor responsável') }}</h2>
                    @if(($setor['nome'] ?? '') !== '' && !empty($titulo))
                        <p class="setor-nome">{{ $setor['nome'] }}</p>
                    @endif
                </div>
            </header>

            <ul class="setor-canais" role="list">
                @if(($setor['endereco'] ?? '') !== '')
                    <li class="canal canal-mapa">
                        <span class="canal-ico" aria-hidden="true"><i class="fa fa-map-marker"></i></span>
                        <div>
                            <span class="canal-rotulo">Endereço</span>
                            <strong title="{{ $setor['endereco'] }}">{{ $setor['endereco'] }}</strong>
                            @if($mapaLink !== '')
                                <a class="canal-acao" href="{{ $mapaLink }}" target="_blank" rel="noopener">Abrir no mapa</a>
                            @endif
                        </div>
                    </li>
                @endif
                @if(($setor['horario'] ?? '') !== '')
                    <li class="canal canal-horario">
                        <span class="canal-ico" aria-hidden="true"><i class="fa fa-clock-o"></i></span>
                        <div>
                            <span class="canal-rotulo">Horário</span>
                            <strong title="{{ $setor['horario'] }}">{{ $setor['horario'] }}</strong>
                        </div>
                    </li>
                @endif
                @if(($setor['telefone'] ?? '') !== '')
                    <li class="canal canal-fone">
                        <span class="canal-ico" aria-hidden="true"><i class="fa fa-phone"></i></span>
                        <div>
                            <span class="canal-rotulo">Telefone</span>
                            <strong>
                                @if($telHref !== '')
                                    <a href="{{ $telHref }}">{{ $setor['telefone'] }}</a>
                                @else
                                    {{ $setor['telefone'] }}
                                @endif
                            </strong>
                        </div>
                    </li>
                @endif
                @if(($setor['email'] ?? '') !== '')
                    <li class="canal canal-mail">
                        <span class="canal-ico" aria-hidden="true"><i class="fa fa-envelope"></i></span>
                        <div>
                            <span class="canal-rotulo">E-mail</span>
                            <strong><a href="mailto:{{ $setor['email'] }}">{{ $setor['email'] }}</a></strong>
                        </div>
                    </li>
                @endif
            </ul>

            @if(($setor['observacao'] ?? '') !== '')
                <p class="obs" title="{{ $setor['observacao'] }}">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    {{ $setor['observacao'] }}
                </p>
            @endif
        </div>

        @if($temMapa && $mapaEmbed !== '')
            <aside class="setor-mapa" aria-label="Mapa do endereço de atendimento">
                <iframe
                    title="Mapa — {{ $setor['endereco'] }}"
                    src="{{ $mapaEmbed }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                ></iframe>
                <a class="mapa-rodape" href="{{ $mapaLink }}" target="_blank" rel="noopener">
                    <i class="fa fa-external-link" aria-hidden="true"></i> Ver no OpenStreetMap
                </a>
            </aside>
        @endif
    </div>
</section>
<style>
.buriti-setor-contato{
    margin:0 0 16px;padding:0;border-radius:16px;overflow:hidden;
    border:1px solid color-mix(in srgb,var(--cor-tela,#1a5f9e) 16%,#cfd8df);
    background:#fff;
    box-shadow:0 10px 28px rgba(20,40,50,.04);
}
.buriti-setor-contato .setor-shell{
    display:grid;grid-template-columns:minmax(0,1.15fr) minmax(240px,.85fr);gap:0;
    min-width:0;
}
.buriti-setor-contato:not(.has-mapa) .setor-shell{grid-template-columns:1fr}
.buriti-setor-contato .setor-info{padding:16px 18px 14px;min-width:0}
.buriti-setor-contato .setor-cab{display:flex;gap:12px;align-items:flex-start;margin:0 0 14px}
.buriti-setor-contato .setor-marca{
    flex:0 0 auto;width:42px;height:42px;border-radius:12px;display:grid;place-items:center;
    background:linear-gradient(145deg,var(--cor-tela,#1a5f9e),color-mix(in srgb,var(--cor-tela,#1a5f9e) 70%,#0a2a4a));
    color:#fff;font-size:1.05rem;
    box-shadow:0 6px 14px color-mix(in srgb,var(--cor-tela,#1a5f9e) 28%,transparent);
}
.buriti-setor-contato .kicker{
    display:block;font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
    color:var(--cor-tela,#1a5f9e);margin:0 0 2px;
}
.buriti-setor-contato h2{
    margin:0;font-size:1.05rem;line-height:1.25;color:var(--tinta,#1a242b);
    overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;
}
.buriti-setor-contato .setor-nome{
    margin:4px 0 0;font-size:.86rem;color:var(--suave,#5b6b76);font-weight:600;
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.buriti-setor-contato .setor-canais{
    list-style:none;margin:0;padding:0;
    display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;
}
.buriti-setor-contato .canal{
    display:flex;gap:10px;align-items:flex-start;min-width:0;
    padding:10px 11px;border-radius:12px;
    border:1px solid #e4ebf0;background:#f8fafb;
}
.buriti-setor-contato .canal-ico{
    flex:0 0 auto;width:30px;height:30px;border-radius:9px;display:grid;place-items:center;
    font-size:.92rem;color:#fff;
}
.buriti-setor-contato .canal-mapa .canal-ico{background:#c2410c}
.buriti-setor-contato .canal-horario .canal-ico{background:#b45309}
.buriti-setor-contato .canal-fone .canal-ico{background:#047857}
.buriti-setor-contato .canal-mail .canal-ico{background:#1d4ed8}
.buriti-setor-contato .canal-mapa{border-color:color-mix(in srgb,#c2410c 18%,#e4ebf0);background:color-mix(in srgb,#c2410c 5%,#fff)}
.buriti-setor-contato .canal-horario{border-color:color-mix(in srgb,#b45309 18%,#e4ebf0);background:color-mix(in srgb,#b45309 5%,#fff)}
.buriti-setor-contato .canal-fone{border-color:color-mix(in srgb,#047857 18%,#e4ebf0);background:color-mix(in srgb,#047857 5%,#fff)}
.buriti-setor-contato .canal-mail{border-color:color-mix(in srgb,#1d4ed8 18%,#e4ebf0);background:color-mix(in srgb,#1d4ed8 5%,#fff)}
.buriti-setor-contato .canal-rotulo{
    display:block;font-size:.65rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;
    color:var(--suave,#5b6b76);margin:0 0 2px;
}
.buriti-setor-contato .canal strong{
    font-size:.88rem;font-weight:700;color:var(--tinta,#1a242b);line-height:1.3;
    overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;
}
.buriti-setor-contato .canal a{color:inherit;text-decoration:none}
.buriti-setor-contato .canal a:hover{text-decoration:underline}
.buriti-setor-contato .canal-acao{
    display:inline-block;margin-top:4px;font-size:.72rem;font-weight:700;
    color:#c2410c;text-decoration:none;
}
.buriti-setor-contato .canal-acao:hover{text-decoration:underline}
.buriti-setor-contato .obs{
    margin:12px 0 0;padding:10px 12px;border-radius:10px;font-size:.84rem;line-height:1.4;
    color:#3d4d58;background:color-mix(in srgb,var(--cor-tela,#1a5f9e) 6%,#fff);
    border:1px solid color-mix(in srgb,var(--cor-tela,#1a5f9e) 14%,#dde5eb);
    overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;
}
.buriti-setor-contato .obs i{color:var(--cor-tela,#1a5f9e);margin-right:4px}
.buriti-setor-contato .setor-mapa{
    position:relative;min-height:220px;background:#dfe7ec;
    border-left:1px solid color-mix(in srgb,var(--cor-tela,#1a5f9e) 12%,#cfd8df);
}
.buriti-setor-contato .setor-mapa iframe{
    position:absolute;inset:0 0 28px 0;width:100%;height:calc(100% - 28px);border:0;
}
.buriti-setor-contato .mapa-rodape{
    position:absolute;left:0;right:0;bottom:0;height:28px;
    display:flex;align-items:center;justify-content:center;gap:6px;
    font-size:.72rem;font-weight:700;color:#334155;text-decoration:none;
    background:rgba(255,255,255,.94);border-top:1px solid #d7e0e6;
}
.buriti-setor-contato .mapa-rodape:hover{background:#fff;color:var(--cor-tela,#1a5f9e)}
.buriti-setor-contato.is-compacto .setor-info{padding:14px 14px 12px}
.buriti-setor-contato.is-compacto .setor-marca{width:38px;height:38px;border-radius:10px;font-size:.95rem}
.buriti-setor-contato.is-compacto h2{font-size:.98rem}
.buriti-setor-contato.is-compacto .setor-mapa{min-height:200px}
@media (max-width:900px){
    .buriti-setor-contato .setor-shell{grid-template-columns:1fr}
    .buriti-setor-contato .setor-mapa{min-height:200px;border-left:0;border-top:1px solid #d7e0e6}
    .buriti-setor-contato .setor-mapa iframe{position:relative;inset:auto;height:200px}
}
@media (max-width:560px){
    .buriti-setor-contato .setor-canais{grid-template-columns:1fr}
}
</style>
@endif
