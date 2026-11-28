{{-- Estilos inline: o CSS do /painel (monorepo) pode estar desatualizado no VPS — o pacote leva o visual. --}}
<style>
    .rodape-op[data-buriti-modulos-deploy] > button {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        max-width: 100%;
        padding: 4px 10px;
        border-radius: 999px;
        text-decoration: none;
        color: var(--suave, #5b6b76);
        background: color-mix(in srgb, var(--info-fundo, #eef3ff) 75%, #fff);
        border: 1px solid color-mix(in srgb, var(--info, #2f6bff) 20%, transparent);
        font: inherit;
        font-size: .68rem;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
    }
    .rodape-op[data-buriti-modulos-deploy] > button:hover {
        color: var(--tinta, #1a242b);
        background: var(--superficie, #fff);
        border-color: color-mix(in srgb, var(--destaque, #1a7a8c) 28%, var(--linha, #cfd8df));
    }
    .rodape-op[data-buriti-modulos-deploy] > button i { font-size: 10px; opacity: .75; }
    .rodape-op[data-buriti-modulos-deploy] > button strong {
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        color: var(--info, #2f6bff);
        letter-spacing: -.02em;
    }
    @media (max-width: 820px) {
        .rodape-op[data-buriti-modulos-deploy] .rodape-op-rotulo { display: none; }
        .rodape-op[data-buriti-modulos-deploy] > button { padding: 4px 8px; }
    }
</style>
<li class="rodape-op info" data-buriti-modulos-deploy>
    <button type="button"
            id="buriti-btn-modulos-deploy"
            title="Módulos instalados, versões e data do deploy"
            aria-controls="buriti-painel-modulos-deploy"
            aria-expanded="false">
        <i class="fa fa-cubes" aria-hidden="true"></i>
        <span class="rodape-op-rotulo">Módulos</span>
        <strong>CI</strong>
    </button>
</li>
