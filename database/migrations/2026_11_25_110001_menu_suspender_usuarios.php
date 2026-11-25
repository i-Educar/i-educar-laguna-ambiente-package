<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiLagunaAmbiente\Support\Processos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $raiz = Menu::query()->where('process', Processos::RAIZ)->first();
        if ($raiz === null) {
            return;
        }

        $grupo = Menu::query()
            ->where('parent_id', $raiz->getKey())
            ->where('title', 'Administração')
            ->first();

        if ($grupo === null) {
            $grupo = Menu::query()->updateOrCreate(
                ['parent_id' => $raiz->getKey(), 'title' => 'Administração'],
                ['order' => 1, 'type' => 2, 'process' => null, 'active' => true, 'link' => null],
            );
        }

        Menu::query()->updateOrCreate(['process' => Processos::USUARIOS], [
            'parent_id' => $grupo->getKey(),
            'title' => 'Suspender usuários',
            'description' => 'Desativar usuários, revogar apps e auditar',
            'link' => '/laguna-ambiente/usuarios',
            'order' => 5,
            'type' => 3,
            'active' => true,
            'icon' => null,
        ]);

        $menuId = Menu::query()->where('process', Processos::USUARIOS)->value('id');
        if ($menuId === null) {
            return;
        }

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
            DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                ['ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario, 'menu_id' => $menuId],
                ['cadastra' => 1, 'visualiza' => 1, 'exclui' => 0],
            );
        }

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        $menu = Menu::query()->where('process', Processos::USUARIOS)->first();
        if ($menu === null) {
            return;
        }
        DB::table('pmieducar.menu_tipo_usuario')->where('menu_id', $menu->getKey())->delete();
        $menu->delete();
        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }
};
