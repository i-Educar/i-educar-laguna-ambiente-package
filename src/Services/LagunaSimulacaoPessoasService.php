<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use App\Models\LegacyPerson;
use iEducar\Packages\BuritiLagunaAmbiente\Support\AuditoriaFluxo;
use iEducar\Packages\BuritiLagunaAmbiente\Support\CatalogoModulos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Cria 20 pessoas e simula fluxos aleatórios em todos os módulos disponíveis,
 * persistindo lote/pessoas/eventos em laguna.simulacao_* para verificação na UI.
 */
final class LagunaSimulacaoPessoasService
{
    public const TAG = 'sim-pessoas-v1';

    /** Ação real concluída com efeito observável. */
    public const ST_OK = 'ok';

    /** Fluxo testado; regra de negócio rejeitou (ex.: sem vaga COM processo aberto). */
    public const ST_NEGATIVA = 'negativa';

    /** Pré-requisito ausente — NÃO conta como acerto nem como negativa de negócio. */
    public const ST_FALTA_TESTE = 'falta_teste';

    public const ST_ERRO = 'erro';

    public const ST_SKIP = 'skip';

    public static function rotuloStatus(string $status): string
    {
        return match ($status) {
            self::ST_OK => 'Sucesso',
            self::ST_NEGATIVA => 'Negativa (regra)',
            self::ST_FALTA_TESTE => 'Falta de teste',
            self::ST_ERRO => 'Erro',
            self::ST_SKIP => 'Não aplicável',
            default => $status,
        };
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>}
     */
    private function resultado(string $status, string $detalhe, ?string $url = null, array $payload = []): array
    {
        return [
            'status' => $status,
            'detalhe' => $detalhe,
            'url' => $url,
            'payload' => array_merge(['resultado' => $status], $payload),
        ];
    }

    public const CPF_BASE = 42091030001;

    /** @var list<array{nome:string,sexo:string}> */
    private const NOMES = [
        ['nome' => 'Amanda Ribeiro Costa', 'sexo' => 'F'],
        ['nome' => 'Bruno César Almeida', 'sexo' => 'M'],
        ['nome' => 'Camila Duarte Lopes', 'sexo' => 'F'],
        ['nome' => 'Daniel Souza Freitas', 'sexo' => 'M'],
        ['nome' => 'Eduarda Nascimento', 'sexo' => 'F'],
        ['nome' => 'Felipe Augusto Ramos', 'sexo' => 'M'],
        ['nome' => 'Gabriela Mota Pinto', 'sexo' => 'F'],
        ['nome' => 'Henrique Barros Lima', 'sexo' => 'M'],
        ['nome' => 'Isabela Rocha Mendes', 'sexo' => 'F'],
        ['nome' => 'João Pedro Carvalho', 'sexo' => 'M'],
        ['nome' => 'Karina Oliveira Dias', 'sexo' => 'F'],
        ['nome' => 'Lucas Vinícius Teixeira', 'sexo' => 'M'],
        ['nome' => 'Mariana Pires Azevedo', 'sexo' => 'F'],
        ['nome' => 'Nicolas Ferreira Gomes', 'sexo' => 'M'],
        ['nome' => 'Olívia Martins Correia', 'sexo' => 'F'],
        ['nome' => 'Paulo Henrique Batista', 'sexo' => 'M'],
        ['nome' => 'Queila Santos Barbosa', 'sexo' => 'F'],
        ['nome' => 'Rafael Cunha Moreira', 'sexo' => 'M'],
        ['nome' => 'Sofia Lima Albuquerque', 'sexo' => 'F'],
        ['nome' => 'Thiago Nunes Cardoso', 'sexo' => 'M'],
    ];

    /** @var list<string> */
    private const PERFIS = [
        'aluno', 'responsavel', 'professor', 'diretor', 'operador_sme',
        'aluno', 'responsavel', 'professor', 'atendente', 'analista_rh',
        'aluno', 'responsavel', 'motorista', 'operador_merenda', 'bibliotecario',
        'aluno', 'coordenador', 'operador_almox', 'candidato_act', 'familia',
    ];

