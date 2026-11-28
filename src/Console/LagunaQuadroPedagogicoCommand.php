<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaQuadroPedagogicoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

/**
 * Matriz + alocação docente nas turmas com alunos (pré-requisito do quadro de horários).
 */
class LagunaQuadroPedagogicoCommand extends Command
{
    protected $signature = 'laguna:quadro-pedagogico
                            {--ano= : Ano letivo (default: ano corrente)}
                            {--dry-run : Só simula, não grava}
                            {--force-production : Permite gravar em production}';

    protected $description = 'Sincroniza matriz e aloca professores nas turmas com alunos (SC/Laguna) para o quadro de horários';

    public function handle(LagunaQuadroPedagogicoService $svc): int
    {
        $dry = (bool) $this->option('dry-run');
        if (App::environment('production') && ! $dry && ! (bool) $this->option('force-production')) {
            $this->error('Recusado em production. Use --force-production (ou --dry-run).');

            return self::FAILURE;
        }

        $ano = (int) ($this->option('ano') ?: date('Y'));
        $this->info(($dry ? '[dry-run] ' : '')."Quadro pedagógico Laguna · ano {$ano}");

        $r = $svc->executar($ano, $dry);

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Escolas', (string) $r['escolas']],
                ['Turmas com alunos', (string) $r['turmas_alvo']],
                ['AEE ignoradas (fora do automático)', (string) $r['aee_ignoradas']],
                ['Matriz CCT sincronizada', (string) $r['matriz_sincronizada']],
                ['Regentes definidos', (string) $r['regentes']],
                ['Vínculos professor_turma novos', (string) $r['vinculos_pt']],
                ['Disciplinas vinculadas', (string) $r['disciplinas_pt']],
            ]
        );

        foreach ($r['avisos'] as $aviso) {
            $this->warn('  · '.$aviso);
        }

        $this->info($dry ? 'Simulação ok — rode sem --dry-run para gravar.' : 'Pronto. Abra /horario-automatico para gerar o quadro.');

        return self::SUCCESS;
    }
}
