<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaIdiarioVinculoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class LagunaIdiarioCommand extends Command
{
    protected $signature = 'laguna:idiario
                            {--skip-vinculos : Não cria professor_turma}
                            {--setup-script : Roda packages/buriti/docs/scripts/setup-idiario-laguna.sh}
                            {--force-production : Permite em production}';

    protected $description = 'Vincula admin/professores a turmas e (opcional) sobe/configura o i-Diário';

    public function handle(LagunaIdiarioVinculoService $vinculos): int
    {
        if (App::environment('production') && ! (bool) $this->option('force-production')) {
            $this->error('Recusado em production. Use --force-production se necessário.');

            return self::FAILURE;
        }

        if (! $this->option('skip-vinculos')) {
            $r = $vinculos->executar();
            $this->line(($r['ok'] ? '  OK  ' : ' FAIL ').$r['passo'].' — '.$r['saida']);
            if (! $r['ok']) {
                return self::FAILURE;
            }
        }

        if ($this->option('setup-script')) {
            $script = base_path('packages/buriti/docs/scripts/setup-idiario-laguna.sh');
            if (! is_file($script)) {
                $this->error("Script ausente: {$script}");

                return self::FAILURE;
            }
            $this->info('Executando setup-idiario-laguna.sh…');
            $code = 0;
            passthru('bash '.escapeshellarg($script), $code);
            if ($code !== 0) {
                $this->warn("Script retornou código {$code}");

                return self::FAILURE;
            }
        } else {
            $this->line('Dica: php artisan laguna:idiario --setup-script');
            $this->line('      (sobe Docker i-Diário, aponta API para host.docker.internal:8000 e alinha SSO)');
        }

        $this->info('Pronto. Sirva o i-Educar em 0.0.0.0:8000 para o sync do container alcançar a API.');

        return self::SUCCESS;
    }
}
