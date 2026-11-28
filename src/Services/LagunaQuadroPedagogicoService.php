<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Ajusta matriz (CCT ← ESD) e aloca professores nas turmas com alunos
 * para o quadro de horários da rede (padrão pedagógico SC / municipal).
 *
 * - Educação Infantil e Anos Iniciais (1º–5º): regente polivalente +
 *   especialistas em Arte, Ed. Física, Inglês e Ensino Religioso.
 * - Anos Finais (6º–9º): distribuição dos componentes entre docentes da escola.
 * - AEE: fora do quadro automático (só registra skip).
 */
final class LagunaQuadroPedagogicoService
{
    /** Componentes tipicamente de especialista nos anos iniciais (BNCC / rede SC). */
    private const ESPECIALISTAS = [
        'arte',
        'educação física',
        'educacao fisica',
        'inglês',
        'ingles',
        'língua inglesa',
        'lingua inglesa',
        'ensino religioso',
    ];

    /**
     * @return array{
     *   ano:int,
     *   dry_run:bool,
     *   turmas_alvo:int,
     *   matriz_sincronizada:int,
     *   regentes:int,
     *   vinculos_pt:int,
     *   disciplinas_pt:int,
     *   aee_ignoradas:int,
     *   escolas:int,
     *   avisos:list<string>
     * }
     */
    public function executar(int $ano = 0, bool $dryRun = false): array
    {
        $ano = $ano > 0 ? $ano : (int) date('Y');
        $inst = (int) (DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->value('cod_instituicao') ?: 1);

        $stats = [
            'ano' => $ano,
            'dry_run' => $dryRun,
            'turmas_alvo' => 0,
            'matriz_sincronizada' => 0,
            'regentes' => 0,
            'vinculos_pt' => 0,
            'disciplinas_pt' => 0,
            'aee_ignoradas' => 0,
            'escolas' => 0,
            'avisos' => [],
        ];

        if (! Schema::hasTable('pmieducar.turma') || ! Schema::hasTable('modules.professor_turma')) {
            $stats['avisos'][] = 'Tabelas turma/professor_turma ausentes.';

            return $stats;
        }

        $turmas = $this->turmasComAlunos($ano);
        $porEscola = [];
        foreach ($turmas as $t) {
            $porEscola[(int) $t->escola_id][] = $t;
        }
        $stats['escolas'] = count($porEscola);
        $stats['turmas_alvo'] = count($turmas);

        foreach ($porEscola as $escolaId => $lista) {
            $professores = $this->professoresDaEscola($escolaId, $ano);
            if ($professores === []) {
                $stats['avisos'][] = "Escola {$escolaId}: sem servidor_alocacao ativa — turmas sem professor.";

                continue;
            }

            $cursorEsp = 0;
            foreach ($lista as $turma) {
                if ($this->ehAee((string) $turma->nm_serie, (string) $turma->nm_curso, (string) $turma->nm_turma)) {
                    $stats['aee_ignoradas']++;

                    continue;
                }

                $sync = $this->sincronizarMatrizTurma((int) $turma->cod_turma, (int) $turma->serie_id, $escolaId, $dryRun);
                $stats['matriz_sincronizada'] += $sync;

                $componentes = $this->componentesDaTurma((int) $turma->cod_turma);
                if ($componentes === []) {
                    $stats['avisos'][] = "Turma {$turma->cod_turma} ({$turma->nm_turma}): sem matriz após sync.";

                    continue;
                }

                $perfil = $this->perfilSerie((string) $turma->nm_serie);
                $regenteId = (int) ($turma->ref_cod_regente ?: 0);
                if ($regenteId < 1 || ! in_array($regenteId, $professores, true)) {
                    $regenteId = $professores[array_rand(array_flip($professores))] ?? $professores[0];
                    if (! $dryRun) {
                        DB::table('pmieducar.turma')->where('cod_turma', (int) $turma->cod_turma)->update([
                            'ref_cod_regente' => $regenteId,
                        ]);
                    }
                    $stats['regentes']++;
                }

                $mapa = $this->mapearProfessorPorComponente(
                    $componentes,
                    $professores,
                    $regenteId,
                    $perfil,
                    $cursorEsp
                );

                foreach ($mapa as $servidorId => $compIds) {
                    $r = $this->garantirProfessorTurma(
                        $servidorId,
                        $inst,
                        (int) $turma->cod_turma,
                        $ano,
                        $compIds,
                        $dryRun
                    );
                    $stats['vinculos_pt'] += $r['vinculo_novo'] ? 1 : 0;
                    $stats['disciplinas_pt'] += $r['disciplinas'];
                }
            }
        }

        return $stats;
    }

