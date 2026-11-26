<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use Illuminate\Support\Facades\DB;

/**
 * 4.10: Encerramento imediato de sessões quando usuário é desabilitado
 * 
 * Garante que ao desabilitar um usuário, todas as suas sessões e tokens
 * são invalidados imediatamente.
 */
class SessaoSegura
{
    /**
     * Encerra todas as sessões e tokens de um usuário
     * 
     * @param int $userId ID do usuário
     * @return array Estatísticas do que foi revogado
     */
    public static function encerrarTodasSessoes(int $userId): array
    {
        $stats = [
            'sessions' => 0,
            'sanctum_tokens' => 0,
            'token_version_updated' => false,
        ];

        // 1. Revogar tokens Sanctum (APIs/mobile)
        if (DB::getSchemaBuilder()->hasTable('personal_access_tokens')) {
            $deleted = DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->where('tokenable_id', $userId)
                ->delete();
            $stats['sanctum_tokens'] = $deleted;
        }

        // 2. Deletar sessões web (Laravel sessions)
        if (DB::getSchemaBuilder()->hasTable('sessions')) {
            $deleted = DB::table('sessions')
                ->where('user_id', $userId)
                ->delete();
            $stats['sessions'] = $deleted;
        }

        // 3. Incrementar token_version para invalidar tokens JWT/cache (se implementado)
        if (DB::getSchemaBuilder()->hasTable('portal.employees')) {
            $employee = DB::table('portal.employees')
                ->where('user_id', $userId)
                ->first();

            if ($employee) {
                $currentVersion = $employee->token_version ?? 0;
                DB::table('portal.employees')
                    ->where('id', $employee->id)
                    ->update([
                        'token_version' => $currentVersion + 1,
                        'updated_at' => now(),
                    ]);
                $stats['token_version_updated'] = true;
            }
        }

        // 4. Marcar usuário como inativo em portal.users se existir coluna
        if (DB::getSchemaBuilder()->hasTable('portal.users')) {
            if (DB::getSchemaBuilder()->hasColumn('portal.users', 'active')) {
                DB::table('portal.users')
                    ->where('id', $userId)
                    ->update(['active' => false, 'updated_at' => now()]);
            }
        }

        return $stats;
    }

    /**
     * Verifica se usuário pode fazer login (não está bloqueado/suspenso)
     * 
     * @param int $userId
     * @return bool
     */
    public static function usuarioPodeLogar(int $userId): bool
    {
        // Verifica status em portal.users
        if (DB::getSchemaBuilder()->hasTable('portal.users')) {
            $user = DB::table('portal.users')->where('id', $userId)->first();
            
            if (!$user) {
                return false;
            }

            // Verificar coluna 'active' se existir
            if (DB::getSchemaBuilder()->hasColumn('portal.users', 'active')) {
                if (!($user->active ?? true)) {
                    return false;
                }
            }

            // Verificar coluna 'suspended' se existir
            if (DB::getSchemaBuilder()->hasColumn('portal.users', 'suspended')) {
                if ($user->suspended ?? false) {
                    return false;
                }
            }
        }

        // Verifica employee force_reset_password
        if (DB::getSchemaBuilder()->hasTable('portal.employees')) {
            $employee = DB::table('portal.employees')
                ->where('user_id', $userId)
                ->first();

            // Se force_reset_password, permite login mas redireciona para troca
            // (não bloqueia aqui, apenas sinaliza)
        }

        return true;
    }

    /**
     * Adiciona coluna token_version se não existir (para controle de versão de tokens)
     * 
     * Deve ser chamado em migration ou setup
     */
    public static function garantirColunaTokenVersion(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('portal.employees')) {
            return;
        }

        if (!DB::getSchemaBuilder()->hasColumn('portal.employees', 'token_version')) {
            DB::statement('ALTER TABLE portal.employees ADD COLUMN token_version INTEGER DEFAULT 0');
        }
    }
}
