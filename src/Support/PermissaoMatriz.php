<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use App\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ACL Laguna: tipos de usuário × módulos Buriti / Portabilis.
 *
 * Regras práticas:
 * - Só o tipo Administrador (cod 1) acessa Ambiente Laguna e Configurações → Módulos.
 * - Operadores recebem apenas os processos do seu domínio.
 * - Secretário SME recebe a rede, sem ferramentas de ambiente/setup.
 *
 * @phpstan-type Acl array{visualiza:int,cadastra:int,exclui:int}
 * @phpstan-type Perfil array{
 *   cod:int,
 *   nome:string,
 *   nivel:int,
 *   desc:string,
 *   modulos:list<string>|'*',
 *   excluir_modulos?:list<string>
 * }
 */
final class PermissaoMatriz
{
    public const int TIPO_ADMIN = 1;

    public const int TIPO_SECRETARIO = 2;

    /** Espelha LegacyUserType::LEVEL_* sem acoplar o smoke test ao app. */
    private const int NIVEL_ADMIN = 1;

    private const int NIVEL_INSTITUCIONAL = 2;

    private const int NIVEL_ESCOLA = 4;

    private const int NIVEL_BIBLIOTECA = 8;

    /**
     * Catálogo de tipos Laguna (sentido prático na rede).
     *
     * @return list<Perfil>
     */
    public static function perfis(): array
    {
        return [
            [
                'cod' => 1,
                'nome' => 'Administrador',
                'nivel' => self::NIVEL_ADMIN,
                'desc' => 'Acesso total: Ambiente Laguna, flags de módulos e toda a rede',
                'modulos' => '*',
            ],
            [
                'cod' => 2,
                'nome' => 'Secretário SME',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Operação da rede municipal — sem Ambiente Laguna / setup / flags de módulos',
                'modulos' => '*',
                'excluir_modulos' => ['laguna-ambiente', 'modulos-config'],
            ],
            [
                'cod' => 3,
                'nome' => 'Diretor escolar',
                // Unidade: acompanha merenda, estoque da escola, chamados e pedagógico.
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Gestão da unidade escolar — pedagógico, merenda, estoque local, chamados e i-Diário',
                'modulos' => ['solicitacao', 'atendimento', 'horario', 'biblioteca', 'busca-ativa', 'alimentacao', 'almoxarifado', 'eventos', 'relatorios', 'idiario'],
            ],
            [
                'cod' => 4,
                'nome' => 'Coordenador pedagógico',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Coordenação pedagógica — horários, formação, acolhimento, busca ativa, relatórios e i-Diário',
                'modulos' => ['horario', 'eventos', 'atendimento', 'busca-ativa', 'relatorios', 'idiario'],
            ],
            [
                'cod' => 5,
                'nome' => 'Professor',
                'nivel' => self::NIVEL_ESCOLA,
                'desc' => 'Sala de aula — i-Diário, consulta a horários/eventos/biblioteca e relatórios básicos',
                'modulos' => ['horario', 'eventos', 'biblioteca', 'relatorios', 'idiario'],
            ],
            [
                'cod' => 6,
                'nome' => 'Operador Transporte',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'SME/transporte — rotas, frota e alunos transportados',
                'modulos' => ['transporte'],
            ],
            [
                'cod' => 7,
                'nome' => 'Operador Alimentação',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'SME/PNAE — cardápio, remessa, balanço e restrições',
                'modulos' => ['alimentacao'],
            ],
            [
                'cod' => 8,
                'nome' => 'Operador Almoxarifado',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'SME/estoque — itens, saldos, pedidos e movimentação',
                'modulos' => ['almoxarifado'],
            ],
            [
                'cod' => 9,
                'nome' => 'Atendimento ao estudante',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Acolhimento na rede — fila/agenda e ponte com Busca Ativa',
                'modulos' => ['atendimento', 'busca-ativa'],
            ],
            [
                'cod' => 10,
                'nome' => 'Solicitação de serviços',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Trâmite de chamados escola ↔ SME',
                'modulos' => ['solicitacao'],
            ],
            [
                'cod' => 11,
                'nome' => 'RH / ACT',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Gestão de pessoas — servidores, ACT, lotação e formação',
                'modulos' => ['servidor', 'act', 'horario', 'eventos'],
            ],
            [
                'cod' => 12,
                'nome' => 'Quadro de horários',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Especialista em geração/validação do quadro de horários',
                'modulos' => ['horario'],
            ],
            [
                'cod' => 13,
                'nome' => 'Operador Eventos',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Formação continuada — eventos, presença e certificados',
                'modulos' => ['eventos'],
            ],
            [
                'cod' => 14,
                'nome' => 'Busca Ativa',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Infrequência e reintegração — com encaminhamento ao atendimento',
                'modulos' => ['busca-ativa', 'atendimento'],
            ],
            [
                'cod' => 15,
                'nome' => 'Bibliotecário',
                'nivel' => self::NIVEL_BIBLIOTECA,
                'desc' => 'Acervo e empréstimos da biblioteca escolar',
                'modulos' => ['biblioteca'],
            ],
            [
                'cod' => 16,
                'nome' => 'Pré-matrícula / Vagas',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'SME/oferta — vagas, inscrição e acompanhamento da pré-matrícula',
                'modulos' => ['vagas'],
            ],
            [
                'cod' => 17,
                'nome' => 'Apps / governança API',
                'nivel' => self::NIVEL_INSTITUCIONAL,
                'desc' => 'Governança técnica dos apps família/professor (Sanctum)',
                'modulos' => ['apps-api'],
            ],
        ];
    }

