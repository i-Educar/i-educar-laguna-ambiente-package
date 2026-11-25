<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use iEducar\Packages\BuritiLagunaAmbiente\Services\AmbienteAuditor;
use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaSetupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class LagunaSetupCommand extends Command
{
    protected $signature = 'laguna:setup
                            {--skip-municipal : Não chama ieducar:setup}
                            {--skip-matriculas : Não chama ieducar:matriculas}
                            {--skip-check : Não reaudita ao final}
                            {--force-production : Permite em APP_ENV=production}';

    protected $description = 'Admin — povoa a base Laguna para teste completo (setup + demos dos módulos)';

    public function handle(LagunaSetupService $setup, AmbienteAuditor $auditor): int
    {
        if (App::environment('production') && ! (bool) $this->option('force-production')) {
            $this->error('Recusado em production. Use homologação ou --force-production.');

            return self::FAILURE;
        }

        $this->info('Laguna · setup de ambiente de teste');
        $passos = $setup->executar(
            comSetupMunicipal: ! (bool) $this->option('skip-municipal'),
            comMatriculas: ! (bool) $this->option('skip-matriculas'),
        );

        $falhas = 0;
        foreach ($passos as $p) {
            $this->line(($p['ok'] ? '  OK  ' : ' FAIL ').$p['passo']);
            if ($p['saida'] !== '') {
                foreach (preg_split('/\R/', $p['saida']) ?: [] as $linha) {
                    if (trim($linha) !== '') {
                        $this->line('       '.$linha);
                    }
                }
            }
            if (! $p['ok']) {
                $falhas++;
            }
        }

        if (! $this->option('skip-check')) {
            $this->newLine();
            $this->call('laguna:check');
        }

        if ($falhas > 0) {
            $this->warn("Setup terminou com {$falhas} passo(s) incompleto(s) — veja detalhes acima.");
        } else {
            $this->info('Setup Laguna concluído.');
        }

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }
}
