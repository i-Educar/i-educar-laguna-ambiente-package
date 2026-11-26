<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Cenários ricos para ensaio Laguna: ausências, ACT diversos, alimentação,
 * almoxarifado, vagas (pré-matrícula) e reforço de transporte.
 */
final class LagunaCargaCenariosService
{
    public const DEMO_TAG = 'laguna-cenarios-v3';

    /**
     * @return list<array{passo:string,ok:bool,saida:string}>
     */
    public function executar(): array
    {
        return [
            $this->passo('Motivos de afastamento / ausências', fn () => $this->motivosAfastamento()),
            $this->passo('Amostras de afastamento de servidores', fn () => $this->amostraAfastamentos()),
            $this->passo('Alimentação — semana e restrições', fn () => $this->alimentacaoSemana()),
            $this->passo('Alimentação — cardápios multi turma/segmento', fn () => $this->alimentacaoMultiEscopo()),
            $this->passo('Almoxarifado — saldos e pedidos por escola', fn () => $this->almoxarifadoSaldos()),
            $this->passo('Skins — sincronizar tema no i-Diário', fn () => $this->skinsIdiario()),
            $this->passo('Pré-matrícula / vagas nas escolas', fn () => $this->vagasPrematricula()),
        ];
    }

    /** @return array{passo:string,ok:bool,saida:string} */
    private function passo(string $rotulo, callable $fn): array
    {
        try {
            return ['passo' => $rotulo, 'ok' => true, 'saida' => (string) $fn()];
        } catch (Throwable $e) {
            return ['passo' => $rotulo, 'ok' => false, 'saida' => $e->getMessage()];
        }
    }

    private function instituicaoId(): int
    {
        return (int) (DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->value('cod_instituicao') ?: 1);
    }

    private function motivosAfastamento(): string
    {
        if (! Schema::hasTable('pmieducar.motivo_afastamento')) {
            return 'tabela motivo_afastamento ausente';
        }

        $inst = $this->instituicaoId();
        $motivos = [
            ['nm' => 'Licença médica', 'desc' => 'Afastamento por atestado / licença saúde'],
            ['nm' => 'Licença maternidade / paternidade', 'desc' => 'Licença prevista em lei'],
            ['nm' => 'Licença capacitação', 'desc' => 'Curso, formação ou congresso autorizado'],
            ['nm' => 'Falta justificada', 'desc' => 'Ausência pontual com justificativa'],
            ['nm' => 'Falta injustificada', 'desc' => 'Ausência sem justificativa formal'],
            ['nm' => 'Cessão temporária', 'desc' => 'Cedido a outro órgão / rede'],
            ['nm' => 'Readaptação de função', 'desc' => 'Afastamento da função original por readaptação'],
            ['nm' => 'Licença interesse particular', 'desc' => 'Sem remuneração, a pedido'],
            ['nm' => 'Suspensão disciplinar', 'desc' => 'Afastamento por processo administrativo'],
            ['nm' => 'Acompanhamento familiar', 'desc' => 'Acompanhamento de familiar enfermo'],
        ];

        $n = 0;
        foreach ($motivos as $m) {
            $existe = DB::table('pmieducar.motivo_afastamento')
                ->where('ref_cod_instituicao', $inst)
                ->where('nm_motivo', $m['nm'])
                ->where('ativo', 1)
                ->exists();
            if ($existe) {
                continue;
            }
            DB::table('pmieducar.motivo_afastamento')->insert([
                'ref_usuario_cad' => 1,
                'nm_motivo' => $m['nm'],
                'descricao' => $m['desc'],
                'data_cadastro' => now(),
                'ativo' => 1,
                'ref_cod_instituicao' => $inst,
            ]);
            $n++;
        }

        $total = DB::table('pmieducar.motivo_afastamento')->where('ativo', 1)->where('ref_cod_instituicao', $inst)->count();

        return "novos={$n} · total_ativos={$total}";
    }

