<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use Illuminate\Console\Command;

class LagunaEnsaiarCommand extends Command
{
    protected $signature = 'laguna:ensaiar
                            {--skip-municipal}
                            {--skip-matriculas}
                            {--skip-povoar}
                            {--por-turma=12}
                            {--force-production}';

    protected $description = 'Admin — check + setup + recheck (ensaio completo Laguna)';

    public function handle(): int
    {
        $this->info('1/2 — Auditoria inicial');
        $this->call('laguna:check');

        $this->newLine();
        $this->info('2/2 — Setup + recheck');
        $opts = [
            '--por-turma' => max(1, min(40, (int) $this->option('por-turma'))),
        ];
        if ($this->option('skip-municipal')) {
            $opts['--skip-municipal'] = true;
        }
        if ($this->option('skip-matriculas')) {
            $opts['--skip-matriculas'] = true;
        }
        if ($this->option('skip-povoar')) {
            $opts['--skip-povoar'] = true;
        }
        if ($this->option('force-production')) {
            $opts['--force-production'] = true;
        }

        return $this->call('laguna:setup', $opts);
    }
}