    /**
     * Módulos transversais puxados pelo módulo de origem (documentos / downloads).
     * Quem opera o módulo-origem ganha ACL da dependência; o card /exportacao no
     * painel fica só para Admin / Secretário SME (hub de config).
     *
     * @return array<string, list<string>>
     */
    public static function dependenciasModulos(): array
    {
        return [
            'transporte' => ['exportacao'],
            'alimentacao' => ['exportacao'],
            'almoxarifado' => ['exportacao'],
            'biblioteca' => ['exportacao'],
            'atendimento' => ['exportacao'],
            'solicitacao' => ['exportacao'],
            'horario' => ['exportacao'],
            'eventos' => ['exportacao'],
            'act' => ['exportacao'],
            'busca-ativa' => ['exportacao'],
            'servidor' => ['exportacao'],
            'relatorios' => ['exportacao'],
            'vagas' => ['exportacao'],
            'censo' => ['exportacao'],
        ];
    }

    /**
     * Hub /exportacao no painel: só governança da rede (config/demo).
     * Operadores exportam pelas telas do módulo de origem.
     */
    public static function exportacaoTemCardPainel(mixed $user): bool
    {
        if ($user === null) {
            return false;
        }
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        $tipo = (int) ($user->ref_cod_tipo_usuario ?? 0);

        return $tipo === self::TIPO_ADMIN || $tipo === self::TIPO_SECRETARIO;
    }

    /**
     * Faixas de processo por módulo (menus Buriti + pré-matrícula + Ambiente Laguna).
     *
     * @return array<string, list<int>>
     */
    public static function faixasModulo(): array
    {
        return [
            'transporte' => self::faixa(880100, 880199),
            'alimentacao' => self::faixa(881100, 881199),
            'almoxarifado' => self::faixa(881400, 881499),
            // Exportação 881500–881509 · Biblioteca 881510+ (não misturar).
            'exportacao' => self::faixa(881500, 881509),
            'biblioteca' => self::faixa(881510, 881599),
            'atendimento' => self::faixa(881600, 881699),
            'solicitacao' => self::faixa(881700, 881799),
            'horario' => self::faixa(881800, 881899),
            'eventos' => self::faixa(881900, 881999),
            'act' => self::faixa(882000, 882099),
            'apps-api' => self::faixa(882100, 882199),
            'busca-ativa' => self::faixa(882200, 882299),
            'servidor' => self::faixa(883000, 883099),
            'relatorios' => self::faixa(884000, 884099),
            'idiario' => [Processos::IDIARIO],
            'vagas' => [56, 5655, 5656],
            'censo' => [9998848],
            'laguna-ambiente' => [
                Processos::RAIZ,
                Processos::PAINEL,
                Processos::CHECK,
                Processos::SETUP,
                Processos::TEMA,
                Processos::USUARIOS,
            ],
            'modulos-config' => [Processos::MODULOS],
        ];
    }