    private function amostraAfastamentos(): string
    {
        if (! Schema::hasTable('pmieducar.servidor_afastamento')
            || ! Schema::hasTable('pmieducar.motivo_afastamento')
            || ! Schema::hasTable('pmieducar.servidor')) {
            return 'tabelas de afastamento/servidor ausentes';
        }

        $motivos = DB::table('pmieducar.motivo_afastamento')->where('ativo', 1)->orderBy('cod_motivo_afastamento')->pluck('cod_motivo_afastamento');
        if ($motivos->isEmpty()) {
            return 'sem motivos cadastrados';
        }

        $inst = $this->instituicaoId();
        $servidores = DB::table('pmieducar.servidor')->where('ativo', 1)->orderBy('cod_servidor')->limit(5)->pluck('cod_servidor');
        $n = 0;
        foreach ($servidores as $i => $cod) {
            $inicio = now()->subDays(7 + $i * 3)->toDateString();
            if (DB::table('pmieducar.servidor_afastamento')
                ->where('ref_cod_servidor', $cod)
                ->whereDate('data_saida', $inicio)
                ->exists()) {
                continue;
            }

            $seq = (int) DB::table('pmieducar.servidor_afastamento')->where('ref_cod_servidor', $cod)->max('sequencial');
            $payload = [
                'ref_cod_servidor' => $cod,
                'sequencial' => $seq + 1,
                'ref_ref_cod_instituicao' => $inst,
                'ref_cod_motivo_afastamento' => (int) $motivos[$i % $motivos->count()],
                'ref_usuario_cad' => 1,
                'data_cadastro' => now(),
                'data_saida' => $inicio,
                'data_retorno' => now()->subDays(max(0, 2 - $i))->toDateString(),
                'ativo' => 1,
            ];

            try {
                DB::table('pmieducar.servidor_afastamento')->insert($payload);
                $n++;
            } catch (Throwable) {
                //
            }
        }

        return "afastamentos_demo={$n}";
    }

