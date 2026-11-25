<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Povoa a base mínima para ensaio completo Laguna (homologação).
 */
final class LagunaSetupService
{
    /**
     * @return list<array{passo:string,ok:bool,saida:string}>
     */
    public function executar(bool $comSetupMunicipal = true, bool $comMatriculas = true): array
    {
        $passos = [];

        if ($comSetupMunicipal) {
            $passos[] = $this->chamarSeExistir(
                'ieducar:setup',
                ['cidade-uf' => 'default-br', '--skip-auditoria' => true],
                'Setup municipal padrão (ieducar:setup default-br)',
            );
        }

        if ($comMatriculas) {
            $passos[] = $this->chamarSeExistir(
                'ieducar:matriculas',
                ['--force' => true],
                'Matrículas de teste (ieducar:matriculas)',
            );
        }

        $passos[] = $this->chamarSeExistir(
            'migrate',
            ['--force' => true],
            'Migrations (schemas dos pacotes liberados no tenant)',
        );

        $passos[] = $this->chamarSeExistir(
            'alimentacao:demo',
            [],
            'Demo alimentação (cardápio + restrições)',
        );

        $passos[] = $this->chamarSeExistir(
            'escolar:onda1-demo',
            [],
            'Demo escolar Onda 1',
        );

        $passos[] = $this->chamarSeExistir(
            'exportacao:demo-criticos',
            [],
            'Export relatórios críticos',
        );

        $passos[] = $this->chamarSeExistir(
            'transporte:passagem-empenho-demo',
            [],
            'Demo transporte passagem/empenho',
        );

        $passos[] = $this->garantirResumoRede();
        $passos[] = $this->garantirCardapioFallback();

        return $passos;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{passo:string,ok:bool,saida:string}
     */
    private function chamarSeExistir(string $comando, array $params, string $rotulo): array
    {
        if (! array_key_exists($comando, Artisan::all())) {
            return [
                'passo' => $rotulo,
                'ok' => false,
                'saida' => "comando {$comando} não registrado — pulado",
            ];
        }

        try {
            $code = Artisan::call($comando, $params);
            $out = trim(Artisan::output());

            return [
                'passo' => $rotulo,
                'ok' => $code === 0,
                'saida' => $out !== '' ? mb_substr($out, 0, 500) : "exit {$code}",
            ];
        } catch (\Throwable $e) {
            return [
                'passo' => $rotulo,
                'ok' => false,
                'saida' => $e->getMessage(),
            ];
        }
    }

    /** @return array{passo:string,ok:bool,saida:string} */
    private function garantirResumoRede(): array
    {
        try {
            $escolas = (int) DB::table('pmieducar.escola')->where('ativo', 1)->count();
            $turmas = (int) DB::table('pmieducar.turma')->where('ativo', 1)->count();
            $alunos = (int) DB::table('pmieducar.aluno')->where('ativo', 1)->count();
            $ok = $escolas >= 1 && $turmas >= 1;

            return [
                'passo' => 'Resumo rede núcleo',
                'ok' => $ok,
                'saida' => "escolas={$escolas} turmas={$turmas} alunos={$alunos}",
            ];
        } catch (\Throwable $e) {
            return [
                'passo' => 'Resumo rede núcleo',
                'ok' => false,
                'saida' => $e->getMessage(),
            ];
        }
    }

    /** @return array{passo:string,ok:bool,saida:string} */
    private function garantirCardapioFallback(): array
    {
        if (! Schema::hasTable('alimentacao.cardapio_dia')) {
            return [
                'passo' => 'Fallback cardápio',
                'ok' => false,
                'saida' => 'schema alimentacao ausente',
            ];
        }

        try {
            $data = date('Y-m-d');
            DB::table('alimentacao.cardapio_dia')->updateOrInsert(
                ['data' => $data, 'escola' => 'Rede'],
                [
                    'refeicoes' => json_encode([
                        ['refeicao' => 'cafe', 'itens' => ['pao', 'leite']],
                        ['refeicao' => 'almoco', 'itens' => ['arroz', 'feijao', 'fruta']],
                    ], JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            if (Schema::hasTable('alimentacao.restricao_aluno')) {
                DB::table('alimentacao.restricao_aluno')->updateOrInsert(
                    ['ref_cod_aluno' => 1, 'restricao' => 'leite'],
                    ['updated_at' => now(), 'created_at' => now()],
                );
            }

            return [
                'passo' => 'Fallback cardápio',
                'ok' => true,
                'saida' => "cardápio {$data} + restrição exemplo",
            ];
        } catch (\Throwable $e) {
            return [
                'passo' => 'Fallback cardápio',
                'ok' => false,
                'saida' => $e->getMessage(),
            ];
        }
    }
}
