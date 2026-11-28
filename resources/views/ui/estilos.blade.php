{{-- Estilos compartilhados: topo Buriti + rodapé --}}
<style>
    .topo.topo-buriti, .topo-buriti {
        position: sticky; top: 0; z-index: 40;
        display: grid; grid-template-columns: minmax(140px, 1fr) minmax(160px, 1.4fr) minmax(140px, 1fr);
        align-items: center; gap: 10px 16px;
        min-height: 58px; padding: 10px 20px;
        background: var(--topo-fundo, color-mix(in srgb, #fff 94%, transparent));
        backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--topo-borda, color-mix(in srgb, var(--linha, #cfd8df) 70%, transparent));
        box-shadow: var(--sombra-leve, 0 8px 24px rgba(21,34,43,.03));
        overflow: visible;
    }
    .topo-esq, .topo-dir { display: flex; align-items: center; gap: 8px; min-width: 0; overflow: visible; }
    .topo-esq { justify-content: flex-start; }
    .topo-dir { justify-content: flex-end; position: relative; z-index: 41; }
    .topo-extra-esq { display: inline-flex; align-items: center; gap: 8px; }
    .topo-centro {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        min-width: 0; padding: 0 10px; text-align: center; gap: 2px;
    }
    .topo-nome-modulo {
        display: block; max-width: min(560px, 78vw);
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        font-size: .92rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--cor-tela, var(--cor, #16384a));
        line-height: 1.2;
    }
    /* Títulos de tela dos módulos Buriti — sempre maiúsculas */
    .cabeca-tela h1,
    .identidade-tela h1,
    .titulo-modulo {
        text-transform: uppercase !important;
        letter-spacing: .08em;
    }
    .topo-desc-modulo {
        display: block; max-width: min(560px, 78vw);
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        font-size: .78rem; font-weight: 400; letter-spacing: 0;
        color: var(--suave, #5b6b76); line-height: 1.25; opacity: .95;
    }
    .barra-acoes-buriti { display: flex; align-items: center; gap: 8px; }
    .acao-topo-buriti {
        display: inline-flex; align-items: center; justify-content: center;
        width: 40px; height: 40px; padding: 0;
        border: 1px solid color-mix(in srgb, var(--linha, #cfd8df) 80%, var(--superficie, #fff));
        border-radius: 12px;
        background: color-mix(in srgb, var(--superficie, #fff) 88%, var(--clara, #e8eef2));
        color: var(--cor-tela, var(--cor, #1a5f9e));
        line-height: 1; text-decoration: none; cursor: pointer; font-size: 15px;
        position: relative; flex: none;
        transition: background .15s ease, border-color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .acao-topo-buriti:hover, .acao-topo-buriti[aria-expanded="true"],
    .acao-topo-buriti[aria-pressed="true"] {
        background: var(--superficie, #fff);
        border-color: color-mix(in srgb, var(--acento, var(--cor-tela, #2a9d8f)) 35%, var(--linha, #cfd8df));
        color: var(--cor-tela, var(--cor, #1a5f9e));
        box-shadow: var(--sombra-leve, 0 6px 14px rgba(21,34,43,.06));
        transform: translateY(-1px);
    }
    .acao-topo-buriti.buriti-modo-toggle[aria-pressed="true"] {
        background: color-mix(in srgb, var(--acento, #2a9d8f) 18%, var(--superficie, #fff));
        border-color: color-mix(in srgb, var(--acento, #2a9d8f) 40%, var(--linha));
        color: var(--acento, #2a9d8f);
    }
    .acao-topo-buriti svg { display: block; width: 18px; height: 18px; }
    .acao-topo-buriti.acao-sistema-icone img {
        display: block; width: 22px; height: 22px; border-radius: 6px;
    }
    .acao-topo-buriti.acao-sair {
        color: var(--erro, #8f2424);
        border-color: color-mix(in srgb, var(--erro, #8f2424) 22%, var(--superficie, #fff));
    }
    .acao-topo-buriti.acao-sair:hover {
        background: var(--erro-fundo, #fceceb);
        border-color: color-mix(in srgb, var(--erro, #8f2424) 40%, var(--linha, #cfd8df));
        color: var(--erro, #8f2424);
    }
    .acao-topo-buriti.user-foto-btn {
        width: auto; min-width: 40px; padding: 0 4px; overflow: hidden;
    }
    .acao-topo-buriti .avatar-topo {
        display: block; width: 28px; height: 28px; border-radius: 9px;
        object-fit: cover; border: 1px solid color-mix(in srgb, var(--linha, #cfd8df) 70%, transparent);
    }
    .acao-topo-buriti .notif-ponto {
        position: absolute; top: 4px; right: 4px; min-width: 16px; height: 16px; padding: 0 4px;
        border-radius: 999px; background: #c2410c; color: #fff;
        font-size: 10px; font-weight: 800; line-height: 16px; text-align: center;
        box-shadow: 0 0 0 2px #fff;
    }
    .acao-topo-buriti .notif-ponto[hidden] { display: none !important; }
    .gaveta-busca { min-width: min(320px, calc(100vw - 24px)) !important; }
    .gaveta-notif { min-width: min(360px, calc(100vw - 24px)) !important; max-height: min(72vh, 520px) !important; padding: 0 !important; }
    .gaveta-notif .notif-barra {
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px;
        padding: 10px 12px; border-bottom: 1px solid var(--linha, #e8edf0);
        position: sticky; top: 0; background: var(--superficie, #fff); z-index: 1;
    }
    .gaveta-notif .notif-barra strong { font-size: .88rem; color: var(--tinta, #16384a); margin-right: auto; }
    .gaveta-notif .notif-barra a, .gaveta-notif .notif-barra button {
        border: 0; background: 0; color: var(--cor-tela, #16384a); font: inherit; font-size: .75rem;
        font-weight: 700; cursor: pointer; padding: 0; text-decoration: none;
    }
    .gaveta-notif .notif-barra a:hover, .gaveta-notif .notif-barra button:hover { text-decoration: underline; }
    .gaveta-notif .notif-lista { max-height: min(52vh, 380px); overflow: auto; padding: 6px; }
    .gaveta-notif .notif-item {
        display: grid; gap: 4px; padding: 10px 12px; border-radius: 10px;
        text-decoration: none; color: var(--tinta, #1a242b); border: 1px solid transparent;
    }
    .gaveta-notif .notif-item:hover { background: var(--clara, #f3f6f8); }
    .gaveta-notif .notif-item.unread {
        background: color-mix(in srgb, var(--clara, #fff7ed) 70%, #fff);
        border-color: color-mix(in srgb, #c2410c 18%, #e8edf0);
    }
    .gaveta-notif .notif-item.unread::before {
        content: ''; width: 8px; height: 8px; border-radius: 50%; background: #c2410c;
        position: absolute; margin-top: 5px; margin-left: -2px;
    }
    .gaveta-notif .notif-item p { margin: 0; font-size: .82rem; font-weight: 600; line-height: 1.35; padding-left: 10px; }
    .gaveta-notif .notif-item .date-notification {
        padding-left: 10px; color: var(--suave, #5b6b76); font-size: .72rem; font-weight: 500;
    }
    .gaveta-notif .notif-vazio { margin: 16px 12px; color: var(--suave, #5b6b76); font-size: .84rem; text-align: center; }
    .gaveta-notif .notif-rodape {
        border-top: 1px solid var(--linha, #e8edf0); padding: 10px 12px; text-align: center;
        position: sticky; bottom: 0; background: var(--superficie, #fff);
    }
    .gaveta-notif .notif-rodape a {
        display: inline-flex; align-items: center; gap: 6px; font-weight: 750; font-size: .84rem;
        color: var(--cor-tela, #16384a); text-decoration: none;
    }
    .gaveta-notif .notif-rodape a:hover { text-decoration: underline; }
    .busca-campo { display: block; padding: 6px 6px 8px; }
    .busca-campo input {
        width: 100%; min-height: 38px; padding: 8px 12px; border-radius: 10px;
        border: 1px solid var(--linha, #cfd8df); font: inherit; background: var(--input-bg, #f8fafc);
    }
    .busca-campo input:focus { outline: 2px solid color-mix(in srgb, var(--cor-tela, #16384a) 28%, #fff); border-color: var(--cor-tela, #16384a); background: var(--superficie, #fff); }
    .busca-lista { max-height: 240px; overflow: auto; }
    .busca-lista a span { display: grid; gap: 1px; min-width: 0; }
    .busca-lista a strong { font-size: .86rem; font-weight: 650; }
    .busca-lista a small { color: var(--suave, #5b6b76); font-weight: 500; font-size: .72rem; }
    .busca-vazio { margin: 8px 10px; color: var(--suave, #5b6b76); font-size: .82rem; }
    .busca-ieducar {
        display: flex; align-items: center; gap: 8px; margin: 4px 6px 2px; padding: 8px 10px;
        border-top: 1px solid var(--linha, #e8edf0); color: var(--cor-tela, #16384a); text-decoration: none; font-weight: 650; font-size: .82rem;
    }
    .busca-ieducar:hover { background: var(--clara, #f3f6f8); border-radius: 8px; }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
    /* Sanduíches legados (não o toggle Buriti) */
    .topo-buriti .hamburguer:not([data-buriti-lateral-toggle]),
    .topo-buriti [aria-controls="menu-transporte"].hamburguer { display: none !important; }

    /* Brasão + marca i-Educar no topo da lateral (não clicáveis) — sanduíche só no cabeçalho */
    .lateral .buriti-rail-topo {
        display: flex; align-items: center; justify-content: center;
        gap: 8px;
        padding: 14px 10px 12px;
        border-bottom: 1px solid rgba(255,255,255,.08);
        pointer-events: none;
        user-select: none;
    }
    .lateral .buriti-rail-topo .buriti-marca-selo {
        width: 40px; height: 40px; padding: 4px; box-sizing: border-box;
        border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--marca-selo-bg, #fff);
        border: 1px solid var(--marca-selo-borda, rgba(255,255,255,.22));
        box-shadow: 0 6px 14px rgba(0,0,0,.18);
        text-decoration: none;
        cursor: default;
    }
    .lateral .buriti-rail-topo .buriti-marca-selo img {
        width: 100%; height: 100%;
        object-fit: contain;
        display: block;
        border-radius: 6px;
        pointer-events: none;
    }
    html.buriti-lateral-icones .lateral .buriti-rail-topo,
    body.buriti-lateral-icones .lateral .buriti-rail-topo,
    .lateral.is-icones .buriti-rail-topo {
        flex-direction: column;
        gap: 6px;
        padding: 10px 6px;
    }
    html.buriti-lateral-icones .lateral .buriti-rail-topo .buriti-marca-selo,
    body.buriti-lateral-icones .lateral .buriti-rail-topo .buriti-marca-selo,
    .lateral.is-icones .buriti-rail-topo .buriti-marca-selo {
        width: 36px; height: 36px; padding: 3px; border-radius: 10px;
    }
    /*
      Com a marca da org no trilho, o ícone do módulo some —
      identidade do módulo permanece no topo central e na cabeça da tela.
    */
    .lateral:has(.buriti-rail-topo) > .marca {
        display: none !important;
    }

    /*
      Lateral — contraste estável em todos os módulos.
      Antes: ativo/hover misturava a cor da marca com branco → texto branco sumia
      em marcas claras (âmbar, bege, teal médio). Agora o fundo ativo escurece
      a marca (mix com #0a1218) e o texto/ícones ficam sempre claros.
    */
    .app > .lateral,
    aside.lateral {
        --nav-base: #101820;
        --nav-link: #e8eef3;
        --nav-link-suave: #a8b6c2;
        --nav-icone: color-mix(in srgb, var(--destaque, var(--cor, #2f7ec4)) 55%, #fff);
        background: linear-gradient(
            180deg,
            color-mix(in srgb, var(--cor, #1a5f9e) 28%, var(--nav-base)) 0%,
            var(--nav-base) 55%
        ) !important;
        color: var(--nav-link) !important;
    }
    .app > .lateral .modulo-nome,
    aside.lateral .modulo-nome {
        color: var(--nav-link-suave) !important;
        opacity: 1;
    }
    .app > .lateral .modulo a,
    .app > .lateral nav a,
    aside.lateral .modulo a,
    aside.lateral nav a {
        color: var(--nav-link) !important;
        background: transparent;
        border: 1px solid transparent;
        text-shadow: none;
    }
    .app > .lateral .modulo a i,
    .app > .lateral nav a i,
    aside.lateral .modulo a i,
    aside.lateral nav a i {
        color: var(--nav-icone) !important;
        opacity: 1 !important;
    }
    .app > .lateral .modulo a small,
    aside.lateral .modulo a small {
        color: var(--nav-link-suave) !important;
    }
    .app > .lateral .modulo a:hover,
    .app > .lateral nav a:hover,
    aside.lateral .modulo a:hover,
    aside.lateral nav a:hover {
        background: color-mix(in srgb, var(--cor, #1a5f9e) 42%, rgba(255,255,255,.1)) !important;
        color: #fff !important;
        border-color: color-mix(in srgb, var(--destaque, var(--cor)) 35%, transparent);
    }
    .app > .lateral .modulo a:hover i,
    .app > .lateral nav a:hover i,
    aside.lateral .modulo a:hover i,
    aside.lateral nav a:hover i {
        color: #fff !important;
    }
    .app > .lateral .modulo a.ativo,
    .app > .lateral nav a.ativo,
    aside.lateral .modulo a.ativo,
    aside.lateral nav a.ativo {
        /* Mix com ink escuro → texto/ícone sempre brancos (terracotta / LibreCode red ok) */
        background: color-mix(in srgb, var(--cor, #1a5f9e) 68%, #060b10) !important;
        color: #fff !important;
        font-weight: 700;
        border-color: color-mix(in srgb, var(--destaque, var(--cor)) 45%, transparent);
        box-shadow: inset 3px 0 0 var(--destaque, var(--cor, #2f7ec4));
    }
    .app > .lateral .modulo a.ativo i,
    .app > .lateral nav a.ativo i,
    aside.lateral .modulo a.ativo i,
    aside.lateral nav a.ativo i,
    .app > .lateral .modulo a.ativo small,
    aside.lateral .modulo a.ativo small {
        color: #fff !important;
    }
    .app > .lateral .marca .icone,
    aside.lateral .marca .icone {
        background: linear-gradient(145deg, var(--destaque, var(--cor)), color-mix(in srgb, var(--cor) 75%, #000));
        color: #fff;
    }

    /* Lateral recolhida → só ícones */
    html.buriti-lateral-icones .app > .lateral,
    body.buriti-lateral-icones .app > .lateral,
    .app.buriti-lateral-icones > .lateral,
    aside.lateral.is-icones {
        width: 72px !important;
        min-width: 72px !important;
        flex: none !important;
        transition: width .2s ease;
    }
    html.buriti-lateral-icones .lateral .marca-texto,
    html.buriti-lateral-icones .lateral .marca-titulo,
    html.buriti-lateral-icones .lateral .marca-desc,
    body.buriti-lateral-icones .lateral .marca-texto,
    body.buriti-lateral-icones .lateral .marca-titulo,
    body.buriti-lateral-icones .lateral .marca-desc,
    .lateral.is-icones .marca-texto,
    .lateral.is-icones .marca-titulo,
    .lateral.is-icones .marca-desc {
        display: none !important;
    }
    html.buriti-lateral-icones .lateral .marca,
    body.buriti-lateral-icones .lateral .marca,
    .lateral.is-icones .marca {
        justify-content: center;
        padding: 12px 8px;
    }
    html.buriti-lateral-icones .lateral .modulo-nome,
    body.buriti-lateral-icones .lateral .modulo-nome,
    .lateral.is-icones .modulo-nome {
        display: none !important;
    }
    html.buriti-lateral-icones .lateral .modulo,
    body.buriti-lateral-icones .lateral .modulo,
    .lateral.is-icones .modulo {
        padding: 6px 8px;
    }
    html.buriti-lateral-icones .lateral .modulo a,
    html.buriti-lateral-icones .lateral nav a,
    body.buriti-lateral-icones .lateral .modulo a,
    body.buriti-lateral-icones .lateral nav a,
    .lateral.is-icones .modulo a,
    .lateral.is-icones nav a {
        justify-content: center !important;
        gap: 0 !important;
        padding: 10px 8px !important;
        font-size: 0 !important; /* esconde rótulo textual; ícone volta abaixo */
        line-height: 0;
    }
    html.buriti-lateral-icones .lateral .modulo a i,
    html.buriti-lateral-icones .lateral nav a i,
    body.buriti-lateral-icones .lateral .modulo a i,
    body.buriti-lateral-icones .lateral nav a i,
    .lateral.is-icones .modulo a i,
    .lateral.is-icones nav a i {
        font-size: 15px !important;
        line-height: 1 !important;
        width: 20px !important;
        margin: 0 !important;
    }
    html.buriti-lateral-icones .lateral .modulo a span,
    html.buriti-lateral-icones .lateral nav a span,
    body.buriti-lateral-icones .lateral .modulo a span,
    body.buriti-lateral-icones .lateral nav a span,
    .lateral.is-icones .modulo a span,
    .lateral.is-icones nav a span {
        display: none !important;
    }
    /* Em telas estreitas: sanduíche reabre a lateral em modo ícones */
    @media (max-width: 860px) {
        html.buriti-lateral-icones .app > .lateral,
        body.buriti-lateral-icones .app > .lateral,
        .app.buriti-lateral-icones > .lateral,
        aside.lateral.is-icones {
            display: flex !important;
            flex-direction: column;
            position: sticky;
            top: 0;
            align-self: flex-start;
            max-height: 100vh;
            overflow: auto;
            z-index: 20;
        }
    }

    .menu-drop-buriti { position: relative; z-index: 42; overflow: visible; }
    .menu-drop-buriti .gaveta-buriti {
        display: none; position: absolute; top: calc(100% + 6px); right: 0;
        min-width: 260px; max-width: min(360px, calc(100vw - 24px));
        max-height: min(70vh, 480px); overflow-x: hidden; overflow-y: auto;
        background: var(--superficie, #fff); border: 1px solid var(--linha, #cfd8df); border-radius: 10px;
        box-shadow: 0 12px 28px rgba(21,34,43,.14); z-index: 50; padding: 6px; list-style: none; margin: 0;
        pointer-events: auto;
    }
    .menu-drop-buriti.aberto .gaveta-buriti { display: block; }
    .gaveta-buriti a {
        display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px;
        color: var(--tinta, #1a242b); text-decoration: none; font-weight: 600; font-size: .86rem;
        pointer-events: auto; cursor: pointer;
    }
    .gaveta-buriti a i { width: 16px; text-align: center; color: var(--suave, #5b6b76); flex: none; }
    .gaveta-buriti a:hover { background: var(--clara, #f3f6f8); color: var(--tinta, #16384a); }
    .gaveta-buriti .sep { height: 1px; background: var(--linha, #e8edf0); margin: 4px 6px; }
    .gaveta-buriti .rotulo {
        padding: 6px 10px 2px; font-size: .68rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; color: var(--suave, #8aa0ad);
    }
    /* Compat: layouts antigos que ainda usam topo-com-acoes + migalha */
    .topo-com-acoes { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .topo-com-acoes .migalha, .topo .migalha { display: none !important; }
    footer.rodape-coop-buriti {
        margin-top: auto; padding: 8px 16px; border-top: 1px solid var(--linha, #d5dde3);
        background: color-mix(in srgb, #15222b 4%, #f4f7f9);
        color: var(--suave, #7a8a95); font-size: .72rem; letter-spacing: .01em;
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 6px 14px;
    }
    footer.rodape-coop-buriti a { color: var(--suave, #5b6b76); text-decoration: none; font-weight: 600; }
    footer.rodape-coop-buriti a:hover { text-decoration: underline; color: var(--tinta, #16384a); }
    footer.rodape-coop-buriti .sep { opacity: .45; margin: 0 4px; }
    footer.rodape-coop-buriti .versoes { opacity: .9; }
    /* —— Shell SME: largura útil + mobile (todos os módulos) —— */
    .app { width: 100%; }
    .miolo { min-width: 0; width: 100%; }
    .miolo > main {
        width: 100%;
        max-width: none;
        box-sizing: border-box;
    }
    .tabela-wrap, .table-wrap, .portal-tabela-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .miolo .bloco:has(table),
    .miolo .cartao:has(table),
    .miolo main:has(> table) {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .miolo table { min-width: 520px; }
    @media (max-width: 1100px) {
        .cabeca-tela {
            flex-wrap: wrap;
            align-items: flex-start !important;
            gap: 10px !important;
        }
        .acoes-tela {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: nowrap !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 2px;
            scrollbar-width: thin;
        }
    }
    @media (max-width: 860px) {
        /* Lateral vira gaveta; sanduíche abre/fecha */
        .app .lateral {
            display: flex !important;
            flex-direction: column;
            position: fixed !important;
            top: 0; left: 0; bottom: 0;
            z-index: 60;
            width: min(280px, 88vw) !important;
            max-width: 280px;
            transform: translateX(-105%);
            transition: transform .22s ease;
            box-shadow: 8px 0 28px rgba(14, 42, 58, .18);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        html.buriti-lateral-aberta .app .lateral,
        body.buriti-lateral-aberta .app .lateral,
        .app.buriti-lateral-aberta .lateral {
            transform: translateX(0);
        }
        .buriti-lateral-backdrop {
            display: none;
            position: fixed; inset: 0; z-index: 55;
            background: rgba(14, 42, 58, .42);
            border: 0; padding: 0; cursor: pointer;
        }
        html.buriti-lateral-aberta .buriti-lateral-backdrop,
        body.buriti-lateral-aberta .buriti-lateral-backdrop {
            display: block;
        }
        .miolo > main { padding: 12px 12px 20px !important; }
        .topo.topo-buriti, .topo-buriti {
            grid-template-columns: auto minmax(0, 1fr) auto !important;
            gap: 6px 8px;
            padding: 8px 10px;
            min-height: 52px;
        }
        .topo-esq, .topo-dir { gap: 4px; }
        .topo-centro {
            min-width: 0;
            padding: 0 4px;
            overflow: hidden;
        }
        .topo-desc-modulo { display: none !important; }
        .topo-nome-modulo {
            font-size: .78rem !important;
            letter-spacing: .04em;
            max-width: 100%;
        }
        .cabeca-tela {
            margin-left: -12px !important;
            margin-right: -12px !important;
            margin-top: -12px !important;
            padding: 10px 12px !important;
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .cabeca-tela .marca-tela,
        .identidade-tela .marca-tela { display: none !important; }
        .cabeca-tela .lead { display: none !important; }
        .cabeca-tela .titulo-modulo { font-size: .65rem; margin-bottom: 0; }
        .cabeca-tela h1,
        .identidade-tela h1 { font-size: 1.05rem !important; line-height: 1.25; }
        .cabeca-tela .acoes-tela { margin-left: 0 !important; }
        .numeros, .atalhos, .card-grid, .kpis, .mod-cards {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important;
        }
        .form-grid { grid-template-columns: 1fr !important; }
        .grade { grid-template-columns: 56px repeat(5, minmax(64px, 1fr)) !important; overflow-x: auto; }
        footer.rodape-coop-buriti {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            padding: 10px 12px 14px;
            font-size: .68rem;
        }
    }
    @media (max-width: 640px) {
        .topo.topo-buriti, .topo-buriti {
            padding: 6px 8px;
            gap: 4px 6px;
            min-height: 48px;
        }
        .topo-esq .acao-topo-buriti[href*="idiario"],
        .topo-esq .acao-topo-buriti[title="i-Diário"] { display: none; }
        .topo-nome-modulo { font-size: .72rem !important; max-width: 38vw; }
        .acao-topo-buriti {
            width: 34px; height: 34px; border-radius: 9px; font-size: 14px;
        }
        .acao-topo-buriti.user-foto-btn { min-width: 34px; padding: 0 2px; }
        .acao-topo-buriti .avatar-topo { width: 24px; height: 24px; border-radius: 7px; }
        .cabeca-tela h1, .identidade-tela h1 { font-size: .98rem !important; }
        .botao.icone,
        .acoes-tela > .botao,
        .acoes-tela > form > .botao,
        .acoes-tela > form > button.botao,
        .acoes-tela > .botao.informes,
        .acoes-tela > .botao.ajuda {
            width: 36px; min-width: 36px; height: 36px; min-height: 36px;
        }
        /* Esconde texto residual dentro de botões de ação (só ícone) */
        .acoes-tela > .botao,
        .acoes-tela > form > .botao {
            font-size: 0 !important;
            gap: 0 !important;
            padding: 0 !important;
        }
        .acoes-tela > .botao > i,
        .acoes-tela > form > .botao > i {
            font-size: 1rem !important;
            line-height: 1;
        }
        .bloco, .cartao, .card { padding: 12px !important; }
        .botao, button.botao { min-height: 40px; }
        .numeros, .atalhos, .card-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Seletores com busca (único e múltiplo) — linguagem clara para a SME */
    .buriti-select, .select-usuario {
        display: grid; gap: 6px; font-size: .8rem; font-weight: 700; color: var(--suave, #5b6b76);
        min-width: 0;
    }
    .buriti-select-rotulo { display: inline-flex; align-items: center; gap: 6px; color: var(--tinta, #1a242b); font-weight: 800; }
    .buriti-select-rotulo i, .select-usuario .buriti-select-rotulo i { color: var(--cor-tela, var(--cor, #16384a)); }
    .buriti-select-busca, .select-usuario-busca {
        min-height: 38px; padding: 8px 12px 8px 36px; border: 1px solid var(--linha, #cfd8df);
        border-radius: 10px; font: inherit; font-weight: 500; background:
            #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%235b6b76' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-3.5-3.5'/%3E%3C/svg%3E") 12px 50% no-repeat;
    }
    .buriti-select-busca:focus, .select-usuario-busca:focus,
    .buriti-select-lista:focus, .select-usuario-lista:focus {
        outline: 2px solid color-mix(in srgb, var(--cor-tela, #16384a) 35%, #fff);
        border-color: var(--cor-tela, #16384a);
    }
    .buriti-select-lista[size], .select-usuario-lista[size] {
        width: 100%; min-height: 120px; padding: 4px; border: 1px solid var(--linha, #cfd8df);
        border-radius: 10px; font: inherit; font-weight: 500; background: var(--superficie, #fff);
    }
    .buriti-select-lista:not([size]), .select-usuario-lista:not([size]),
    .buriti-select--compacto .buriti-select-lista {
        width: 100%; min-height: 36px; height: 36px; padding: 6px 8px;
        border: 1px solid var(--linha, #cfd8df); border-radius: 8px;
        font: inherit; font-weight: 500; background: var(--superficie, #fff);
    }
    .buriti-select-lista option, .select-usuario-lista option { padding: 6px 8px; border-radius: 6px; }
    .buriti-select-lista option:checked, .select-usuario-lista option:checked {
        background: color-mix(in srgb, var(--cor-tela, #16384a) 18%, #fff); color: var(--tinta, #1a242b); font-weight: 700;
    }
    .buriti-select-ajuda, .select-usuario-aviso {
        font-weight: 500; color: var(--suave, #5b6b76); font-size: .75rem;
    }
    .buriti-select--compacto { gap: 4px; }
    .buriti-select--compacto .buriti-select-rotulo { font-size: .72rem; font-weight: 700; color: var(--suave, #5b6b76); letter-spacing: .02em; }
    .buriti-select--compacto .buriti-select-busca { min-height: 34px; padding: 5px 10px 5px 32px; border-radius: 8px; }

    /* Barra de filtros compacta (GET) — não usar form-grid/select aberto */
    .buriti-filtro,
    form.buriti-filtro {
        display: flex; flex-wrap: wrap; align-items: flex-end; gap: 8px 10px;
        margin: 0 0 12px; padding: 10px 12px;
        background: color-mix(in srgb, var(--clara, #eef2f5) 55%, #fff);
        border: 1px solid var(--linha, #cfd8df); border-radius: 12px;
    }
    .buriti-filtro > label,
    .buriti-filtro .buriti-select {
        flex: 1 1 140px; max-width: 220px; margin: 0;
        display: grid; gap: 4px; font-size: .72rem; font-weight: 700; color: var(--suave, #5b6b76);
    }
    .buriti-filtro > label.col-wide,
    .buriti-filtro .buriti-select.col-wide { flex: 1 1 100%; max-width: none; }
    .buriti-filtro input,
    .buriti-filtro select {
        min-height: 36px; width: 100%; padding: 6px 8px;
        border: 1px solid var(--linha, #cfd8df); border-radius: 8px;
        font: inherit; font-weight: 500; background: var(--superficie, #fff); color: var(--tinta, #1a242b);
    }
    .buriti-filtro .acoes {
        flex: 0 0 auto; display: inline-flex; gap: 6px; align-items: center;
        margin: 0; grid-column: auto;
    }
    .buriti-filtro .acoes .botao { padding: 7px 12px; white-space: nowrap; }
    .buriti-filtro-bloco {
        background: var(--superficie, #fff); border: 1px solid var(--linha, #cfd8df); border-radius: 12px;
        padding: 8px 10px; margin: 0 0 12px; border-top: 2px solid var(--cor-tela, var(--cor, #16384a));
        box-shadow: none;
    }
    .buriti-filtro-bloco > h2 {
        margin: 0 0 8px; font-size: .72rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; color: var(--suave, #5b6b76);
    }
    .buriti-filtro-bloco .buriti-filtro { margin: 0; padding: 0; border: 0; background: transparent; border-radius: 0; }
    @media (max-width: 640px) {
        .buriti-filtro > label,
        .buriti-filtro .buriti-select { flex: 1 1 100%; max-width: none; }
        .buriti-filtro .acoes { width: 100%; justify-content: flex-end; }
    }
    .buriti-alerta {
        display: flex; gap: 12px; align-items: flex-start; padding: 12px 14px; border-radius: 12px;
        border: 1px solid var(--linha, #cfd8df); background: var(--superficie, #fff); margin: 0 0 12px;
    }
    .buriti-alerta i { font-size: 1.2rem; margin-top: 2px; }
    .buriti-alerta.erro { background: var(--erro-fundo, #fdeceb); border-color: color-mix(in srgb, var(--erro, #9b2c2c) 30%, #fff); color: var(--erro, #9b2c2c); }
    .buriti-alerta.aviso { background: #fff6e8; border-color: #f0d9a8; color: var(--ambar, #b86e00); }
    .buriti-alerta.ok { background: var(--ok-fundo, #e5f6ec); border-color: color-mix(in srgb, var(--ok, #17663f) 30%, #fff); color: var(--ok, #17663f); }
    .buriti-alerta strong { display: block; margin: 0 0 4px; }
    .buriti-alerta p { margin: 0; font-weight: 500; }

    /* Separação clara entre fluxos diferentes na mesma tela */
    .fluxo-grupo { display: grid; gap: 14px; margin: 0 0 18px; }
    .fluxo-bloco {
        background: var(--superficie, #fff); border: 1px solid var(--linha, #cfd8df); border-radius: 14px;
        padding: 14px 16px; box-shadow: 0 8px 18px rgba(21,34,43,.04);
        border-left: 4px solid var(--cor-tela, var(--cor, #16384a));
    }
    .fluxo-bloco.consulta { border-left-color: var(--suave, #5b6b76); background: color-mix(in srgb, #f4f7f9 70%, #fff); }
    .fluxo-bloco.acao { border-left-color: var(--cor-tela, var(--cor, #16384a)); }
    .fluxo-bloco.equipe { border-left-color: #0d7a6f; }
    .fluxo-bloco.docs { border-left-color: #b86e00; }
    .fluxo-bloco > header.fluxo-cabeca,
    .fluxo-cabeca {
        display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between;
        gap: 6px 12px; margin: 0 0 12px; padding: 0 0 10px;
        border-bottom: 1px dashed color-mix(in srgb, var(--linha, #cfd8df) 80%, transparent);
    }
    .fluxo-cabeca h2 { margin: 0; font-size: 1.02rem; display: flex; align-items: center; gap: 8px; }
    .fluxo-cabeca .selo-fluxo {
        display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px;
        font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        background: color-mix(in srgb, var(--cor-tela, #16384a) 12%, #fff);
        color: var(--cor-tela, #16384a);
    }
    .fluxo-bloco.consulta .selo-fluxo { background: var(--muted-fundo, #eef1f3); color: var(--suave, #5b6b76); }
    .fluxo-bloco.equipe .selo-fluxo { background: #e5f6f3; color: #0d7a6f; }
    .fluxo-bloco.docs .selo-fluxo { background: #fff6e8; color: #b86e00; }
    .fluxo-cabeca p { margin: 0; color: var(--suave, #5b6b76); font-size: .86rem; font-weight: 500; max-width: 40rem; }
    .fluxo-sep {
        display: flex; align-items: center; gap: 10px; margin: 4px 0 2px;
        color: var(--suave, #8aa0ad); font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
    }
    .fluxo-sep::before, .fluxo-sep::after {
        content: ''; flex: 1; height: 1px; background: var(--linha, #cfd8df);
    }

    /* Thread de mensagens (legado — preferir protocolo-etapas) */
    .buriti-chat {
        display: flex; flex-direction: column; min-height: 280px; max-height: min(62vh, 560px);
        border: 1px solid var(--linha, #cfd8df); border-radius: 16px; overflow: hidden;
        background: #f4f6f8;
    }
    .buriti-chat-topo {
        display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        background: color-mix(in srgb, var(--cor-tela, var(--cor, #16384a)) 92%, #000);
        color: #fff; flex: none;
    }
    .buriti-chat-topo .avatar {
        width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center;
        background: rgba(255,255,255,.18); font-weight: 800; flex: none;
    }
    .buriti-chat-topo strong { display: block; font-size: .95rem; }
    .buriti-chat-topo small { display: block; opacity: .85; font-weight: 500; font-size: .75rem; }
    .buriti-chat-fios {
        flex: 1; overflow: auto; padding: 14px 12px 18px; display: flex; flex-direction: column; gap: 8px;
    }
    .buriti-chat-vazio {
        margin: auto; text-align: center; color: var(--suave, #5b6b76); background: rgba(255,255,255,.72);
        border-radius: 12px; padding: 14px 16px; max-width: 280px; font-size: .88rem;
    }
    .buriti-balao {
        max-width: min(78%, 420px); padding: 8px 10px 6px; border-radius: 10px;
        box-shadow: 0 1px 1px rgba(0,0,0,.08); position: relative; font-size: .92rem; line-height: 1.35;
        word-break: break-word;
    }
    .buriti-balao.eles {
        align-self: flex-start; background: var(--superficie, #fff); border-top-left-radius: 2px;
    }
    .buriti-balao.eu {
        align-self: flex-end; background: #d9fdd3; border-top-right-radius: 2px;
    }
    .buriti-balao.sistema {
        align-self: center; background: #fff8c4; color: #5b4a12; font-size: .8rem;
        text-align: center; max-width: 90%; border-radius: 8px; box-shadow: none;
    }
    .buriti-balao .quem {
        display: block; font-size: .72rem; font-weight: 800; color: var(--cor-tela, #075e54); margin: 0 0 2px;
    }
    .buriti-balao .selo {
        display: inline-block; font-size: .65rem; font-weight: 700; letter-spacing: .02em;
        padding: 1px 6px; border-radius: 999px; margin: 0 0 4px;
        background: rgba(0,0,0,.06); color: var(--suave, #5b6b76);
    }
    .buriti-balao .texto { margin: 0; white-space: pre-wrap; }
    .buriti-balao .quando {
        display: block; text-align: right; font-size: .68rem; color: #667781; margin-top: 4px;
    }
    .buriti-balao .anexo-chip {
        display: inline-flex; align-items: center; gap: 6px; margin-top: 6px;
        padding: 6px 8px; border-radius: 8px; background: rgba(0,0,0,.04);
        color: inherit; text-decoration: none; font-size: .82rem; font-weight: 600;
    }
    .buriti-balao .anexo-chip:hover { background: rgba(0,0,0,.08); }
    .buriti-chat-composer {
        display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: end;
        padding: 10px; background: var(--clara, #f0f2f5); border-top: 1px solid var(--linha, #d5dde3); flex: none;
    }
    .buriti-chat-composer textarea {
        width: 100%; min-height: 44px; max-height: 120px; resize: vertical;
        border: 0; border-radius: 22px; padding: 10px 14px; font: inherit; background: var(--superficie, #fff);
        box-shadow: 0 1px 1px rgba(0,0,0,.06);
    }
    .buriti-chat-composer .composer-acoes { display: flex; gap: 6px; align-items: center; }
    .buriti-chat-composer .botao-enviar {
        width: 44px; height: 44px; border-radius: 50%; border: 0; cursor: pointer;
        background: var(--cor-tela, var(--cor, #075e54)); color: #fff; font-size: 1rem;
        display: grid; place-items: center; box-shadow: 0 4px 12px color-mix(in srgb, var(--cor-tela, #075e54) 35%, transparent);
    }
    .buriti-chat-composer .botao-enviar:hover { filter: brightness(1.06); }
    .buriti-chat-abas {
        display: flex; gap: 6px; padding: 8px 10px 0; background: var(--clara, #f0f2f5); flex-wrap: wrap;
    }
    .buriti-chat-abas button {
        border: 0; background: var(--superficie, #fff); border-radius: 999px; padding: 6px 12px; font: inherit;
        font-size: .78rem; font-weight: 700; color: var(--suave, #5b6b76); cursor: pointer;
        border: 1px solid #d5dde3;
    }
    .buriti-chat-abas button.ativo {
        background: color-mix(in srgb, var(--cor-tela, #075e54) 14%, #fff);
        color: var(--cor-tela, #075e54); border-color: color-mix(in srgb, var(--cor-tela, #075e54) 35%, #fff);
    }

    /* Anexo — “colar o papel no envelope” */
    .buriti-anexo {
        display: grid; gap: 8px; width: 100%;
    }
    .buriti-anexo-rotulo {
        display: inline-flex; align-items: center; gap: 6px; font-size: .8rem; font-weight: 800; color: var(--tinta, #1a242b);
    }
    .buriti-anexo-drop {
        position: relative; display: grid; place-items: center; text-align: center;
        min-height: 96px; padding: 16px; border-radius: 14px; cursor: pointer;
        border: 2px dashed color-mix(in srgb, var(--cor-tela, #16384a) 35%, #cfd8df);
        background:
            linear-gradient(180deg, color-mix(in srgb, var(--clara, #e8eef2) 55%, #fff), #fff);
        transition: border-color .15s, background .15s, transform .15s;
    }
    .buriti-anexo-drop:hover, .buriti-anexo-drop.arrasto {
        border-color: var(--cor-tela, #16384a);
        background: color-mix(in srgb, var(--cor-tela, #16384a) 8%, #fff);
        transform: translateY(-1px);
    }
    .buriti-anexo-drop i.ico {
        width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center;
        margin: 0 auto 8px; font-size: 1.15rem; color: #fff;
        background: linear-gradient(145deg, var(--destaque, #1f4e66), var(--cor-tela, #16384a));
    }
    .buriti-anexo-drop strong { display: block; font-size: .92rem; color: var(--tinta, #1a242b); }
    .buriti-anexo-drop span { display: block; margin-top: 4px; font-size: .78rem; color: var(--suave, #5b6b76); font-weight: 500; }
    .buriti-anexo-drop input[type=file] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .buriti-anexo-nome {
        display: none; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px;
        background: var(--superficie, #fff); border: 1px solid var(--linha, #cfd8df); font-size: .85rem; font-weight: 600;
    }
    .buriti-anexo-nome.visivel { display: flex; }
    .buriti-anexo-nome i { color: var(--cor-tela, #16384a); }
    .buriti-anexo-lista {
        display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none;
    }
    .buriti-anexo-lista a {
        display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 12px;
        background: var(--superficie, #fff); border: 1px solid var(--linha, #cfd8df); text-decoration: none;
        color: var(--tinta, #1a242b); font-weight: 600; font-size: .85rem;
        box-shadow: 0 4px 12px rgba(21,34,43,.04);
    }
    .buriti-anexo-lista a:hover { border-color: var(--cor-tela, #16384a); }
    .buriti-anexo-lista a i { color: #c0392b; }

    /* ------------------------------------------------------------------
       Padrão Operação (todos os módulos, exceto transporte)
       Ordem visual fixa das ações: Primária → Secundária → Consulta → Mais → Perigo
       Diferença entre módulos: só --cor / --destaque (e botões ocultos por permissão)
       ------------------------------------------------------------------ */
    .buriti-foco {
        display: grid; gap: 6px; margin: 0 0 14px; padding: 16px 18px;
        border-radius: 16px; border: 1px solid var(--linha, #cfd8df);
        border-left: 5px solid var(--cor-tela, var(--cor, #16384a));
        background: linear-gradient(115deg, color-mix(in srgb, var(--clara, #e8eef2) 72%, #fff), #fff 58%);
        box-shadow: 0 10px 22px rgba(21,34,43,.04);
    }
    .buriti-foco .buriti-foco-kicker {
        margin: 0; font-size: .7rem; font-weight: 800; letter-spacing: .1em;
        text-transform: uppercase; color: var(--cor-tela, var(--cor, #16384a));
    }
    .buriti-foco .buriti-foco-titulo { margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--tinta, #1a242b); line-height: 1.25; }
    .buriti-foco .buriti-foco-texto { margin: 0; color: var(--suave, #5b6b76); font-size: .9rem; line-height: 1.45; max-width: 62ch; }
    .buriti-foco .buriti-foco-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }

    .buriti-trilho,
    .acoes-tela {
        display: flex; flex-wrap: nowrap; align-items: center; gap: 8px;
    }
    .acoes-tela { flex: none; }
    .buriti-trilho {
        margin: 0 0 14px; padding: 10px 12px; border-radius: 14px;
        background: color-mix(in srgb, #fff 88%, var(--clara, #e8eef2));
        border: 1px solid var(--linha, #cfd8df);
    }
    .buriti-trilho .buriti-trilho-grupo {
        display: inline-flex; flex-wrap: wrap; align-items: center; gap: 8px;
    }
    .buriti-trilho .buriti-trilho-grupo.fim { margin-left: auto; }

    /* Analogias de botão comuns a todos os módulos */
    .botao.primaria,
    .buriti-trilho .botao:not(.claro):not(.suave):not(.perigo):not(.consulta):not(.informes):not(.ajuda),
    .acoes-tela .botao:not(.claro):not(.suave):not(.perigo):not(.consulta):not(.informes):not(.ajuda) {
        order: 1;
    }
    .botao.consulta { order: 2; background: var(--superficie, #fff); color: var(--cor-tela, var(--cor)); border: 1px solid color-mix(in srgb, var(--cor-tela, var(--cor)) 28%, var(--linha)); }
    .botao.secundaria,
    .botao.claro { order: 3; }
    .botao.suave {
        order: 4; background: color-mix(in srgb, var(--clara) 55%, #fff); color: var(--suave);
        border: 1px solid var(--linha);
    }
    .botao.mais { order: 5; }
    /*
     * Padrão de ações do cabeçalho (todos os módulos):
     * botões só com ícone + title/aria-label; ao fim: Informes (I) e Ajuda (?).
     */
    .botao.icone,
    .acoes-tela > .botao,
    .acoes-tela > form > .botao,
    .acoes-tela > form > button.botao {
        width: 40px; min-width: 40px; height: 40px; min-height: 40px;
        padding: 0; gap: 0; justify-content: center; flex: none;
        white-space: nowrap;
        /* Isola de .marca .icone / .icone legado nos layouts de módulo */
        box-shadow: none;
        border-radius: 10px;
    }
    .botao.icone:not(.claro):not(.informes):not(.ajuda):not(.perigo),
    .acoes-tela > .botao:not(.claro):not(.informes):not(.ajuda):not(.perigo),
    .acoes-tela > form > .botao:not(.claro):not(.informes):not(.ajuda):not(.perigo) {
        background: var(--cor-tela, var(--cor, #0f4c5c));
        color: #fff;
    }
    .botao.claro.icone,
    .acoes-tela > .botao.claro,
    .acoes-tela > form > .botao.claro {
        background: var(--superficie, #fff);
        color: var(--cor-tela, var(--cor, #0f4c5c));
        border: 1px solid color-mix(in srgb, var(--cor-tela, var(--cor)) 35%, var(--linha, #cfd8df));
    }
    .botao.icone i,
    .acoes-tela > .botao > i,
    .acoes-tela > form > .botao > i {
        font-size: 1.05rem; line-height: 1; margin: 0;
    }
    /* Informes (I) penúltimo; Ajuda (?) sempre o último no cabeçalho */
    .botao.informes,
    .acoes-tela > .botao.informes {
        order: 98;
        width: 40px; min-width: 40px; height: 40px; min-height: 40px;
        padding: 0; gap: 0; justify-content: center;
        border-radius: 10px; border: 0;
        background: #1d6fd8; color: #fff;
        box-shadow: 0 1px 0 color-mix(in srgb, #0b4ea8 35%, transparent);
    }
    .botao.informes:hover { filter: brightness(1.06); background: #185fc0; }
    .botao.informes i { font-size: 1.05rem; line-height: 1; margin: 0; }
    .botao.ajuda,
    .acoes-tela > .botao.ajuda {
        order: 99;
        width: 40px; min-width: 40px; height: 40px; min-height: 40px;
        padding: 0; gap: 0; justify-content: center;
        border-radius: 10px;
        background: var(--superficie, #fff);
        color: var(--cor-tela, var(--cor, #1a5f9e));
        border: 1px solid color-mix(in srgb, var(--cor-tela, var(--cor)) 35%, var(--linha, #cfd8df));
    }
    .botao.ajuda:hover {
        background: color-mix(in srgb, var(--clara, #e8eef2) 55%, #fff);
        border-color: color-mix(in srgb, var(--cor-tela, var(--cor)) 50%, var(--linha));
    }
    .botao.ajuda i { font-size: 1.05rem; line-height: 1; margin: 0; }
    .botao.alerta-semaforo { color: #b42318; border-color: color-mix(in srgb, #b42318 35%, var(--linha, #cfd8df)); }
    .botao.alerta-semaforo:hover { background: #fdeceb; }
    .icone-semaforo {
        display: inline-grid; gap: 2px; place-items: center; width: 14px; padding: 3px 2px;
        border-radius: 5px; background: #1f2933; box-shadow: inset 0 0 0 1px rgba(255,255,255,.12);
    }
    .icone-semaforo i {
        width: 7px; height: 7px; border-radius: 50%; display: block;
        background: #4b5563; box-shadow: inset 0 0 0 1px rgba(0,0,0,.25);
    }
    .icone-semaforo i:nth-child(1) { background: #ef4444; box-shadow: 0 0 6px rgba(239,68,68,.75); }
    .icone-semaforo i:nth-child(2) { background: #6b7280; }
    .icone-semaforo i:nth-child(3) { background: #4b5563; }
    .botao.perigo {
        order: 7; background: var(--erro, #9b2c2c); color: #fff; border: 0;
    }
    .botao.perigo.claro {
        background: var(--superficie, #fff); color: var(--erro, #9b2c2c);
        border: 1px solid color-mix(in srgb, var(--erro, #9b2c2c) 35%, var(--linha));
    }
    .botao[disabled] {
        opacity: .45; filter: grayscale(.35); cursor: not-allowed; pointer-events: none;
    }
    /* Visível sem permissão: cinza, mas ainda abre o modal explicativo */
    .botao.buriti-acao-bloqueada {
        opacity: .72; filter: grayscale(.45); cursor: pointer;
        background: color-mix(in srgb, var(--suave) 55%, #fff) !important;
        color: var(--suave) !important; border: 1px solid var(--linha) !important;
    }
    .botao.buriti-acao-oculta { display: none !important; }

    /* Painéis secundários: formulários longos ficam fora da dobra até o botão abrir */
    [data-buriti-painel][hidden] { display: none !important; }
    .buriti-painel-ancora { scroll-margin-top: 88px; }

    /* Modal padrão — corpo rolável; cabeçalho fixo; sem estouro da viewport */
    .buriti-modal-backdrop {
        position: fixed; inset: 0; z-index: 80;
        display: grid; place-items: center; padding: 12px 16px;
        background: rgba(21,34,43,.45); backdrop-filter: blur(3px);
        overflow: auto;
    }
    .buriti-modal-backdrop[hidden] { display: none !important; }
    .buriti-modal {
        width: min(640px, 100%);
        max-height: min(88vh, 860px);
        display: flex; flex-direction: column;
        overflow: hidden;
        background: var(--superficie, #fff); border-radius: 16px; border: 1px solid var(--linha, #cfd8df);
        box-shadow: 0 24px 60px rgba(21,34,43,.22);
        animation: buriti-modal-in .18s ease both;
    }
    .buriti-modal header {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
        padding: 14px 16px; border-bottom: 1px solid var(--linha, #cfd8df);
        background: linear-gradient(120deg, color-mix(in srgb, var(--clara) 65%, #fff), #fff);
        flex: none;
    }
    .buriti-modal header h2 { margin: 0; font-size: 1.05rem; }
    .buriti-modal header p { margin: 4px 0 0; color: var(--suave); font-size: .82rem; }
    .buriti-modal .buriti-modal-corpo {
        padding: 14px 16px 18px;
        overflow-y: auto; overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        flex: 1 1 auto; min-height: 0;
    }
    .buriti-modal .buriti-modal-corpo .form-grid {
        grid-template-columns: 1fr;
        align-items: start;
    }
    .buriti-modal .buriti-modal-corpo .form-grid .buriti-select,
    .buriti-modal .buriti-modal-corpo .form-grid .buriti-select.col-wide {
        grid-column: 1 / -1;
        width: 100%;
        max-width: none;
    }
    .buriti-modal .buriti-modal-corpo .form-grid .acoes {
        position: sticky; bottom: 0;
        background: linear-gradient(180deg, transparent, #fff 28%);
        padding-top: 12px; margin-top: 4px;
    }
    .buriti-modal .buriti-modal-fechar {
        width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--linha);
        background: var(--superficie, #fff); color: var(--suave); cursor: pointer; flex: none;
    }
    .buriti-modal .buriti-modal-fechar:hover { color: var(--cor-tela); border-color: var(--cor-tela); }
    @keyframes buriti-modal-in {
        from { opacity: 0; transform: translateY(8px) scale(.98); }
        to { opacity: 1; transform: none; }
    }

    /* Densidade: blocos de consulta mais leves; ação em destaque */
    .fluxo-bloco.consulta {
        background: color-mix(in srgb, #fff 92%, var(--clara));
        border-style: dashed;
    }
    .fluxo-bloco.acao {
        border-left: 4px solid var(--cor-tela, var(--cor));
        box-shadow: 0 12px 28px rgba(21,34,43,.06);
    }
    .fluxo-sep {
        margin: 8px 0 10px; font-size: .72rem; font-weight: 800; letter-spacing: .1em;
        text-transform: uppercase; color: var(--suave);
    }
    .cabeca-tela .acoes-tela .botao { white-space: nowrap; }
    @media (max-width: 720px) {
        .buriti-trilho .buriti-trilho-grupo.fim { margin-left: 0; width: 100%; }
        .buriti-modal { max-height: 92vh; }
    }

    /* —— Modo escuro: elevações LibreCode + forma estável —— */
    html[data-buriti-modo="escuro"] {
        color-scheme: dark;
    }
    html[data-buriti-modo="escuro"] body {
        color: var(--tinta) !important;
        background:
            radial-gradient(920px 380px at 12% -10%, color-mix(in srgb, var(--cor-tela, var(--cor)) 16%, transparent), transparent 58%),
            radial-gradient(700px 320px at 95% 0%, color-mix(in srgb, var(--acento, #7ad3de) 10%, transparent), transparent 50%),
            linear-gradient(180deg, #0e121a 0%, var(--fundo) 42%, var(--fundo) 100%) !important;
    }
    html[data-buriti-modo="escuro"] .miolo,
    html[data-buriti-modo="escuro"] main,
    html[data-buriti-modo="escuro"] .app {
        color: var(--tinta);
        background: transparent;
    }

    /* Superfícies / cards */
    html[data-buriti-modo="escuro"] .bloco,
    html[data-buriti-modo="escuro"] .painel,
    html[data-buriti-modo="escuro"] .card,
    html[data-buriti-modo="escuro"] .cartao,
    html[data-buriti-modo="escuro"] .kpi,
    html[data-buriti-modo="escuro"] .numeros article,
    html[data-buriti-modo="escuro"] .atalhos a,
    html[data-buriti-modo="escuro"] .mod-cards a,
    html[data-buriti-modo="escuro"] .fluxo-bloco,
    html[data-buriti-modo="escuro"] .buriti-painel,
    html[data-buriti-modo="escuro"] .buriti-modal,
    html[data-buriti-modo="escuro"] .gaveta-buriti,
    html[data-buriti-modo="escuro"] .select-busca-lista,
    html[data-buriti-modo="escuro"] .buriti-select-painel {
        background: var(--superficie) !important;
        border-color: var(--linha) !important;
        color: var(--tinta) !important;
        box-shadow: var(--sombra-leve) !important;
    }
    html[data-buriti-modo="escuro"] .numeros article,
    html[data-buriti-modo="escuro"] .kpi {
        background: linear-gradient(180deg, color-mix(in srgb, var(--tom, var(--cor-tela, var(--cor))) 14%, var(--superficie)), var(--superficie) 70%) !important;
        border-color: color-mix(in srgb, var(--tom, var(--cor-tela, var(--cor))) 28%, var(--linha)) !important;
    }
    html[data-buriti-modo="escuro"] .numeros .kpi-ico,
    html[data-buriti-modo="escuro"] .marca-tela {
        background: var(--superficie-alta) !important;
        border-color: var(--linha) !important;
        color: var(--destaque, var(--cor-tela)) !important;
        box-shadow: none !important;
    }

    /* Cabeça / topo */
    html[data-buriti-modo="escuro"] .topo,
    html[data-buriti-modo="escuro"] .topo.topo-buriti,
    html[data-buriti-modo="escuro"] .topo-buriti {
        background: var(--topo-fundo) !important;
        border-bottom-color: var(--topo-borda) !important;
        box-shadow: 0 1px 0 var(--topo-borda) !important;
    }
    html[data-buriti-modo="escuro"] .cabeca-tela {
        background: linear-gradient(120deg, color-mix(in srgb, var(--clara) 88%, var(--superficie)), var(--superficie) 62%) !important;
        border-bottom-color: var(--linha) !important;
        border-left-color: var(--cor-tela, var(--cor)) !important;
    }
    html[data-buriti-modo="escuro"] .topo-nome-modulo,
    html[data-buriti-modo="escuro"] .titulo-modulo {
        color: var(--destaque, var(--cor-tela, var(--cor))) !important;
    }
    html[data-buriti-modo="escuro"] .topo-desc-modulo,
    html[data-buriti-modo="escuro"] .cabeca-tela .lead,
    html[data-buriti-modo="escuro"] .lead {
        color: var(--suave) !important;
    }
    html[data-buriti-modo="escuro"] .acao-topo-buriti {
        background: var(--superficie-alta) !important;
        border-color: var(--linha) !important;
        color: var(--tinta) !important;
        box-shadow: none !important;
    }
    html[data-buriti-modo="escuro"] .acao-topo-buriti.acao-sair {
        color: #f0a8a8 !important;
        border-color: color-mix(in srgb, #c45c5c 35%, var(--linha)) !important;
    }
    html[data-buriti-modo="escuro"] .acao-topo-buriti.acao-sair:hover {
        background: var(--erro-fundo, #3a1c1c) !important;
        color: #ffc9c9 !important;
    }
    html[data-buriti-modo="escuro"] .acao-topo-buriti .avatar-topo {
        border-color: var(--linha) !important;
    }
    html[data-buriti-modo="escuro"] .acao-topo-buriti:hover,
    html[data-buriti-modo="escuro"] .acao-topo-buriti[aria-expanded="true"],
    html[data-buriti-modo="escuro"] .acao-topo-buriti[aria-pressed="true"],
    html[data-buriti-modo="escuro"] .acao-topo-buriti.buriti-modo-toggle[aria-pressed="true"] {
        background: color-mix(in srgb, var(--acento) 16%, var(--superficie)) !important;
        border-color: color-mix(in srgb, var(--acento) 40%, var(--linha)) !important;
        color: var(--acento) !important;
    }
    html[data-buriti-modo="escuro"] .acao-topo-buriti .notif-ponto {
        box-shadow: 0 0 0 2px var(--superficie) !important;
    }

    /* Botões */
    html[data-buriti-modo="escuro"] .botao {
        background: var(--cor-tela, var(--cor)) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    html[data-buriti-modo="escuro"] .botao.claro,
    html[data-buriti-modo="escuro"] .botao.consulta {
        background: var(--superficie-alta) !important;
        color: var(--tinta) !important;
        border: 1px solid var(--linha-forte, var(--linha)) !important;
    }
    html[data-buriti-modo="escuro"] .botao.claro:hover,
    html[data-buriti-modo="escuro"] .botao.consulta:hover {
        border-color: color-mix(in srgb, var(--cor-tela, var(--cor)) 45%, var(--linha)) !important;
        color: var(--destaque, var(--cor-tela)) !important;
    }
    html[data-buriti-modo="escuro"] .botao.informes {
        background: #3b82f6 !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    html[data-buriti-modo="escuro"] .botao.informes:hover {
        background: #2563eb !important;
    }
    html[data-buriti-modo="escuro"] .botao.perigo,
    html[data-buriti-modo="escuro"] .botao.excluir {
        background: color-mix(in srgb, var(--erro) 22%, var(--superficie)) !important;
        color: var(--erro) !important;
        border: 1px solid color-mix(in srgb, var(--erro) 40%, var(--linha)) !important;
    }
    html[data-buriti-modo="escuro"] .botao.ghost,
    html[data-buriti-modo="escuro"] .botao.suave {
        background: var(--clara) !important;
        color: var(--suave) !important;
        border-color: var(--linha) !important;
    }

    /* Formulários */
    html[data-buriti-modo="escuro"] input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="image"]),
    html[data-buriti-modo="escuro"] select,
    html[data-buriti-modo="escuro"] textarea,
    html[data-buriti-modo="escuro"] .busca-campo input,
    html[data-buriti-modo="escuro"] .buriti-select-busca,
    html[data-buriti-modo="escuro"] .select-usuario-busca,
    html[data-buriti-modo="escuro"] .form-grid input,
    html[data-buriti-modo="escuro"] .form-grid select,
    html[data-buriti-modo="escuro"] .form-grid textarea {
        background: var(--input-bg) !important;
        color: var(--tinta) !important;
        border-color: var(--linha) !important;
        box-shadow: none !important;
        color-scheme: dark;
    }
    html[data-buriti-modo="escuro"] input:focus,
    html[data-buriti-modo="escuro"] select:focus,
    html[data-buriti-modo="escuro"] textarea:focus,
    html[data-buriti-modo="escuro"] .busca-campo input:focus {
        background: var(--input-bg-foco) !important;
        border-color: color-mix(in srgb, var(--acento) 55%, var(--linha)) !important;
        outline: none !important;
        box-shadow: var(--foco) !important;
    }
    html[data-buriti-modo="escuro"] option {
        background: var(--superficie-alta);
        color: var(--tinta);
    }
    html[data-buriti-modo="escuro"] ::placeholder {
        color: var(--suave) !important;
        opacity: .85;
    }
    html[data-buriti-modo="escuro"] label,
    html[data-buriti-modo="escuro"] .form-grid label,
    html[data-buriti-modo="escuro"] .dica {
        color: var(--suave) !important;
    }

    /* Tabelas */
    html[data-buriti-modo="escuro"] table,
    html[data-buriti-modo="escuro"] .tablelistagem,
    html[data-buriti-modo="escuro"] .tablelist {
        color: var(--tinta);
        border-color: var(--linha);
    }
    html[data-buriti-modo="escuro"] th,
    html[data-buriti-modo="escuro"] thead td,
    html[data-buriti-modo="escuro"] .formdktd {
        background: var(--clara) !important;
        color: var(--tinta) !important;
        border-color: var(--linha) !important;
    }
    html[data-buriti-modo="escuro"] td {
        border-bottom-color: var(--linha) !important;
        color: var(--tinta);
    }
    html[data-buriti-modo="escuro"] tr:hover td {
        background: color-mix(in srgb, var(--clara) 70%, transparent) !important;
    }

    /* Badges / flash / alertas */
    html[data-buriti-modo="escuro"] .badge.ok,
    html[data-buriti-modo="escuro"] .flash.ok,
    html[data-buriti-modo="escuro"] .buriti-alerta.ok { background: var(--ok-fundo) !important; color: var(--ok) !important; border-color: color-mix(in srgb, var(--ok) 35%, var(--linha)) !important; }
    html[data-buriti-modo="escuro"] .badge.fail,
    html[data-buriti-modo="escuro"] .flash.erro,
    html[data-buriti-modo="escuro"] .buriti-alerta.erro { background: var(--erro-fundo) !important; color: var(--erro) !important; border-color: color-mix(in srgb, var(--erro) 35%, var(--linha)) !important; }
    html[data-buriti-modo="escuro"] .badge.ambar,
    html[data-buriti-modo="escuro"] .flash.alerta,
    html[data-buriti-modo="escuro"] .buriti-alerta.aviso { background: var(--ambar-fundo) !important; color: var(--ambar) !important; border-color: color-mix(in srgb, var(--ambar) 35%, var(--linha)) !important; }
    html[data-buriti-modo="escuro"] .badge.info,
    html[data-buriti-modo="escuro"] .badge.muted { background: var(--muted-fundo) !important; color: var(--muted-tinta) !important; }
    html[data-buriti-modo="escuro"] .selo,
    html[data-buriti-modo="escuro"] .selo-fluxo {
        background: var(--clara) !important;
        color: var(--tinta) !important;
    }

    /* Gavetas / notificações / busca */
    html[data-buriti-modo="escuro"] .gaveta-notif .notif-barra,
    html[data-buriti-modo="escuro"] .gaveta-notif .notif-rodape,
    html[data-buriti-modo="escuro"] .buriti-modal header,
    html[data-buriti-modo="escuro"] .buriti-modal footer {
        background: var(--superficie-alta) !important;
        border-color: var(--linha) !important;
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .gaveta-notif .notif-item { color: var(--tinta) !important; }
    html[data-buriti-modo="escuro"] .gaveta-notif .notif-item:hover,
    html[data-buriti-modo="escuro"] .gaveta-buriti a:hover,
    html[data-buriti-modo="escuro"] .busca-ieducar:hover,
    html[data-buriti-modo="escuro"] .busca-lista a:hover {
        background: var(--clara) !important;
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .gaveta-notif .notif-item.unread {
        background: color-mix(in srgb, var(--ambar) 12%, var(--superficie)) !important;
        border-color: color-mix(in srgb, var(--ambar) 28%, var(--linha)) !important;
    }
    html[data-buriti-modo="escuro"] .gaveta-buriti a,
    html[data-buriti-modo="escuro"] .gaveta-buriti .rotulo,
    html[data-buriti-modo="escuro"] .busca-lista a strong,
    html[data-buriti-modo="escuro"] .busca-lista a small,
    html[data-buriti-modo="escuro"] .busca-vazio,
    html[data-buriti-modo="escuro"] .notif-vazio {
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .gaveta-buriti .rotulo,
    html[data-buriti-modo="escuro"] .busca-lista a small,
    html[data-buriti-modo="escuro"] .gaveta-notif .notif-item .date-notification {
        color: var(--suave) !important;
    }
    html[data-buriti-modo="escuro"] .gaveta-buriti .sep,
    html[data-buriti-modo="escuro"] .busca-ieducar {
        border-color: var(--linha) !important;
        color: var(--destaque, var(--cor-tela)) !important;
    }

    /* Fluxo / chat PoC / anexos */
    html[data-buriti-modo="escuro"] .fluxo-bloco.consulta {
        background: color-mix(in srgb, var(--clara) 80%, var(--superficie)) !important;
    }
    html[data-buriti-modo="escuro"] .buriti-anexo-drop,
    html[data-buriti-modo="escuro"] .buriti-anexo-nome {
        background: var(--input-bg) !important;
        border-color: var(--linha) !important;
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .buriti-chat,
    html[data-buriti-modo="escuro"] .chat-shell {
        background: var(--clara) !important;
        border-color: var(--linha) !important;
    }
    html[data-buriti-modo="escuro"] .chat-bolha,
    html[data-buriti-modo="escuro"] .msg.eu,
    html[data-buriti-modo="escuro"] .msg.outro {
        background: var(--superficie) !important;
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .chat-composer,
    html[data-buriti-modo="escuro"] .chat-toolbar {
        background: var(--superficie-alta) !important;
        border-color: var(--linha) !important;
    }

    /* Rodapé */
    html[data-buriti-modo="escuro"] footer.rodape-coop-buriti {
        background: color-mix(in srgb, var(--superficie) 92%, #000) !important;
        border-top-color: var(--linha) !important;
        color: var(--suave) !important;
    }
    html[data-buriti-modo="escuro"] footer.rodape-coop-buriti a {
        color: var(--acento) !important;
    }

    /* Lateral já é ink; só refinamos no escuro */
    html[data-buriti-modo="escuro"] .app > .lateral,
    html[data-buriti-modo="escuro"] aside.lateral {
        --nav-base: #0a0c11;
        --nav-link: #e9ebf2;
        --nav-link-suave: #8b95a8;
        background: linear-gradient(
            180deg,
            color-mix(in srgb, var(--cor, #b86a63) 26%, var(--nav-base)) 0%,
            var(--nav-base) 58%
        ) !important;
        border-right: 1px solid var(--nav-borda, var(--linha));
    }
    html[data-buriti-modo="escuro"] .lateral .buriti-rail-topo {
        border-bottom-color: rgba(255,255,255,.06) !important;
    }

    /* Links em conteúdo (não botões) */
    html[data-buriti-modo="escuro"] .miolo a:not(.botao):not(.acao-topo-buriti):not(.badge) {
        color: var(--link, var(--acento));
    }
    html[data-buriti-modo="escuro"] .miolo a:not(.botao):not(.acao-topo-buriti):hover {
        color: color-mix(in srgb, var(--link, var(--acento)) 82%, #fff);
    }

    /* Código / pre */
    html[data-buriti-modo="escuro"] code,
    html[data-buriti-modo="escuro"] pre {
        background: var(--input-bg) !important;
        color: var(--acento) !important;
        border-color: var(--linha);
    }

    /* Empty / muted */
    html[data-buriti-modo="escuro"] .lista-vazia,
    html[data-buriti-modo="escuro"] .vazio,
    html[data-buriti-modo="escuro"] .muted,
    html[data-buriti-modo="escuro"] .fluxo-sep {
        color: var(--suave) !important;
    }

    /* Painel analítico: tipografia e superfícies remanescentes */
    html[data-buriti-modo="escuro"] .numeros article span,
    html[data-buriti-modo="escuro"] .numeros article small,
    html[data-buriti-modo="escuro"] .kpi span,
    html[data-buriti-modo="escuro"] .kpi small,
    html[data-buriti-modo="escuro"] .atalhos a small,
    html[data-buriti-modo="escuro"] .mod-cards a small,
    html[data-buriti-modo="escuro"] .bloco .sub,
    html[data-buriti-modo="escuro"] .painel-resumo {
        color: var(--suave) !important;
    }
    html[data-buriti-modo="escuro"] .numeros article strong,
    html[data-buriti-modo="escuro"] .kpi strong,
    html[data-buriti-modo="escuro"] .atalhos a strong,
    html[data-buriti-modo="escuro"] .mod-cards a strong,
    html[data-buriti-modo="escuro"] .bloco h2,
    html[data-buriti-modo="escuro"] .cabeca-tela h1,
    html[data-buriti-modo="escuro"] .card h3 {
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .painel-analise,
    html[data-buriti-modo="escuro"] .painel-grade .bloco,
    html[data-buriti-modo="escuro"] .hero-painel,
    html[data-buriti-modo="escuro"] .kpi-grupo,
    html[data-buriti-modo="escuro"] .prio-card,
    html[data-buriti-modo="escuro"] .mod-card,
    html[data-buriti-modo="escuro"] .buriti-filtro-bloco,
    html[data-buriti-modo="escuro"] .buriti-filtro {
        background: var(--superficie) !important;
        border-color: var(--linha) !important;
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .cabeca-tela,
    html[data-buriti-modo="escuro"] .hero-painel,
    html[data-buriti-modo="escuro"] .fluxo-bloco.acao {
        background: linear-gradient(120deg, color-mix(in srgb, var(--clara) 85%, var(--superficie)), var(--superficie) 65%) !important;
    }
    html[data-buriti-modo="escuro"] .atalhos a:hover,
    html[data-buriti-modo="escuro"] .mod-cards a:hover,
    html[data-buriti-modo="escuro"] .mod-card:hover {
        border-color: color-mix(in srgb, var(--destaque, var(--cor-tela)) 42%, var(--linha)) !important;
        box-shadow: var(--sombra) !important;
    }
    html[data-buriti-modo="escuro"] .fluxo-bloco.consulta {
        background: color-mix(in srgb, var(--clara) 72%, var(--superficie)) !important;
    }
    html[data-buriti-modo="escuro"] .fluxo-bloco.docs .selo-fluxo,
    html[data-buriti-modo="escuro"] .fluxo-bloco.equipe .selo-fluxo {
        background: var(--clara) !important;
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .buriti-alerta.aviso {
        background: var(--ambar-fundo) !important;
        color: var(--ambar) !important;
        border-color: color-mix(in srgb, var(--ambar) 35%, var(--linha)) !important;
    }
    html[data-buriti-modo="escuro"] .miolo .bloco:has(table) {
        background: var(--superficie) !important;
    }
    html[data-buriti-modo="escuro"] .filtro,
    html[data-buriti-modo="escuro"] .escola-card,
    html[data-buriti-modo="escuro"] .bandeja,
    html[data-buriti-modo="escuro"] .chip-item,
    html[data-buriti-modo="escuro"] .alerta-card .selo,
    html[data-buriti-modo="escuro"] .busca-select-lista,
    html[data-buriti-modo="escuro"] .busca-select-input,
    html[data-buriti-modo="escuro"] .card-escola,
    html[data-buriti-modo="escuro"] .item-lista,
    html[data-buriti-modo="escuro"] .lista-item,
    html[data-buriti-modo="escuro"] .painel-card {
        background: var(--superficie) !important;
        border-color: var(--linha) !important;
        color: var(--tinta) !important;
    }
    html[data-buriti-modo="escuro"] .numeros .kpi-ico {
        background: var(--superficie-alta) !important;
        border-color: color-mix(in srgb, var(--tom, var(--cor-tela)) 28%, var(--linha)) !important;
    }
    html[data-buriti-modo="escuro"] .prato {
        background: color-mix(in srgb, var(--clara) 70%, var(--superficie)) !important;
        border-color: var(--linha) !important;
    }
    html[data-buriti-modo="escuro"] .chip-pick.on {
        background: color-mix(in srgb, var(--tom-ref, var(--cor)) 18%, var(--superficie)) !important;
        color: var(--tom-ref, var(--destaque)) !important;
        border-color: color-mix(in srgb, var(--tom-ref, var(--cor)) 40%, var(--linha)) !important;
    }
</style>
<script>
(function () {
    if (window.__buritiSelectBusca) return;
    window.__buritiSelectBusca = true;
    function filtrar(input) {
        var id = input.getAttribute('data-for');
        if (!id) return;
        var sel = document.getElementById(id);
        if (!sel) return;
        var q = (input.value || '').toLowerCase().normalize('NFD').replace(/\p{M}/gu, '');
        Array.prototype.forEach.call(sel.options, function (opt) {
            if (!opt.value) { opt.hidden = false; return; }
            if (opt.getAttribute('data-fora-escola') === '1') { opt.hidden = true; return; }
            var txt = (opt.getAttribute('data-busca') || opt.textContent || '').toLowerCase().normalize('NFD').replace(/\p{M}/gu, '');
            opt.hidden = q !== '' && txt.indexOf(q) === -1;
        });
    }
    document.addEventListener('input', function (e) {
        var t = e.target;
        if (t && (t.classList.contains('buriti-select-busca') || t.classList.contains('select-usuario-busca'))) {
            filtrar(t);
        }
    });
})();
(function () {
    if (window.__buritiAnexo) return;
    window.__buritiAnexo = true;
    function pintar(input) {
        var wrap = input.closest('.buriti-anexo');
        if (!wrap) return;
        var nome = wrap.querySelector('.buriti-anexo-nome');
        var rotulo = wrap.querySelector('.buriti-anexo-nome-txt');
        var drop = wrap.querySelector('.buriti-anexo-drop');
        var arq = input.files && input.files[0];
        if (!arq) {
            if (nome) nome.classList.remove('visivel');
            if (drop) drop.classList.remove('arrasto');
            return;
        }
        if (rotulo) rotulo.textContent = arq.name + (arq.size ? ' · ' + Math.max(1, Math.round(arq.size / 1024)) + ' KB' : '');
        if (nome) nome.classList.add('visivel');
    }
    document.addEventListener('change', function (e) {
        if (e.target && e.target.matches('.buriti-anexo input[type=file]')) pintar(e.target);
    });
    document.addEventListener('dragover', function (e) {
        var drop = e.target.closest && e.target.closest('.buriti-anexo-drop');
        if (!drop) return;
        e.preventDefault();
        drop.classList.add('arrasto');
    });
    document.addEventListener('dragleave', function (e) {
        var drop = e.target.closest && e.target.closest('.buriti-anexo-drop');
        if (drop) drop.classList.remove('arrasto');
    });
    document.addEventListener('drop', function (e) {
        var drop = e.target.closest && e.target.closest('.buriti-anexo-drop');
        if (!drop) return;
        e.preventDefault();
        drop.classList.remove('arrasto');
        var input = drop.querySelector('input[type=file]');
        if (input && e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
            try {
                input.files = e.dataTransfer.files;
                pintar(input);
            } catch (err) {}
        }
    });
})();
(function () {
    if (window.__buritiOperacao) return;
    window.__buritiOperacao = true;

    function abrirModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.hidden = false;
        document.documentElement.style.overflow = 'hidden';
        var focus = el.querySelector('input, select, textarea, button');
        if (focus) setTimeout(function () { focus.focus(); }, 30);
    }
    function fecharModal(el) {
        if (!el) return;
        el.hidden = true;
        if (!document.querySelector('.buriti-modal-backdrop:not([hidden])')) {
            document.documentElement.style.overflow = '';
        }
    }
    function togglePainel(id, force) {
        var el = document.getElementById(id) || document.querySelector('[data-buriti-painel="'+id+'"]');
        if (!el) return;
        var abrir = typeof force === 'boolean' ? force : el.hidden;
        el.hidden = !abrir;
        if (abrir) {
            el.classList.add('buriti-painel-ancora');
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    window.buritiAbrirModal = abrirModal;
    window.buritiFecharModal = fecharModal;
    window.buritiTogglePainel = togglePainel;

    document.addEventListener('click', function (e) {
        var btnModal = e.target.closest && e.target.closest('[data-buriti-modal]');
        if (btnModal) {
            e.preventDefault();
            abrirModal(btnModal.getAttribute('data-buriti-modal'));
            return;
        }
        var btnPainel = e.target.closest && e.target.closest('[data-buriti-abrir]');
        if (btnPainel) {
            e.preventDefault();
            togglePainel(btnPainel.getAttribute('data-buriti-abrir'), true);
            return;
        }
        var fechar = e.target.closest && e.target.closest('[data-buriti-fechar]');
        if (fechar) {
            e.preventDefault();
            fecharModal(fechar.closest('.buriti-modal-backdrop'));
            return;
        }
        if (e.target.classList && e.target.classList.contains('buriti-modal-backdrop')) {
            fecharModal(e.target);
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        var aberto = document.querySelector('.buriti-modal-backdrop:not([hidden])');
        if (aberto) fecharModal(aberto);
    });
})();
</script>