    /**
     * @return Acl
     */
    public static function paraNivel(int $nivel, ?int $codTipo = null): array
    {
        if ($codTipo === 5) {
            return ['visualiza' => 1, 'cadastra' => 0, 'exclui' => 0];
        }

        return match ($nivel) {
            self::NIVEL_ADMIN => [
                'visualiza' => 1,
                'cadastra' => 1,
                'exclui' => ($codTipo !== null && $codTipo === self::TIPO_ADMIN) ? 1 : 0,
            ],
            self::NIVEL_INSTITUCIONAL => [
                'visualiza' => 1,
                'cadastra' => 1,
                'exclui' => 0,
            ],
            self::NIVEL_ESCOLA,
            self::NIVEL_BIBLIOTECA => [
                'visualiza' => 1,
                'cadastra' => $codTipo === 15 ? 1 : 0,
                'exclui' => 0,
            ],
            default => ['visualiza' => 1, 'cadastra' => 0, 'exclui' => 0],
        };
    }

    /**
     * Garante tipos Laguna e reaplica ACL Buriti conforme a matriz.
     *
     * Operadores (≠ admin) têm menu_tipo_usuario zerado e regravado — remove
     * poluição legada (ex.: tipo 8 com RH/vagas/matrícula).
     *
     * @return array{tipos:int,acl:int,revogados:int}
     */
    public static function sincronizar(): array
    {
        if (! Schema::hasTable('pmieducar.tipo_usuario') || ! Schema::hasTable('menus')) {
            return ['tipos' => 0, 'acl' => 0, 'revogados' => 0];
        }

        $tipos = 0;
        foreach (self::perfis() as $p) {
            DB::table('pmieducar.tipo_usuario')->updateOrInsert(
                ['cod_tipo_usuario' => $p['cod']],
                [
                    'nm_tipo' => $p['nome'],
                    'descricao' => $p['desc'],
                    'nivel' => $p['nivel'],
                    'ref_funcionario_cad' => 1,
                    'data_cadastro' => now(),
                    'ativo' => 1,
                ]
            );
            $tipos++;
        }

        $revogados = 0;
        $acl = 0;
        foreach (self::perfis() as $p) {
            $cod = (int) $p['cod'];
            if ($cod !== self::TIPO_ADMIN) {
                $revogados += (int) DB::table('pmieducar.menu_tipo_usuario')
                    ->where('ref_cod_tipo_usuario', $cod)
                    ->delete();
            }
            $acl += self::conceder(self::processosDoPerfil($p), [$cod]);
        }

        // Secretário SME: núcleo legado (Escola, Pessoas, …) fora das faixas Buriti.
        $acl += self::concederNucleoSecretario();

        // Ambiente Laguna + flags: só admin.
        $revogados += self::revogarForaDe(
            array_merge(
                self::faixasModulo()['laguna-ambiente'],
                self::faixasModulo()['modulos-config'],
            ),
            [self::TIPO_ADMIN]
        );

        return ['tipos' => $tipos, 'acl' => $acl, 'revogados' => $revogados];
    }

