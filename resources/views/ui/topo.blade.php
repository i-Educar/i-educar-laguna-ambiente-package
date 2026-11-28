{{--
  Cabeçalho único Buriti (todos os módulos):
  [Sanduíche · Início · ícone sistema · i-Diário]  ·  [título + descrição]  ·  [modo · notificações · sair · user/foto]
  Sanduíche (só aqui) colapsa a lateral para somente ícones.
  No topo da lateral: brasão do Brasil + marca i-Educar (não clicáveis).
--}}
@php
    $homeModulo = $homeModulo ?? url('/');
    $nomeModulo = mb_strtoupper((string) ($nomeModulo ?? ($identidade['nome'] ?? 'Módulo')), 'UTF-8');
    $descricaoModulo = $descricaoModulo ?? ($identidade['descricao'] ?? null);
    $brasaoUrl = asset('vendor/buriti/brasil.png');
    $ieducarMarcaUrl = 'https://i-educar.github.io/assets/img/brand-mark.svg';
    $ieducarMarcaFallback = asset('vendor/buriti/ieducar-brand-mark.svg');
    $ieducarSistemaIcone = '/svg/i-educar-fav.svg';
    $ieducarHome = \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/intranet/index.php');
    $idiarioUrl = class_exists(\App\Support\IdiarioPortal::class)
        ? (\App\Support\IdiarioPortal::urlEntrada() ?? \App\Support\IdiarioPortal::url())
        : null;
    $notificacoesUrl = \Illuminate\Support\Facades\Route::has('notifications.index')
        ? route('notifications.index')
        : url('/notificacoes');
    $loggedUserTopo = session('logged_user');
    $fotoUsuarioTopo = session('logged_user_picture') ?: asset('intranet/imagens/user-perfil.png');
    $nomeUsuarioTopo = is_object($loggedUserTopo) ? ($loggedUserTopo->name ?? 'Usuário') : (is_array($loggedUserTopo) ? ($loggedUserTopo['name'] ?? 'Usuário') : 'Usuário');
    $logoutUrl = url('/intranet/logof.php');
