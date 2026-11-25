{{--
  Cabeçalho único Buriti (todos os módulos):
  [Início · i-Educar · i-Diário]  ·  [título + descrição]  ·  [Buscar · Notificações]
  Sem sanduíche — o menu do módulo fica na lateral.
--}}
@php
    $homeModulo = $homeModulo ?? url('/');
    $nomeModulo = $nomeModulo ?? ($identidade['nome'] ?? 'Módulo');
    $descricaoModulo = $descricaoModulo ?? ($identidade['descricao'] ?? null);
    $ieducarHome = \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/intranet/index.php');
    $idiarioUrl = class_exists(\App\Support\IdiarioPortal::class)
        ? (\App\Support\IdiarioPortal::urlEntrada() ?? \App\Support\IdiarioPortal::url())
        : null;
    $notificacoesUrl = \Illuminate\Support\Facades\Route::has('notifications.index')
        ? route('notifications.index')
        : url('/notificacoes');

    // Itens para busca rápida no próprio módulo (lateral)
    $itensBusca = [];
    foreach (($menuGrupos ?? $grupos ?? []) as $g) {
        foreach (($g['itens'] ?? []) as $item) {
            if (empty($item['url']) || empty($item['rotulo'])) {
                continue;
            }
            $itensBusca[] = [
                'rotulo' => (string) $item['rotulo'],
                'url' => url($item['url']),
                'grupo' => (string) ($g['nome'] ?? ''),
                'icone' => (string) ($item['icone'] ?? 'fa-circle-o'),
            ];
        }
    }
