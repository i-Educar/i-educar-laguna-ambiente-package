<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use App\Models\LegacyOrganization;
use App\Models\LegacyPerson;
use App\Models\LegacySchool;
use App\Models\SchoolInep;
use iEducar\Packages\BuritiSetup\Shared\MunicipalFuncionarioUserProvisioner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Povoa Laguna como cidade média: 10+ escolas, turmas, matrículas, servidores,
 * usuários multi-perfil e dados de demonstração nos módulos Buriti.
 */
final class LagunaPovoarCidadeService
{
    private const USUARIO_CAD = 1;

    private const INEP_BASE = 420910200;

    /** @var list<array{nome:string,sigla:string,zona:int,perfil:string}> */
    private const ESCOLAS = [
        ['nome' => 'EMEF Prefeito Júlio Garcia — Laguna', 'sigla' => 'EJG', 'zona' => 1, 'perfil' => 'fundamental'],
        ['nome' => 'EMEF Anita Garibaldi', 'sigla' => 'EAG', 'zona' => 1, 'perfil' => 'fundamental'],
        ['nome' => 'EMEF Domingos Fernandes de Oliveira', 'sigla' => 'EDF', 'zona' => 1, 'perfil' => 'fundamental'],
        ['nome' => 'EMEF Professora Rosa Maria', 'sigla' => 'ERM', 'zona' => 1, 'perfil' => 'fundamental'],
        ['nome' => 'EMEF Marcolino Martins Cabral', 'sigla' => 'EMC', 'zona' => 1, 'perfil' => 'fundamental_medio'],
        ['nome' => 'EMEI Pequeno Príncipe', 'sigla' => 'EPP', 'zona' => 1, 'perfil' => 'infantil'],
        ['nome' => 'EMEI Cantinho do Saber', 'sigla' => 'ECS', 'zona' => 1, 'perfil' => 'infantil'],
        ['nome' => 'EMEF Marechal Deodoro', 'sigla' => 'EMD', 'zona' => 1, 'perfil' => 'fundamental'],
        ['nome' => 'CEIM Flor do Mar', 'sigla' => 'CFM', 'zona' => 1, 'perfil' => 'infantil_aee'],
        ['nome' => 'EMEF João da Cruz e Souza', 'sigla' => 'EJC', 'zona' => 1, 'perfil' => 'fundamental'],
        ['nome' => 'EMEF Vila Nova', 'sigla' => 'EVN', 'zona' => 1, 'perfil' => 'fundamental'],
        ['nome' => 'EMEF Ribeirão Pequeno', 'sigla' => 'ERP', 'zona' => 2, 'perfil' => 'fundamental_rural'],
    ];

    /**
     * @return list<array{passo:string,ok:bool,saida:string}>
     */
    public function executar(int $alunosPorTurma = 12, bool $comMatriculas = true): array
    {
        $passos = [];
        $passos[] = $this->passo('Perfis de usuário (tipo_usuario)', fn () => $this->garantirPerfis());
        $passos[] = $this->passo('Rede escolar Laguna (10+ escolas)', fn () => $this->garantirEscolas());
        $passos[] = $this->passo('Vínculos curso/série/ano por escola', fn () => $this->clonarOfertaEscolar());
        $passos[] = $this->chamar('db:seed', [
            '--class' => 'Database\\Seeders\\GerarTurmasSeeder',
            '--force' => true,
        ], 'Gerar turmas em todas as escolas');
        if ($comMatriculas) {
            $passos[] = $this->chamar('ieducar:matriculas', [
                '--force' => true,
                '--por-turma' => $alunosPorTurma,
            ], "Matrículas de teste ({$alunosPorTurma}/turma)");
        }
        $passos[] = $this->passo('Servidores (docentes e apoio)', fn () => $this->garantirServidores());
        $passos[] = $this->passo('Usuários multi-ator', fn () => $this->garantirUsuarios());
        $passos[] = $this->passo('Dados módulos (alimentação e PoC)', fn () => $this->povoarModulos());
        $passos[] = $this->chamar('transporte:simula', [], 'Simulação transporte na rede ampliada');
        $passos[] = $this->chamar('alimentacao:demo', [], 'Cardápio alimentação');
        $passos[] = $this->chamar('busca-ativa:demo', ['--faltas' => 28], 'Busca Ativa multi-etapa / atores');
        $passos[] = $this->chamar('solicitacao:demo', [], 'Chamados multi-etapa / atores');
        $passos[] = $this->chamar('atendimento:demo', [], 'Fila atendimento multi-situação');
        $passos[] = $this->chamar('eventos:demo', [], 'Eventos, inscrições e certificados');
        $passos[] = $this->chamar('biblioteca:demo', [], 'Acervo e empréstimos');
        $passos[] = $this->chamar('almoxarifado:demo', [], 'Itens e pedidos do almoxarifado');
        $passos[] = $this->chamar('horario:demo', [], 'Quadro de horários demo');
        $passos[] = $this->chamar('act:demo', [], 'Processo ACT com vagas/inscrições');
        $passos[] = $this->passo('Identidade Laguna', fn () => $this->garantirIdentidade());
        $passos[] = $this->passo('Resumo rede', fn () => $this->resumo());

        return $passos;
    }

