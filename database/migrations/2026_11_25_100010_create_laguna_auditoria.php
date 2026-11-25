<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS laguna');

        if (! Schema::hasTable('laguna.auditoria')) {
            Schema::create('laguna.auditoria', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('acao', 80);
                $table->string('detalhe', 500)->nullable();
                $table->unsignedInteger('ref_id')->nullable();
                $table->string('usuario', 120)->nullable();
                $table->jsonb('payload')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['acao', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('laguna.auditoria');
    }
};
