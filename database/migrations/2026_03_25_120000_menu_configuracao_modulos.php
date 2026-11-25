<?php

use App\Menu;
use App\Services\MenuCacheService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $configuracoes = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 25)->orWhere('title', 'Configurações');
            })
            ->first();

        if ($configuracoes === null) {
            return;
        }

        $menu = Menu::query()->updateOrCreate(['process' => 889910], [
            'parent_id' => $configuracoes->getKey(),
            'title' => 'Módulos',
            'description' => 'Flags, diretivas e ativação dos módulos municipais — com aviso de risco ao desligar',
            'link' => '/configuracoes/modulos',
            'icon' => 'fa-cubes',
            'order' => 3,
            'type' => 2,
            'active' => true,
        ]);

        $tipos = DB::table('pmieducar.tipo_usuario')->where('ativo', 1)->get(['cod_tipo_usuario', 'nivel']);
        foreach ($tipos as $tipo) {
            // Só admin (nível 1) cadastra; demais níveis da SME só visualizam se tiverem menu
            $nivel = (int) ($tipo->nivel ?? 9);
            $cadastra = $nivel <= 1 ? 1 : 0;
            $visualiza = $nivel <= 2 ? 1 : 0;
            if ($visualiza === 0) {
                continue;
            }
            DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                [
                    'ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario,
                    'menu_id' => $menu->getKey(),
                ],
                [
                    'cadastra' => $cadastra,
                    'visualiza' => 1,
                    'exclui' => 0,
                ],
            );
        }

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        $menu = Menu::query()->where('process', 889910)->first();
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
