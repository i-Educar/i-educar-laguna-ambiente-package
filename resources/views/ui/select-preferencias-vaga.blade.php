{{--
  Seletor elegante de preferências de vaga/escola (portal externo).
  @var string $campo
  @var string $rotulo
  @var list<array{id:int|string,titulo?:string,cargo?:string,escola?:string,endereco?:string,segmento?:string,turmas?:string,carga_horaria?:string,quantidade?:int,detalhes?:string}> $opcoes
  @var list<int|string> $selecionados
  @var int $maximo
  @var string|null $ajuda
--}}
@php
    $campo = $campo ?? 'preferencias[]';
    $rotulo = $rotulo ?? 'Preferência de lotação';
    $opcoes = $opcoes ?? [];
    $selecionados = array_values(array_map('strval', (array) ($selecionados ?? [])));
    $maximo = max(1, (int) ($maximo ?? 3));
    $ajuda = $ajuda ?? ('Escolha até '.$maximo.' opções. A ordem define a prioridade de lotação.');
    $uid = 'pv-'.substr(md5($campo.$rotulo.count($opcoes)), 0, 8);
@endphp
<section class="buriti-pref-vaga col-wide" id="{{ $uid }}" data-max="{{ $maximo }}" aria-label="{{ $rotulo }}">
    <header class="buriti-pref-vaga-head">
        <div>
            <h3>@if(!empty($icone))<i class="fa {{ $icone }}"></i> @endif{{ $rotulo }}</h3>
            <p>{{ $ajuda }}</p>
        </div>
        <div class="buriti-pref-vaga-resumo" data-resumo>
            <span data-count>0</span>/<span>{{ $maximo }}</span> escolhida(s)
        </div>
    </header>

    <div class="buriti-pref-vaga-busca">
        <i class="fa fa-search" aria-hidden="true"></i>
        <input type="search" placeholder="Buscar por cargo, escola, segmento ou endereço…" data-filtro autocomplete="off">
    </div>

    <ol class="buriti-pref-vaga-ordem" data-ordem aria-live="polite"></ol>
    <div data-inputs></div>

    <div class="buriti-pref-vaga-grade" data-grade role="list">
        @foreach($opcoes as $o)
            @php
                $oid = (string) ($o['id'] ?? '');
                $cargo = trim((string) ($o['cargo'] ?? ''));
                $escola = trim((string) ($o['escola'] ?? ''));
                $titulo = trim((string) ($o['titulo'] ?? ($cargo !== '' && $escola !== '' ? $cargo.' — '.$escola : ($o['nome'] ?? $oid))));
                $endereco = trim((string) ($o['endereco'] ?? ''));
                $segmento = trim((string) ($o['segmento'] ?? ''));
                $turmas = trim((string) ($o['turmas'] ?? ''));
                $carga = trim((string) ($o['carga_horaria'] ?? ''));
                $qtd = (int) ($o['quantidade'] ?? 1);
                $busca = mb_strtolower(implode(' ', [$titulo, $cargo, $escola, $endereco, $segmento, $turmas]));
                $marcado = in_array($oid, $selecionados, true);
            @endphp
            <article class="buriti-pref-card @if($marcado) marcado @endif"
                     role="listitem"
                     tabindex="0"
                     data-id="{{ $oid }}"
                     data-busca="{{ $busca }}"
                     data-titulo="{{ $titulo }}"
                     aria-pressed="{{ $marcado ? 'true' : 'false' }}">
                <div class="buriti-pref-card-topo">
                    <span class="buriti-pref-rank" data-rank hidden></span>
                    <div class="buriti-pref-titulos">
                        @if($cargo !== '')
                            <span class="buriti-pref-cargo">{{ $cargo }}</span>
                        @endif
                        <strong>{{ $escola !== '' ? $escola : $titulo }}</strong>
                    </div>
                    <span class="buriti-pref-check" aria-hidden="true"><i class="fa fa-check"></i></span>
                </div>
                <dl class="buriti-pref-meta">
                    @if($segmento !== '')
                        <div><dt>Segmento / cursos</dt><dd>{{ $segmento }}</dd></div>
                    @endif
                    @if($turmas !== '')
                        <div><dt>Turmas</dt><dd>{{ $turmas }}</dd></div>
                    @endif
                    @if($endereco !== '')
                        <div class="wide"><dt>Endereço</dt><dd>{{ $endereco }}</dd></div>
                    @endif
                    @if($carga !== '' || $qtd > 0)
                        <div>
                            <dt>Oferta</dt>
                            <dd>
                                @if($qtd > 0){{ $qtd }} vaga{{ $qtd > 1 ? 's' : '' }}@endif
                                @if($carga !== ''){{ $qtd > 0 ? ' · ' : '' }}{{ $carga }}@endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </article>
        @endforeach
    </div>
    @if($opcoes === [])
        <p class="buriti-pref-vazio">Nenhuma vaga/cargo publicado neste edital ainda.</p>
    @endif