    /** @return array{passo:string,ok:bool,saida:string} */
    private function passo(string $rotulo, callable $fn): array
    {
        try {
            $saida = (string) $fn();

            return ['passo' => $rotulo, 'ok' => true, 'saida' => $saida];
        } catch (\Throwable $e) {
            return ['passo' => $rotulo, 'ok' => false, 'saida' => $e->getMessage()];
        }
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{passo:string,ok:bool,saida:string}
     */
    private function chamar(string $comando, array $params, string $rotulo): array
    {
        if (! array_key_exists($comando, Artisan::all())) {
            return ['passo' => $rotulo, 'ok' => false, 'saida' => "comando {$comando} ausente"];
        }

        try {
            Artisan::call($comando, $params);

            return ['passo' => $rotulo, 'ok' => true, 'saida' => trim(Artisan::output()) ?: 'OK'];
        } catch (\Throwable $e) {
            return ['passo' => $rotulo, 'ok' => false, 'saida' => $e->getMessage()];
        }
    }

    private function instituicaoId(): int
    {
        return (int) DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->value('cod_instituicao');
    }

    private function escolaModeloId(): int
    {
        return (int) DB::table('pmieducar.escola')->where('ativo', 1)->orderBy('cod_escola')->value('cod_escola');
    }

    private function ano(): int
    {
        return (int) (DB::table('pmieducar.escola_ano_letivo')
            ->where('andamento', 1)
            ->orderByDesc('ano')
            ->value('ano') ?: date('Y'));
    }

    private function garantirPerfis(): string
    {
        $perfis = [
            ['cod' => 1, 'nome' => 'Administrador', 'nivel' => 1, 'desc' => 'Acesso total SME'],
            ['cod' => 2, 'nome' => 'Secretário SME', 'nivel' => 1, 'desc' => 'Rede municipal completa'],
            ['cod' => 3, 'nome' => 'Diretor escolar', 'nivel' => 2, 'desc' => 'Gestão da escola'],
            ['cod' => 4, 'nome' => 'Coordenador pedagógico', 'nivel' => 2, 'desc' => 'Pedagógico escolar'],
            ['cod' => 5, 'nome' => 'Professor', 'nivel' => 4, 'desc' => 'Docente'],
            ['cod' => 6, 'nome' => 'Operador Transporte', 'nivel' => 1, 'desc' => 'Transporte escolar'],
            ['cod' => 7, 'nome' => 'Operador Alimentação', 'nivel' => 1, 'desc' => 'Merenda / PNAE'],
            ['cod' => 8, 'nome' => 'Operador Almoxarifado', 'nivel' => 1, 'desc' => 'Material e estoque'],
            ['cod' => 9, 'nome' => 'Atendimento estudante', 'nivel' => 2, 'desc' => 'Acolhimento e filas'],
            ['cod' => 10, 'nome' => 'Solicitação de serviços', 'nivel' => 2, 'desc' => 'Chamados escola↔SME'],
            ['cod' => 11, 'nome' => 'RH / ACT', 'nivel' => 1, 'desc' => 'Contratações temporárias'],
            ['cod' => 12, 'nome' => 'Quadro de horários', 'nivel' => 2, 'desc' => 'Grade e conflitos'],
            ['cod' => 13, 'nome' => 'Operador Eventos', 'nivel' => 1, 'desc' => 'Eventos, formação e certificados'],
            ['cod' => 14, 'nome' => 'Busca Ativa', 'nivel' => 1, 'desc' => 'Infrequência e processos de busca ativa'],
            ['cod' => 15, 'nome' => 'Assistência / rede de proteção', 'nivel' => 1, 'desc' => 'Encaminhamentos e acompanhamento familiar'],
        ];

        foreach ($perfis as $p) {
            DB::table('pmieducar.tipo_usuario')->updateOrInsert(
                ['cod_tipo_usuario' => $p['cod']],
                [
                    'nm_tipo' => $p['nome'],
                    'descricao' => $p['desc'],
                    'nivel' => $p['nivel'],
                    'ref_funcionario_cad' => self::USUARIO_CAD,
                    'data_cadastro' => now(),
                    'ativo' => 1,
                ]
            );
        }

        $menus = DB::table('menus')->where('active', true)->whereNotNull('process')->pluck('id');
        $criados = 0;
        foreach ($perfis as $p) {
            foreach ($menus as $menuId) {
                $exists = DB::table('pmieducar.menu_tipo_usuario')
                    ->where('ref_cod_tipo_usuario', $p['cod'])
                    ->where('menu_id', $menuId)
                    ->exists();
                if ($exists) {
                    continue;
                }
                // Professor: só visualiza; demais perfis cadastram.
                $cadastra = $p['cod'] === 5 ? 0 : 1;
                DB::table('pmieducar.menu_tipo_usuario')->insert([
                    'ref_cod_tipo_usuario' => $p['cod'],
                    'menu_id' => $menuId,
                    'cadastra' => $cadastra,
                    'visualiza' => 1,
                    'exclui' => $p['cod'] <= 2 ? 1 : 0,
                ]);
                $criados++;
            }
        }

        return count($perfis).' perfis · '.$criados.' ACL novas';
    }

    private function garantirEscolas(): string
    {
        $instituicao = $this->instituicaoId();
        $criadas = 0;
        $existentes = 0;

        foreach (self::ESCOLAS as $i => $def) {
            $inep = (string) (self::INEP_BASE + $i + 1);
            $ja = SchoolInep::query()->where('cod_escola_inep', $inep)->first();
            if ($ja) {
                $existentes++;
                LegacyOrganization::query()->whereKey(
                    LegacySchool::query()->whereKey($ja->cod_escola)->value('ref_idpes')
                )->update(['fantasia' => $def['nome']]);
                LegacyPerson::query()->whereKey(
                    LegacySchool::query()->whereKey($ja->cod_escola)->value('ref_idpes')
                )->update(['nome' => $def['nome']]);
                LegacySchool::query()->whereKey($ja->cod_escola)->update([
                    'sigla' => $def['sigla'],
                    'ativo' => 1,
                    'zona_localizacao' => $def['zona'],
                ]);

                continue;
            }

            // Reaproveita a escola modelo no primeiro slot se ainda não tiver INEP Laguna.
            if ($i === 0) {
                $modelo = LegacySchool::query()->where('ativo', 1)->orderBy('cod_escola')->first();
                if ($modelo) {
                    LegacyPerson::query()->whereKey($modelo->ref_idpes)->update(['nome' => $def['nome']]);
                    LegacyOrganization::query()->whereKey($modelo->ref_idpes)->update(['fantasia' => $def['nome']]);
                    $modelo->update(['sigla' => $def['sigla'], 'zona_localizacao' => $def['zona'], 'ativo' => 1]);
                    SchoolInep::query()->updateOrCreate(
                        ['cod_escola' => $modelo->getKey()],
                        ['cod_escola_inep' => $inep, 'nome_inep' => $def['nome'], 'fonte' => 'LAGUNA_POC']
                    );
                    $existentes++;

                    continue;
                }
            }

            $pessoa = LegacyPerson::query()->create([
                'nome' => $def['nome'],
                'tipo' => 'J',
                'situacao' => 'A',
                'origem_gravacao' => 'M',
                'operacao' => 'I',
                'email' => strtolower($def['sigla']).'@sme.laguna.sc.gov.br',
                'data_cad' => now(),
            ]);

            $cnpj = sprintf('42%012d', 9102000 + $i);
            LegacyOrganization::query()->create([
                'idpes' => $pessoa->getKey(),
                'cnpj' => $cnpj,
                'insc_estadual' => 0,
                'origem_gravacao' => 'M',
                'idpes_cad' => self::USUARIO_CAD,
                'data_cad' => now(),
                'operacao' => 'I',
                'fantasia' => $def['nome'],
            ]);

            $attrs = [
                'ref_usuario_cad' => self::USUARIO_CAD,
                'ref_cod_instituicao' => $instituicao,
                'sigla' => $def['sigla'],
                'ref_idpes' => $pessoa->getKey(),
                'ativo' => 1,
                'zona_localizacao' => $def['zona'],
                'nao_ha_funcionarios_para_funcoes' => false,
                'data_cadastro' => now(),
            ];

            // Campos opcionais do Educacenso quando existirem no modelo/fillable.
            try {
                $escola = LegacySchool::query()->create($attrs + [
                    'situacao_funcionamento' => 1,
                    'dependencia_administrativa' => 3,
                    'regulamentacao' => 1,
                ]);
            } catch (\Throwable) {
                $escola = LegacySchool::query()->create($attrs);
            }

            SchoolInep::query()->create([
                'cod_escola' => $escola->getKey(),
                'cod_escola_inep' => $inep,
                'nome_inep' => $def['nome'],
                'fonte' => 'LAGUNA_POC',
            ]);
            $criadas++;
        }

        return "criadas={$criadas} atualizadas={$existentes} total=".LegacySchool::query()->where('ativo', 1)->count();
    }

    private function clonarOfertaEscolar(): string
    {
        $modelo = $this->escolaModeloId();
        $ano = $this->ano();
        $escolas = DB::table('pmieducar.escola')->where('ativo', 1)->pluck('cod_escola');
        $copias = 0;

        foreach ($escolas as $codEscola) {
            $codEscola = (int) $codEscola;
            if ($codEscola === $modelo) {
                continue;
            }

            DB::table('pmieducar.escola_ano_letivo')->updateOrInsert(
                ['ref_cod_escola' => $codEscola, 'ano' => $ano],
                [
                    'ref_usuario_cad' => self::USUARIO_CAD,
                    'andamento' => 1,
                    'ativo' => 1,
                    'copia_dados_professor' => false,
                    'copia_dados_demais_servidores' => false,
                    'copia_turmas' => true,
                    'created_at' => now(),
                ]
            );

            $cursos = DB::table('pmieducar.escola_curso')
                ->where('ref_cod_escola', $modelo)
                ->where('ativo', 1)
                ->get();
            foreach ($cursos as $c) {
                DB::table('pmieducar.escola_curso')->updateOrInsert(
                    ['ref_cod_escola' => $codEscola, 'ref_cod_curso' => $c->ref_cod_curso],
                    [
                        'ref_usuario_cad' => self::USUARIO_CAD,
                        'ativo' => 1,
                        'data_cadastro' => now(),
                        'anos_letivos' => $c->anos_letivos ?? '{'.$ano.'}',
                    ]
                );
                $copias++;
            }

            $series = DB::table('pmieducar.escola_serie')
                ->where('ref_cod_escola', $modelo)
                ->where('ativo', 1)
                ->get();
            foreach ($series as $s) {
                DB::table('pmieducar.escola_serie')->updateOrInsert(
                    ['ref_cod_escola' => $codEscola, 'ref_cod_serie' => $s->ref_cod_serie],
                    [
                        'ref_usuario_cad' => self::USUARIO_CAD,
                        'ativo' => 1,
                        'data_cadastro' => now(),
                        'anos_letivos' => $s->anos_letivos ?? '{'.$ano.'}',
                    ]
                );
            }

            if (Schema::hasTable('pmieducar.escola_serie_disciplina')) {
                $disc = DB::table('pmieducar.escola_serie_disciplina')
                    ->where('ref_ref_cod_escola', $modelo)
                    ->get();
                foreach ($disc as $d) {
                    $row = (array) $d;
                    $row['ref_ref_cod_escola'] = $codEscola;
                    unset($row['updated_at']);
                    try {
                        DB::table('pmieducar.escola_serie_disciplina')->updateOrInsert(
                            [
                                'ref_ref_cod_escola' => $codEscola,
                                'ref_ref_cod_serie' => $d->ref_ref_cod_serie,
                                'ref_cod_disciplina' => $d->ref_cod_disciplina,
                            ],
                            $row
                        );
                    } catch (\Throwable) {
                        // ignora conflitos de colunas específicas do tenant
                    }
                }
            }
        }

        return "ofertas clonadas≈{$copias} · ano={$ano}";
    }

    private function garantirServidores(): string
    {
        $instituicao = $this->instituicaoId();
        $escolas = DB::table('pmieducar.escola')->where('ativo', 1)->pluck('cod_escola');
        $n = 0;
        $nomes = [
            'Ana Paula Souza', 'Bruno Henrique Lima', 'Carla Fernanda Dias', 'Diego Nunes',
            'Elena Martins', 'Fábio Rocha', 'Gina Alves', 'Hugo Prado', 'Iris Campos', 'João Vitor',
            'Karen Melo', 'Luis Felipe', 'Marina Costa', 'Nicolas Vieira', 'Olívia Santos',
        ];

        foreach ($escolas as $idx => $codEscola) {
            for ($k = 0; $k < 3; $k++) {
                $nome = $nomes[($idx * 3 + $k) % count($nomes)].' (E'.$codEscola.')';
                $cpf = (int) sprintf('42%09d', 100000 + ($idx * 10) + $k);
                $idpes = DB::table('cadastro.fisica')->where('cpf', $cpf)->value('idpes');
                if ($idpes) {
                    continue;
                }

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
                    'data_nasc' => '1985-01-15',
                    'sexo' => $k % 2 === 0 ? 'F' : 'M',
                    'cpf' => $cpf,
                    'ativo' => 1,
                    'operacao' => 'I',
                    'origem_gravacao' => 'M',
                    'data_cad' => now(),
                ]);
                DB::table('pmieducar.servidor')->updateOrInsert(
                    ['cod_servidor' => $idpes],
                    [
                        'ref_cod_instituicao' => $instituicao,
                        'carga_horaria' => 40,
                        'data_cadastro' => now(),
                        'ativo' => 1,
                    ]
                );

                if (Schema::hasTable('pmieducar.servidor_alocacao')) {
                    try {
                        $exists = DB::table('pmieducar.servidor_alocacao')
                            ->where('ref_cod_servidor', $idpes)
                            ->where('ref_cod_escola', $codEscola)
                            ->where('ano', $this->ano())
                            ->exists();
                        if (! $exists) {
                            DB::table('pmieducar.servidor_alocacao')->insert([
                                'ref_ref_cod_instituicao' => $instituicao,
                                'ref_cod_escola' => $codEscola,
                                'ref_cod_servidor' => $idpes,
                                'ref_usuario_cad' => self::USUARIO_CAD,
                                'carga_horaria' => 40,
                                'periodo' => 3,
                                'ano' => $this->ano(),
                                'data_admissao' => now()->toDateString(),
                                'data_cadastro' => now(),
                                'ativo' => 1,
                            ]);
                        }
                    } catch (\Throwable) {
                        // schema de alocação varia entre versões
                    }
                }
                $n++;
            }
        }

