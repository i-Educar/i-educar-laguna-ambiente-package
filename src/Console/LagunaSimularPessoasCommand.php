<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaSimulacaoPessoasService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class LagunaSimularPessoasCommand extends Command
{
    protected $signature = 'laguna:simular-pessoas
                            {--qtd=20 : Quantidade de pessoas (1–40)}
                            {--seed= : Seed opcional para reprodutibilidade}
                            {--force-production : Permite em APP_ENV=production}';

    protected $description = 'Admin — simula N pessoas em cenários aleatórios em todos os módulos disponíveis';

    public function handle(LagunaSimulacaoPessoasService $sim): int
    {
        if (App::environment('production') && ! (bool) $this->option('force-production')) {
            $this->error('Recusado em production. Use homologação ou --force-production.');

            return self::FAILURE;
        }

        $qtd = max(1, min(40, (int) $this->option('qtd')));
        $seed = $this->option('seed') !== null && $this->option('seed') !== ''
            ? (int) $this->option('seed')
            : null;

        $this->info("Laguna · simulação de {$qtd} pessoas (cenários aleatórios multi-módulo)");
        $resultado = $sim->executar($qtd, $seed);

        $this->line(($resultado['ok'] ? '  OK  ' : ' FAIL ').$resultado['passo']);
        $this->line('       '.$resultado['saida']);

        if (! empty($resultado['contagem_status'])) {
            $this->newLine();
            $this->info('Contagem por status (ok ≠ falta_teste):');
            foreach ($resultado['contagem_status'] as $st => $n) {
                $rotulo = LagunaSimulacaoPessoasService::rotuloStatus((string) $st);
                $this->line(sprintf('  %-22s %d', $rotulo, $n));
            }
        }

        if (! empty($resultado['pessoas'])) {
            $this->newLine();
            $this->table(
                ['#', 'Nome', 'Perfil', 'ok', 'negativa', 'falta_teste', 'erro', 'outros'],
                collect($resultado['pessoas'])->map(static function ($p, $i) {
                    $c = ['ok' => 0, 'negativa' => 0, 'falta_teste' => 0, 'erro' => 0, 'outros' => 0];
                    foreach ($p['eventos'] ?? [] as $ev) {
                        $st = $ev['status'] ?? 'outros';
                        if (isset($c[$st])) {
                            $c[$st]++;
                        } else {
                            $c['outros']++;
                        }
                    }

                    return [
                        $i + 1,
                        $p['nome'],
                        $p['perfil'],
                        $c['ok'],
                        $c['negativa'],
                        $c['falta_teste'],
                        $c['erro'],
                        $c['outros'],
                    ];
                })->all()
            );
        }

        if ($resultado['lote_id']) {
            $this->line('UI: /laguna-ambiente/simulacao (lote #'.$resultado['lote_id'].')');
        }

        return $resultado['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
