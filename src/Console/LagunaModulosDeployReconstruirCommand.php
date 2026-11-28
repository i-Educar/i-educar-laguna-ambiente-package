<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use iEducar\Packages\BuritiLagunaAmbiente\Services\ModulosDeployCatalogo;
use Illuminate\Console\Command;

class LagunaModulosDeployReconstruirCommand extends Command
{
    protected $signature = 'laguna:modulos-deploy-reconstruir
                            {--inventariar : Cria deploy-info.json ausente/ inválido a partir do composer.json (mtime)}
                            {--json : Saída JSON}';

    protected $description = 'Reconstrói storage/app/buriti/modules-deploy.json a partir dos deploy-info.json dos pacotes';

    public function handle(ModulosDeployCatalogo $catalogo): int
    {
        $resultado = $catalogo->reconstruirManifesto((bool) $this->option('inventariar'));

        if ($this->option('json')) {
            $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Manifesto: %d módulo(s), %d inventariado(s), %d com CI.',
            (int) ($resultado['total'] ?? 0),
            (int) ($resultado['inventariados'] ?? 0),
            (int) ($resultado['com_ci'] ?? 0),
        ));
        $this->line('Arquivo: '.$resultado['manifest_path']);

        return self::SUCCESS;
    }
}
