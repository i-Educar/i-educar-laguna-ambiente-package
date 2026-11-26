<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use Illuminate\Support\Facades\DB;

/**
 * 4.7: Serviço para gerenciar política de senha cíclica por usuário
 * 
 * Permite configurar expiração individual e forçar troca de senha.
 */
class PoliticaSenhaUsuario
{
    /**
     * Define dias de expiração para um usuário específico
     * 
     * @param int $userId ID do usuário (portal.users.id)
     * @param int|null $dias Dias até expiração (null = usar padrão do sistema)
     * @return bool
     */
    public static function definirExpiracao(int $userId, ?int $dias): bool
    {
        $employee = DB::table('portal.employees')
            ->where('user_id', $userId)
            ->first();

        if (!$employee) {
            return false;
        }

        $proximaTroca = null;
        if ($dias !== null && $dias > 0) {
            // Calcular próxima troca baseado na última alteração de senha
            $dataUltimaTroca = $employee->data_troca_senha ?? now();
            $proximaTroca = date('Y-m-d', strtotime($dataUltimaTroca . " + {$dias} days"));
        }

        DB::table('portal.employees')
            ->where('id', $employee->id)
            ->update([
                'dias_expiracao_senha' => $dias,
                'data_proxima_troca_senha' => $proximaTroca,
                'updated_at' => now(),
            ]);

        return true;
    }

    /**
     * Força troca de senha no próximo login
     * 
     * @param int $userId ID do usuário
     * @param bool $forcar true para forçar, false para remover flag
     * @return bool
     */
    public static function forcarTroca(int $userId, bool $forcar = true): bool
    {
        $employee = DB::table('portal.employees')
            ->where('user_id', $userId)
            ->first();

        if (!$employee) {
            return false;
        }

        DB::table('portal.employees')
            ->where('id', $employee->id)
            ->update([
                'force_password_change' => $forcar,
                'updated_at' => now(),
            ]);

        return true;
    }

    /**
     * Verifica se senha do usuário está expirada
     * 
     * @param int $userId
     * @return bool
     */
    public static function senhaExpirada(int $userId): bool
    {
        $employee = DB::table('portal.employees')
            ->where('user_id', $userId)
            ->first();

        if (!$employee) {
            return false;
        }

        // Verifica flag manual
        if ($employee->force_password_change ?? false) {
            return true;
        }

        // Verifica data de próxima troca
        if (!empty($employee->data_proxima_troca_senha)) {
            return date('Y-m-d') >= $employee->data_proxima_troca_senha;
        }

        // Verifica dias de expiração individual
        if (!empty($employee->dias_expiracao_senha) && !empty($employee->data_troca_senha)) {
            $diasDesdeUltimaTroca = floor((time() - strtotime($employee->data_troca_senha)) / 86400);
            return $diasDesdeUltimaTroca >= $employee->dias_expiracao_senha;
        }

        return false;
    }

    /**
     * Atualiza data de próxima troca após alteração de senha
     * 
     * @param int $userId
     * @return void
     */
    public static function atualizarAposTroca(int $userId): void
    {
        $employee = DB::table('portal.employees')
            ->where('user_id', $userId)
            ->first();

        if (!$employee) {
            return;
        }

        $update = [
            'force_password_change' => false,
            'data_troca_senha' => now(),
            'updated_at' => now(),
        ];

        // Recalcular próxima troca se tiver dias definidos
        if (!empty($employee->dias_expiracao_senha)) {
            $update['data_proxima_troca_senha'] = date('Y-m-d', strtotime("+{$employee->dias_expiracao_senha} days"));
        } else {
            $update['data_proxima_troca_senha'] = null;
        }

        DB::table('portal.employees')
            ->where('id', $employee->id)
            ->update($update);
    }
}
