<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Garante datas do ano letivo, parâmetros Educacenso, settings e calendário
 * para o ambiente de demonstração Laguna (PE-77/2026).
 */
final class LagunaConfiguracaoSistemaService
{
    private const USUARIO_CAD = 1;

    private const IBGE = '4209102';

    private const CEP = 88790000;

    /**
     * @return list<array{passo:string,ok:bool,saida:string}>
     */
    public function executar(?int $ano = null): array
    {
        $ano = $ano ?: $this->anoVigente();
        $passos = [];

        $passos[] = $this->passo('Instituição · datas e regras', fn () => $this->garantirInstituicao($ano));
        $passos[] = $this->passo('Settings · locale/censo/timezone', fn () => $this->garantirSettings());
        $passos[] = $this->passo('Ano letivo em andamento', fn () => $this->garantirAnoLetivo($ano));
        $passos[] = $this->passo('Calendário escolar', fn () => $this->garantirCalendarios($ano));

        return $passos;
    }

    private function anoVigente(): int
    {
        $env = (int) (getenv('IEDUCAR_ANO_VIGENTE') ?: 0);
        if ($env > 2000) {
            return $env;
        }

        return (int) (DB::table('pmieducar.escola_ano_letivo')
            ->where('andamento', 1)
            ->orderByDesc('ano')
            ->value('ano') ?: date('Y'));
    }

    /** @return array{passo:string,ok:bool,saida:string} */
    private function passo(string $rotulo, callable $fn): array
    {
        try {
            return ['passo' => $rotulo, 'ok' => true, 'saida' => (string) $fn()];
        } catch (\Throwable $e) {
            return ['passo' => $rotulo, 'ok' => false, 'saida' => $e->getMessage()];
        }
    }

    private function garantirInstituicao(int $ano): string
    {
        // Datas típicas do calendário escolar municipal (1º semestre + Educacenso maio).
        $dataEducacenso = sprintf('%d-05-27', $ano); // 4ª quarta de maio (referência PoC)
        $dataMatricula = sprintf('%d-03-31', $ano);
        $dataRemanejamento = sprintf('%d-04-30', $ano);
        $dataTransferencia = sprintf('%d-12-20', $ano);
        $dataFechamento = sprintf('%d-12-20', $ano);

        $payload = [
            'nm_instituicao' => 'Prefeitura Municipal de Laguna',
            'cidade' => 'Laguna',
            'ref_sigla_uf' => 'SC',
            'cep' => self::CEP,
            'bairro' => 'Centro',
            'logradouro' => 'Praça República Julião Fernandes',
            'numero' => 1,
            'nm_responsavel' => 'Secretaria Municipal de Educação de Laguna',
            'data_base_matricula' => $dataMatricula,
            'data_base_remanejamento' => $dataRemanejamento,
            'data_base_transferencia' => $dataTransferencia,
            'data_fechamento' => $dataFechamento,
            'data_educacenso' => $dataEducacenso,
            'obrigar_campos_censo' => true,
            'obrigar_documento_pessoa' => true,
            'exigir_vinculo_turma_professor' => 1,
            'permitir_carga_horaria' => true,
            'exigir_dados_socioeconomicos' => true,
            'bloquear_vinculo_professor_sem_alocacao_escola' => true,
            'permitir_matricula_fora_periodo_letivo' => false,
            'ativo' => 1,
        ];

        $n = DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->update($payload);

        return "atualizadas={$n} · ano={$ano} · Educacenso={$dataEducacenso} · IBGE ".self::IBGE
            ." · matrícula até {$dataMatricula} · fechamento {$dataFechamento}";
    }