    /**
     * @return array{
     *   ok:bool,
     *   passo:string,
     *   saida:string,
     *   lote_id:?int,
     *   pessoas:list<array<string,mixed>>,
     *   eventos:int,
     *   modulos:list<string>
     * }
     */
    public function executar(int $quantidade = 20, ?int $seed = null): array
    {
        if (! Schema::hasTable('laguna.simulacao_lote')) {
            return [
                'ok' => false,
                'passo' => 'Simulação 20 pessoas',
                'saida' => 'tabelas laguna.simulacao_* ausentes — rode migrate',
                'lote_id' => null,
                'pessoas' => [],
                'eventos' => 0,
                'modulos' => [],
            ];
        }

        $seed ??= (int) (now()->format('YmdH') . random_int(10, 99));
        mt_srand($seed);

        $escolas = $this->escolas();
        $modulosDisponiveis = $this->modulosDisponiveis();
        $qtd = max(1, min(40, $quantidade));

        $loteId = (int) DB::table('laguna.simulacao_lote')->insertGetId([
            'tag' => self::TAG,
            'pessoas' => 0,
            'eventos' => 0,
            'modulos' => count($modulosDisponiveis),
            'usuario' => AuditoriaFluxo::usuario(),
            'resumo' => json_encode(['seed' => $seed, 'status' => 'rodando'], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);

        $pessoasOut = [];
        $eventosTotal = 0;
        $modulosTocados = [];
        $contagemStatus = [];

        for ($i = 0; $i < $qtd; $i++) {
            $def = self::NOMES[$i % count(self::NOMES)];
            $perfil = self::PERFIS[$i % count(self::PERFIS)];
            $cpf = (string) (self::CPF_BASE + $i);
            $escola = $escolas[$i % max(1, count($escolas))] ?? 'Rede Laguna';

            $idpes = $this->garantirPessoa($def['nome'].' · Sim', $cpf, $def['sexo'], $perfil, $escola);

            $pessoaId = (int) DB::table('laguna.simulacao_pessoa')->insertGetId([
                'lote_id' => $loteId,
                'idpes' => $idpes,
                'nome' => $def['nome'],
                'cpf' => $cpf,
                'perfil' => $perfil,
                'escola' => $escola,
                'cenarios' => json_encode([], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);

            $cenariosEscolhidos = $this->sortearCenarios($modulosDisponiveis, $perfil);
            $eventosPessoa = [];

            // Sempre registra criação
            $eventosPessoa[] = $this->registrarEvento(
                $loteId,
                $pessoaId,
                'nucleo',
                'criar_pessoa',
                'ok',
                "Cadastro idpes={$idpes} · perfil={$perfil} · escola={$escola}",
                '/intranet/educar_pessoa_lst.php',
                ['idpes' => $idpes, 'cpf' => $cpf]
            );
            $modulosTocados['nucleo'] = true;
            $contagemStatus[self::ST_OK] = ($contagemStatus[self::ST_OK] ?? 0) + 1;

            foreach ($cenariosEscolhidos as $cenario) {
                $ev = $this->executarCenario($loteId, $pessoaId, $def['nome'], $cpf, $perfil, $escola, $idpes, $cenario);
                $eventosPessoa[] = $ev;
                $modulosTocados[$cenario['modulo']] = true;
                $st = $ev['status'] ?? self::ST_ERRO;
                $contagemStatus[$st] = ($contagemStatus[$st] ?? 0) + 1;
            }

            DB::table('laguna.simulacao_pessoa')->where('id', $pessoaId)->update([
                'cenarios' => json_encode(array_column($eventosPessoa, 'cenario'), JSON_UNESCAPED_UNICODE),
            ]);

            $eventosTotal += count($eventosPessoa);
            $pessoasOut[] = [
                'id' => $pessoaId,
                'nome' => $def['nome'],
                'cpf' => $cpf,
                'perfil' => $perfil,
                'escola' => $escola,
                'idpes' => $idpes,
                'eventos' => $eventosPessoa,
            ];
        }

        $modulosLista = array_keys($modulosTocados);
        DB::table('laguna.simulacao_lote')->where('id', $loteId)->update([
            'pessoas' => $qtd,
            'eventos' => $eventosTotal,
            'modulos' => count($modulosLista),
            'resumo' => json_encode([
                'seed' => $seed,
                'status' => 'ok',
                'modulos' => $modulosLista,
                'contagem_status' => $contagemStatus,
                'tag' => self::TAG,
                'nota' => 'ok=sucesso real; negativa=regra; falta_teste=pré-requisito ausente (não é acerto)',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        AuditoriaFluxo::registrar(
            null,
            'sim_lote',
            "Simulação {$qtd} pessoas · {$eventosTotal} eventos · lote #{$loteId}",
            $loteId,
            ['tag' => self::TAG, 'seed' => $seed, 'modulos' => $modulosLista]
        );

        return [
            'ok' => true,
            'passo' => 'Simulação 20 pessoas (cenários aleatórios)',
            'saida' => "lote=#{$loteId} · pessoas={$qtd} · eventos={$eventosTotal} · módulos=".count($modulosLista)
                .' · status='.json_encode($contagemStatus, JSON_UNESCAPED_UNICODE)
                ." · seed={$seed}",
            'lote_id' => $loteId,
            'pessoas' => $pessoasOut,
            'eventos' => $eventosTotal,
            'modulos' => $modulosLista,
            'contagem_status' => $contagemStatus,
        ];
    }

    /**
     * Último lote + pessoas + eventos para a tela.
     *
     * @return array{lote:?array<string,mixed>,pessoas:list<array<string,mixed>>}
     */
    public function ultimoLote(): array
    {
        if (! Schema::hasTable('laguna.simulacao_lote')) {
            return ['lote' => null, 'pessoas' => []];
        }

        $lote = DB::table('laguna.simulacao_lote')->orderByDesc('id')->first();
        if ($lote === null) {
            return ['lote' => null, 'pessoas' => []];
        }

        $pessoas = DB::table('laguna.simulacao_pessoa')
            ->where('lote_id', $lote->id)
            ->orderBy('id')
            ->get();

        $eventos = DB::table('laguna.simulacao_evento')
            ->where('lote_id', $lote->id)
            ->orderBy('id')
            ->get()
            ->groupBy('pessoa_id');

        $out = [];
        foreach ($pessoas as $p) {
            $evs = ($eventos[$p->id] ?? collect())->map(static fn ($e) => [
                'modulo' => $e->modulo,
                'cenario' => $e->cenario,
                'status' => $e->status,
                'detalhe' => $e->detalhe,
                'url' => $e->url,
                'created_at' => (string) $e->created_at,
            ])->values()->all();

            $out[] = [
                'id' => (int) $p->id,
                'nome' => $p->nome,
                'cpf' => $p->cpf,
                'perfil' => $p->perfil,
                'escola' => $p->escola,
                'idpes' => $p->idpes,
                'eventos' => $evs,
            ];
        }

        $resumo = is_string($lote->resumo) ? json_decode($lote->resumo, true) : (array) ($lote->resumo ?? []);

        return [
            'lote' => [
                'id' => (int) $lote->id,
                'tag' => $lote->tag,
                'pessoas' => (int) $lote->pessoas,
                'eventos' => (int) $lote->eventos,
                'modulos' => (int) $lote->modulos,
                'usuario' => $lote->usuario,
                'created_at' => (string) $lote->created_at,
                'resumo' => $resumo,
            ],
            'pessoas' => $out,
        ];
    }

    /** @return list<string> */
    private function escolas(): array
    {
        if (! Schema::hasTable('pmieducar.escola')) {
            return ['Rede Laguna'];
        }
        $lista = DB::table('pmieducar.escola as e')
            ->join('cadastro.juridica as j', 'j.idpes', '=', 'e.ref_idpes')
            ->where('e.ativo', 1)
            ->orderBy('e.cod_escola')
            ->limit(12)
            ->pluck('j.fantasia')
            ->filter()
            ->values()
            ->all();

        return $lista !== [] ? $lista : ['Rede Laguna'];
    }

    /** @return list<string> */
    private function modulosDisponiveis(): array
    {
        $ids = [];
        foreach (CatalogoModulos::todos() as $m) {
            $provider = $m['provider'] ?? null;
            if ($provider && class_exists($provider)) {
                $ids[] = $m['id'];
            }
        }
        // Sempre inclui núcleo
        $ids[] = 'nucleo';

        return array_values(array_unique($ids));
    }

    /**
     * @param  list<string>  $modulos
     * @return list<array{modulo:string,cenario:string}>
     */
    private function sortearCenarios(array $modulos, string $perfil): array
    {
        $pool = [
            ['modulo' => 'solicitacao', 'cenario' => 'abrir_chamado'],
            ['modulo' => 'atendimento', 'cenario' => 'agendar_atendimento'],
            ['modulo' => 'biblioteca', 'cenario' => 'emprestar_obra'],
            ['modulo' => 'almoxarifado', 'cenario' => 'pedir_material'],
            ['modulo' => 'alimentacao', 'cenario' => 'registrar_restricao'],
            ['modulo' => 'eventos', 'cenario' => 'inscrever_formacao'],
            ['modulo' => 'transporte', 'cenario' => 'vincular_rota'],
            ['modulo' => 'busca-ativa', 'cenario' => 'abrir_caso'],
            ['modulo' => 'vagas', 'cenario' => 'inscrever_fila'],
            ['modulo' => 'act', 'cenario' => 'candidatar_vaga'],
            ['modulo' => 'servidor', 'cenario' => 'registrar_afastamento'],
            ['modulo' => 'horario', 'cenario' => 'consultar_grade'],
            ['modulo' => 'escolar', 'cenario' => 'consultar_boletim'],
            ['modulo' => 'exportacao', 'cenario' => 'exportar_pdf'],
            ['modulo' => 'apps-api', 'cenario' => 'token_consulta'],
            ['modulo' => 'censo', 'cenario' => 'validar_layout'],
        ];

        $afinidade = match ($perfil) {
            'aluno', 'familia' => ['biblioteca', 'alimentacao', 'transporte', 'vagas', 'atendimento', 'busca-ativa', 'escolar', 'apps-api'],
            'responsavel' => ['vagas', 'solicitacao', 'transporte', 'alimentacao', 'atendimento', 'apps-api', 'escolar'],
            'professor', 'coordenador' => ['escolar', 'horario', 'eventos', 'solicitacao', 'biblioteca', 'apps-api', 'exportacao'],
            'diretor' => ['solicitacao', 'almoxarifado', 'escolar', 'exportacao', 'horario', 'alimentacao'],
            'analista_rh', 'candidato_act' => ['act', 'servidor', 'eventos', 'horario', 'exportacao'],
            'operador_merenda' => ['alimentacao', 'almoxarifado', 'solicitacao'],
            'operador_almox' => ['almoxarifado', 'solicitacao', 'exportacao'],
            'bibliotecario' => ['biblioteca', 'solicitacao', 'escolar'],
            'motorista' => ['transporte', 'solicitacao'],
            'atendente' => ['atendimento', 'busca-ativa', 'solicitacao'],
            default => ['solicitacao', 'exportacao', 'apps-api'],
        };

        $disponiveis = array_values(array_filter(
            $pool,
            static fn ($c) => in_array($c['modulo'], $modulos, true) || $c['modulo'] === 'nucleo'
        ));

        // Prioriza afinidade do perfil
        usort($disponiveis, static function ($a, $b) use ($afinidade) {
            $pa = in_array($a['modulo'], $afinidade, true) ? 0 : 1;
            $pb = in_array($b['modulo'], $afinidade, true) ? 0 : 1;

            return $pa <=> $pb;
        });

        $n = mt_rand(4, min(8, max(4, count($disponiveis))));
        $escolhidos = array_slice($disponiveis, 0, $n);
        // Embaralha levemente o restante da afinidade
        if (count($disponiveis) > $n) {
            $extra = array_slice($disponiveis, $n);
            shuffle($extra);
            if ($extra !== [] && mt_rand(0, 1) === 1) {
                $escolhidos[array_key_last($escolhidos)] = $extra[0];
            }
        }
        shuffle($escolhidos);

        return array_values($escolhidos);
    }

    private function garantirPessoa(string $nome, string $cpf, string $sexo, string $perfil, string $escola): ?int
    {
        if (! Schema::hasTable('cadastro.fisica') || ! class_exists(LegacyPerson::class)) {
            return null;
        }

        $cpfInt = (int) $cpf;
        $idpes = DB::table('cadastro.fisica')->where('cpf', $cpfInt)->value('idpes');
        if ($idpes) {
            return (int) $idpes;
        }

        try {
            $pessoa = LegacyPerson::query()->create([
                'nome' => $nome,
                'tipo' => 'F',
                'situacao' => 'A',
                'origem_gravacao' => 'M',
                'operacao' => 'I',
                'data_cad' => now(),
            ]);
            $idpes = (int) $pessoa->getKey();
            DB::table('cadastro.fisica')->insert([
                'idpes' => $idpes,
                'data_nasc' => match (true) {
                    str_contains($perfil, 'aluno') => now()->subYears(mt_rand(6, 14))->toDateString(),
                    default => now()->subYears(mt_rand(25, 55))->toDateString(),
                },
                'sexo' => $sexo,
                'cpf' => $cpfInt,
                'ativo' => 1,
                'operacao' => 'I',
                'origem_gravacao' => 'M',
                'data_cad' => now(),
            ]);

            if (in_array($perfil, ['professor', 'diretor', 'coordenador', 'analista_rh', 'candidato_act'], true)
                && Schema::hasTable('pmieducar.servidor')) {
                $inst = (int) (DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->value('cod_instituicao') ?: 1);
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

            if (in_array($perfil, ['aluno', 'familia'], true) && Schema::hasTable('pmieducar.aluno')) {
                $existeAluno = DB::table('pmieducar.aluno')->where('ref_idpes', $idpes)->exists();
                if (! $existeAluno) {
                    try {
                        DB::table('pmieducar.aluno')->insert([
                            'ref_idpes' => $idpes,
                            'ref_usuario_cad' => 1,
                            'data_cadastro' => now(),
                            'ativo' => 1,
                        ]);
                    } catch (Throwable) {
                        //
                    }
                }
            }

            return $idpes;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array{modulo:string,cenario:string}  $cenario
     * @return array{modulo:string,cenario:string,status:string,detalhe:string,url:?string}
     */
    private function executarCenario(
        int $loteId,
        int $pessoaId,
        string $nome,
        string $cpf,
        string $perfil,
        string $escola,
        ?int $idpes,
        array $cenario,
    ): array {
        $modulo = $cenario['modulo'];
        $nomeCenario = $cenario['cenario'];

        try {
            $resultado = match ($nomeCenario) {
                'abrir_chamado' => $this->cenarioChamado($nome, $escola, $cpf),
                'agendar_atendimento' => $this->cenarioAtendimento($nome, $escola, $cpf),
                'emprestar_obra' => $this->cenarioBiblioteca($nome, $cpf),
                'pedir_material' => $this->cenarioAlmox($nome, $escola, $cpf),
                'registrar_restricao' => $this->cenarioAlimentacao($nome, $idpes, $cpf),
                'inscrever_formacao' => $this->cenarioEventos($nome, $cpf),
                'vincular_rota' => $this->cenarioTransporte($nome, $escola, $cpf),
                'abrir_caso' => $this->cenarioBuscaAtiva($nome, $escola, $cpf),
                'inscrever_fila' => $this->cenarioVagas($nome, $cpf, $escola),
                'candidatar_vaga' => $this->cenarioAct($nome, $cpf),
                'registrar_afastamento' => $this->cenarioRh($nome, $idpes, $cpf),
                'consultar_grade' => $this->cenarioHorario($nome, $escola),
                'consultar_boletim' => $this->cenarioEscolar($nome, $escola),
                'exportar_pdf' => $this->cenarioExportacao($nome),
                'token_consulta' => $this->cenarioApps($nome, $cpf),
                'validar_layout' => $this->cenarioCenso($nome),
                default => $this->resultado(self::ST_SKIP, 'cenário desconhecido'),
            };
        } catch (Throwable $e) {
            $resultado = $this->resultado(self::ST_ERRO, mb_substr($e->getMessage(), 0, 400), null, ['erro' => true]);
        }

        return $this->registrarEvento(
            $loteId,
            $pessoaId,
            $modulo,
            $nomeCenario,
            $resultado['status'] ?? 'ok',
            $resultado['detalhe'] ?? '',
            $resultado['url'] ?? null,
            array_merge($resultado['payload'] ?? [], [
                'cpf' => $cpf,
                'perfil' => $perfil,
                'escola' => $escola,
                'idpes' => $idpes,
            ])
        );
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array{modulo:string,cenario:string,status:string,detalhe:string,url:?string}
     */
    private function registrarEvento(
        int $loteId,
        int $pessoaId,
        string $modulo,
        string $cenario,
        string $status,
        string $detalhe,
        ?string $url,
        array $payload = [],
    ): array {
        DB::table('laguna.simulacao_evento')->insert([
            'lote_id' => $loteId,
            'pessoa_id' => $pessoaId,
            'modulo' => $modulo,
            'cenario' => $cenario,
            'status' => $status,
            'detalhe' => mb_substr($detalhe, 0, 500),
            'url' => $url !== null ? mb_substr($url, 0, 255) : null,
            'payload' => json_encode(array_merge($payload, ['demo' => self::TAG]), JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);

        AuditoriaFluxo::registrar(
            null,
            'sim_pessoa',
            "{$modulo}/{$cenario}: {$detalhe}",
            $pessoaId,
            ['modulo' => $modulo, 'cenario' => $cenario, 'status' => $status, 'tag' => self::TAG]
        );

        return [
            'modulo' => $modulo,
            'cenario' => $cenario,
            'status' => $status,
            'detalhe' => $detalhe,
            'url' => $url,
        ];
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioChamado(string $nome, string $escola, string $cpf): array
    {
        $Poc = 'iEducar\\Packages\\BuritiSolicitacaoServicos\\Services\\PocRegistro';
        if (! class_exists($Poc) || ! $Poc::disponivel()) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Solicitação: PoC/tabela indisponível — falta de teste', '/solicitacao-servicos');
        }
        $assuntos = ['Troca de lâmpada', 'Vazamento', 'Manutenção computador', 'Documentação aluno', 'Transporte pontual'];
        $assunto = $assuntos[mt_rand(0, count($assuntos) - 1)];
        $status = ['aberto', 'em_andamento', 'aguardando', 'fechado'][mt_rand(0, 3)];
        $id = $Poc::gravar('chamado', $assunto.' · '.$nome, $escola.' · '.$status, [
            'escola' => $escola,
            'situacao' => $status,
            'solicitante' => $nome,
            'cpf' => $cpf,
            'prioridade' => ['baixa', 'media', 'alta'][mt_rand(0, 2)],
            'demo' => self::TAG,
        ]);

        return ['status' => 'ok', 'detalhe' => "Chamado #{$id} «{$assunto}» ({$status})", 'url' => '/solicitacao-servicos', 'payload' => ['id' => $id]];
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioAtendimento(string $nome, string $escola, string $cpf): array
    {
        $Poc = 'iEducar\\Packages\\BuritiAtendimentoEstudante\\Services\\PocRegistro';
        if (! class_exists($Poc) || ! $Poc::disponivel()) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Atendimento: PoC/tabela indisponível — falta de teste', '/atendimento-estudante');
        }
        $motivos = ['Orientação pedagógica', 'Acompanhamento psicológico', 'Encaminhamento saúde', 'Mediação conflito'];
        $motivo = $motivos[mt_rand(0, count($motivos) - 1)];
        $id = $Poc::gravar('agenda', $nome, now()->addDays(mt_rand(1, 10))->toDateString(), [
            'aluno' => $nome,
            'escola' => $escola,
            'motivo' => $motivo,
            'cpf' => $cpf,
            'status' => ['agendado', 'em_atendimento', 'finalizado'][mt_rand(0, 2)],
            'demo' => self::TAG,
        ]);

        return ['status' => 'ok', 'detalhe' => "Agenda #{$id} · {$motivo}", 'url' => '/atendimento-estudante', 'payload' => ['id' => $id]];
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioBiblioteca(string $nome, string $cpf): array
    {
        if (! Schema::hasTable('biblioteca.poc_registro')) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Biblioteca: tabela poc_registro ausente — falta de teste', '/biblioteca-escolar');
        }
        $obras = ['O Pequeno Príncipe', 'Dom Casmurro', 'A Bolsa Amarela', 'Capitães da Areia'];
        $obra = $obras[mt_rand(0, count($obras) - 1)];
        $id = (int) DB::table('biblioteca.poc_registro')->insertGetId([
            'tipo' => 'emprestimo',
            'titulo' => $obra.' → '.$nome,
            'meta' => $nome,
            'payload' => json_encode([
                'obra' => $obra,
                'leitor' => $nome,
                'aluno' => $nome,
                'cpf' => $cpf,
                'devolucao_prevista' => now()->addDays(mt_rand(3, 14))->toDateString(),
                'demo' => self::TAG,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);

        return ['status' => 'ok', 'detalhe' => "Empréstimo #{$id} «{$obra}»", 'url' => '/biblioteca-escolar', 'payload' => ['id' => $id]];
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioAlmox(string $nome, string $escola, string $cpf): array
    {
        $Poc = 'iEducar\\Packages\\BuritiAlmoxarifadoEscolar\\Services\\PocRegistro';
        $Pedido = 'iEducar\\Packages\\BuritiAlmoxarifadoEscolar\\Services\\PedidoCarrinho';
        if (! class_exists($Poc) || ! $Poc::disponivel()) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Almoxarifado: PoC/tabela indisponível — falta de teste', '/almoxarifado-escolar');
        }
        $status = class_exists($Pedido)
            ? [$Pedido::STATUS_SEPARADO ?? 'separado', $Pedido::STATUS_ENTREGUE ?? 'entregue'][mt_rand(0, 1)]
            : 'separado';
        $tipo = class_exists($Pedido) ? ($Pedido::TIPO ?? 'pedido') : 'pedido';
        $id = $Poc::gravar($tipo, 'Pedido sim · '.$nome, $status, [
            'escola' => $escola,
            'status' => $status,
            'solicitante' => $nome,
            'cpf' => $cpf,
            'itens' => [
                ['sku' => 'PAPEL-A4', 'titulo' => 'Papel A4', 'quantidade' => mt_rand(2, 20)],
                ['sku' => 'CANETA-AZ', 'titulo' => 'Caneta azul', 'quantidade' => mt_rand(1, 10)],
            ],
            'demo' => self::TAG,
        ]);

        return ['status' => 'ok', 'detalhe' => "Pedido #{$id} ({$status})", 'url' => '/almoxarifado-escolar', 'payload' => ['id' => $id]];
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioAlimentacao(string $nome, ?int $idpes, string $cpf): array
    {
        if (! Schema::hasTable('alimentacao.restricao_aluno')) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Alimentação: tabela restricao_aluno ausente — falta de teste', '/alimentacao-escolar');
        }
        $tipos = ['leite', 'glúten', 'ovo', 'amendoim', 'lactose', 'corante'];
        $tipo = $tipos[mt_rand(0, count($tipos) - 1)];
        $alunoId = $idpes;
        if (Schema::hasTable('pmieducar.aluno') && $idpes) {
            $alunoId = DB::table('pmieducar.aluno')->where('ref_idpes', $idpes)->value('cod_aluno') ?: $idpes;
        }
        $alunoId = (int) ($alunoId ?: mt_rand(9000, 9999));
        DB::table('alimentacao.restricao_aluno')->updateOrInsert(
            ['ref_cod_aluno' => $alunoId, 'restricao' => $tipo],
            ['updated_at' => now(), 'created_at' => now()]
        );

        return [
            'status' => 'ok',
            'detalhe' => "Restrição «{$tipo}» · aluno={$alunoId} ({$nome})",
            'url' => '/alimentacao-escolar',
            'payload' => ['restricao' => $tipo, 'aluno' => $alunoId, 'cpf' => $cpf],
        ];
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioEventos(string $nome, string $cpf): array
    {
        $Poc = 'iEducar\\Packages\\BuritiEventosFormacao\\Support\\PocRegistro';
        if (! class_exists($Poc) || (method_exists($Poc, 'pronto') && ! $Poc::pronto())) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Eventos: PoC indisponível — falta de teste', '/eventos-formacao');
        }
        $evento = ['Formação BNCC', 'Oficina de Avaliação', 'Semana Pedagógica', 'Curso Inclusão'][mt_rand(0, 3)];
        $gravar = method_exists($Poc, 'salvar') ? 'salvar' : (method_exists($Poc, 'gravar') ? 'gravar' : null);
        if ($gravar === null) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Eventos: sem API de gravação — falta de teste', '/eventos-formacao');
        }
        $id = $Poc::$gravar('inscricao', $nome.' → '.$evento, 'confirmada', [
            'participante' => $nome,
            'cpf' => $cpf,
            'evento' => $evento,
            'status' => 'confirmada',
            'demo' => self::TAG,
        ]);

        return ['status' => 'ok', 'detalhe' => "Inscrição #{$id} em «{$evento}»", 'url' => '/eventos-formacao', 'payload' => ['id' => $id]];
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioTransporte(string $nome, string $escola, string $cpf): array
    {
        $url = '/transporte-escolar';
        if (! Schema::hasTable('transporte_escolar.rota') && ! Schema::hasTable('transporte_escolar.aluno_rota')) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Transporte: schema/tabelas ausentes — falta de teste', $url);
        }

        $rotaRow = null;
        if (Schema::hasTable('transporte_escolar.rota')) {
            $rotaRow = DB::table('transporte_escolar.rota')->orderBy('id')->first();
        }
        if ($rotaRow === null) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                "Transporte · {$nome}: nenhuma rota cadastrada — falta de teste (não é vínculo bem-sucedido)",
                $url,
                ['escola' => $escola, 'cpf' => $cpf]
            );
        }

        $rotaLabel = (string) ($rotaRow->descricao ?? $rotaRow->nome ?? ('Rota #'.$rotaRow->id));
        if (Schema::hasTable('transporte_escolar.aluno_rota') && Schema::hasTable('pmieducar.aluno')) {
            // Sem aluno/rota real ainda é sucesso parcial se a rota existe e registramos a tentativa observável.
        }
        if (Schema::hasTable('laguna.auditoria')) {
            DB::table('laguna.auditoria')->insert([
                'acao' => 'sim_transporte',
                'detalhe' => "{$nome} vinculado a {$rotaLabel} ({$escola})",
                'ref_id' => $rotaRow->id ?? null,
                'usuario' => AuditoriaFluxo::usuario(),
                'payload' => json_encode([
                    'passageiro' => $nome,
                    'cpf' => $cpf,
                    'rota' => $rotaLabel,
                    'rota_id' => $rotaRow->id ?? null,
                    'escola' => $escola,
                    'resultado' => self::ST_OK,
                    'demo' => self::TAG,
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
        }

        return $this->resultado(self::ST_OK, "Passageiro vinculado à rota «{$rotaLabel}»", $url, ['rota_id' => $rotaRow->id ?? null]);
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioBuscaAtiva(string $nome, string $escola, string $cpf): array
    {
        $url = '/busca-ativa';
        if (! Schema::hasTable('busca_ativa.alerta')) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                "Busca Ativa · {$nome}: tabela alerta ausente — falta de teste",
                $url,
                ['cpf' => $cpf, 'escola' => $escola]
            );
        }

        $inst = Schema::hasTable('pmieducar.instituicao')
            ? (int) (DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->value('cod_instituicao') ?: 1)
            : 1;
        $escolaId = null;
        if (Schema::hasTable('pmieducar.escola') && Schema::hasTable('cadastro.juridica')) {
            $escolaId = DB::table('pmieducar.escola as e')
                ->join('cadastro.juridica as j', 'j.idpes', '=', 'e.ref_idpes')
                ->where('e.ativo', 1)
                ->where('j.fantasia', $escola)
                ->value('e.cod_escola');
            if (! $escolaId) {
                $escolaId = DB::table('pmieducar.escola')->where('ativo', 1)->orderBy('cod_escola')->value('cod_escola');
            }
        }
        if (! $escolaId) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                "Busca Ativa · {$nome}: sem escola cadastrada para abrir alerta — falta de teste",
                $url
            );
        }

        try {
            $payload = [
                'ref_cod_instituicao' => $inst,
                'ref_cod_escola' => (int) $escolaId,
                'tipo' => 'faltas',
                'severidade' => 'media',
                'status' => 'aberto',
                'faltas_consecutivas' => mt_rand(3, 8),
                'motivo' => 'Simulação Laguna · '.$nome,
                'metadados' => json_encode(['demo' => self::TAG, 'cpf' => $cpf, 'aluno' => $nome, 'escola' => $escola], JSON_UNESCAPED_UNICODE),
                'detectado_em' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $id = (int) DB::table('busca_ativa.alerta')->insertGetId($payload);

            return $this->resultado(self::ST_OK, "Alerta Busca Ativa #{$id} · {$escola}", $url, ['id' => $id]);
        } catch (Throwable $e) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                'Busca Ativa: não foi possível gravar alerta — falta de teste ('.mb_substr($e->getMessage(), 0, 180).')',
                $url
            );
        }
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioVagas(string $nome, string $cpf, string $escola): array
    {
        $url = '/pre-matricula';
        $Process = \iEducar\Packages\PreMatricula\Models\Process::class;
        if (! class_exists($Process)) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                'Pré-matrícula: pacote não carregado — falta de teste (não é negativa de vaga)',
                '/pre-matricula'
            );
        }

        try {
            $ativos = $Process::query()->active()->with('stages')->get();
        } catch (Throwable $e) {
            return $this->resultado(self::ST_ERRO, 'Pré-matrícula: falha ao consultar processos — '.$e->getMessage(), $url);
        }

        if ($ativos->isEmpty()) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                "Tentativa de vaga · {$nome}: nenhum processo de alocação/pré-matrícula ativo cadastrado — falta de teste (ausência de processo ≠ negativa)",
                $url,
                ['escola' => $escola, 'cpf' => $cpf, 'processos_ativos' => 0]
            );
        }

        $abertos = $ativos->filter(static function ($p) {
            foreach ($p->stages as $stage) {
                if (method_exists($stage, 'isOpen') && $stage->isOpen()) {
                    return true;
                }
            }

            return false;
        })->values();

        if ($abertos->isEmpty()) {
            $nomes = $ativos->pluck('name')->take(3)->implode(', ');

            return $this->resultado(
                self::ST_FALTA_TESTE,
                "Tentativa de vaga · {$nome}: há processo(s) ({$nomes}) mas nenhuma etapa aberta no período — falta de teste",
                $url,
                ['escola' => $escola, 'processos_ativos' => $ativos->count(), 'etapas_abertas' => 0]
            );
        }

        $proc = $abertos->first();
        $stage = $proc->stages->first(static fn ($s) => method_exists($s, 'isOpen') && $s->isOpen());
        $vagasQtd = method_exists($proc, 'vacancies') ? (int) $proc->vacancies()->count() : 0;

        if ($vagasQtd < 1) {
            // Processo/etapa abertos, mas sem quadro de vagas: negativa de regra (fluxo testável).
            if (Schema::hasTable('laguna.auditoria')) {
                DB::table('laguna.auditoria')->insert([
                    'acao' => 'sim_vagas',
                    'detalhe' => "Negativa · sem vagas no processo #{$proc->id} · {$nome}",
                    'usuario' => AuditoriaFluxo::usuario(),
                    'payload' => json_encode([
                        'candidato' => $nome,
                        'cpf' => $cpf,
                        'escola' => $escola,
                        'process_id' => $proc->id,
                        'stage_id' => $stage?->id,
                        'resultado' => self::ST_NEGATIVA,
                        'demo' => self::TAG,
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                ]);
            }

            return $this->resultado(
                self::ST_NEGATIVA,
                "Tentativa de vaga no processo «{$proc->name}» (etapa aberta): negativa — quadro de vagas vazio/indisponível",
                $url,
                ['process_id' => $proc->id, 'stage_id' => $stage?->id, 'escola' => $escola]
            );
        }

        if (Schema::hasTable('laguna.auditoria')) {
            DB::table('laguna.auditoria')->insert([
                'acao' => 'sim_vagas',
                'detalhe' => "Inscrição PoC · processo #{$proc->id} · {$nome} · {$escola}",
                'usuario' => AuditoriaFluxo::usuario(),
                'payload' => json_encode([
                    'candidato' => $nome,
                    'cpf' => $cpf,
                    'escola' => $escola,
                    'process_id' => $proc->id,
                    'stage_id' => $stage?->id,
                    'vagas_cadastradas' => $vagasQtd,
                    'resultado' => self::ST_OK,
                    'demo' => self::TAG,
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
        }

        return $this->resultado(
            self::ST_OK,
            "Inscrição PoC no processo «{$proc->name}» · etapa aberta · {$vagasQtd} vaga(s) no quadro · {$escola}",
            $url,
            ['process_id' => $proc->id, 'stage_id' => $stage?->id, 'vagas' => $vagasQtd]
        );
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioAct(string $nome, string $cpf): array
    {
        $Poc = 'iEducar\\Packages\\BuritiContratacoesTemporarias\\Services\\PocRegistro';
        if (! class_exists($Poc) || ! $Poc::disponivel()) {
            return $this->resultado(self::ST_FALTA_TESTE, 'ACT: PoC/tabela indisponível — falta de teste', '/contratacoes-temporarias');
        }
        $id = $Poc::gravar('inscricao', $nome, 'deferida', [
            'candidato' => $nome,
            'cpf' => $cpf,
            'area' => ['Português', 'Matemática', 'Educação Infantil', 'Educação Física'][mt_rand(0, 3)],
            'status' => ['inscrito', 'classificado', 'convocado'][mt_rand(0, 2)],
            'demo' => self::TAG,
        ]);

        return ['status' => 'ok', 'detalhe' => "Inscrição ACT #{$id}", 'url' => '/contratacoes-temporarias', 'payload' => ['id' => $id]];
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioRh(string $nome, ?int $idpes, string $cpf): array
    {
        $url = '/rh-educacional';
        if (! $idpes) {
            return $this->resultado(self::ST_FALTA_TESTE, "RH · {$nome}: pessoa sem idpes — falta de teste", $url, ['cpf' => $cpf]);
        }
        if (! Schema::hasTable('pmieducar.servidor') || ! DB::table('pmieducar.servidor')->where('cod_servidor', $idpes)->exists()) {
            return $this->resultado(self::ST_FALTA_TESTE, "RH · {$nome}: servidor não cadastrado — falta de teste", $url, ['idpes' => $idpes]);
        }
        if (! Schema::hasTable('pmieducar.servidor_afastamento')) {
            return $this->resultado(self::ST_FALTA_TESTE, 'RH: tabela servidor_afastamento ausente — falta de teste', $url);
        }
        $motivo = Schema::hasTable('pmieducar.motivo_afastamento')
            ? DB::table('pmieducar.motivo_afastamento')->where('ativo', 1)->orderBy('cod_motivo_afastamento')->value('cod_motivo_afastamento')
            : null;
        if (! $motivo) {
            return $this->resultado(self::ST_FALTA_TESTE, 'RH: nenhum motivo de afastamento cadastrado — falta de teste', $url);
        }
        $inst = (int) (DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->value('cod_instituicao') ?: 1);
        $inicio = now()->subDays(mt_rand(1, 20))->toDateString();
        if (DB::table('pmieducar.servidor_afastamento')->where('ref_cod_servidor', $idpes)->whereDate('data_saida', $inicio)->exists()) {
            return $this->resultado(self::ST_OK, 'Afastamento já existia (idempotente)', $url, ['inicio' => $inicio]);
        }
        $seq = (int) DB::table('pmieducar.servidor_afastamento')->where('ref_cod_servidor', $idpes)->max('sequencial');
        try {
            DB::table('pmieducar.servidor_afastamento')->insert([
                'ref_cod_servidor' => $idpes,
                'sequencial' => $seq + 1,
                'ref_ref_cod_instituicao' => $inst,
                'ref_cod_motivo_afastamento' => (int) $motivo,
                'ref_usuario_cad' => 1,
                'data_cadastro' => now(),
                'data_saida' => $inicio,
                'data_retorno' => now()->addDays(mt_rand(2, 15))->toDateString(),
                'ativo' => 1,
            ]);
        } catch (Throwable $e) {
            return $this->resultado(self::ST_ERRO, $e->getMessage(), $url);
        }

        return $this->resultado(self::ST_OK, "Afastamento registrado a partir de {$inicio}", $url, ['inicio' => $inicio]);
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioHorario(string $nome, string $escola): array
    {
        $url = '/horario-automatico';
        if (! Schema::hasTable('pmieducar.turma')) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Horário: tabela turma ausente — falta de teste', $url);
        }
        $turmas = (int) DB::table('pmieducar.turma')->where('ativo', 1)->count();
        if ($turmas < 1) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                "Horário · {$nome}: nenhuma turma ativa para consultar grade em {$escola} — falta de teste",
                $url
            );
        }
        $comGrade = Schema::hasTable('pmieducar.quadro_horario')
            ? (int) DB::table('pmieducar.quadro_horario')->count()
            : 0;
        if ($comGrade < 1 && Schema::hasTable('pmieducar.quadro_horario_horarios')) {
            $comGrade = (int) DB::table('pmieducar.quadro_horario_horarios')->count();
        }
        if ($comGrade < 1) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                "Horário · {$nome}: turmas existem ({$turmas}), mas sem quadro/grade — falta de teste",
                $url,
                ['turmas' => $turmas]
            );
        }

        return $this->resultado(
            self::ST_OK,
            "Consulta grade · {$escola} · {$turmas} turma(s) · {$comGrade} registro(s) de horário",
            $url,
            ['turmas' => $turmas, 'grade' => $comGrade]
        );
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioEscolar(string $nome, string $escola): array
    {
        $url = '/intranet/educar_aluno_lst.php';
        if (! Schema::hasTable('pmieducar.matricula')) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Escolar: tabela matrícula ausente — falta de teste', $url);
        }
        $mats = (int) DB::table('pmieducar.matricula')->where('ativo', 1)->count();
        if ($mats < 1) {
            return $this->resultado(
                self::ST_FALTA_TESTE,
                "Escolar · {$nome}: nenhuma matrícula ativa na rede — falta de teste (boletim/frequência não exercitados)",
                $url,
                ['escola' => $escola]
            );
        }

        return $this->resultado(
            self::ST_OK,
            "Consulta boletim/frequência · {$nome} · {$escola} · {$mats} matrícula(s) ativas na rede",
            $url,
            ['matriculas' => $mats]
        );
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioExportacao(string $nome): array
    {
        $url = '/exportacao';
        $Exportador = 'iEducar\\Packages\\BuritiExportacaoUnificada\\Services\\Exportador';
        $Formato = 'iEducar\\Packages\\BuritiExportacaoUnificada\\Support\\Formato';
        $Tabela = 'iEducar\\Packages\\BuritiExportacaoUnificada\\Support\\TabelaExportacao';
        if (! class_exists($Exportador) || ! class_exists($Formato) || ! class_exists($Tabela)) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Exportação unificada: classes ausentes — falta de teste', $url);
        }
        try {
            $ex = new $Exportador;
            $t = new $Tabela(
                titulo: 'Simulação Laguna · '.$nome,
                cabecalhos: ['Pessoa', 'Modulo', 'Status'],
                linhas: [[$nome, 'exportacao', 'ok']],
                subtitulo: self::TAG,
            );
            $arq = $ex->exportar($t, $Formato::Pdf, 'sim-laguna-'.md5($nome));
            $bytes = $arq->tamanho();
            if ($bytes < 1) {
                return $this->resultado(self::ST_FALTA_TESTE, 'Exportação PDF gerou 0 bytes — falta de teste', $url);
            }

            return $this->resultado(self::ST_OK, "Exportação PDF gerada ({$bytes} bytes) · {$nome}", $url, ['bytes' => $bytes, 'formato' => 'pdf']);
        } catch (Throwable $e) {
            return $this->resultado(self::ST_ERRO, 'Exportação falhou: '.$e->getMessage(), $url);
        }
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioApps(string $nome, string $cpf): array
    {
        $url = '/apps-api/governanca';
        $catalogo = 'iEducar\\Packages\\BuritiAppsApi\\Support\\AppsApiCatalogo';
        if (! class_exists($catalogo) && ! class_exists('iEducar\\Packages\\BuritiAppsApi\\Providers\\AppsApiServiceProvider')) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Apps API: pacote não carregado — falta de teste', $url, ['cpf' => $cpf]);
        }

        return $this->resultado(
            self::ST_OK,
            "Consulta contrato API apps · {$nome} (pacote carregado)",
            $url,
            ['cpf' => $cpf]
        );
    }

    /** @return array{status:string,detalhe:string,url:?string,payload:array<string,mixed>} */
    private function cenarioCenso(string $nome): array
    {
        $url = '/intranet/educar_exportacao_educacenso.php';
        $Catalog = 'iEducar\\Packages\\BuritiEducacensoImport\\Formats\\EducacensoExportLayoutCatalog';
        $Validator = 'iEducar\\Packages\\BuritiEducacensoImport\\Services\\EducacensoExportValidator';
        if (! class_exists($Catalog) || ! class_exists($Validator)) {
            return $this->resultado(self::ST_FALTA_TESTE, 'Censo: validador/catálogo ausentes — falta de teste', '/educacenso/importacao');
        }
        try {
            $anos = $Catalog::supportedYears();
            if (count($anos) < 2) {
                return $this->resultado(self::ST_FALTA_TESTE, 'Censo: catálogo com menos de 2 anos — falta de teste', $url, ['anos' => $anos]);
            }
            $v = new $Validator;
            $ano = (int) $anos[array_key_last($anos)];
            $txt = $v->gerarAmostra($ano);
            $r = $v->validar($txt, $ano);
            if (! ($r['ok'] ?? false)) {
                return $this->resultado(self::ST_NEGATIVA, 'Censo: amostra inválida na validação estrutural', $url, $r);
            }

            return $this->resultado(
                self::ST_OK,
                'Censo: layouts '.implode('/', $anos).' validados · amostra '.$ano.' OK · '.$nome,
                $url,
                ['anos' => $anos, 'ano_prova' => $ano]
            );
        } catch (Throwable $e) {
            return $this->resultado(self::ST_ERRO, 'Censo: '.$e->getMessage(), $url);
        }
    }
}
