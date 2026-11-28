<?php

use Illuminate\Database\Migrations\Migration;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;

/**
 * Separa faixas exportação/biblioteca e concede exportação como dependência
 * dos módulos que emitem documentos (ACL via matriz).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! class_exists(PermissaoMatriz::class)) {
            return;
        }

        PermissaoMatriz::sincronizar();
    }

    public function down(): void
    {
        //
    }
};