</section>

<style>
.buriti-pref-vaga{display:grid;gap:12px;margin:4px 0 8px}
.buriti-pref-vaga-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap}
.buriti-pref-vaga-head h3{margin:0;font-size:1rem;display:flex;align-items:center;gap:8px}
.buriti-pref-vaga-head p{margin:4px 0 0;color:var(--suave,#5b6b76);font-size:.88rem;max-width:42rem}
.buriti-pref-vaga-resumo{font-size:.78rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--cor-tela,var(--cor,#7c2d3a));background:var(--clara,#f8e8eb);border-radius:999px;padding:6px 12px}
.buriti-pref-vaga-busca{position:relative}
.buriti-pref-vaga-busca i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--suave,#5b6b76)}
.buriti-pref-vaga-busca input{width:100%;min-height:42px;padding:8px 12px 8px 36px;border:1px solid var(--linha,#d5c8cb);border-radius:12px;font:inherit;background:#fff}
.buriti-pref-vaga-ordem{list-style:none;margin:0;padding:0;display:flex;flex-wrap:wrap;gap:8px;min-height:0}
.buriti-pref-vaga-ordem:empty{display:none}
.buriti-pref-vaga-ordem li{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:color-mix(in srgb,var(--cor-tela,var(--cor,#7c2d3a)) 12%,#fff);border:1px solid color-mix(in srgb,var(--cor-tela,var(--cor,#7c2d3a)) 28%,#fff);font-size:.82rem;font-weight:700}
.buriti-pref-vaga-ordem .n{width:22px;height:22px;border-radius:999px;display:grid;place-items:center;background:var(--cor-tela,var(--cor,#7c2d3a));color:#fff;font-size:.72rem}
.buriti-pref-vaga-grade{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px}
.buriti-pref-card{position:relative;border:1px solid var(--linha,#d5c8cb);border-radius:14px;padding:12px 14px;background:#fff;cursor:pointer;transition:border-color .15s,box-shadow .15s,transform .15s;outline:none;display:grid;gap:10px}
.buriti-pref-card:hover,.buriti-pref-card:focus{border-color:color-mix(in srgb,var(--cor-tela,var(--cor,#7c2d3a)) 45%,var(--linha,#d5c8cb));box-shadow:0 10px 22px rgba(40,18,24,.08);transform:translateY(-1px)}
.buriti-pref-card.marcado{border-color:var(--cor-tela,var(--cor,#7c2d3a));background:linear-gradient(165deg,color-mix(in srgb,var(--clara,#f8e8eb) 70%,#fff),#fff);box-shadow:0 0 0 1px color-mix(in srgb,var(--cor-tela,var(--cor,#7c2d3a)) 35%,transparent)}
.buriti-pref-card.oculto{display:none}
.buriti-pref-card-topo{display:grid;grid-template-columns:auto 1fr auto;gap:10px;align-items:start}
.buriti-pref-rank{width:26px;height:26px;border-radius:999px;display:grid;place-items:center;background:var(--cor-tela,var(--cor,#7c2d3a));color:#fff;font-size:.75rem;font-weight:800}
.buriti-pref-rank[hidden]{display:none}
.buriti-pref-cargo{display:inline-flex;padding:2px 8px;border-radius:999px;font-size:.68rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;background:var(--clara,#f8e8eb);color:var(--cor-tela,var(--cor,#7c2d3a));margin-bottom:4px}
.buriti-pref-titulos strong{display:block;font-size:.95rem;line-height:1.25}
.buriti-pref-check{width:28px;height:28px;border-radius:999px;border:1.5px solid var(--linha,#d5c8cb);display:grid;place-items:center;color:transparent;background:#fff;transition:.15s}
.buriti-pref-card.marcado .buriti-pref-check{background:var(--cor-tela,var(--cor,#7c2d3a));border-color:var(--cor-tela,var(--cor,#7c2d3a));color:#fff}
.buriti-pref-meta{margin:0;display:grid;grid-template-columns:1fr 1fr;gap:8px}
.buriti-pref-meta .wide{grid-column:1/-1}
.buriti-pref-meta dt{font-size:.68rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--suave,#5b6b76)}
.buriti-pref-meta dd{margin:2px 0 0;font-size:.82rem;line-height:1.35;color:var(--tinta,#1a242b)}
.buriti-pref-vazio{margin:0;padding:14px;border:1px dashed var(--linha,#d5c8cb);border-radius:12px;color:var(--suave,#5b6b76);text-align:center}
@media (max-width:560px){.buriti-pref-meta{grid-template-columns:1fr}}
</style>

<script>
(function () {
    var root = document.getElementById(@json($uid));
    if (!root || root.dataset.bound) return;
    root.dataset.bound = '1';
    var max = parseInt(root.getAttribute('data-max') || '3', 10) || 3;
    var grade = root.querySelector('[data-grade]');
    var ordem = root.querySelector('[data-ordem]');
    var inputsBox = root.querySelector('[data-inputs]');
    var countEl = root.querySelector('[data-count]');
    var filtro = root.querySelector('[data-filtro]');
    var campoNome = @json($campo);
    var order = [];

    function cards() { return Array.prototype.slice.call(grade.querySelectorAll('.buriti-pref-card')); }
    function sync() {
        cards().forEach(function (card) {
            var id = card.getAttribute('data-id');
            var idx = order.indexOf(id);
            var on = idx >= 0;
            card.classList.toggle('marcado', on);
            card.setAttribute('aria-pressed', on ? 'true' : 'false');
            var rank = card.querySelector('[data-rank]');
            if (rank) {
                if (on) { rank.hidden = false; rank.textContent = String(idx + 1); }
                else { rank.hidden = true; rank.textContent = ''; }
            }
        });
        if (inputsBox) {
            inputsBox.innerHTML = '';
            order.forEach(function (id) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = campoNome;
                input.value = id;
                inputsBox.appendChild(input);
            });
        }
        if (countEl) countEl.textContent = String(order.length);
        if (ordem) {
            ordem.innerHTML = '';
            order.forEach(function (id, i) {
                var card = grade.querySelector('.buriti-pref-card[data-id="' + CSS.escape(id) + '"]');
                if (!card) return;
                var li = document.createElement('li');
                li.innerHTML = '<span class="n">' + (i + 1) + '</span><span>' + (card.getAttribute('data-titulo') || id) + '</span>';
                ordem.appendChild(li);
            });
        }
    }
    function toggle(card) {
        var id = card.getAttribute('data-id');
        var idx = order.indexOf(id);
        if (idx >= 0) order.splice(idx, 1);
        else {
            if (order.length >= max) {
                order.shift();
            }
            order.push(id);
        }
        sync();
    }
    cards().forEach(function (card) {
        if (card.classList.contains('marcado')) {
            var id = card.getAttribute('data-id');
            if (order.indexOf(id) < 0) order.push(id);
        }
        card.addEventListener('click', function (e) {
            e.preventDefault();
            toggle(card);
        });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggle(card);
            }
        });
    });
    if (filtro) {
        filtro.addEventListener('input', function () {
            var q = (filtro.value || '').toLowerCase().trim();
            cards().forEach(function (card) {
                var ok = !q || (card.getAttribute('data-busca') || '').indexOf(q) >= 0;
                card.classList.toggle('oculto', !ok);
            });
        });
    }
    // Limita seleção inicial ao máximo
    if (order.length > max) order = order.slice(0, max);
    sync();
})();
</script>
