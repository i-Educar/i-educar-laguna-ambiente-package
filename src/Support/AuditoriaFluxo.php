<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoria transversal leve para pacotes Buriti (poc_registro ou tabela dedicada).
 */
final class AuditoriaFluxo
{
    /**
     * @param  callable(string,string,?string,array):int|null  $gravar  fn($tipo,$titulo,$meta,$payload):int
     */
    public static function registrar(
        ?callable $gravar,
        string $acao,
        string $detalhe,
        ?int $refId = null,
        array $extra = [],
    ): void {
        $payload = array_merge([
            'acao' => $acao,
            'detalhe' => $detalhe,
            'ref_id' => $refId,
            'usuario' => self::usuario(),
            'em' => now()->toIso8601String(),
        ], $extra);

        if ($gravar !== null) {
            $gravar('auditoria', $acao, $detalhe, $payload);

            return;
        }

        // Fallback genérico se existir schema laguna.auditoria
        if (Schema::hasTable('laguna.auditoria')) {
            DB::table('laguna.auditoria')->insert([
                'acao' => mb_substr($acao, 0, 80),
                'detalhe' => mb_substr($detalhe, 0, 500),
                'ref_id' => $refId,
                'usuario' => self::usuario(),
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
        }
    }

    public static function usuario(): string
    {
        $u = Auth::user();

        return $u?->name ?? $u?->login ?? $u?->email ?? 'sistema';
    }
}