    /**
     * Concede ao Secretário SME os menus ativos fora da governança Buriti
     * (Escola, Pessoas, Educacenso, etc.).
     */
    public static function concederNucleoSecretario(): int
    {
        if (! Schema::hasTable('pmieducar.menu_tipo_usuario') || ! Schema::hasTable('menus')) {
            return 0;
        }

        $governados = self::menusGovernados();
        $menus = DB::table('menus')
            ->where('active', true)
            ->whereNotNull('process')
            ->when($governados !== [], static fn ($q) => $q->whereNotIn('id', $governados))
            // Configurações / tools / faixas Buriti (já cobertas por menusGovernados + sincronizar).
            ->whereNotIn('process', [25, 3000, 999873, 999910])
            ->whereNotBetween('process', [884000, 884199])
            ->pluck('id');

        $n = 0;
        foreach ($menus as $menuId) {
            DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                [
                    'ref_cod_tipo_usuario' => self::TIPO_SECRETARIO,
                    'menu_id' => $menuId,
                ],
                ['visualiza' => 1, 'cadastra' => 1, 'exclui' => 0],
            );
            $n++;
        }

        return $n;
    }

    /**
     * Usuário pode acessar o módulo do catálogo (algum process da faixa/ACL).
     */
    public static function usuarioPodeAcessarModulo(mixed $user, string $moduloId): bool
    {
        if ($user === null) {
            return false;
        }
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }
        if ((int) ($user->ref_cod_tipo_usuario ?? 0) === self::TIPO_ADMIN) {
            return true;
        }

        $moduloId = trim($moduloId);
        if ($moduloId === '' || in_array($moduloId, ['laguna-ambiente', 'modulos-config'], true)) {
            return false;
        }

        // Dependência transversal: quem tem o módulo de origem acessa a exportação.
        if ($moduloId === 'exportacao') {
            foreach (array_keys(self::dependenciasModulos()) as $origem) {
                if (self::usuarioPodeAcessarModuloDireto($user, $origem)) {
                    return true;
                }
            }
        }

        return self::usuarioPodeAcessarModuloDireto($user, $moduloId);
    }

    /**
     * ACL direta do módulo (sem expandir dependências reversas).
     */
    private static function usuarioPodeAcessarModuloDireto(mixed $user, string $moduloId): bool
    {
        $processos = self::faixasModulo()[$moduloId] ?? null;
        if ($processos === null) {
            foreach (CatalogoModulos::todos() as $mod) {
                if ((string) ($mod['id'] ?? '') === $moduloId) {
                    $processos = array_map('intval', $mod['processos'] ?? []);
                    break;
                }
            }
        }
        $processos = array_values(array_filter(
            array_map('intval', $processos ?? []),
            static fn (int $p): bool => $p > 0
        ));
        if ($processos === []) {
            return false;
        }

        try {
            if (method_exists($user, 'can')) {
                foreach ($processos as $processo) {
                    if ($user->can('view', $processo)) {
                        return true;
                    }
                }

                return false;
            }

            $menuIds = Menu::query()->whereIn('process', $processos)->pluck('id')->all();
            if ($menuIds === []) {
                return false;
            }
            $tipo = (int) ($user->ref_cod_tipo_usuario ?? 0);
            if ($tipo <= 0) {
                return false;
            }

            return DB::table('pmieducar.menu_tipo_usuario')
                ->where('ref_cod_tipo_usuario', $tipo)
                ->whereIn('menu_id', $menuIds)
                ->where('visualiza', 1)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Expande módulos do perfil com dependências transversais (ex.: almox → exportação).
     *
     * @param  list<string>|'*'  $modulos
     * @param  list<string>  $excluir
     * @return list<string>|'*'
     */
    public static function modulosComDependencias(array|string $modulos, array $excluir = []): array|string
    {
        if ($modulos === '*') {
            return '*';
        }

        $deps = self::dependenciasModulos();
        $out = [];
        $fila = array_values($modulos);
        while ($fila !== []) {
            $mod = array_shift($fila);
            if (! is_string($mod) || $mod === '' || in_array($mod, $excluir, true)) {
                continue;
            }
            if (in_array($mod, $out, true)) {
                continue;
            }
            $out[] = $mod;
            foreach ($deps[$mod] ?? [] as $dep) {
                if (! in_array($dep, $out, true) && ! in_array($dep, $excluir, true)) {
                    $fila[] = $dep;
                }
            }
        }

        return $out;
    }

    /**
     * @param  Perfil  $perfil
     * @return list<int>
     */
    public static function processosDoPerfil(array $perfil): array
    {
        $faixas = self::faixasModulo();
        $excluir = $perfil['excluir_modulos'] ?? [];
        $modulos = self::modulosComDependencias($perfil['modulos'], $excluir);

        if ($modulos === '*') {
            $ids = [];
            foreach ($faixas as $chave => $processos) {
                if (in_array($chave, $excluir, true)) {
                    continue;
                }
                foreach ($processos as $p) {
                    $ids[] = $p;
                }
            }

            return array_values(array_unique($ids));
        }

        $ids = [];
        foreach ($modulos as $mod) {
            foreach ($faixas[$mod] ?? [] as $p) {
                $ids[] = $p;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Menus cujo process está sob governança da matriz Laguna/Buriti.
     *
     * @return list<int>
     */
    public static function menusGovernados(): array
    {
        $processos = [];
        foreach (self::faixasModulo() as $lista) {
            foreach ($lista as $p) {
                $processos[] = $p;
            }
        }
        $processos = array_values(array_unique($processos));

        // Também captura folhas criadas na faixa numérica (ex.: 880110) mesmo fora da lista explícita de vagas/censo.
        $ids = Menu::query()
            ->where(function ($q) use ($processos): void {
                $q->whereIn('process', $processos)
                    ->orWhere(function ($r): void {
                        $r->whereBetween('process', [880100, 883099])
                            ->orWhereBetween('process', [884000, 884199])
                            ->orWhereBetween('process', [889900, 889999]);
                    });
            })
            ->pluck('id')
            ->all();

        return array_map('intval', $ids);
    }

    /**
     * Concede ACL só aos tipos listados (ou a todos ativos se $tiposCodigos for null).
     *
     * @param  list<int>  $processos
     * @param  list<int>|null  $tiposCodigos
     * @param  list<int>  $processosSomenteVisualiza  força cadastra/exclui=0
     */
    public static function conceder(
        array $processos,
        ?array $tiposCodigos = null,
        array $processosSomenteVisualiza = [],
    ): int {
        $q = DB::table('pmieducar.tipo_usuario')->where('ativo', 1);
        if ($tiposCodigos !== null) {
            $q->whereIn('cod_tipo_usuario', $tiposCodigos);
        }
        $tipos = $q->get(['cod_tipo_usuario', 'nivel']);
        $n = 0;

        foreach ($tipos as $tipo) {
            $base = self::paraNivel((int) $tipo->nivel, (int) $tipo->cod_tipo_usuario);
            foreach ($processos as $processo) {
                $menuId = Menu::query()->where('process', $processo)->value('id');
                if ($menuId === null) {
                    continue;
                }
                $acl = $base;
                if (in_array($processo, $processosSomenteVisualiza, true)) {
                    $acl['cadastra'] = 0;
                    $acl['exclui'] = 0;
                }
                DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                    [
                        'ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario,
                        'menu_id' => $menuId,
                    ],
                    $acl,
                );
                $n++;
            }
        }

        return $n;
    }

    /**
     * Remove ACL do módulo nos tipos que não devem herdar acesso automático.
     *
     * @param  list<int>  $processos
     * @param  list<int>  $excetoTipos
     */
    public static function revogarForaDe(array $processos, array $excetoTipos): int
    {
        $menuIds = Menu::query()->whereIn('process', $processos)->pluck('id');
        if ($menuIds->isEmpty()) {
            return 0;
        }

        return DB::table('pmieducar.menu_tipo_usuario')
            ->whereIn('menu_id', $menuIds)
            ->whereNotIn('ref_cod_tipo_usuario', $excetoTipos)
            ->delete();
    }

    /** @return list<int> */
    private static function faixa(int $ini, int $fim): array
    {
        // Não materializa a faixa inteira: concede pelos menus existentes no intervalo.
        return Menu::query()
            ->whereBetween('process', [$ini, $fim])
            ->pluck('process')
            ->map(static fn ($p) => (int) $p)
            ->unique()
            ->values()
            ->all();
    }
}
