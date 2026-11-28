<?php

use Illuminate\Database\Migrations\Migration;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;

/** Reaplica matriz após inclusão do módulo papeis-temporarios (só admin). */
return new class extends Migration
{
    public function up(): void
    {
        if (class_exists(PermissaoMatriz::class)) {
            PermissaoMatriz::sincronizar();
        }
    }

    public function down(): void
    {
        //
    }
};