    private function garantirSettings(): string
    {
        $pares = [
            'legacy.app.locale.country' => '45',
            'legacy.app.locale.province' => 'SC',
            'legacy.app.locale.timezone' => 'America/Sao_Paulo',
            'legacy.educacenso.enable_export' => '1',
            'legacy.app.template.vars.instituicao' => 'Prefeitura Municipal de Laguna',
            'legacy.app.entity.name' => 'SME Laguna',
        ];

        if (! Schema::hasTable('settings')) {
            return 'tabela settings ausente — pulado';
        }

        $n = 0;
        foreach ($pares as $key => $value) {
            $row = DB::table('settings')->where('key', $key)->first();
            if ($row) {
                DB::table('settings')->where('key', $key)->update([
                    'value' => $value,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'type' => 'string',
                    'description' => 'Laguna PoC',
                    'setting_category_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $n++;
        }

        DB::table('pmieducar.configuracoes_gerais')->update([
            'ieducar_entity_name' => 'Secretaria Municipal de Educação de Laguna',
            'ieducar_image' => '/intranet/imagens/logo-laguna.png',
            'ieducar_internal_footer' => '<p>Prefeitura Municipal de Laguna — ambiente de demonstração.</p>',
            'ieducar_login_footer' => '<p>Prefeitura Municipal de Laguna / SC — SME · PoC PE-77/2026</p>',
        ]);

        if (class_exists(\App\Services\CacheService::class)) {
            \App\Services\CacheService::clearSettings();
            \App\Services\CacheService::clearGeneralConfiguration();
        }

        return "{$n} settings · UF=SC · timezone=America/Sao_Paulo · educacenso.export=1";
    }

    private function garantirAnoLetivo(int $ano): string
    {
        $escolas = DB::table('pmieducar.escola')->where('ativo', 1)->pluck('cod_escola');
        $n = 0;

        foreach ($escolas as $codEscola) {
            DB::table('pmieducar.escola_ano_letivo')->updateOrInsert(
                ['ref_cod_escola' => (int) $codEscola, 'ano' => $ano],
                [
                    'ref_usuario_cad' => self::USUARIO_CAD,
                    'andamento' => 1,
                    'ativo' => 1,
                    'copia_dados_professor' => false,
                    'copia_dados_demais_servidores' => false,
                    'copia_turmas' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $n++;
        }

        // Encerra anos anteriores ainda marcados como "em andamento".
        DB::table('pmieducar.escola_ano_letivo')
            ->where('ano', '<', $ano)
            ->where('andamento', 1)
            ->update(['andamento' => 2, 'updated_at' => now()]);

        return "escolas com ano {$ano} em andamento={$n}";
    }

    private function garantirCalendarios(int $ano): string
    {
        $escolas = DB::table('pmieducar.escola')->where('ativo', 1)->pluck('cod_escola');
        $modelo = (int) DB::table('pmieducar.calendario_ano_letivo')
            ->where('ano', $ano)
            ->where('ativo', 1)
            ->orderBy('cod_calendario_ano_letivo')
            ->value('cod_calendario_ano_letivo');

        $diasModelo = $modelo
            ? DB::table('pmieducar.calendario_dia')
                ->where('ref_cod_calendario_ano_letivo', $modelo)
                ->where('ativo', 1)
                ->get()
            : collect();

        $criados = 0;
        $diasCopiados = 0;

        foreach ($escolas as $codEscola) {
            $codEscola = (int) $codEscola;
            $cal = DB::table('pmieducar.calendario_ano_letivo')
                ->where('ref_cod_escola', $codEscola)
                ->where('ano', $ano)
                ->where('ativo', 1)
                ->first();

            if (! $cal) {
                $id = (int) DB::table('pmieducar.calendario_ano_letivo')->insertGetId([
                    'ref_cod_escola' => $codEscola,
                    'ref_usuario_cad' => self::USUARIO_CAD,
                    'ano' => $ano,
                    'data_cadastra' => now(),
                    'ativo' => 1,
                ], 'cod_calendario_ano_letivo');
                $criados++;
            } else {
                $id = (int) $cal->cod_calendario_ano_letivo;
            }

            if ($diasModelo->isEmpty() || $id === $modelo) {
                continue;
            }

            $jaTem = DB::table('pmieducar.calendario_dia')
                ->where('ref_cod_calendario_ano_letivo', $id)
                ->where('ativo', 1)
                ->exists();
            if ($jaTem) {
                continue;
            }

            foreach ($diasModelo as $d) {
                DB::table('pmieducar.calendario_dia')->insert([
                    'ref_cod_calendario_ano_letivo' => $id,
                    'mes' => $d->mes,
                    'dia' => $d->dia,
                    'ref_usuario_cad' => self::USUARIO_CAD,
                    'ref_cod_calendario_dia_motivo' => $d->ref_cod_calendario_dia_motivo,
                    'data_cadastro' => now(),
                    'ativo' => 1,
                    'descricao' => $d->descricao,
                ]);
                $diasCopiados++;
            }
        }

        // Se o modelo não tinha dias, gera feriados/recesso básicos no primeiro calendário 2026.
        if ($diasModelo->isEmpty()) {
            $diasCopiados += $this->semearDiasBasicos($ano);
        }

        return "calendários criados={$criados} · dias copiados/semeados={$diasCopiados} · ano={$ano}";
    }

    private function semearDiasBasicos(int $ano): int
    {
        $calIds = DB::table('pmieducar.calendario_ano_letivo')
            ->where('ano', $ano)
            ->where('ativo', 1)
            ->pluck('cod_calendario_ano_letivo');

        // Feriados nacionais + recesso típico (motivo null = dia letivo especial/descricao).
        $marcos = [
            [1, 1, 'Confraternização Universal'],
            [4, 21, 'Tiradentes'],
            [5, 1, 'Dia do Trabalho'],
            [9, 7, 'Independência do Brasil'],
            [10, 12, 'Nossa Senhora Aparecida'],
            [11, 2, 'Finados'],
            [11, 15, 'Proclamação da República'],
            [12, 25, 'Natal'],
        ];

        $n = 0;
        foreach ($calIds as $calId) {
            $ja = DB::table('pmieducar.calendario_dia')
                ->where('ref_cod_calendario_ano_letivo', (int) $calId)
                ->where('ativo', 1)
                ->exists();
            if ($ja) {
                continue;
            }
            foreach ($marcos as [$mes, $dia, $desc]) {
                DB::table('pmieducar.calendario_dia')->insert([
                    'ref_cod_calendario_ano_letivo' => (int) $calId,
                    'mes' => $mes,
                    'dia' => $dia,
                    'ref_usuario_cad' => self::USUARIO_CAD,
                    'ref_cod_calendario_dia_motivo' => null,
                    'data_cadastro' => now(),
                    'ativo' => 1,
                    'descricao' => $desc,
                ]);
                $n++;
            }
        }

        return $n;
    }
}
