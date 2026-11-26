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

        if (! Schema::hasTable('laguna.simulacao_lote')) {
            Schema::create('laguna.simulacao_lote', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('tag', 40)->default('sim-pessoas-v1');
                $table->unsignedSmallInteger('pessoas')->default(0);
                $table->unsignedSmallInteger('eventos')->default(0);
                $table->unsignedSmallInteger('modulos')->default(0);
                $table->string('usuario', 120)->nullable();
                $table->jsonb('resumo')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('laguna.simulacao_pessoa')) {
            Schema::create('laguna.simulacao_pessoa', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('lote_id');
                $table->unsignedInteger('idpes')->nullable();
                $table->string('nome', 160);
                $table->string('cpf', 11);
                $table->string('perfil', 40);
                $table->string('escola', 160)->nullable();
                $table->jsonb('cenarios')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['lote_id', 'cpf']);
            });
        }

        if (! Schema::hasTable('laguna.simulacao_evento')) {
            Schema::create('laguna.simulacao_evento', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('lote_id');
                $table->unsignedInteger('pessoa_id');
                $table->string('modulo', 40);
                $table->string('cenario', 80);
                $table->string('status', 20)->default('ok');
                $table->string('detalhe', 500)->nullable();
                $table->string('url', 255)->nullable();
                $table->jsonb('payload')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['lote_id', 'pessoa_id']);
                $table->index(['modulo', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('laguna.simulacao_evento');
        Schema::dropIfExists('laguna.simulacao_pessoa');
        Schema::dropIfExists('laguna.simulacao_lote');
    }
};