    /**
     * @return list<object{
     *   cod_turma:int|string,
     *   nm_turma:string,
     *   escola_id:int|string,
     *   serie_id:int|string,
     *   nm_serie:string,
     *   nm_curso:string,
     *   ref_cod_regente:int|string|null
     * }>
     */
    private function turmasComAlunos(int $ano): array
    {
        return DB::select(
            'SELECT DISTINCT t.cod_turma, t.nm_turma, t.ref_ref_cod_escola AS escola_id,
                    t.ref_ref_cod_serie AS serie_id, t.ref_cod_regente,
                    COALESCE(s.nm_serie, \'\') AS nm_serie,
                    COALESCE(c.nm_curso, \'\') AS nm_curso
             FROM pmieducar.turma t
             INNER JOIN pmieducar.matricula_turma mt
                ON mt.ref_cod_turma = t.cod_turma AND mt.ativo = 1
             LEFT JOIN pmieducar.serie s ON s.cod_serie = t.ref_ref_cod_serie
             LEFT JOIN pmieducar.curso c ON c.cod_curso = t.ref_cod_curso
             WHERE t.ano = ? AND t.ativo = 1
             ORDER BY t.ref_ref_cod_escola, t.ref_ref_cod_serie, t.cod_turma',
            [$ano]
        );
    }

    /** @return list<int> */
    private function professoresDaEscola(int $escolaId, int $ano): array
    {
        if (! Schema::hasTable('pmieducar.servidor_alocacao')) {
            return [];
        }

        return DB::table('pmieducar.servidor_alocacao as sa')
            ->join('pmieducar.servidor as s', 's.cod_servidor', '=', 'sa.ref_cod_servidor')
            ->where('sa.ref_cod_escola', $escolaId)
            ->where('sa.ano', $ano)
            ->where('sa.ativo', 1)
            ->where('s.ativo', 1)
            ->orderBy('sa.ref_cod_servidor')
            ->pluck('sa.ref_cod_servidor')
            ->map(static fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    private function sincronizarMatrizTurma(int $turmaId, int $serieId, int $escolaId, bool $dryRun): int
    {
        if (! Schema::hasTable('modules.componente_curricular_turma')
            || ! Schema::hasTable('pmieducar.escola_serie_disciplina')) {
            return 0;
        }

        $ja = (int) DB::table('modules.componente_curricular_turma')->where('turma_id', $turmaId)->count();
        if ($ja > 0) {
            return 0;
        }

        $esd = DB::table('pmieducar.escola_serie_disciplina')
            ->where('ref_ref_cod_serie', $serieId)
            ->where('ref_ref_cod_escola', $escolaId)
            ->where('ativo', 1)
            ->get(['ref_cod_disciplina', 'carga_horaria']);

        if ($esd->isEmpty()) {
            // Copia de outra escola com a mesma série (rede homogênea Laguna/SC).
            $esd = DB::table('pmieducar.escola_serie_disciplina')
                ->where('ref_ref_cod_serie', $serieId)
                ->where('ativo', 1)
                ->orderBy('ref_ref_cod_escola')
                ->limit(20)
                ->get(['ref_cod_disciplina', 'carga_horaria']);
        }

        $n = 0;
        foreach ($esd as $row) {
            $cid = (int) $row->ref_cod_disciplina;
            if ($cid < 1) {
                continue;
            }
            if ($dryRun) {
                $n++;

                continue;
            }
            try {
                $exists = DB::table('modules.componente_curricular_turma')
                    ->where('turma_id', $turmaId)
                    ->where('componente_curricular_id', $cid)
                    ->exists();
                if ($exists) {
                    continue;
                }
                $payload = [
                    'ano_escolar_id' => $serieId,
                    'componente_curricular_id' => $cid,
                    'turma_id' => $turmaId,
                ];
                if (Schema::hasColumn('modules.componente_curricular_turma', 'carga_horaria')) {
                    $payload['carga_horaria'] = $row->carga_horaria;
                }
                DB::table('modules.componente_curricular_turma')->insert($payload);
                $n++;
            } catch (Throwable) {
                // unique / schema
            }
        }

        return $n;
    }

    /**
     * @return list<array{id:int,nome:string}>
     */
    private function componentesDaTurma(int $turmaId): array
    {
        if (! Schema::hasTable('modules.componente_curricular_turma')) {
            return [];
        }

        return DB::table('modules.componente_curricular_turma as cct')
            ->join('modules.componente_curricular as cc', 'cc.id', '=', 'cct.componente_curricular_id')
            ->where('cct.turma_id', $turmaId)
            ->orderBy('cc.nome')
            ->get(['cc.id', 'cc.nome'])
            ->map(static fn ($r): array => ['id' => (int) $r->id, 'nome' => (string) $r->nome])
            ->all();
    }

    /**
     * @param  list<array{id:int,nome:string}>  $componentes
     * @param  list<int>  $professores
     * @return array<int, list<int>> servidor_id => componente_ids
     */
    private function mapearProfessorPorComponente(
        array $componentes,
        array $professores,
        int $regenteId,
        string $perfil,
        int &$cursorEsp
    ): array {
        $mapa = [];
        $outros = array_values(array_filter($professores, static fn (int $id): bool => $id !== $regenteId));
        if ($outros === []) {
            $outros = $professores;
        }

        foreach ($componentes as $comp) {
            $cid = (int) $comp['id'];
            $nome = mb_strtolower(trim((string) $comp['nome']));
            $ehEsp = $this->ehComponenteEspecialista($nome);

            if ($perfil === 'finais') {
                // Anos finais: um docente por componente, round-robin na escola.
                $docente = $professores[$cursorEsp % count($professores)];
                $cursorEsp++;
            } elseif ($ehEsp && count($outros) > 0) {
                $docente = $outros[$cursorEsp % count($outros)];
                $cursorEsp++;
            } else {
                // EI / anos iniciais: regente polivalente.
                $docente = $regenteId;
            }

            $mapa[$docente][] = $cid;
        }

        return $mapa;
    }

    /**
     * @param  list<int>  $compIds
     * @return array{vinculo_novo:bool,disciplinas:int}
     */
    private function garantirProfessorTurma(
        int $servidorId,
        int $inst,
        int $turmaId,
        int $ano,
        array $compIds,
        bool $dryRun
    ): array {
        $compIds = array_values(array_unique(array_filter($compIds, static fn ($v) => (int) $v > 0)));
        if ($servidorId < 1 || $turmaId < 1 || $compIds === []) {
            return ['vinculo_novo' => false, 'disciplinas' => 0];
        }

        if ($dryRun) {
            return ['vinculo_novo' => true, 'disciplinas' => count($compIds)];
        }

        $existente = DB::table('modules.professor_turma')
            ->where('servidor_id', $servidorId)
            ->where('turma_id', $turmaId)
            ->where('ano', $ano)
            ->value('id');

        $novo = false;
        if ($existente) {
            $ptId = (int) $existente;
        } else {
            $payload = [
                'ano' => $ano,
                'instituicao_id' => $inst,
                'turma_id' => $turmaId,
                'servidor_id' => $servidorId,
                'funcao_exercida' => 1,
                'tipo_vinculo' => 1,
                'permite_lancar_faltas_componente' => 1,
            ];
            if (Schema::hasColumn('modules.professor_turma', 'updated_at')) {
                $payload['updated_at'] = now();
            }
            $ptId = (int) DB::table('modules.professor_turma')->insertGetId($payload);
            $novo = true;
        }

        $disc = 0;
        if (Schema::hasTable('modules.professor_turma_disciplina')) {
            foreach ($compIds as $cid) {
                $exists = DB::table('modules.professor_turma_disciplina')
                    ->where('professor_turma_id', $ptId)
                    ->where('componente_curricular_id', $cid)
                    ->exists();
                if ($exists) {
                    continue;
                }
                DB::table('modules.professor_turma_disciplina')->insert([
                    'professor_turma_id' => $ptId,
                    'componente_curricular_id' => $cid,
                ]);
                $disc++;
            }
        }

        return ['vinculo_novo' => $novo, 'disciplinas' => $disc];
    }

    private function perfilSerie(string $nmSerie): string
    {
        $s = mb_strtolower(trim($nmSerie));
        if (preg_match('/\b(6|7|8|9)[ºo°]?\s*ano\b/u', $s) || str_contains($s, 'anos finais')) {
            return 'finais';
        }
        if (preg_match('/berç|berc|maternal|pré|pre-?escolar|infantil/u', $s)) {
            return 'infantil';
        }

        return 'iniciais';
    }

    private function ehComponenteEspecialista(string $nomeLower): bool
    {
        foreach (self::ESPECIALISTAS as $esp) {
            if ($nomeLower === $esp || str_contains($nomeLower, $esp)) {
                return true;
            }
        }

        return false;
    }

    private function ehAee(string $serie, string $curso, string $turma): bool
    {
        $blob = mb_strtolower($serie.' '.$curso.' '.$turma);

        return str_contains($blob, 'aee')
            || str_contains($blob, 'atendimento educacional especial')
            || str_contains($blob, 'educação especial')
            || str_contains($blob, 'educacao especial');
    }
}
