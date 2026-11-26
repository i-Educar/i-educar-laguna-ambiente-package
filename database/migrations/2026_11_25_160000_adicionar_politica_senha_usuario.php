<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 4.7: Política de senha cíclica configurável por usuário
 * 
 * Adiciona campos para controlar expiração de senha individual:
 * - dias_expiracao_senha: dias até forçar troca (NULL = usar padrão do sistema)
 * - data_proxima_troca_senha: data calculada para próxima troca obrigatória
 * - force_password_change: flag para forçar troca no próximo login
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pmieducar.funcionario')) {
            return;
        }

        Schema::table('pmieducar.funcionario', function (Blueprint $table) {
            if (!Schema::hasColumn('pmieducar.funcionario', 'dias_expiracao_senha')) {
                $table->integer('dias_expiracao_senha')->nullable()->comment('Dias para expiração da senha deste usuário (NULL=padrão sistema)');
            }
            if (!Schema::hasColumn('pmieducar.funcionario', 'data_proxima_troca_senha')) {
                $table->date('data_proxima_troca_senha')->nullable()->comment('Data calculada para próxima troca obrigatória');
            }
            if (!Schema::hasColumn('pmieducar.funcionario', 'force_password_change')) {
                $table->boolean('force_password_change')->default(false)->comment('Forçar troca de senha no próximo login');
            }
        });

        // Também adicionar em portal.employees se existir (Sanctum/APIs)
        if (Schema::hasTable('portal.employees') && !Schema::hasColumn('portal.employees', 'dias_expiracao_senha')) {
            Schema::table('portal.employees', function (Blueprint $table) {
                $table->integer('dias_expiracao_senha')->nullable();
                $table->date('data_proxima_troca_senha')->nullable();
                $table->boolean('force_password_change')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pmieducar.funcionario')) {
            Schema::table('pmieducar.funcionario', function (Blueprint $table) {
                $table->dropColumn(['dias_expiracao_senha', 'data_proxima_troca_senha', 'force_password_change']);
            });
        }

        if (Schema::hasTable('portal.employees')) {
            Schema::table('portal.employees', function (Blueprint $table) {
                $table->dropColumn(['dias_expiracao_senha', 'data_proxima_troca_senha', 'force_password_change']);
            });
        }
    }
};
