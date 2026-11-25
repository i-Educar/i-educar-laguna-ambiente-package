<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Garante admin/professores com servidor + professor_turma para o diário sincronizar.
 */
final class LagunaIdiarioVinculoService
{
    /**
     * @return array{passo:string,ok:bool,saida:string}
     */
    public function executar(): array
    {
        try {
            $ano = (int) date('Y');
            $inst = (int) (DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->value('cod_instituicao') ?: 1);

            $alvos = $this->alvos();
            $criados = 0;
            $turmas = 0;

            foreach ($alvos as $alvo) {
                $idpes = (int) $alvo['idpes'];
                if ($idpes < 1) {
                    continue;
                }
                $this->garantirServidor($idpes, $inst);
                $criados++;

                $escola = (int) ($alvo['escola'] ?: $this->primeiraEscola());
                if ($escola < 1) {
                    continue;
                }

                $this->garantirAlocacao($idpes, $inst, $escola, $ano);

                foreach ($this->turmasDaEscola($escola, $ano, (int) ($alvo['limite_turmas'] ?? 2)) as $turmaId) {
                    if ($this->vincularProfessorTurma($idpes, $inst, $turmaId, $ano)) {
                        $turmas++;
                    }
                }
            }

            // Também vincula alguns servidores pedagógicos já povoados
            $extras = DB::table('pmieducar.servidor')->where('ativo', 1)->orderBy('cod_servidor')->limit(6)->pluck('cod_servidor');
            foreach ($extras as $sid) {
                $sid = (int) $sid;
                $escola = (int) (DB::table('pmieducar.servidor_alocacao')
                    ->where('ref_cod_servidor', $sid)
                    ->where('ativo', 1)
                    ->orderByDesc('ano')
                    ->value('ref_cod_escola') ?: $this->primeiraEscola());
                foreach ($this->turmasDaEscola($escola, $ano, 1) as $turmaId) {
                    if ($this->vincularProfessorTurma($sid, $inst, $turmaId, $ano)) {
                        $turmas++;
                    }
                }
            }

            $totalPt = (int) DB::table('modules.professor_turma')->where('ano', $ano)->count();

            return [
                'passo' => 'i-Diário · vínculos professor/turma',
                'ok' => true,
                'saida' => "servidores_ok={$criados} vinculos_novos={$turmas} professor_turma_ano={$totalPt}",
            ];
        } catch (\Throwable $e) {
            return [
                'passo' => 'i-Diário · vínculos professor/turma',
                'ok' => false,
                'saida' => $e->getMessage(),
            ];
        }
    }

    /** @return list<array{idpes:int,escola:?int,limite_turmas:int}> */
    private function alvos(): array
    {
        $out = [];
        // Admin legado + Admin Laguna SME + Professor Rede
        foreach ([1, 604, 2856] as $id) {
            $existe = DB::table('cadastro.pessoa')->where('idpes', $id)->exists();
            if (! $existe) {
                continue;
            }
            $escolaUsuario = null;
            if (Schema::hasTable('pmieducar.escola_usuario')) {
                $escolaUsuario = DB::table('pmieducar.escola_usuario')
                    ->where('ref_cod_usuario', $id)
                    ->orderBy('ref_cod_escola')
                    ->value('ref_cod_escola');
            }
            $out[] = [
                'idpes' => $id,
                'escola' => $escolaUsuario ? (int) $escolaUsuario : null,
                'limite_turmas' => $id === 2856 ? 3 : 2,
            ];
        }

        return $out;
    }

    private function garantirServidor(int $idpes, int $inst): void
    {
        DB::table('pmieducar.servidor')->updateOrInsert(
            ['cod_servidor' => $idpes],
            [
                'ref_cod_instituicao' => $inst,
                'carga_horaria' => 40,
                'data_cadastro' => now(),
                'ativo' => 1,
            ]
        );
    }

    private function garantirAlocacao(int $idpes, int $inst, int $escola, int $ano): void
    {
        if (! Schema::hasTable('pmieducar.servidor_alocacao')) {
            return;
        }
        $exists = DB::table('pmieducar.servidor_alocacao')
            ->where('ref_cod_servidor', $idpes)
            ->where('ref_cod_escola', $escola)
            ->where('ano', $ano)
            ->exists();
        if ($exists) {
            return;
        }
        try {
            DB::table('pmieducar.servidor_alocacao')->insert([
                'ref_ref_cod_instituicao' => $inst,
                'ref_cod_escola' => $escola,
                'ref_cod_servidor' => $idpes,
                'ref_usuario_cad' => 1,
                'carga_horaria' => 40,
                'periodo' => 3,
                'ano' => $ano,
                'data_admissao' => now()->toDateString(),
                'data_cadastro' => now(),
                'ativo' => 1,
            ]);
        } catch (\Throwable) {
            // schema varia
        }
    }

    /** @return list<int> */
    private function turmasDaEscola(int $escola, int $ano, int $limite): array
    {
        return DB::table('pmieducar.turma')
            ->where('ativo', 1)
            ->where('ano', $ano)
            ->where('ref_ref_cod_escola', $escola)
            ->whereNotNull('ref_cod_curso')
            ->orderBy('cod_turma')
            ->limit(max(1, $limite))
            ->pluck('cod_turma')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function primeiraEscola(): int
    {
        return (int) (DB::table('pmieducar.escola')->where('ativo', 1)->orderBy('cod_escola')->value('cod_escola') ?: 0);
    }

    private function vincularProfessorTurma(int $servidorId, int $inst, int $turmaId, int $ano): bool
    {
        if (! Schema::hasTable('modules.professor_turma')) {
            return false;
        }

        $existente = DB::table('modules.professor_turma')
            ->where('servidor_id', $servidorId)
            ->where('turma_id', $turmaId)
            ->where('ano', $ano)
            ->value('id');

        if ($existente) {
            $this->garantirDisciplinas((int) $existente, $turmaId);

            return false;
        }

        $id = (int) DB::table('modules.professor_turma')->insertGetId([
            'ano' => $ano,
            'instituicao_id' => $inst,
            'turma_id' => $turmaId,
            'servidor_id' => $servidorId,
            'funcao_exercida' => 1, // docente
            'tipo_vinculo' => 1,
            'permite_lancar_faltas_componente' => 1,
            'updated_at' => now(),
        ]);
        $this->garantirDisciplinas($id, $turmaId);

        return true;
    }

    private function garantirDisciplinas(int $professorTurmaId, int $turmaId): void
    {
        if (! Schema::hasTable('modules.professor_turma_disciplina')
            || ! Schema::hasTable('modules.componente_curricular_turma')) {
            return;
        }

        $comps = DB::table('modules.componente_curricular_turma')
            ->where('turma_id', $turmaId)
            ->orderBy('componente_curricular_id')
            ->limit(8)
            ->pluck('componente_curricular_id');

        foreach ($comps as $cid) {
            $cid = (int) $cid;
            $exists = DB::table('modules.professor_turma_disciplina')
                ->where('professor_turma_id', $professorTurmaId)
                ->where('componente_curricular_id', $cid)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('modules.professor_turma_disciplina')->insert([
                'professor_turma_id' => $professorTurmaId,
                'componente_curricular_id' => $cid,
            ]);
        }
    }
}
