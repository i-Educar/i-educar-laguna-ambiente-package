<?php

use App\Menu;
use App\Services\MenuCacheService;
use Illuminate\Database\Migrations\Migration;

/**
 * - Remove "escolar" dos títulos de Transporte, Alimentação e Almoxarifado
 * - Move Atendimento ao estudante para dentro de Escola
 */
return new class extends Migration
{
    public function up(): void
    {
        Menu::query()->where('process', 880100)->whereNull('parent_id')->update([
            'title' => 'Transporte',
        ]);
        Menu::query()->where('process', 881100)->whereNull('parent_id')->update([
            'title' => 'Alimentação',
        ]);
        Menu::query()->where('process', 881400)->whereNull('parent_id')->update([
            'title' => 'Almoxarifado',
        ]);

        $escola = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 55)->orWhere('title', 'Escola');
            })
            ->first();

        $atendimento = Menu::query()->where('process', 881600)->first();
        if ($escola !== null && $atendimento !== null) {
            $atendimento->forceFill([
                'parent_id' => $escola->getKey(),
                'title' => 'Atendimento ao estudante',
                'order' => 8,
                'type' => 2,
                'active' => true,
                'link' => '/atendimento-estudante',
                'icon' => 'fa-heartbeat',
            ])->save();

            // Achata folhas sob a raiz do atendimento (remove grupo Operação intermediário)
            $operacao = Menu::query()
                ->where('parent_id', $atendimento->getKey())
                ->where('title', 'Operação')
                ->whereNull('process')
                ->first();

            if ($operacao !== null) {
                Menu::query()
                    ->where('parent_id', $operacao->getKey())
                    ->update(['parent_id' => $atendimento->getKey()]);
                $operacao->delete();
            }

            foreach ([881601 => 1, 881602 => 2, 881603 => 3, 881604 => 4] as $processo => $ordem) {
                Menu::query()->where('process', $processo)->update([
                    'parent_id' => $atendimento->getKey(),
                    'order' => $ordem,
                    'type' => 3,
                ]);
            }
        }

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        //
    }
};
