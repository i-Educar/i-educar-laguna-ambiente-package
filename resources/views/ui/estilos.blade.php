{{-- Estilos compartilhados: topo Buriti + rodapé --}}
<style>
    .topo.topo-buriti, .topo-buriti {
        position: sticky; top: 0; z-index: 40;
        display: grid; grid-template-columns: minmax(140px, 1fr) minmax(160px, 1.4fr) minmax(140px, 1fr);
        align-items: center; gap: 10px 16px;
        min-height: 58px; padding: 10px 20px;
        background: color-mix(in srgb, #fff 94%, transparent);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid color-mix(in srgb, var(--linha, #cfd8df) 70%, transparent);
        box-shadow: 0 1px 0 rgba(255,255,255,.7) inset, 0 8px 24px rgba(21,34,43,.03);
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
        font-size: 1rem; font-weight: 700; letter-spacing: .01em;
        text-transform: none;
        color: var(--cor-tela, var(--cor, #16384a));
        line-height: 1.2;
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
        border: 1px solid color-mix(in srgb, var(--linha, #cfd8df) 80%, #fff);
        border-radius: 12px;
        background: color-mix(in srgb, #fff 88%, var(--clara, #e8eef2));
        color: var(--cor-tela, var(--cor, #16384a));
        line-height: 1; text-decoration: none; cursor: pointer; font-size: 15px;
        position: relative; flex: none;
        transition: background .15s ease, border-color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .acao-topo-buriti:hover, .acao-topo-buriti[aria-expanded="true"] {
        background: #fff;
        border-color: color-mix(in srgb, var(--cor-tela, #16384a) 28%, var(--linha, #cfd8df));
        color: var(--cor-tela, #16384a);
        box-shadow: 0 6px 14px rgba(21,34,43,.06);
        transform: translateY(-1px);
    }
    .acao-topo-buriti svg { display: block; width: 18px; height: 18px; }
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
        padding: 10px 12px; border-bottom: 1px solid #e8edf0;
        position: sticky; top: 0; background: #fff; z-index: 1;
    }
    .gaveta-notif .notif-barra strong { font-size: .88rem; color: #16384a; margin-right: auto; }
    .gaveta-notif .notif-barra a, .gaveta-notif .notif-barra button {
        border: 0; background: 0; color: var(--cor-tela, #16384a); font: inherit; font-size: .75rem;
        font-weight: 700; cursor: pointer; padding: 0; text-decoration: none;
    }
    .gaveta-notif .notif-barra a:hover, .gaveta-notif .notif-barra button:hover { text-decoration: underline; }
    .gaveta-notif .notif-lista { max-height: min(52vh, 380px); overflow: auto; padding: 6px; }
    .gaveta-notif .notif-item {
        display: grid; gap: 4px; padding: 10px 12px; border-radius: 10px;
        text-decoration: none; color: #1a242b; border: 1px solid transparent;
    }
    .gaveta-notif .notif-item:hover { background: #f3f6f8; }
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
        padding-left: 10px; color: #5b6b76; font-size: .72rem; font-weight: 500;
    }
    .gaveta-notif .notif-vazio { margin: 16px 12px; color: #5b6b76; font-size: .84rem; text-align: center; }
    .gaveta-notif .notif-rodape {
        border-top: 1px solid #e8edf0; padding: 10px 12px; text-align: center;
        position: sticky; bottom: 0; background: #fff;
    }
    .gaveta-notif .notif-rodape a {
        display: inline-flex; align-items: center; gap: 6px; font-weight: 750; font-size: .84rem;
        color: var(--cor-tela, #16384a); text-decoration: none;
    }
    .gaveta-notif .notif-rodape a:hover { text-decoration: underline; }
    .busca-campo { display: block; padding: 6px 6px 8px; }
    .busca-campo input {
        width: 100%; min-height: 38px; padding: 8px 12px; border-radius: 10px;
        border: 1px solid var(--linha, #cfd8df); font: inherit; background: #f8fafc;
    }
    .busca-campo input:focus { outline: 2px solid color-mix(in srgb, var(--cor-tela, #16384a) 28%, #fff); border-color: var(--cor-tela, #16384a); background: #fff; }
    .busca-lista { max-height: 240px; overflow: auto; }
    .busca-lista a span { display: grid; gap: 1px; min-width: 0; }
    .busca-lista a strong { font-size: .86rem; font-weight: 650; }
    .busca-lista a small { color: var(--suave, #5b6b76); font-weight: 500; font-size: .72rem; }
    .busca-vazio { margin: 8px 10px; color: var(--suave, #5b6b76); font-size: .82rem; }
    .busca-ieducar {
        display: flex; align-items: center; gap: 8px; margin: 4px 6px 2px; padding: 8px 10px;
        border-top: 1px solid #e8edf0; color: var(--cor-tela, #16384a); text-decoration: none; font-weight: 650; font-size: .82rem;
    }
    .busca-ieducar:hover { background: #f3f6f8; border-radius: 8px; }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
    /* Sanduíche nunca no topo padronizado */
    .topo-buriti .hamburguer,
    .topo-buriti [aria-controls="menu-transporte"].hamburguer { display: none !important; }
    .menu-drop-buriti { position: relative; z-index: 42; overflow: visible; }
    .menu-drop-buriti .gaveta-buriti {
        display: none; position: absolute; top: calc(100% + 6px); right: 0;
        min-width: 260px; max-width: min(360px, calc(100vw - 24px));
        max-height: min(70vh, 480px); overflow-x: hidden; overflow-y: auto;
        background: #fff; border: 1px solid #cfd8df; border-radius: 10px;
        box-shadow: 0 12px 28px rgba(21,34,43,.14); z-index: 50; padding: 6px; list-style: none; margin: 0;
        pointer-events: auto;
    }
    .menu-drop-buriti.aberto .gaveta-buriti { display: block; }
    .gaveta-buriti a {
        display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px;
        color: #1a242b; text-decoration: none; font-weight: 600; font-size: .86rem;
        pointer-events: auto; cursor: pointer;
    }
    .gaveta-buriti a i { width: 16px; text-align: center; color: #5b6b76; flex: none; }
    .gaveta-buriti a:hover { background: #f3f6f8; color: #16384a; }
    .gaveta-buriti .sep { height: 1px; background: #e8edf0; margin: 4px 6px; }
    .gaveta-buriti .rotulo {
        padding: 6px 10px 2px; font-size: .68rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; color: #8aa0ad;
    }
    /* Compat: layouts antigos que ainda usam topo-com-acoes + migalha */
    .topo-com-acoes { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .topo-com-acoes .migalha, .topo .migalha { display: none !important; }
    footer.rodape-coop-buriti {
        margin-top: auto; padding: 8px 16px; border-top: 1px solid #d5dde3;
        background: color-mix(in srgb, #15222b 4%, #f4f7f9);
        color: #7a8a95; font-size: .72rem; letter-spacing: .01em;
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 6px 14px;
    }
    footer.rodape-coop-buriti a { color: #5b6b76; text-decoration: none; font-weight: 600; }
    footer.rodape-coop-buriti a:hover { text-decoration: underline; color: #16384a; }
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
        }
        .acoes-tela { width: 100%; justify-content: flex-start; }
    }
    @media (max-width: 860px) {
        .app .lateral { display: none !important; }
        .miolo > main { padding: 14px 14px 22px !important; }
        .cabeca-tela {
            margin-left: -14px !important;
            margin-right: -14px !important;
            padding: 12px 14px !important;
            flex-direction: column;
            align-items: stretch !important;
        }
        .numeros, .atalhos, .card-grid, .kpis, .mod-cards {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important;
        }
        .form-grid { grid-template-columns: 1fr !important; }
        .grade { grid-template-columns: 56px repeat(5, minmax(64px, 1fr)) !important; overflow-x: auto; }
    }
    @media (max-width: 640px) {
        .topo.topo-buriti { padding: 8px 12px; gap: 8px; grid-template-columns: auto 1fr auto; }
        .topo-nome-modulo { font-size: .9rem; max-width: 46vw; }
        .topo-desc-modulo { font-size: .7rem; max-width: 46vw; }
        .acao-topo-buriti { width: 36px; height: 36px; border-radius: 10px; }
        .bloco, .cartao, .card { padding: 12px !important; }
        .botao, button.botao { min-height: 40px; }
        .numeros, .atalhos, .card-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Seletores com busca (único e múltiplo) — linguagem clara para a SME */
    .buriti-select, .select-usuario {
        display: grid; gap: 6px; font-size: .8rem; font-weight: 700; color: var(--suave, #5b6b76);
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
    .buriti-select-lista, .select-usuario-lista {
        width: 100%; min-height: 120px; padding: 4px; border: 1px solid var(--linha, #cfd8df);
        border-radius: 10px; font: inherit; font-weight: 500; background: #fff;
    }
    .buriti-select-lista option, .select-usuario-lista option { padding: 6px 8px; border-radius: 6px; }
    .buriti-select-lista option:checked, .select-usuario-lista option:checked {
        background: color-mix(in srgb, var(--cor-tela, #16384a) 18%, #fff); color: var(--tinta, #1a242b); font-weight: 700;
    }
    .buriti-select-ajuda, .select-usuario-aviso {
        font-weight: 500; color: var(--suave, #5b6b76); font-size: .75rem;
    }
    .buriti-alerta {
        display: flex; gap: 12px; align-items: flex-start; padding: 12px 14px; border-radius: 12px;
        border: 1px solid var(--linha, #cfd8df); background: #fff; margin: 0 0 12px;
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
        background: #fff; border: 1px solid var(--linha, #cfd8df); border-radius: 14px;
        padding: 14px 16px; box-shadow: 0 8px 18px rgba(21,34,43,.04);
        border-left: 4px solid var(--cor-tela, var(--cor, #16384a));
    }
    .fluxo-bloco.consulta { border-left-color: #5b6b76; background: color-mix(in srgb, #f4f7f9 70%, #fff); }
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
    .fluxo-bloco.consulta .selo-fluxo { background: #eef1f3; color: #5b6b76; }
    .fluxo-bloco.equipe .selo-fluxo { background: #e5f6f3; color: #0d7a6f; }
    .fluxo-bloco.docs .selo-fluxo { background: #fff6e8; color: #b86e00; }
    .fluxo-cabeca p { margin: 0; color: var(--suave, #5b6b76); font-size: .86rem; font-weight: 500; max-width: 40rem; }
    .fluxo-sep {
        display: flex; align-items: center; gap: 10px; margin: 4px 0 2px;
        color: #8aa0ad; font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
    }
    .fluxo-sep::before, .fluxo-sep::after {
        content: ''; flex: 1; height: 1px; background: var(--linha, #cfd8df);
    }

    /* Conversa (analogia WhatsApp) */
    .buriti-chat {
        display: flex; flex-direction: column; min-height: 280px; max-height: min(62vh, 560px);
        border: 1px solid var(--linha, #cfd8df); border-radius: 16px; overflow: hidden;
        background: #e5ddd5;
        background-image:
            radial-gradient(circle at 12% 18%, rgba(255,255,255,.35) 0 1px, transparent 2px),
            radial-gradient(circle at 78% 42%, rgba(0,0,0,.04) 0 1px, transparent 2px);
        background-size: 28px 28px, 36px 36px;
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
        margin: auto; text-align: center; color: #5b6b76; background: rgba(255,255,255,.72);
        border-radius: 12px; padding: 14px 16px; max-width: 280px; font-size: .88rem;
    }
    .buriti-balao {
        max-width: min(78%, 420px); padding: 8px 10px 6px; border-radius: 10px;
        box-shadow: 0 1px 1px rgba(0,0,0,.08); position: relative; font-size: .92rem; line-height: 1.35;
        word-break: break-word;
    }
    .buriti-balao.eles {
        align-self: flex-start; background: #fff; border-top-left-radius: 2px;
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
        background: rgba(0,0,0,.06); color: #5b6b76;
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
        padding: 10px; background: #f0f2f5; border-top: 1px solid #d5dde3; flex: none;
    }
    .buriti-chat-composer textarea {
        width: 100%; min-height: 44px; max-height: 120px; resize: vertical;
        border: 0; border-radius: 22px; padding: 10px 14px; font: inherit; background: #fff;
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
        display: flex; gap: 6px; padding: 8px 10px 0; background: #f0f2f5; flex-wrap: wrap;
    }
    .buriti-chat-abas button {
        border: 0; background: #fff; border-radius: 999px; padding: 6px 12px; font: inherit;
        font-size: .78rem; font-weight: 700; color: #5b6b76; cursor: pointer;
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
        background: #fff; border: 1px solid var(--linha, #cfd8df); font-size: .85rem; font-weight: 600;
    }
    .buriti-anexo-nome.visivel { display: flex; }
    .buriti-anexo-nome i { color: var(--cor-tela, #16384a); }
    .buriti-anexo-lista {
        display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none;
    }
    .buriti-anexo-lista a {
        display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 12px;
        background: #fff; border: 1px solid var(--linha, #cfd8df); text-decoration: none;
        color: var(--tinta, #1a242b); font-weight: 600; font-size: .85rem;
        box-shadow: 0 4px 12px rgba(21,34,43,.04);
    }
    .buriti-anexo-lista a:hover { border-color: var(--cor-tela, #16384a); }
    .buriti-anexo-lista a i { color: #c0392b; }
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
</script>
