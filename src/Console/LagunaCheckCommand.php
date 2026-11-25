<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use iEducar\Packages\BuritiLagunaAmbiente\Services\AmbienteAuditor;
use Illuminate\Console\Command;

class LagunaCheckCommand extends Command
{
    protected $signature = 'laguna:check
                            {--json : Saída JSON}
                            {--strict : Falha se houver avisos em recomendados}';

    protected $description = 'Admin — audita se os módulos Laguna necessários estão instalados e liberados';

    public function handle(AmbienteAuditor $auditor): int
    {
        $relatorio = $auditor->auditar();

        if ($this->option('json')) {
            $this->line(json_encode($relatorio, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $this->codigoSaida($relatorio);
        }

        $this->info('Laguna · auditoria de ambiente PE-77/2026');
        $this->newLine();
        $this->line('Núcleo');
        foreach ($relatorio['nucleo'] as $c) {
            $this->line(($c['ok'] ? '  OK  ' : ' FAIL ').$c['chave'].' — '.$c['detalhe']);
        }

        $this->newLine();
        $this->line('Módulos');
        foreach ($relatorio['modulos'] as $m) {
            $marca = $m['ok'] ? 'OK' : '!!';
            $this->line(sprintf('[%s] %-12s %-28s (%s)', $marca, $m['tr'], $m['nome'], $m['nivel']));
            if (! $m['ok'] || $this->output->isVerbose()) {
                foreach ($m['checks'] as $c) {
                    if ($c['ok'] && ! $this->output->isVerbose()) {
                        continue;
                    }
                    $this->line('      '.($c['ok'] ? '·' : 'x').' '.$c['nome'].': '.$c['detalhe']);
                }
            }
        }

        $r = $relatorio['resumo'];
        $this->newLine();
        $this->info(sprintf(
            'Obrigatórios %d/%d · falhas %d · avisos %d',
            $r['obrigatorios_ok'],
            $r['obrigatorios_total'],
            $r['falhas'],
            $r['avisos'],
        ));

        if ($relatorio['ok'] && (! $this->option('strict') || $r['avisos'] === 0)) {
            $this->info('Ambiente apto para ensaio Laguna.');

            return self::SUCCESS;
        }

        $this->error('Ambiente incompleto. Corrija falhas ou rode laguna:setup.');

        return self::FAILURE;
    }

    /** @param  array{ok:bool,resumo:array{avisos:int}}  $relatorio */
    private function codigoSaida(array $relatorio): int
    {
        if (! $relatorio['ok']) {
            return self::FAILURE;
        }
        if ($this->option('strict') && ($relatorio['resumo']['avisos'] ?? 0) > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
