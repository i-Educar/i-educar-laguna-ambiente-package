<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaPovoarCidadeService;
use Illuminate\Console\Command;

class LagunaPovoarCidadeCommand extends Command
{
    protected $signature = 'laguna:povoar-cidade
                            {--por-turma=12 : Alunos por turma na matrícula de teste}';

    protected $description = 'Admin — povoa Laguna como cidade média (10+ escolas, turmas, servidores, usuários multi-ator e módulos)';

    public function handle(LagunaPovoarCidadeService $service): int
    {
        $porTurma = max(1, min(40, (int) $this->option('por-turma')));
        $this->info('Laguna · povoamento cidade média (PE-77/2026)');
        $this->line("Alunos por turma: {$porTurma}");

        $passos = $service->executar($porTurma);
        $falhas = 0;
        foreach ($passos as $p) {
            $ok = $p['ok'] ? 'OK' : 'FAIL';
            $this->line("  {$ok}  {$p['passo']}");
            if (trim((string) $p['saida']) !== '') {
                foreach (preg_split('/\R/', (string) $p['saida']) as $linha) {
                    if (trim($linha) !== '') {
                        $this->line('       '.$linha);
                    }
                }
            }
            if (! $p['ok']) {
                $falhas++;
            }
        }

        if ($falhas > 0) {
            $this->error("Concluído com {$falhas} falha(s).");

            return self::FAILURE;
        }

        $this->info('Povoamento Laguna concluído.');
        $this->line('Cenários: ausências, ACT diversos, alimentação semanal, almoxarifado, vagas pré-matrícula e transporte.');
        $this->line('Logins PoC (senha=CPF): 42091020001 admin · 42091020003 diretor · 42091020014 busca ativa · 42091020010 chamados · 42091020009 atendimento · 42091020011 RH.');

        return self::SUCCESS;
    }
}
