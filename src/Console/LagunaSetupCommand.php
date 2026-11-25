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
                            {--skip-matriculas : Não gera matrículas de teste}
                            {--skip-povoar : Não povoa cidade média (10+ escolas); usa caminho mínimo}
                            {--por-turma=12 : Alunos por turma nas matrículas de teste}
                            {--skip-check : Não reaudita ao final}
                            {--force-production : Permite em APP_ENV=production}';

    protected $description = 'Admin — povoa a base Laguna para teste completo (configs + cidade média + demos)';

    public function handle(LagunaSetupService $setup, AmbienteAuditor $auditor): int
    {
        if (App::environment('production') && ! (bool) $this->option('force-production')) {
            $this->error('Recusado em production. Use homologação ou --force-production.');

            return self::FAILURE;
        }

        $porTurma = max(1, min(40, (int) $this->option('por-turma')));

        $this->info('Laguna · setup de ambiente de teste');
        $this->line('Ordem: migrate → datas/censo → cidade média → metadados → demos');
        $this->line("Alunos/turma: {$porTurma}"
            .((bool) $this->option('skip-povoar') ? ' · povoar=OFF' : ' · povoar=ON')
            .((bool) $this->option('skip-matriculas') ? ' · matrículas=OFF' : ' · matrículas=ON'));

        $passos = $setup->executar(
            comSetupMunicipal: ! (bool) $this->option('skip-municipal'),
            comMatriculas: ! (bool) $this->option('skip-matriculas'),
            comPovoarCidade: ! (bool) $this->option('skip-povoar'),
            alunosPorTurma: $porTurma,
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
            $this->line('Logins PoC: CPF = senha (ex.: 42091020001 admin SME).');
        }

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }
}