        return "servidores novos={$n} · total=".DB::table('pmieducar.servidor')->where('ativo', 1)->count();
    }

    private function garantirUsuarios(): string
    {
        $provisioner = app(MunicipalFuncionarioUserProvisioner::class);
        $instituicao = [$this->instituicaoId()];
        $todas = DB::table('pmieducar.escola')->where('ativo', 1)->orderBy('cod_escola')->pluck('cod_escola')->map(fn ($v) => (int) $v)->all();
        $primeira = $todas[0] ?? null;
        $segunda = $todas[1] ?? $primeira;

        $atores = [
            ['nome' => 'Admin Laguna SME', 'cpf' => '42091020001', 'tipo' => 1, 'escolas' => $todas, 'atual' => $primeira],
            ['nome' => 'Secretária Municipal Educação', 'cpf' => '42091020002', 'tipo' => 2, 'escolas' => $todas, 'atual' => $primeira],
            ['nome' => 'Diretor EMEF Anita', 'cpf' => '42091020003', 'tipo' => 3, 'escolas' => array_values(array_filter([$segunda])), 'atual' => $segunda],
            ['nome' => 'Coordenadora Pedagógica', 'cpf' => '42091020004', 'tipo' => 4, 'escolas' => array_values(array_filter([$primeira, $segunda])), 'atual' => $primeira],
            ['nome' => 'Professor Rede', 'cpf' => '42091020005', 'tipo' => 5, 'escolas' => array_values(array_filter([$primeira])), 'atual' => $primeira],
            ['nome' => 'Operador Transporte', 'cpf' => '42091020006', 'tipo' => 6, 'escolas' => $todas, 'atual' => $primeira],
            ['nome' => 'Operadora Alimentação', 'cpf' => '42091020007', 'tipo' => 7, 'escolas' => $todas, 'atual' => $primeira],
            ['nome' => 'Operador Almoxarifado', 'cpf' => '42091020008', 'tipo' => 8, 'escolas' => $todas, 'atual' => $primeira],
            ['nome' => 'Atendente Estudante', 'cpf' => '42091020009', 'tipo' => 9, 'escolas' => array_values(array_filter([$primeira, $segunda])), 'atual' => $primeira],
            ['nome' => 'Gestor Chamados SME', 'cpf' => '42091020010', 'tipo' => 10, 'escolas' => $todas, 'atual' => $primeira],
            ['nome' => 'Analista ACT/RH', 'cpf' => '42091020011', 'tipo' => 11, 'escolas' => $todas, 'atual' => $primeira],
            ['nome' => 'Organizador Horários', 'cpf' => '42091020012', 'tipo' => 12, 'escolas' => array_values(array_filter([$primeira, $segunda])), 'atual' => $primeira],
            // Multi-escola: diretor com 3 escolas
            ['nome' => 'Diretor Polo Norte', 'cpf' => '42091020013', 'tipo' => 3, 'escolas' => array_slice($todas, 0, 3), 'atual' => $primeira],
            ['nome' => 'Analista Busca Ativa SME', 'cpf' => '42091020014', 'tipo' => 14, 'escolas' => $todas, 'atual' => $primeira],
            ['nome' => 'Assistente Social Rede', 'cpf' => '42091020015', 'tipo' => 15, 'escolas' => $todas, 'atual' => $primeira],
        ];

        $novos = 0;
        foreach ($atores as $a) {
            $criou = $provisioner->upsert(
                [
                    'nome' => $a['nome'],
                    'nascimento' => '20/05/1980',
                    'sexo' => str_contains(mb_strtolower($a['nome']), 'a ') || str_ends_with($a['nome'], 'a') ? 'F' : 'M',
                    'cpf' => $a['cpf'],
                    'email' => 'laguna.'.$a['cpf'].'@sme.laguna.sc.gov.br',
                ],
                $a['tipo'],
                $instituicao,
                $a['escolas'],
                $a['atual']
            );
            if ($criou) {
                $novos++;
            }
        }

        return "usuários novos/sincronizados · novos={$novos} · login=CPF · senha=CPF · total_ativos="
            .DB::table('pmieducar.usuario')->where('ativo', 1)->count();
    }

    private function povoarModulos(): string
    {
        $escolas = DB::table('pmieducar.escola as e')
            ->join('cadastro.juridica as j', 'j.idpes', '=', 'e.ref_idpes')
            ->where('e.ativo', 1)
            ->get(['e.cod_escola', 'j.fantasia']);
        $n = 0;

        if (Schema::hasTable('alimentacao.cardapio_dia')) {
            foreach ($escolas as $e) {
                DB::table('alimentacao.cardapio_dia')->updateOrInsert(
                    ['data' => now()->toDateString(), 'escola' => $e->fantasia],
                    [
                        'refeicoes' => json_encode([
                            ['refeicao' => 'cafe', 'itens' => ['pão', 'leite', 'fruta']],
                            ['refeicao' => 'almoco', 'itens' => ['arroz', 'feijão', 'carne', 'salada', 'suco']],
                        ], JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $n++;
            }
        }

        foreach (['almoxarifado', 'atendimento', 'solicitacao_servicos', 'horario_automatico', 'act', 'eventos_formacao', 'biblioteca'] as $schema) {
            if (! Schema::hasTable("{$schema}.poc_registro")) {
                continue;
            }
            foreach ($escolas as $e) {
                DB::table("{$schema}.poc_registro")->insert([
                    'tipo' => 'escola',
                    'titulo' => $e->fantasia,
                    'meta' => 'cod_escola='.$e->cod_escola,
                    'payload' => json_encode(['cod_escola' => $e->cod_escola], JSON_UNESCAPED_UNICODE),
                ]);
                $n++;
            }
        }

        return "registros módulo≈{$n}";
    }

    private function garantirIdentidade(): string
    {
        DB::table('pmieducar.instituicao')->orderBy('cod_instituicao')->update([
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

        return 'Prefeitura Municipal de Laguna / SME · IBGE 4209102';
    }

    private function resumo(): string
    {
        return sprintf(
            'escolas=%d turmas=%d alunos=%d servidores=%d usuarios=%d cursos=%d series=%d',
            DB::table('pmieducar.escola')->where('ativo', 1)->count(),
            DB::table('pmieducar.turma')->where('ativo', 1)->count(),
            DB::table('pmieducar.aluno')->where('ativo', 1)->count(),
            DB::table('pmieducar.servidor')->where('ativo', 1)->count(),
            DB::table('pmieducar.usuario')->where('ativo', 1)->count(),
            DB::table('pmieducar.curso')->where('ativo', 1)->count(),
            DB::table('pmieducar.serie')->where('ativo', 1)->count(),
        );
    }
}
