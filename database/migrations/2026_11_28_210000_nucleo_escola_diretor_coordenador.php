<?php

use Illuminate\Database\Migrations\Migration;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;

/**
 * Diretor e coordenador passam a ter ACL do núcleo Escola/Pessoas
 * (corrige card Escola no painel sem process 55).
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
