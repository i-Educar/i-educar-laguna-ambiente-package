<?php

use App\Services\MenuCacheService;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;
use Illuminate\Database\Migrations\Migration;

/**
 * Reaplica matriz após inclusão de almoxarifado no Diretor e revisão pedagógica.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! class_exists(PermissaoMatriz::class)) {
            return;
        }

        PermissaoMatriz::sincronizar();

        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (Throwable) {
            }
        }
    }

    public function down(): void
    {
        //
    }
};