    private function alimentacaoSemana(): string
    {
        if (! Schema::hasTable('alimentacao.cardapio_dia')) {
            return 'schema alimentacao ausente';
        }

        $escolas = DB::table('pmieducar.escola as e')
            ->join('cadastro.juridica as j', 'j.idpes', '=', 'e.ref_idpes')
            ->where('e.ativo', 1)
            ->limit(12)
            ->pluck('j.fantasia');
        if ($escolas->isEmpty()) {
            $escolas = collect(['Rede Laguna']);
        }

        $cardapios = [
            'cafe' => [
                ['pão francês', 'leite', 'fruta da estação'],
                ['bolo de milho', 'café com leite', 'banana'],
                ['pão de forma', 'achocolatado', 'maçã'],
                ['cuscuz', 'leite', 'mamão'],
                ['biscoito', 'suco natural', 'pera'],
            ],
            'almoco' => [
                ['arroz', 'feijão', 'carne moída', 'salada', 'suco'],
                ['arroz', 'feijão', 'frango assado', 'legumes', 'sobremesa'],
                ['arroz integral', 'feijão', 'peixe', 'salada', 'fruta'],
                ['arroz', 'lentilha', 'carne de panela', 'couve', 'suco'],
                ['macarrão', 'feijão', 'ovo', 'salada', 'gelatina'],
            ],
            'lanche' => [
                ['vitamina de fruta', 'biscoito'],
                ['iogurte', 'barra de cereal'],
                ['suco', 'pão com queijo'],
                ['mingau', 'fruta'],
                ['suco', 'bolo simples'],
            ],
        ];

        $n = 0;
        for ($d = 0; $d < 5; $d++) {
            $data = now()->startOfWeek()->addDays($d)->toDateString();
            foreach ($escolas as $escola) {
                $refeicoes = [
                    ['refeicao' => 'cafe', 'itens' => $cardapios['cafe'][$d]],
                    ['refeicao' => 'almoco', 'itens' => $cardapios['almoco'][$d]],
                    ['refeicao' => 'lanche', 'itens' => $cardapios['lanche'][$d]],
                ];
                if ($d % 2 === 1) {
                    $refeicoes[] = ['refeicao' => 'lanche_2', 'itens' => ['fruta', 'água']];
                }
                DB::table('alimentacao.cardapio_dia')->updateOrInsert(
                    ['data' => $data, 'escola' => $escola],
                    [
                        'refeicoes' => json_encode($refeicoes, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $n++;
            }
        }

        $restricoes = 0;
        if (Schema::hasTable('alimentacao.restricao_aluno')) {
            $alunos = DB::table('pmieducar.aluno')->where('ativo', 1)->orderBy('cod_aluno')->limit(8)->pluck('cod_aluno');
            $tipos = ['leite', 'glúten', 'ovo', 'amendoim', 'corante', 'lactose'];
            foreach ($alunos as $i => $aluno) {
                DB::table('alimentacao.restricao_aluno')->updateOrInsert(
                    ['ref_cod_aluno' => $aluno, 'restricao' => $tipos[$i % count($tipos)]],
                    ['updated_at' => now(), 'created_at' => now()]
                );
                $restricoes++;
            }
        }

        return "cardapios={$n} · restricoes={$restricoes} · escolas=".$escolas->count();
    }

    private function alimentacaoMultiEscopo(): string
    {
        if (! Schema::hasTable('alimentacao.cardapio_dia')) {
            return 'schema alimentacao ausente';
        }
        if (! class_exists(\iEducar\Packages\BuritiAlimentacaoEscolar\Services\EscopoCardapio::class)) {
            return 'EscopoCardapio ausente';
        }

        $Escopo = \iEducar\Packages\BuritiAlimentacaoEscolar\Services\EscopoCardapio::class;
        $escola = (string) (DB::table('pmieducar.escola as e')
            ->join('cadastro.juridica as j', 'j.idpes', '=', 'e.ref_idpes')
            ->where('e.ativo', 1)
            ->orderBy('e.cod_escola')
            ->value('j.fantasia') ?: 'EMEF Demo Laguna');

        $refeicoes = json_encode([
            ['refeicao' => 'almoco', 'itens' => ['arroz', 'feijão', 'frango', 'salada']],
            ['refeicao' => 'lanche', 'itens' => ['fruta', 'suco']],
        ], JSON_UNESCAPED_UNICODE);

        $data = now()->startOfWeek()->addDays(2)->toDateString();
        $gravados = 0;

        $cenarios = [
            $Escopo::expandirDestinos('turma', $escola, [
                ['nome' => '1º A Demo', 'id' => 9001],
                ['nome' => '2º B Demo', 'id' => 9002],
            ], []),
            $Escopo::expandirDestinos('segmento', $escola, [], ['infantil', 'fundamental_1']),
            $Escopo::expandirDestinos('escola', $escola, [], []),
            $Escopo::expandirDestinos('rede', $escola, [], []),
        ];

        $hasTurma = Schema::hasColumn('alimentacao.cardapio_dia', 'turma');
        $hasEscopo = Schema::hasColumn('alimentacao.cardapio_dia', 'escopo');
        $hasSegmento = Schema::hasColumn('alimentacao.cardapio_dia', 'segmento');

        foreach ($cenarios as $exp) {
            if (! ($exp['ok'] ?? false)) {
                continue;
            }
            foreach ($exp['destinos'] as $destino) {
                $chave = [
                    'data' => $data,
                    'escola' => $destino['escola'],
                ];
                $dados = [
                    'refeicoes' => $refeicoes,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
                if ($hasTurma) {
                    $chave['turma'] = $destino['turma'] ?: '';
                    if (Schema::hasColumn('alimentacao.cardapio_dia', 'turma_id')) {
                        $dados['turma_id'] = $destino['turma_id'] > 0 ? $destino['turma_id'] : null;
                    }
                }
                if ($hasEscopo) {
                    $dados['escopo'] = $exp['escopo'];
                }
                if ($hasSegmento) {
                    $dados['segmento'] = $destino['segmento'];
                }
                try {
                    DB::table('alimentacao.cardapio_dia')->updateOrInsert($chave, $dados);
                    $gravados++;
                } catch (Throwable) {
                    // colunas podem divergir entre versões
                }
            }
        }

        return "multi_escopo_gravados={$gravados} · escola={$escola}";
    }

    private function almoxarifadoSaldos(): string
    {
        if (! class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\SaldoRede::class)) {
            return 'SaldoRede ausente';
        }

        $catalogo = [
            ['sku' => 'PAPEL-A4', 'titulo' => 'Papel A4', 'saldo' => 120, 'minimo' => 80],
            ['sku' => 'CANETA-AZ', 'titulo' => 'Caneta azul', 'saldo' => 15, 'minimo' => 40],
            ['sku' => 'GIZ-BX', 'titulo' => 'Giz branco', 'saldo' => 200, 'minimo' => 50],
            ['sku' => 'ALCOOL', 'titulo' => 'Álcool 70%', 'saldo' => 8, 'minimo' => 20],
        ];
        $dep = \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\SaldoRede::avaliarDeposito($catalogo);
        $criticos = (int) $dep['criticos'];

        $pedidos = 0;
        if (class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PocRegistro::class)
            && \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PocRegistro::disponivel()
            && class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PedidoCarrinho::class)) {
            $Pedido = \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PedidoCarrinho::class;
            $Poc = \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PocRegistro::class;
            $escolas = DB::table('pmieducar.escola as e')
                ->join('cadastro.juridica as j', 'j.idpes', '=', 'e.ref_idpes')
                ->where('e.ativo', 1)
                ->orderBy('e.cod_escola')
                ->limit(4)
                ->pluck('j.fantasia');
            if ($escolas->isEmpty()) {
                $escolas = collect(['EMEF Centro', 'EMEI Norte']);
            }
            foreach ($escolas as $i => $escola) {
                $status = $i % 2 === 0 ? $Pedido::STATUS_ENTREGUE : $Pedido::STATUS_SEPARADO;
                $Poc::gravar($Pedido::TIPO, 'Demo '.$escola, $status, [
                    'escola' => $escola,
                    'status' => $status,
                    'itens' => [
                        ['sku' => 'PAPEL-A4', 'titulo' => 'Papel A4', 'quantidade' => 10 + $i, 'origem' => 'catalogo'],
                        ['sku' => 'CANETA-AZ', 'titulo' => 'Caneta azul', 'quantidade' => 5, 'origem' => 'catalogo'],
                    ],
                    'baixa_estoque' => $status === $Pedido::STATUS_ENTREGUE,
                    'finalizado_em' => now()->subDays($i)->toIso8601String(),
                    'demo_tag' => self::DEMO_TAG,
                ]);
                $pedidos++;
            }
        }

        return "criticos_deposito={$criticos} · pedidos_demo={$pedidos}";
    }

    private function skinsIdiario(): string
    {
        if (! class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\TemaBuriti::class)) {
            return 'TemaBuriti ausente';
        }
        $Tema = \iEducar\Packages\BuritiLagunaAmbiente\Support\TemaBuriti::class;
        $atual = $Tema::skinId();
        if ($atual === $Tema::SKIN_NENHUM) {
            $Tema::sincronizarIdiario('buriti');
            $atual = 'buriti';
        } else {
            $Tema::sincronizarIdiario($atual);
        }
        $root = base_path('packages/portabilis/i-diario');
        $flag = is_dir($root) && is_file($root.'/.laguna-tema-skin')
            ? trim((string) file_get_contents($root.'/.laguna-tema-skin'))
            : '(sem pacote)';

        return "skin={$atual} · flag_idiario={$flag} · catalogo=".count($Tema::catalogo());
    }

    private function vagasPrematricula(): string
    {
        if (class_exists(\iEducar\Packages\PreMatricula\Services\PrematriculaPovoarService::class)) {
            try {
                $r = app(\iEducar\Packages\PreMatricula\Services\PrematriculaPovoarService::class)
                    ->executar(fresh: false, porStatus: 3);

                return ($r['ok'] ? '' : 'FALHA · ').$r['resumo'];
            } catch (Throwable $e) {
                return 'falha pré-matrícula: '.$e->getMessage();
            }
        }

        if (! class_exists(\iEducar\Packages\PreMatricula\Models\Process::class)) {
            return 'pacote pré-matrícula ausente';
        }

        return 'PrematriculaPovoarService ausente — rode php artisan prematricula:povoar';
    }
}
