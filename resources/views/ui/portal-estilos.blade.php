{{-- Shell compartilhado dos portais externos (candidato / família) — largura fluida + mobile --}}
<style>
    :root {
        --portal-max: 1320px;
        --portal-gutter: clamp(16px, 3vw, 36px);
    }
    .portal-shell {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
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
    .portal-topo-inner {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        padding-top: 14px;
        padding-bottom: 14px;
    }
    .portal-main {
        flex: 1;
        padding-top: 24px;
        padding-bottom: 40px;
    }
    .portal-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }
    .portal-passos {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin: 0 0 20px;
    }
    .portal-edital-linha {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 18px;
        align-items: center;
        justify-content: space-between;
    }
    .portal-edital-linha > div { min-width: 0; flex: 1 1 220px; }
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
        .portal-main { padding-top: 16px; padding-bottom: 28px; }
        .portal-nav a { width: 100%; }
    }
</style>