@endphp
{{-- Tokens + anti-FOUC do modo (módulos não passam pelo layout core) --}}
<script>
(function () {
    try {
        var m = localStorage.getItem('buriti.tema.modo');
        if (m !== 'claro' && m !== 'escuro') m = 'claro';
        document.documentElement.setAttribute('data-buriti-modo', m);
        if (document.body) document.body.setAttribute('data-buriti-modo', m);
    } catch (e) {
        document.documentElement.setAttribute('data-buriti-modo', 'claro');
    }
})();
</script>
<link rel="stylesheet" href="/vendor/buriti-tema/tokens.css?v=9">
@include('buriti-ui::estilos')
<header class="topo topo-buriti" role="banner">
    <div class="topo-esq">
        <button type="button"
                class="acao-topo-buriti buriti-lateral-toggle"
                data-buriti-lateral-toggle
                aria-pressed="false"
                aria-controls="buriti-lateral"
                title="Recolher menu"
                aria-label="Recolher ou expandir menu lateral">
            <i class="fa fa-bars" aria-hidden="true" data-buriti-lateral-icone></i>
        </button>
        <a class="acao-topo-buriti" href="{{ $homeModulo }}" title="Início do módulo" aria-label="Início do módulo">
            <i class="fa fa-home" aria-hidden="true"></i>
        </a>
        <a class="acao-topo-buriti acao-sistema-icone" href="{{ $ieducarHome }}" title="i-Educar" aria-label="Abrir i-Educar">
            <img src="{{ $ieducarSistemaIcone }}" width="22" height="22" alt="i-Educar">
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

        <button type="button"
                class="acao-topo-buriti buriti-modo-toggle"
                data-buriti-modo-toggle
                title="Alternar modo claro ou escuro"
                aria-label="Alternar modo claro ou escuro">
            <i class="fa fa-moon-o" aria-hidden="true" data-buriti-modo-icone></i>
        </button>

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

        <a class="acao-topo-buriti acao-sair" href="{{ $logoutUrl }}" title="Sair" aria-label="Sair">
            <i class="fa fa-sign-out" aria-hidden="true"></i>
        </a>

        <div class="menu-drop-buriti menu-user-buriti" data-drop-buriti data-drop-user>
            <button type="button" class="acao-topo-buriti user-foto-btn" aria-expanded="false" aria-haspopup="true"
                    title="{{ $nomeUsuarioTopo }}" aria-label="Conta de {{ $nomeUsuarioTopo }}">
                <img class="avatar-topo" src="{{ $fotoUsuarioTopo }}" alt="Perfil" width="28" height="28">
            </button>
            <div class="gaveta-buriti" role="menu" aria-label="Conta">
                <div class="rotulo">{{ $nomeUsuarioTopo }}</div>
                <a role="menuitem" href="{{ url('/intranet/meusdados.php') }}">
                    <i class="fa fa-id-card" aria-hidden="true"></i> Meus dados
                </a>
                <a role="menuitem" href="{{ url('/intranet/agenda.php') }}">
                    <i class="fa fa-calendar" aria-hidden="true"></i> Agenda
                </a>
                <div class="sep"></div>
                <a role="menuitem" href="{{ $logoutUrl }}">
                    <i class="fa fa-sign-out" aria-hidden="true"></i> Sair
                </a>
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
    var KEY_MODO = 'buriti.tema.modo';

    function modoAtual() {
        var m = document.documentElement.getAttribute('data-buriti-modo');
        return m === 'escuro' ? 'escuro' : 'claro';
    }
    function aplicarModo(modo) {
        modo = modo === 'escuro' ? 'escuro' : 'claro';
        document.documentElement.setAttribute('data-buriti-modo', modo);
        if (document.body) document.body.setAttribute('data-buriti-modo', modo);
        try { localStorage.setItem(KEY_MODO, modo); } catch (e) {}
        document.querySelectorAll('[data-buriti-modo-toggle]').forEach(function (btn) {
            var ico = btn.querySelector('[data-buriti-modo-icone]') || btn.querySelector('i');
            var escuro = modo === 'escuro';
            btn.title = escuro ? 'Mudar para modo claro' : 'Mudar para modo escuro';
            btn.setAttribute('aria-label', btn.title);
            btn.setAttribute('aria-pressed', escuro ? 'true' : 'false');
            if (ico) {
                ico.className = escuro ? 'fa fa-sun-o' : 'fa fa-moon-o';
            }
        });
    }
    aplicarModo(modoAtual());
    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest && e.target.closest('[data-buriti-modo-toggle]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        aplicarModo(modoAtual() === 'escuro' ? 'claro' : 'escuro');
    });

    /* Remove sanduíches duplicados de layouts antigos (mantém o toggle Buriti) */
    document.querySelectorAll('[data-topo-extra-esq] .hamburguer, [data-topo-extra-esq] [aria-label*="Abrir módulos"]').forEach(function (el) {
        if (el.closest('[data-buriti-lateral-toggle]')) return;
        var btn = el.closest('button, a') || el;
        if (btn && btn.parentNode) btn.parentNode.removeChild(btn);
    });

    var KEY_LATERAL = 'buriti.lateral.icones';
    var BRASAO_URL = @json($brasaoUrl);
    var IEDUCAR_MARCA_URL = @json($ieducarMarcaUrl);
    var IEDUCAR_MARCA_FALLBACK = @json($ieducarMarcaFallback);
    var MQ_MOBILE = window.matchMedia ? window.matchMedia('(max-width: 860px)') : null;
    function ehMobileLateral() {
        return MQ_MOBILE ? MQ_MOBILE.matches : (window.innerWidth <= 860);
    }
    function lateralEl() {
        return document.querySelector('aside.lateral, .app > .lateral, [data-buriti-lateral]');
    }
    function garantirBackdropLateral() {
        var el = document.querySelector('[data-buriti-lateral-backdrop]');
        if (el) return el;
        el = document.createElement('button');
        el.type = 'button';
        el.className = 'buriti-lateral-backdrop';
        el.setAttribute('data-buriti-lateral-backdrop', '1');
        el.setAttribute('aria-label', 'Fechar menu');
        el.addEventListener('click', function () { aplicarLateralAberta(false); });
        document.body.appendChild(el);
        return el;
    }
    function aplicarLateralAberta(on) {
        on = !!on;
        document.documentElement.classList.toggle('buriti-lateral-aberta', on);
        document.body.classList.toggle('buriti-lateral-aberta', on);
        var app = document.querySelector('.app');
        if (app) app.classList.toggle('buriti-lateral-aberta', on);
        garantirBackdropLateral();
        document.querySelectorAll('.topo-buriti [data-buriti-lateral-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            btn.title = on ? 'Fechar menu' : 'Abrir menu';
            btn.setAttribute('aria-label', btn.title);
            var ico = btn.querySelector('[data-buriti-lateral-icone]') || btn.querySelector('i');
            if (ico) ico.className = on ? 'fa fa-times' : 'fa fa-bars';
        });
        if (on) {
            var lat = lateralEl();
            if (lat) {
                lat.classList.remove('is-icones');
                document.documentElement.classList.remove('buriti-lateral-icones');
                document.body.classList.remove('buriti-lateral-icones');
                if (app) app.classList.remove('buriti-lateral-icones');
            }
        }
    }
    function lateralAbertaAtiva() {
        return document.body.classList.contains('buriti-lateral-aberta');
    }
    function garantirMarcaOrgNaLateral() {
        var lat = lateralEl();
        if (!lat) return null;
        if (!lat.id) lat.id = 'buriti-lateral';
        /* remove sanduíche antigo injetado na lateral */
        lat.querySelectorAll('[data-buriti-lateral-toggle-rail], .buriti-rail-toggle').forEach(function (el) {
            var wrap = el.closest('.buriti-rail-topo') || el;
            if (wrap && wrap.parentNode) wrap.parentNode.removeChild(wrap);
        });
        var existente = lat.querySelector('[data-buriti-org-marca]');
        if (existente
            && existente.querySelector('[data-buriti-brasao]')
            && existente.querySelector('[data-buriti-ieducar-marca]')
            && existente.tagName !== 'A') {
            return existente;
        }
        if (existente) {
            var antigo = existente.closest('.buriti-rail-topo') || existente;
            if (antigo && antigo.parentNode) antigo.parentNode.removeChild(antigo);
        }
        var wrap = document.createElement('div');
        wrap.className = 'buriti-rail-topo';
        wrap.setAttribute('data-buriti-org-marca', '1');
        wrap.setAttribute('role', 'img');
        wrap.setAttribute('aria-label', 'Brasão da República e marca i-Educar');
        wrap.innerHTML =
            '<span class="buriti-marca-selo" data-buriti-brasao title="Brasão da República Federativa do Brasil">'
            + '<img src="' + BRASAO_URL + '" width="34" height="34" alt="Brasão do Brasil" draggable="false">'
            + '</span>'
            + '<span class="buriti-marca-selo" data-buriti-ieducar-marca title="i-Educar">'
            + '<img src="' + IEDUCAR_MARCA_URL + '" width="34" height="34" alt="i-Educar" draggable="false"'
            + ' onerror="this.onerror=null;this.src=\'' + IEDUCAR_MARCA_FALLBACK.replace(/'/g, "\\'") + '\'">'
            + '</span>';
        lat.insertBefore(wrap, lat.firstChild);
        return wrap;
    }
    function aplicarLateralIcones(on) {
        garantirMarcaOrgNaLateral();
        document.documentElement.classList.toggle('buriti-lateral-icones', !!on);
        document.body.classList.toggle('buriti-lateral-icones', !!on);
        var app = document.querySelector('.app');
        if (app) app.classList.toggle('buriti-lateral-icones', !!on);
        var lat = lateralEl();
        if (lat) {
            lat.classList.toggle('is-icones', !!on);
            if (!lat.id) lat.id = 'buriti-lateral';
        }
        /* Só o sanduíche do cabeçalho — nunca o trilho da lateral */
        document.querySelectorAll('.topo-buriti [data-buriti-lateral-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            btn.title = on ? 'Expandir menu' : 'Recolher menu';
            btn.setAttribute('aria-label', on ? 'Expandir menu lateral' : 'Recolher menu lateral');
            var ico = btn.querySelector('[data-buriti-lateral-icone]') || btn.querySelector('i');
            if (ico) {
                ico.className = on ? 'fa fa-chevron-right' : 'fa fa-bars';
            }
        });
        /* Tooltip nos itens quando só há ícone */
        document.querySelectorAll('.lateral .modulo a, .lateral nav a').forEach(function (a) {
            if (!a.getAttribute('title') || a.dataset.buritiTitleAuto === '1') {
                var t = (a.textContent || '').replace(/\s+/g, ' ').trim();
                if (t) {
                    a.setAttribute('title', t);
                    a.dataset.buritiTitleAuto = '1';
                }
            }
        });
        try { localStorage.setItem(KEY_LATERAL, on ? '1' : '0'); } catch (e) {}
    }
    function lateralIconesAtivo() {
        return document.body.classList.contains('buriti-lateral-icones');
    }
    garantirMarcaOrgNaLateral();
    garantirBackdropLateral();
    try {
        if (!ehMobileLateral() && localStorage.getItem(KEY_LATERAL) === '1') aplicarLateralIcones(true);
        else aplicarLateralIcones(false);
    } catch (e) {
        aplicarLateralIcones(false);
    }
    aplicarLateralAberta(false);
    document.addEventListener('click', function (e) {
        var tgl = e.target && e.target.closest && e.target.closest('[data-buriti-lateral-toggle]');
        if (!tgl) return;
        e.preventDefault();
        e.stopPropagation();
        if (ehMobileLateral()) {
            aplicarLateralAberta(!lateralAbertaAtiva());
            return;
        }
        aplicarLateralIcones(!lateralIconesAtivo());
    });
    if (MQ_MOBILE && MQ_MOBILE.addEventListener) {
        MQ_MOBILE.addEventListener('change', function () {
            aplicarLateralAberta(false);
            if (ehMobileLateral()) {
                aplicarLateralIcones(false);
            } else {
                try {
                    aplicarLateralIcones(localStorage.getItem(KEY_LATERAL) === '1');
                } catch (err) {
                    aplicarLateralIcones(false);
                }
            }
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && lateralAbertaAtiva()) aplicarLateralAberta(false);
    });
    /* Fecha gaveta ao tocar um link do menu no mobile */
    document.addEventListener('click', function (e) {
        if (!ehMobileLateral() || !lateralAbertaAtiva()) return;
        var link = e.target && e.target.closest && e.target.closest('.lateral a, .lateral .modulo a');
        if (link) aplicarLateralAberta(false);
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

    /** DD/MM/YYYY HH:mm + rótulo humanizado (hoje/ontem/há X). */
    function formatarDataHoraBr(valor) {
        if (!valor) return '';
        var d = new Date(valor);
        if (isNaN(d.getTime())) return String(valor);
        function pad(n) { return n < 10 ? '0' + n : String(n); }
        var abs = pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear()
            + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        var agora = new Date();
        var diffMs = agora.getTime() - d.getTime();
        if (diffMs < 0) return abs;
        var diffMin = Math.floor(diffMs / 60000);
        if (diffMin < 1) return 'agora · ' + abs;
        if (diffMin < 60) return 'há ' + diffMin + ' min · ' + abs;
        var mesmoDia = d.toDateString() === agora.toDateString();
        var ontem = new Date(agora); ontem.setDate(agora.getDate() - 1);
        if (mesmoDia) return 'hoje às ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ' · ' + abs;
        if (d.toDateString() === ontem.toDateString()) {
            return 'ontem às ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ' · ' + abs;
        }
        return abs;
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
            var data = formatarDataHoraBr(v.created_at);
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
            if (aberto && wrap.hasAttribute('data-drop-notif')) {
                carregarLista();
                carregarContagem();
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

    carregarContagem();
    setInterval(carregarContagem, 60000);
})();
</script>
