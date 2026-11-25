<?php

use App\Menu;
use App\Services\MenuCacheService;
use Illuminate\Database\Migrations\Migration;

/**
 * Educacenso fica imediatamente antes de Configurações no menu raiz.
 */
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

        $educacenso = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 70)->orWhere('title', 'Educacenso');
            })
            ->first();

        $ordemConfig = 98;
        if ($configuracoes !== null) {
            $ordemConfig = max(2, (int) ($configuracoes->order ?? 98));
            $configuracoes->forceFill(['order' => $ordemConfig, 'type' => 1, 'active' => true])->save();
        }

        if ($educacenso !== null) {
            $educacenso->forceFill([
                'order' => $ordemConfig - 1,
                'type' => 1,
                'active' => true,
            ])->save();
        }

        // Garante módulos de domínio antes do Educacenso
        $antes = [
            880100 => $ordemConfig - 6, // Transporte
            881100 => $ordemConfig - 5, // Alimentação
            881400 => $ordemConfig - 4, // Almoxarifado
            881700 => $ordemConfig - 3, // Solicitação
        ];
        foreach ($antes as $processo => $ordem) {
            Menu::query()
                ->where('process', $processo)
                ->whereNull('parent_id')
                ->update(['order' => max(1, $ordem), 'type' => 1]);
        }

        Menu::query()
            ->where('process', 889900)
            ->whereNull('parent_id')
            ->update(['order' => $ordemConfig + 1, 'type' => 1]);

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        Menu::query()
            ->whereNull('parent_id')
            ->where('process', 70)
            ->update(['order' => 6]);
    }
};
