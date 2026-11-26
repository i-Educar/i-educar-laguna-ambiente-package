{{-- Shell compartilhado dos portais externos (candidato / família) — largura + contraste --}}
<style>
    :root {
        --portal-max: min(1360px, 96vw);
        --portal-gutter: clamp(16px, 2.4vw, 28px);
    }
    /* Evita que a skin pinte links do portal com --destaque (baixa legibilidade). */
    body.portal-shell.tema-buriti-skin a {
        color: inherit;
    }
    body.portal-shell.tema-buriti-skin header.portal-header a,
    body.portal-shell.tema-buriti-skin header.portal-header .marca,
    body.portal-shell.tema-buriti-skin header.portal-header .marca strong,
    body.portal-shell.tema-buriti-skin header.portal-header .marca small,
    body.portal-shell.tema-buriti-skin header.portal-header nav a {
        color: #fff !important;
    }
    body.portal-shell.tema-buriti-skin .botao {
        color: #fff !important;
    }
    body.portal-shell.tema-buriti-skin .botao.claro {
        color: var(--pmd-marca, var(--cor, #2f6f6a)) !important;
    }
    .portal-topo-inner,
    .portal-main,
    .portal-rodape-inner {
        width: 100%;
        max-width: var(--portal-max);
        margin-left: auto;
        margin-right: auto;
        padding-left: var(--portal-gutter);
        padding-right: var(--portal-gutter);
    }
    .portal-main {
        flex: 1;
        padding-top: 28px;
        padding-bottom: 48px;
    }
    .portal-passos {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin: 0 0 22px;
    }
    .portal-edital-linha {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 16px;
        align-items: center;
        justify-content: space-between;
    }
    .portal-edital-linha > div { min-width: 0; flex: 1 1 240px; }
    .portal-tabela-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 900px) {
        .portal-passos { grid-template-columns: 1fr; }
        .portal-topo-inner {
            flex-direction: column;
            align-items: stretch;
        }
        .portal-nav { justify-content: flex-start; }
        .portal-nav a { flex: 1 1 auto; text-align: center; justify-content: center; }
    }
    @media (max-width: 560px) {
        :root { --portal-gutter: 14px; }
        .portal-main { padding-top: 18px; padding-bottom: 32px; }
        .portal-nav a { width: 100%; }
    }
</style>
