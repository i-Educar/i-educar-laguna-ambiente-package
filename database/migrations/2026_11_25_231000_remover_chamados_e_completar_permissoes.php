<?php

use App\Menu;
use App\Services\MenuCacheService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remove o menu do pacote serventec/chamados (substituído por Solicitação de serviços)
 * e garante processos de Cadastros / Relatórios / Auditoria / Decisão no rol ACL.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->removerChamados();
        $this->completarSolicitacao();
        $this->garantirGrupoModulo(
            raizProcesso: 881100,
            grupos: [
                ['title' => 'Cadastros', 'order' => 1, 'folhas' => [
                    [881120, 'Cardápio (cadastro)', '/alimentacao-escolar/cardapio', 1],
                    [881121, 'Restrições (cadastro)', '/alimentacao-escolar/restricoes', 2],
                ]],
                ['title' => 'Relatórios', 'order' => 3, 'folhas' => [
                    [881130, 'Distribuição', '/alimentacao-escolar/mapa', 1],
                ]],
                ['title' => 'Auditoria', 'order' => 4, 'folhas' => [
                    [881140, 'Eventos alimentação', '/alimentacao-escolar', 1],
                ]],
            ],
        );
        $this->garantirGrupoModulo(
            raizProcesso: 881400,
            grupos: [
                ['title' => 'Cadastros', 'order' => 1, 'folhas' => [
                    [881410, 'Itens', '/almoxarifado-escolar/itens', 1],
                ]],
                ['title' => 'Relatórios', 'order' => 3, 'folhas' => [
                    [881420, 'Saldos', '/almoxarifado-escolar/saldos', 1],
                ]],
                ['title' => 'Auditoria', 'order' => 4, 'folhas' => [
                    [881430, 'Pedidos', '/almoxarifado-escolar/pedidos', 1],
                ]],
            ],
        );
        $this->garantirGrupoModulo(
            raizProcesso: 881600,
            grupos: [
                ['title' => 'Cadastros', 'order' => 1, 'folhas' => [
                    [881610, 'Agenda', '/atendimento-estudante/agenda', 1],
                ]],
                ['title' => 'Relatórios', 'order' => 3, 'folhas' => [
                    [881620, 'Registros', '/atendimento-estudante/registro', 1],
                ]],
                ['title' => 'Auditoria', 'order' => 4, 'folhas' => [
                    [881630, 'Fila', '/atendimento-estudante/fila', 1],
                ]],
            ],
        );
        $this->garantirGrupoModulo(
            raizProcesso: 881800,
            grupos: [
                ['title' => 'Relatórios', 'order' => 3, 'folhas' => [
                    [881820, 'Grade', '/horario-automatico/grade', 1],
                ]],
                ['title' => 'Auditoria', 'order' => 4, 'folhas' => [
                    [881830, 'Conflitos', '/horario-automatico/choques', 1],
                ]],
            ],
            parentIsRoot: false,
        );
        $this->garantirGrupoModulo(
            raizProcesso: 882000,
            grupos: [
                ['title' => 'Cadastros', 'order' => 1, 'folhas' => [
                    [882010, 'Processos', '/contratacoes-temporarias/processos', 1],
                ]],
                ['title' => 'Relatórios', 'order' => 3, 'folhas' => [
                    [882020, 'Classificados', '/contratacoes-temporarias/classificados', 1],
                ]],
                ['title' => 'Auditoria', 'order' => 4, 'folhas' => [
                    [882030, 'Alocação', '/contratacoes-temporarias/alocacao', 1],
                ]],
            ],
            parentIsRoot: false,
        );

        // Transporte: já possui Cadastros/Relatórios/Auditoria/Solicitações (deferimento) — só reforça ACL.
        $this->concederAcl([880110, 880151, 880162, 880161]);

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        //
    }

    private function removerChamados(): void
    {
        $raiz = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 72)->orWhere('title', 'Chamados');
            })
            ->first();

        if ($raiz === null) {
            return;
        }

        $filhos = Menu::query()->where('parent_id', $raiz->getKey())->pluck('id');
        $netos = Menu::query()->whereIn('parent_id', $filhos)->pluck('id');
        $ids = $filhos->merge($netos)->push($raiz->getKey())->unique()->values();

        DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $ids)->delete();
        Menu::query()->whereIn('id', $netos)->delete();
        Menu::query()->whereIn('id', $filhos)->delete();
        $raiz->delete();
    }

    private function completarSolicitacao(): void
    {
        $raiz = Menu::query()->where('process', 881700)->first();
        if ($raiz === null) {
            return;
        }

        $grupos = [
            'Cadastros' => 1,
            'Operação' => 2,
            'Relatórios' => 3,
            'Auditoria' => 4,
            'Decisão' => 5,
        ];
        $ids = [];
        foreach ($grupos as $titulo => $ordem) {
            $g = Menu::query()->updateOrCreate(
                ['parent_id' => $raiz->getKey(), 'title' => $titulo],
                ['order' => $ordem, 'type' => 2, 'process' => null, 'active' => true, 'link' => null],
            );
            $ids[$titulo] = $g->getKey();
        }

        $folhas = [
            [881710, $ids['Cadastros'], 'Categorias', '/solicitacao-servicos/categorias', 1],
            [881701, $ids['Operação'], 'Painel', '/solicitacao-servicos', 1],
            [881702, $ids['Operação'], 'Abertos', '/solicitacao-servicos/abertos', 2],
            [881703, $ids['Operação'], 'SLA', '/solicitacao-servicos/sla', 3],
            [881704, $ids['Operação'], 'Novo chamado', '/solicitacao-servicos/novo', 4],
            [881720, $ids['Relatórios'], 'Andamento', '/solicitacao-servicos/relatorios', 1],
            [881730, $ids['Auditoria'], 'Histórico', '/solicitacao-servicos/auditoria', 1],
            [881740, $ids['Decisão'], 'Deferir', '/solicitacao-servicos/abertos', 1],
            [881741, $ids['Decisão'], 'Indeferir', '/solicitacao-servicos/abertos', 2],
        ];

        foreach ($folhas as [$processo, $pai, $titulo, $link, $ordem]) {
            Menu::query()->updateOrCreate(['process' => $processo], [
                'parent_id' => $pai,
                'title' => $titulo,
                'link' => $link,
                'order' => $ordem,
                'type' => 3,
                'active' => true,
            ]);
        }

        $this->concederAcl([881700, 881701, 881702, 881703, 881704, 881710, 881720, 881730, 881740, 881741], cadastra: [881704, 881710, 881740, 881741]);
    }

    /**
     * @param  list<array{title:string,order:int,folhas:list<array{0:int,1:string,2:string,3:int}>}>  $grupos
     */
    private function garantirGrupoModulo(int $raizProcesso, array $grupos, bool $parentIsRoot = true): void
    {
        $raiz = Menu::query()->where('process', $raizProcesso)->first();
        if ($raiz === null) {
            return;
        }

        // Se o módulo está aninhado (ex.: sob Servidores), os grupos ficam sob a raiz do módulo.
        $paiGrupos = $raiz->getKey();

        $processos = [$raizProcesso];
        foreach ($grupos as $grupo) {
            $g = Menu::query()->updateOrCreate(
                ['parent_id' => $paiGrupos, 'title' => $grupo['title']],
                ['order' => $grupo['order'], 'type' => 2, 'process' => null, 'active' => true, 'link' => null],
            );
            foreach ($grupo['folhas'] as [$processo, $titulo, $link, $ordem]) {
                Menu::query()->updateOrCreate(['process' => $processo], [
                    'parent_id' => $g->getKey(),
                    'title' => $titulo,
                    'link' => $link,
                    'order' => $ordem,
                    'type' => 3,
                    'active' => true,
                ]);
                $processos[] = $processo;
            }
        }

        $this->concederAcl($processos);
    }

    /** @param list<int> $processos @param list<int> $cadastra */
    private function concederAcl(array $processos, array $cadastra = []): void
    {
        $tipos = DB::table('pmieducar.tipo_usuario')->where('ativo', 1)->get(['cod_tipo_usuario']);
        foreach ($tipos as $tipo) {
            foreach ($processos as $processo) {
                $menuId = Menu::query()->where('process', $processo)->value('id');
                if ($menuId === null) {
                    continue;
                }
                DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                    ['ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario, 'menu_id' => $menuId],
                    [
                        'cadastra' => ($cadastra === [] || in_array($processo, $cadastra, true)) ? 1 : 0,
                        'visualiza' => 1,
                        'exclui' => 0,
                    ],
                );
            }
        }
    }
};
