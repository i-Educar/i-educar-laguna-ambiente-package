<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Setup completo Laguna: configurações (datas/censo) + cidade média + demos dos módulos.
 *
 * Ordem: setup municipal → migrate → configs → povoar rede → metadados Educacenso → demos.
 */
final class LagunaSetupService
{
    /**
     * @return list<array{passo:string,ok:bool,saida:string}>
     */
    public function executar(
        bool $comSetupMunicipal = true,
        bool $comMatriculas = true,
        bool $comPovoarCidade = true,
        int $alunosPorTurma = 12,
    ): array {
        $passos = [];

        if ($comSetupMunicipal) {
            $passos[] = $this->chamarSeExistir(
                'ieducar:setup',
                ['cidade-uf' => 'default-br', '--skip-auditoria' => true],
                'Setup municipal padrão (ieducar:setup default-br)',
            );
        }

        $passos[] = $this->chamarSeExistir(
            'migrate',
            ['--force' => true],
            'Migrations (schemas dos pacotes liberados no tenant)',
        );

        // Datas, Educacenso, settings, ano letivo e calendário — antes da carga pedagógica.
        foreach (app(LagunaConfiguracaoSistemaService::class)->executar() as $cfg) {
            $passos[] = $cfg;
        }

        if ($comPovoarCidade) {
            foreach (app(LagunaPovoarCidadeService::class)->executar(
                alunosPorTurma: $alunosPorTurma,
                comMatriculas: $comMatriculas,
            ) as $p) {
                $passos[] = $p;
            }
        } else {
            $passos[] = $this->chamarSeExistir(
                'db:seed',
                ['--class' => 'Database\\Seeders\\GerarTurmasSeeder', '--force' => true],
                'Gerar turmas (GerarTurmasSeeder)',
            );
            if ($comMatriculas) {
                $passos[] = $this->chamarSeExistir(
                    'ieducar:matriculas',
                    ['--force' => true, '--por-turma' => $alunosPorTurma],
                    "Matrículas de teste ({$alunosPorTurma}/turma)",
                );
            }
            $passos[] = $this->chamarSeExistir('alimentacao:demo', [], 'Demo alimentação (cardápio + restrições)');
            $passos[] = $this->chamarSeExistir('transporte:simula', [], 'Simulação completa do transporte');
        }

        // Metadados Educacenso / idades de referência após turmas existirem.
        $passos[] = $this->chamarSeExistir(
            'ieducar:idades-referencia',
            [],
            'Idades de referência + etapa Educacenso nas turmas',
        );
        $passos[] = $this->chamarSeExistir(
            'ieducar:cadastro-metadados',
            ['--sem-infantil' => true],
            'Metadados escolares (Educacenso / vínculos)',
        );

        // Demos extras (não cobertos pelo povoar-cidade).
        $passos[] = $this->chamarSeExistir('escolar:onda1-demo', [], 'Demo escolar Onda 1');
        $passos[] = $this->chamarSeExistir('exportacao:demo-criticos', [], 'Export relatórios críticos');
        $passos[] = $this->chamarSeExistir('transporte:passagem-empenho-demo', [], 'Demo transporte passagem/empenho');
        $passos[] = $this->chamarSeExistir('exportacao:demo', [], 'Demo exportação unificada (múltiplos formatos)');

        // Quando --skip-povoar, ainda assim carrega processos multi-etapa (se rede mínima existir).
        if (! $comPovoarCidade) {
            $passos[] = $this->chamarSeExistir('busca-ativa:demo', ['--faltas' => 20], 'Busca Ativa multi-etapa / atores');
            $passos[] = $this->chamarSeExistir('solicitacao:demo', [], 'Chamados multi-etapa / atores');
            $passos[] = $this->chamarSeExistir('atendimento:demo', [], 'Fila atendimento multi-situação');
            $passos[] = $this->chamarSeExistir('eventos:demo', [], 'Eventos, inscrições e certificados');
            $passos[] = $this->chamarSeExistir('biblioteca:demo', [], 'Acervo e empréstimos');
            $passos[] = $this->chamarSeExistir('almoxarifado:demo', [], 'Itens e pedidos do almoxarifado');
            $passos[] = $this->chamarSeExistir('horario:demo', [], 'Quadro de horários demo');
            $passos[] = $this->chamarSeExistir('act:demo', [], 'Processo ACT com vagas/inscrições');
        }

        $passos[] = $this->garantirIdentidadeLaguna();
        $passos[] = $this->garantirCardapioFallback();
        $passos[] = $this->garantirResumoRede($comPovoarCidade);
        $passos[] = app(LagunaIdiarioVinculoService::class)->executar();

        return $passos;
    }

    /** @return array{passo:string,ok:bool,saida:string} */
    private function garantirIdentidadeLaguna(): array
    {
        try {
            $n = DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->update([
                'nm_instituicao' => 'Prefeitura Municipal de Laguna',
                'cidade' => 'Laguna',
                'ref_sigla_uf' => 'SC',
            ]);

            DB::table('pmieducar.configuracoes_gerais')->update([
                'ieducar_entity_name' => 'Secretaria Municipal de Educação de Laguna',
                'ieducar_image' => '/intranet/imagens/logo-laguna.png',
                'ieducar_internal_footer' => '<p>Prefeitura Municipal de Laguna — ambiente de demonstração.</p>',
                'ieducar_login_footer' => '<p>Prefeitura Municipal de Laguna / SC — SME · PoC PE-77/2026</p>',
            ]);

            if (Schema::hasTable('settings')) {
                DB::table('settings')->where('key', 'legacy.app.template.vars.instituicao')
                    ->update(['value' => 'Prefeitura Municipal de Laguna']);
                DB::table('settings')->where('key', 'legacy.app.entity.name')
                    ->update(['value' => 'SME Laguna']);
            }

            if (class_exists(\App\Services\CacheService::class)) {
                \App\Services\CacheService::clearSettings();
                \App\Services\CacheService::clearGeneralConfiguration();
            }

            return [
                'passo' => 'Identidade Laguna/SC',
                'ok' => true,
                'saida' => "instituição + login/footer atualizados ({$n} linha(s)) · IBGE 4209102",
            ];
        } catch (\Throwable $e) {
            return [
                'passo' => 'Identidade Laguna/SC',
                'ok' => false,
                'saida' => $e->getMessage(),
            ];
        }
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
    private function garantirResumoRede(bool $cidadeMedia): array
    {
        try {
            $escolas = (int) DB::table('pmieducar.escola')->where('ativo', 1)->count();
            $turmas = (int) DB::table('pmieducar.turma')->where('ativo', 1)->count();
            $alunos = (int) DB::table('pmieducar.aluno')->where('ativo', 1)->count();
            $servidores = (int) DB::table('pmieducar.servidor')->where('ativo', 1)->count();
            $usuarios = (int) DB::table('pmieducar.usuario')->where('ativo', 1)->count();
            $minEscolas = $cidadeMedia ? 10 : 1;
            $ok = $escolas >= $minEscolas && $turmas >= 1;

            return [
                'passo' => 'Resumo rede núcleo',
                'ok' => $ok,
                'saida' => "escolas={$escolas} turmas={$turmas} alunos={$alunos} servidores={$servidores} usuarios={$usuarios}"
                    .($cidadeMedia ? ' · gate cidade média (≥10 escolas)' : ''),
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
