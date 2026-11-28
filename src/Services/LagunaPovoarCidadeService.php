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

    /** Centro urbano de Laguna/SC (referência do mapa da pré-matrícula). */
    private const MAP_LAT = -28.4829;

    private const MAP_LNG = -48.7811;

    /**
     * Rede escolar Laguna/SC (IBGE 4209102) com endereço e coordenadas para o mapa.
     *
     * @var list<array{
     *   nome:string,sigla:string,zona:int,perfil:string,
     *   lat:float,lng:float,fone:string,
     *   logradouro:string,numero:string,bairro:string,cep:string
     * }>
     */
    private const ESCOLAS = [
        ['nome' => 'EMEF Prefeito Júlio Garcia — Laguna', 'sigla' => 'EJG', 'zona' => 1, 'perfil' => 'fundamental', 'lat' => -28.4829, 'lng' => -48.7811, 'fone' => '36441001', 'logradouro' => 'Rua Conselheiro Trajano', 'numero' => '320', 'bairro' => 'Centro', 'cep' => '88790000'],
        ['nome' => 'EMEF Anita Garibaldi', 'sigla' => 'EAG', 'zona' => 1, 'perfil' => 'fundamental', 'lat' => -28.4855, 'lng' => -48.7750, 'fone' => '36441002', 'logradouro' => 'Rua Jerônimo Coelho', 'numero' => '145', 'bairro' => 'Centro', 'cep' => '88790000'],
        ['nome' => 'EMEF Domingos Fernandes de Oliveira', 'sigla' => 'EDF', 'zona' => 1, 'perfil' => 'fundamental', 'lat' => -28.4780, 'lng' => -48.7880, 'fone' => '36441003', 'logradouro' => 'Avenida João Pinho', 'numero' => '890', 'bairro' => 'Mar Grosso', 'cep' => '88790000'],
        ['nome' => 'EMEF Professora Rosa Maria', 'sigla' => 'ERM', 'zona' => 1, 'perfil' => 'fundamental', 'lat' => -28.4900, 'lng' => -48.7700, 'fone' => '36441004', 'logradouro' => 'Rua Vidal Ramos', 'numero' => '210', 'bairro' => 'Magalhães', 'cep' => '88790000'],
        ['nome' => 'EMEF Marcolino Martins Cabral', 'sigla' => 'EMC', 'zona' => 1, 'perfil' => 'fundamental_medio', 'lat' => -28.4750, 'lng' => -48.7950, 'fone' => '36441005', 'logradouro' => 'Rua Coronel Fernandes Martins', 'numero' => '55', 'bairro' => 'Progresso', 'cep' => '88790000'],
        ['nome' => 'EMEI Pequeno Príncipe', 'sigla' => 'EPP', 'zona' => 1, 'perfil' => 'infantil', 'lat' => -28.4880, 'lng' => -48.7650, 'fone' => '36441006', 'logradouro' => 'Rua São Pedro', 'numero' => '78', 'bairro' => 'Magalhães', 'cep' => '88790000'],
        ['nome' => 'EMEI Cantinho do Saber', 'sigla' => 'ECS', 'zona' => 1, 'perfil' => 'infantil', 'lat' => -28.4700, 'lng' => -48.7850, 'fone' => '36441007', 'logradouro' => 'Rua das Nações', 'numero' => '112', 'bairro' => 'Mar Grosso', 'cep' => '88790000'],
        ['nome' => 'EMEF Marechal Deodoro', 'sigla' => 'EMD', 'zona' => 1, 'perfil' => 'fundamental', 'lat' => -28.4920, 'lng' => -48.7920, 'fone' => '36441008', 'logradouro' => 'Rua Marechal Deodoro', 'numero' => '430', 'bairro' => 'Centro', 'cep' => '88790000'],
        ['nome' => 'CEIM Flor do Mar', 'sigla' => 'CFM', 'zona' => 1, 'perfil' => 'infantil_aee', 'lat' => -28.4680, 'lng' => -48.7720, 'fone' => '36441009', 'logradouro' => 'Rua Beira Mar', 'numero' => '250', 'bairro' => 'Mar Grosso', 'cep' => '88790000'],
        ['nome' => 'EMEF João da Cruz e Souza', 'sigla' => 'EJC', 'zona' => 1, 'perfil' => 'fundamental', 'lat' => -28.4950, 'lng' => -48.7780, 'fone' => '36441010', 'logradouro' => 'Rua Cruz e Souza', 'numero' => '95', 'bairro' => 'Vila Nova', 'cep' => '88790000'],
        ['nome' => 'EMEF Vila Nova', 'sigla' => 'EVN', 'zona' => 1, 'perfil' => 'fundamental', 'lat' => -28.4600, 'lng' => -48.7600, 'fone' => '36441011', 'logradouro' => 'Rua das Acácias', 'numero' => '180', 'bairro' => 'Vila Nova', 'cep' => '88790000'],
        ['nome' => 'EMEF Ribeirão Pequeno', 'sigla' => 'ERP', 'zona' => 2, 'perfil' => 'fundamental_rural', 'lat' => -28.5200, 'lng' => -48.8200, 'fone' => '36441012', 'logradouro' => 'Estrada Geral Ribeirão Pequeno', 'numero' => 's/n', 'bairro' => 'Ribeirão Pequeno', 'cep' => '88790000'],
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
        $passos[] = $this->chamar('transporte:passagem-empenho-demo', [], 'Passagem/empenho transporte');
        $passos[] = $this->chamar('alimentacao:demo', [], 'Cardápio alimentação (base)');
        $passos[] = $this->chamar('busca-ativa:demo', ['--faltas' => 28, '--force' => true], 'Busca Ativa multi-etapa / atores');
        $passos[] = $this->chamar('solicitacao:demo', ['--force' => true], 'Chamados multi-etapa / atores');
        $passos[] = $this->chamar('atendimento:demo', ['--force' => true], 'Fila atendimento multi-situação');
        $passos[] = $this->chamar('eventos:demo', ['--force' => true], 'Eventos, inscrições e certificados');
        $passos[] = $this->chamar('biblioteca:demo', ['--force' => true], 'Acervo e empréstimos');
        $passos[] = $this->chamar('almoxarifado:demo', ['--force' => true], 'Itens e pedidos do almoxarifado');
        $passos[] = $this->chamar('horario:demo', ['--force' => true], 'Quadro de horários demo');
        $passos[] = $this->chamar('act:demo', ['--force' => true], 'Processos seletivos ACT diversos');
        $passos[] = $this->chamar('rh:demo', ['--force' => true], 'RH educacional (lotação / integrações)');
        $passos = array_merge($passos, (new LagunaCargaCenariosService)->executar());
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
        $r = \iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz::sincronizar();

        // Secretário SME: além dos módulos Buriti, herda menus núcleo do i-Educar
        // (Cadastros, Escola, etc.) — sem Ambiente Laguna / flags de módulos.
        $this->concederMenusNucleoAoSecretario();

        return $r['tipos'].' perfis · '.$r['acl'].' ACL · '.$r['revogados'].' revogadas';
    }

    private function concederMenusNucleoAoSecretario(): void
    {
        $governados = \iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz::menusGovernados();
        $menus = DB::table('menus')
            ->where('active', true)
            ->whereNotNull('process')
            ->when($governados !== [], fn ($q) => $q->whereNotIn('id', $governados))
            ->pluck('id');

        foreach ($menus as $menuId) {
            DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                [
                    'ref_cod_tipo_usuario' => \iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz::TIPO_SECRETARIO,
                    'menu_id' => $menuId,
                ],
                ['visualiza' => 1, 'cadastra' => 1, 'exclui' => 0],
            );
        }
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
                $escolaId = (int) $ja->cod_escola;
                $idpes = LegacySchool::query()->whereKey($escolaId)->value('ref_idpes');
                LegacyOrganization::query()->whereKey($idpes)->update(['fantasia' => $def['nome']]);
                LegacyPerson::query()->whereKey($idpes)->update([
                    'nome' => $def['nome'],
                    'email' => strtolower($def['sigla']).'@sme.laguna.sc.gov.br',
                ]);
                LegacySchool::query()->whereKey($escolaId)->update([
                    'sigla' => $def['sigla'],
                    'ativo' => 1,
                    'zona_localizacao' => $def['zona'],
                    'latitude' => (string) $def['lat'],
                    'longitude' => (string) $def['lng'],
                ]);
                $this->garantirTelefoneEscola((int) $idpes, $def['fone']);
                $this->garantirEnderecoEscola((int) $idpes, $def);

                continue;
            }

            // Reaproveita a escola modelo no primeiro slot se ainda não tiver INEP Laguna.
            if ($i === 0) {
                $modelo = LegacySchool::query()->where('ativo', 1)->orderBy('cod_escola')->first();
                if ($modelo) {
                    $cnpjLaguna = sprintf('42%012d', 9102000 + $i);
                    LegacyPerson::query()->whereKey($modelo->ref_idpes)->update([
                        'nome' => $def['nome'],
                        'email' => strtolower($def['sigla']).'@sme.laguna.sc.gov.br',
                    ]);
                    // Troca o CNPJ da escola modelo (990…191) para não bloquear reexecução do ieducar:setup.
                    LegacyOrganization::query()->whereKey($modelo->ref_idpes)->update([
                        'fantasia' => $def['nome'],
                        'cnpj' => $cnpjLaguna,
                    ]);
                    $modelo->update([
                        'sigla' => $def['sigla'],
                        'zona_localizacao' => $def['zona'],
                        'ativo' => 1,
                        'latitude' => (string) $def['lat'],
                        'longitude' => (string) $def['lng'],
                    ]);
                    SchoolInep::query()->updateOrCreate(
                        ['cod_escola' => $modelo->getKey()],
                        ['cod_escola_inep' => $inep, 'nome_inep' => $def['nome'], 'fonte' => 'LAGUNA_POC']
                    );
                    $this->garantirTelefoneEscola((int) $modelo->ref_idpes, $def['fone']);
                    $this->garantirEnderecoEscola((int) $modelo->ref_idpes, $def);
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
                'latitude' => (string) $def['lat'],
                'longitude' => (string) $def['lng'],
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
            $this->garantirTelefoneEscola((int) $pessoa->getKey(), $def['fone']);
            $this->garantirEnderecoEscola((int) $pessoa->getKey(), $def);
            $criadas++;
        }

        return "criadas={$criadas} atualizadas={$existentes} total=".LegacySchool::query()->where('ativo', 1)->count();
    }

    private function garantirTelefoneEscola(int $idpes, string $fone): void
    {
        if ($idpes <= 0 || $fone === '') {
            return;
        }

        DB::table('cadastro.fone_pessoa')->updateOrInsert(
            [
                'idpes' => $idpes,
                'tipo' => 1,
            ],
            [
                'ddd' => 48,
                'fone' => (int) $fone,
                'origem_gravacao' => 'M',
                'idpes_cad' => self::USUARIO_CAD,
                'data_cad' => now(),
                'operacao' => 'I',
            ]
        );
    }

    /**
     * @param  array{
     *   logradouro:string,numero:string,bairro:string,cep:string,lat:float,lng:float
     * }  $def
     */
    private function garantirEnderecoEscola(int $idpes, array $def): void
    {
        if ($idpes <= 0 || ! Schema::hasTable('places') || ! Schema::hasTable('person_has_place')) {
            return;
        }

        $cityId = $this->cidadeLagunaId();
        if ($cityId === null) {
            return;
        }

        $postal = (int) preg_replace('/\D+/', '', (string) ($def['cep'] ?? '88790000'));
        $placeId = DB::table('person_has_place')
            ->where('person_id', $idpes)
            ->where('type', 1)
            ->value('place_id');

        $dados = [
            'city_id' => $cityId,
            'address' => (string) $def['logradouro'],
            'number' => (string) ($def['numero'] ?? ''),
            'complement' => null,
            'neighborhood' => (string) $def['bairro'],
            'postal_code' => $postal > 0 ? $postal : 88790000,
            'latitude' => (string) $def['lat'],
            'longitude' => (string) $def['lng'],
            'updated_at' => now(),
        ];

        if ($placeId) {
            DB::table('places')->where('id', $placeId)->update($dados);
        } else {
            $placeId = DB::table('places')->insertGetId($dados + ['created_at' => now()]);
            DB::table('person_has_place')->updateOrInsert(
                ['person_id' => $idpes, 'type' => 1],
                [
                    'place_id' => $placeId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function cidadeLagunaId(): ?int
    {
        if (! Schema::hasTable('cities')) {
            return null;
        }

        $id = DB::table('cities')->where('ibge_code', 4209102)->value('id');
        if ($id) {
            return (int) $id;
        }

        $id = DB::table('cities')->whereRaw('lower(name) = ?', ['laguna'])->value('id');

        return $id ? (int) $id : null;
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

        $mapa = [
            'prematricula.city' => ['value' => 'Laguna', 'type' => 'string'],
            'prematricula.state' => ['value' => 'SC', 'type' => 'string'],
            'prematricula.ibge_codes' => ['value' => '4209102', 'type' => 'string'],
            'prematricula.map.lat' => ['value' => (string) self::MAP_LAT, 'type' => 'float'],
            'prematricula.map.lng' => ['value' => (string) self::MAP_LNG, 'type' => 'float'],
            'prematricula.map.zoom' => ['value' => '13', 'type' => 'integer'],
            'prematricula.slogan' => ['value' => 'Prefeitura Municipal de ', 'type' => 'string'],
        ];

        foreach ($mapa as $key => $meta) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $meta['value'],
                    'type' => $meta['type'],
                    'description' => $key,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->garantirSetorVagasLaguna();

        return 'Prefeitura Municipal de Laguna / SME · IBGE 4209102 · mapa '.$mapa['prematricula.map.lat']['value'].','.$mapa['prematricula.map.lng']['value'];
    }

    /**
     * Preenche o setor do módulo «vagas» nas diretivas Laguna (editável no painel do módulo).
     * Não sobrescreve campos já preenchidos pela cidade.
     */
    private function garantirSetorVagasLaguna(): void
    {
        if (! class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class)) {
            return;
        }

        $cfg = \iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class;
        $salvas = $cfg::diretivasSalvas('vagas');
        $padrao = [
            'setor_nome' => 'Setor de Vagas / Matrícula — SME Laguna',
            'setor_endereco' => 'Secretaria Municipal de Educação · Laguna/SC',
            'setor_horario' => 'Segunda a sexta, 8h–12h e 13h30–17h',
            'setor_telefone' => '4836441000',
            'setor_email' => 'vagas@sme.laguna.sc.gov.br',
            'setor_observacao' => 'Traga documento da criança e do responsável. Guarde o protocolo da inscrição digital.',
            'portal_externo' => true,
            'aparecer_na_home' => true,
        ];
        $mudou = false;
        foreach ($padrao as $chave => $valor) {
            $atual = $salvas[$chave] ?? null;
            $vazio = $atual === null || $atual === '' || $atual === false;
            if ($vazio) {
                $salvas[$chave] = $valor;
                $mudou = true;
            }
        }
        if (! $mudou) {
            return;
        }

        $json = json_encode($salvas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return;
        }

        DB::table('settings')->updateOrInsert(
            ['key' => $cfg::settingDiretivasKey('vagas')],
            [
                'value' => $json,
                'type' => 'string',
                'description' => 'Diretivas do módulo Pré-matrícula / vagas',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
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