@endphp
@include('buriti-ui::estilos')
<header class="topo topo-buriti" role="banner">
    <div class="topo-esq">
        <a class="acao-topo-buriti" href="{{ $homeModulo }}" title="Início do módulo" aria-label="Início do módulo">
            <i class="fa fa-home" aria-hidden="true"></i>
        </a>
        <a class="acao-topo-buriti" href="{{ $ieducarHome }}" title="i-Educar" aria-label="Abrir i-Educar">
            <svg viewBox="0 0 16 16" width="18" height="18" aria-hidden="true">
                <path fill="currentColor" d="M13.12,10,8.06,1.22,2.88,10l.65.39.32-.53a1.93,1.93,0,0,1,1.57-.77A1.78,1.78,0,0,1,7.29,10.7H8.71a1.77,1.77,0,0,1,1.86-1.62,2,2,0,0,1,1.78,1.1h0l.11.2ZM10.57,8.32A2.58,2.58,0,0,0,8,10.22,2.6,2.6,0,0,0,5.42,8.31a2.84,2.84,0,0,0-.72.1L8.05,2.74l3.28,5.68A2.68,2.68,0,0,0,10.57,8.32Z"/>
                <polygon fill="#8314f7" points="7.04 5.32 8.98 5.32 8.01 3.73 7.04 5.32"/>
            </svg>
        </a>
        @if(!empty($idiarioUrl))
            <a class="acao-topo-buriti" href="{{ $idiarioUrl }}" title="i-Diário" aria-label="Abrir i-Diário" target="_blank" rel="noopener">
                <i class="fa fa-book" aria-hidden="true"></i>
            </a>
        @endif
        {{-- Extras à esquerda (sem sanduíche) --}}
        @if(!empty($topoExtraEsq))
            <span class="topo-extra-esq" data-topo-extra-esq>{!! $topoExtraEsq !!}</span>
        @endif
    </div>

    <div class="topo-centro">
        <span class="topo-nome-modulo">{{ $nomeModulo }}</span>
        @if(!empty($descricaoModulo))
            <span class="topo-desc-modulo">{{ $descricaoModulo }}</span>
        @endif
    </div>

    <div class="topo-dir">
        {!! $topoExtraDir ?? '' !!}

        <div class="menu-drop-buriti" data-drop-buriti data-drop-busca>
            <button type="button" class="acao-topo-buriti" aria-expanded="false" aria-haspopup="true" title="Buscar" aria-label="Buscar no módulo">
                <i class="fa fa-search" aria-hidden="true"></i>
            </button>
            <div class="gaveta-buriti gaveta-busca" role="dialog" aria-label="Busca rápida">
                <label class="busca-campo">
                    <span class="sr-only">Buscar</span>
                    <input type="search" placeholder="Buscar telas deste módulo…" autocomplete="off" data-busca-topo>
                </label>
                <div class="busca-lista" data-busca-lista>
                    @forelse($itensBusca as $item)
                        <a role="menuitem" href="{{ $item['url'] }}" data-busca-item="{{ mb_strtolower($item['rotulo'].' '.$item['grupo']) }}">
                            <i class="fa {{ $item['icone'] }}"></i>
                            <span>
                                <strong>{{ $item['rotulo'] }}</strong>
                                @if($item['grupo'] !== '')
                                    <small>{{ $item['grupo'] }}</small>
                                @endif
                            </span>
                        </a>
                    @empty
                        <p class="busca-vazio">Nenhum atalho cadastrado neste módulo.</p>
                    @endforelse
                </div>
                <a class="busca-ieducar" href="{{ $ieducarHome }}"><i class="fa fa-external-link"></i> Busca no i-Educar</a>
            </div>
        </div>

        <div class="menu-drop-buriti" data-drop-buriti data-drop-notif>
            <button type="button" class="acao-topo-buriti" aria-expanded="false" aria-haspopup="true"
                    title="Notificações" aria-label="Notificações" data-notif-btn>
                <i class="fa fa-bell" aria-hidden="true"></i>
                <span class="notif-ponto" data-notif-ponto hidden aria-hidden="true">0</span>
            </button>
            <div class="gaveta-buriti gaveta-notif" role="dialog" aria-label="Notificações">
                <div class="notif-barra">
                    <strong>Notificações</strong>
                    <button type="button" data-notif-marcar-todas>Marcar todas como lidas (<span data-notif-count>0</span>)</button>
                </div>
                <div class="notif-lista" data-notif-lista>
                    <p class="notif-vazio" data-notif-loading>Carregando…</p>
                </div>
                <div class="notif-rodape">
                    <a href="{{ $notificacoesUrl }}" data-notif-ver-todas>
                        <i class="fa fa-list" aria-hidden="true"></i> Ver todas as notificações
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
<script>
(function () {
    if (window.__buritiTopoPadrao) return;
    window.__buritiTopoPadrao = true;

    var URL_COUNT = @json(url('/notificacoes/quantidade-nao-lidas'));
    var URL_LIST = @json(url('/notificacoes/retorna-notificacoes-usuario'));
    var URL_READ = @json(url('/notificacoes/marca-como-lida'));
    var URL_READ_ALL = @json(url('/notificacoes/marca-todas-como-lidas'));
    var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* Remove sanduíches injetados por layouts antigos */
    document.querySelectorAll('[data-topo-extra-esq] .hamburguer, [data-topo-extra-esq] [aria-label*="Abrir módulos"], [data-topo-extra-esq] .fa-bars').forEach(function (el) {
        var btn = el.closest('button, a') || el;
        if (btn && btn.parentNode) btn.parentNode.removeChild(btn);
    });

    function fecharTodos(exceto) {
        document.querySelectorAll('[data-drop-buriti].aberto').forEach(function (wrap) {
            if (exceto && wrap === exceto) return;
            wrap.classList.remove('aberto');
            var b = wrap.querySelector(':scope > button');
            if (b) b.setAttribute('aria-expanded', 'false');
        });
    }

    function atualizarBadge(n) {
        n = parseInt(n, 10) || 0;
        document.querySelectorAll('[data-notif-ponto]').forEach(function (el) {
            if (n < 1) {
                el.hidden = true;
                el.textContent = '0';
                return;
            }
            el.hidden = false;
            el.textContent = n > 99 ? '99+' : String(n);
        });
        document.querySelectorAll('[data-notif-count]').forEach(function (el) {
            el.textContent = String(n);
        });
    }

    function carregarContagem() {
        return fetch(URL_COUNT, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) { return r.ok ? r.json() : 0; }).then(function (j) {
            var n = 0;
            if (typeof j === 'number') n = j;
            else if (j && typeof j === 'object') n = j.count ?? j.quantidade ?? j.not_read_count ?? 0;
            atualizarBadge(n);
            return n;
        }).catch(function () { return 0; });
    }

    function escapar(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    var listaCarregada = false;

    function renderLista(items) {
        var box = document.querySelector('[data-notif-lista]');
        if (!box) return;
        if (!items || !items.length) {
            box.innerHTML = '<p class="notif-vazio">Nenhuma notificação recente.</p>';
            return;
        }
        box.innerHTML = items.map(function (v) {
            var unread = !v.read_at;
            var cls = unread ? 'notif-item unread' : 'notif-item read';
            var d = v.created_at ? new Date(v.created_at) : null;
            var data = d && !isNaN(d.getTime()) ? d.toLocaleString('pt-BR') : '';
            var href = v.link || '#';
            return '<a class="' + cls + '" href="' + escapar(href) + '" data-id="' + escapar(v.id) + '" data-notif-item>'
                + '<p>' + (v.text || '') + '</p>'
                + '<p class="date-notification">' + escapar(data) + '</p></a>';
        }).join('');
    }

    function carregarLista() {
        var box = document.querySelector('[data-notif-lista]');
        if (box && !listaCarregada) {
            box.innerHTML = '<p class="notif-vazio" data-notif-loading>Carregando…</p>';
        }
        return fetch(URL_LIST, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) { return r.ok ? r.json() : []; }).then(function (data) {
            listaCarregada = true;
            renderLista(Array.isArray(data) ? data : []);
            return data;
        }).catch(function () {
            if (box) box.innerHTML = '<p class="notif-vazio">Não foi possível carregar as notificações.</p>';
        });
    }

    function postJson(url, body) {
        var headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        };
        if (CSRF) headers['X-CSRF-TOKEN'] = CSRF;
        return fetch(url, {
            method: 'POST',
            headers: headers,
            credentials: 'same-origin',
            body: body ? JSON.stringify(body) : '{}'
        });
    }

    document.addEventListener('click', function (e) {
        var alvo = e.target;
        if (!alvo || !alvo.closest) return;

        var marcar = alvo.closest('[data-notif-marcar-todas]');
        if (marcar) {
            e.preventDefault();
            e.stopPropagation();
            postJson(URL_READ_ALL).then(function () {
                document.querySelectorAll('[data-notif-lista] .notif-item.unread').forEach(function (a) {
                    a.classList.remove('unread');
                    a.classList.add('read');
                });
                atualizarBadge(0);
            });
            return;
        }

        var item = alvo.closest('[data-notif-item]');
        if (item) {
            var id = item.getAttribute('data-id');
            if (id) {
                postJson(URL_READ, { notifications: [id] });
                item.classList.remove('unread');
                item.classList.add('read');
                var ponto = document.querySelector('[data-notif-ponto]');
                var atual = ponto && !ponto.hidden ? (parseInt(ponto.textContent, 10) || 1) : 0;
                if (ponto && ponto.textContent.indexOf('+') >= 0) {
                    carregarContagem();
                } else {
                    atualizarBadge(Math.max(0, atual - 1));
                }
            }
            /* deixa o navegador seguir o link */
            return;
        }

        var btn = alvo.closest('[data-drop-buriti] > button');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            var wrap = btn.parentElement;
            var aberto = wrap.classList.toggle('aberto');
            btn.setAttribute('aria-expanded', aberto ? 'true' : 'false');
            fecharTodos(wrap);
            if (aberto) {
                var input = wrap.querySelector('[data-busca-topo]');
                if (input) setTimeout(function () { input.focus(); }, 30);
                if (wrap.hasAttribute('data-drop-notif')) {
                    carregarLista();
                    carregarContagem();
                }
            }
            return;
        }
        if (alvo.closest('[data-drop-buriti] .gaveta-buriti a')) return;
        if (alvo.closest('[data-drop-buriti].aberto .gaveta-buriti')) return;
        fecharTodos();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') fecharTodos();
    });

    document.addEventListener('input', function (e) {
        var input = e.target;
        if (!input || !input.matches || !input.matches('[data-busca-topo]')) return;
        var q = (input.value || '').toLowerCase().normalize('NFD').replace(/\p{M}/gu, '');
        var lista = input.closest('.gaveta-busca');
        if (!lista) return;
        lista.querySelectorAll('[data-busca-item]').forEach(function (a) {
            var txt = (a.getAttribute('data-busca-item') || '').normalize('NFD').replace(/\p{M}/gu, '');
            a.style.display = (!q || txt.indexOf(q) !== -1) ? '' : 'none';
        });
    });

    carregarContagem();
    setInterval(carregarContagem, 60000);
})();
</script>
