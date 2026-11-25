{{--
  Rodapé discreto — versão i-Educar + cooperação Buriti / LibreCode.
  Variáveis opcionais: $moduloRodape, $versaoApp
--}}
@php
    $moduloRodape = $moduloRodape ?? ($identidade['nome'] ?? 'Laguna');
    $versaoApp = $versaoApp
        ?? (string) (config('app.version')
            ?? (\Composer\InstalledVersions::isInstalled('portabilis/ieducar')
                ? \Composer\InstalledVersions::getPrettyVersion('portabilis/ieducar')
                : null)
            ?? '2.12');
    $ano = date('Y');
@endphp
<footer class="rodape-coop-buriti">
    <span class="versoes">
        i-Educar {{ $versaoApp }}
        <span class="sep">·</span>
        {{ $moduloRodape }}
        <span class="sep">·</span>
        {{ $ano }}
    </span>
    <span>
        Cooperação
        <a href="https://buriti.dev.br/public/" target="_blank" rel="noopener">Buriti</a>
        <span class="sep">·</span>
        <a href="https://librecode.coop/" target="_blank" rel="noopener">LibreCode</a>
    </span>
</footer>
