<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiLagunaAmbiente\Support\Processos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menu admin Ambiente Laguna (ACL preferencialmente nivel admin).
 * Rotas web ainda exigem User::isAdmin() via middleware.
 */
return new class extends Migration
{
    public function up(): void
    {
        $raiz = Menu::query()->updateOrCreate(['process' => Processos::RAIZ], [
            'parent_id' => null,
            'title' => 'Ambiente Laguna',
            'description' => 'Auditoria e setup PE-77 (admin)',
            'link' => '/laguna-ambiente',
            'icon' => 'fa-heartbeat',
            'order' => 99,
            'type' => 1,
            'active' => true,
        ]);

        $grupo = Menu::query()->updateOrCreate(
            ['parent_id' => $raiz->getKey(), 'title' => 'Administração'],
            ['order' => 1, 'type' => 2, 'process' => null, 'active' => true, 'link' => null],
        );

        $links = [
            [Processos::PAINEL, 'Painel ambiente', '/laguna-ambiente', 'Visão geral'],
            [Processos::CHECK, 'Verificar módulos', '/laguna-ambiente/check', 'Auditoria de instalação'],
            [Processos::SETUP, 'Setup / povoar teste', '/laguna-ambiente/setup', 'Base de ensaio completo'],
        ];

        foreach ($links as $i => [$processo, $titulo, $link, $descricao]) {
            Menu::query()->updateOrCreate(['process' => $processo], [
                'parent_id' => $grupo->getKey(),
                'title' => $titulo,
                'description' => $descricao,
                'link' => $link,
                'order' => $i + 1,
                'type' => 3,
                'active' => true,
                'icon' => null,
            ]);
        }

        $processos = [Processos::RAIZ, Processos::PAINEL, Processos::CHECK, Processos::SETUP];

        $query = DB::table('pmieducar.tipo_usuario')->where('ativo', 1);
        try {
            if (Schema::hasColumn('pmieducar.tipo_usuario', 'nivel')) {
                $query->where('nivel', 1);
            }
        } catch (\Throwable) {
            //
        }
        $tipos = $query->get(['cod_tipo_usuario']);
        if ($tipos->isEmpty()) {
            $tipos = DB::table('pmieducar.tipo_usuario')->where('ativo', 1)->limit(1)->get(['cod_tipo_usuario']);
        }

        foreach ($tipos as $tipo) {
            foreach ($processos as $processo) {
                $menuId = Menu::query()->where('process', $processo)->value('id');
                if ($menuId === null) {
                    continue;
                }
                DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                    ['ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario, 'menu_id' => $menuId],
                    ['cadastra' => 1, 'visualiza' => 1, 'exclui' => 0],
                );
            }
        }

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        $raiz = Menu::query()->where('process', Processos::RAIZ)->first();
        if ($raiz === null) {
            return;
        }
        $grupos = Menu::query()->where('parent_id', $raiz->getKey())->pluck('id');
        $folhas = Menu::query()->whereIn('parent_id', $grupos)->pluck('id');
        $ids = $grupos->merge($folhas)->push($raiz->getKey());
        DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $ids)->delete();
        Menu::query()->whereIn('id', $folhas)->delete();
        Menu::query()->whereIn('id', $grupos)->delete();
        $raiz->delete();
        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }
};
