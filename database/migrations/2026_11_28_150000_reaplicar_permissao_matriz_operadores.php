<?php

use App\Services\MenuCacheService;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;
use Illuminate\Database\Migrations\Migration;

/**
 * Reaplica a matriz Laguna limpando ACL poluída de operadores
 * (ex.: tipo 8 Almoxarifado com RH/vagas/matrícula).
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
