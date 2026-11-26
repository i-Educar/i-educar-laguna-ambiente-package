<?php

use App\Menu;
use App\Services\MenuCacheService;
use Illuminate\Database\Migrations\Migration;

/**
 * Hierarquia Laguna:
 * - Quadro de horários e Contratações sob Servidores
 * - Apps API sob Configurações
 * - Módulos de domínio antes de Configurações
 * - Configurações por último, só antes de Ambiente Laguna
 */
return new class extends Migration
{
    public function up(): void
    {
        $servidores = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 71)->orWhere('title', 'Servidores');
            })
            ->first();

        $configuracoes = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 25)->orWhere('title', 'Configurações');
            })
            ->first();

        if ($servidores !== null) {
            Menu::query()
                ->where('parent_id', $servidores->getKey())
                ->where('title', 'Relatórios')
                ->update(['order' => 4]);

            $this->anexarGrupoSobPai(
                processoRaiz: 881800,
                paiId: $servidores->getKey(),
                titulo: 'Quadro de horários',
                link: '/horario-automatico',
                icone: 'fa-calendar',
                ordem: 2,
                grupoIntermediario: 'Operação',
                folhas: [
                    [881801, 'Painel', '/horario-automatico', 1],
                    [881802, 'Grade semanal', '/horario-automatico/grade', 2],
                    [881803, 'Conflitos', '/horario-automatico/choques', 3],
                    [881804, 'Por cargo', '/horario-automatico/cargos', 4],
                ],
            );

            $this->anexarGrupoSobPai(
                processoRaiz: 882000,
                paiId: $servidores->getKey(),
                titulo: 'Contratações',
                link: '/contratacoes-temporarias',
                icone: 'fa-briefcase',
                ordem: 3,
                grupoIntermediario: 'Operação',
                folhas: [
                    [882001, 'Painel', '/contratacoes-temporarias', 1],
                    [882002, 'Processos', '/contratacoes-temporarias/processos', 2],
                    [882003, 'Classificados', '/contratacoes-temporarias/classificados', 3],
                    [882004, 'Alocação', '/contratacoes-temporarias/alocacao', 4],
                ],
            );
        }

        if ($configuracoes !== null) {
            $configuracoes->forceFill(['order' => 98])->save();

            $this->anexarGrupoSobPai(
                processoRaiz: 882101,
                paiId: $configuracoes->getKey(),
                titulo: 'APIs',
                link: '/apps-api/configuracao',
                icone: 'fa-mobile',
                ordem: 4,
                grupoIntermediario: 'Governança',
                folhas: [
                    [882110, 'Configuração', '/apps-api/configuracao', 1],
                    [882111, 'Funções disponíveis', '/apps-api/funcoes', 2],
                ],
            );
        }

        $ordensTopo = [
            880100 => 8,   // Transporte escolar
            881100 => 9,   // Alimentação escolar
            881400 => 10,  // Almoxarifado escolar
            881600 => 11,  // Atendimento ao estudante
            881700 => 12,  // Solicitação de serviços
            889900 => 99,  // Ambiente Laguna
        ];

        foreach ($ordensTopo as $processo => $ordem) {
            Menu::query()
                ->where('process', $processo)
                ->whereNull('parent_id')
                ->update(['order' => $ordem, 'type' => 1]);
        }

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        // Irreversível de forma segura: mantém hierarquia nova.
    }

    /**
     * @param  list<array{0:int,1:string,2:string,3:int}>  $folhas
     */
    private function anexarGrupoSobPai(
        int $processoRaiz,
        int $paiId,
        string $titulo,
        string $link,
        string $icone,
        int $ordem,
        string $grupoIntermediario,
        array $folhas,
    ): void {
        $raiz = Menu::query()->where('process', $processoRaiz)->first();
        if ($raiz === null) {
            return;
        }

        $raiz->forceFill([
            'parent_id' => $paiId,
            'title' => $titulo,
            'link' => $link,
            'icon' => $icone,
            'order' => $ordem,
            'type' => 2,
            'active' => true,
        ])->save();

        $intermediarios = Menu::query()
            ->where('parent_id', $raiz->getKey())
            ->where('title', $grupoIntermediario)
            ->whereNull('process')
            ->pluck('id');

        foreach ($folhas as [$processo, $tituloFolha, $linkFolha, $ordemFolha]) {
            Menu::query()->where('process', $processo)->update([
                'parent_id' => $raiz->getKey(),
                'title' => $tituloFolha,
                'link' => $linkFolha,
                'order' => $ordemFolha,
                'type' => 3,
                'active' => true,
            ]);
        }

        if ($intermediarios->isNotEmpty()) {
            Menu::query()->whereIn('id', $intermediarios)->delete();
        }
    }
};
