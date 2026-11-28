<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use App\Models\LegacyUserType;
use App\Models\NotificationType;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Ponte única dos módulos Buriti com o notificador do núcleo i-Educar.
 */
final class Notificador
{
    /**
     * Notifica usuários com permissão de visualizar o processo (menu),
     * opcionalmente restritos à escola (mais níveis institucionais/admin).
     */
    public static function paraProcesso(
        int $processo,
        string $texto,
        string $link,
        ?int $escolaId = null,
        int $tipo = NotificationType::OTHER,
    ): int {
        try {
            $service = app(NotificationService::class);
            $userIds = self::usuariosDoProcesso($processo, $escolaId);
            $atual = Auth::id();
            $n = 0;
            foreach ($userIds as $uid) {
                if ($atual && (int) $uid === (int) $atual) {
                    continue;
                }
                $service->createByUser((int) $uid, $texto, $link, $tipo);
                $n++;
            }

            return $n;
        } catch (Throwable $e) {
            Log::warning('Notificador::paraProcesso falhou', [
                'processo' => $processo,
                'erro' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Notifica um usuário específico.
     */
    public static function paraUsuario(int $userId, string $texto, string $link, int $tipo = NotificationType::OTHER): bool
    {
        try {
            app(NotificationService::class)->createByUser($userId, $texto, $link, $tipo);

            return true;
        } catch (Throwable $e) {
            Log::warning('Notificador::paraUsuario falhou', ['user' => $userId, 'erro' => $e->getMessage()]);

            return false;
        }
    }


    /**
     * Notifica uma lista explícita de usuários (pula o usuário atual).
     *
     * @param  list<int>  $userIds
     */
    public static function paraUsuarios(array $userIds, string $texto, string $link, int $tipo = NotificationType::OTHER): int
    {
        try {
            $service = app(NotificationService::class);
            $atual = Auth::id();
            $n = 0;
            $vistos = [];
            foreach ($userIds as $uid) {
                $uid = (int) $uid;
                if ($uid <= 0 || isset($vistos[$uid])) {
                    continue;
                }
                $vistos[$uid] = true;
                if ($atual && $uid === (int) $atual) {
                    continue;
                }
                $service->createByUser($uid, $texto, $link, $tipo);
                $n++;
            }

            return $n;
        } catch (Throwable $e) {
            Log::warning('Notificador::paraUsuarios falhou', ['erro' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * Notifica usuários ativos cujo tipo_usuario.nivel está na lista.
     * Aceita tokens (admin, institucional, escolar, biblioteca) ou inteiros de nível.
     *
     * @param  list<string|int>  $niveis
     */
    public static function paraNiveis(array $niveis, string $texto, string $link, ?int $escolaId = null, int $tipo = NotificationType::OTHER): int
    {
        return self::paraUsuarios(self::usuariosDosNiveis($niveis, $escolaId), $texto, $link, $tipo);
    }

    /**
     * @param  list<string|int>  $niveis
     * @return list<int>
     */
    public static function usuariosDosNiveis(array $niveis, ?int $escolaId = null): array
    {
        $mapa = [
            'admin' => LegacyUserType::LEVEL_ADMIN,
            'poli' => LegacyUserType::LEVEL_ADMIN,
            'poli-institucional' => LegacyUserType::LEVEL_ADMIN,
            'institucional' => LegacyUserType::LEVEL_INSTITUTIONAL,
            'escolar' => LegacyUserType::LEVEL_SCHOOLING,
            'escola' => LegacyUserType::LEVEL_SCHOOLING,
            'biblioteca' => LegacyUserType::LEVEL_LIBRARY,
        ];
        $idsNivel = [];
        $todos = false;
        foreach ($niveis as $token) {
            if ($token === '*' || $token === 'todos' || $token === 'any') {
                $todos = true;
                break;
            }
            if (is_numeric($token)) {
                $idsNivel[] = (int) $token;
                continue;
            }
            $chave = strtolower(trim((string) $token));
            if (isset($mapa[$chave])) {
                $idsNivel[] = $mapa[$chave];
            }
        }
        $idsNivel = array_values(array_unique($idsNivel));
        if (! $todos && $idsNivel === []) {
            return [];
        }

        try {
            $q = DB::table('pmieducar.usuario as u')
                ->select('u.cod_usuario')
                ->distinct()
                ->join('pmieducar.tipo_usuario as tu', 'tu.cod_tipo_usuario', '=', 'u.ref_cod_tipo_usuario')
                ->leftJoin('pmieducar.escola_usuario as eu', 'eu.ref_cod_usuario', '=', 'u.cod_usuario')
                ->where('u.ativo', 1);
            if (! $todos) {
                $q->whereIn('tu.nivel', $idsNivel);
            }
            if ($escolaId !== null) {
                $q->where(function ($w) use ($escolaId): void {
                    $w->where('eu.ref_cod_escola', $escolaId)
                        ->orWhere('tu.nivel', '<=', LegacyUserType::LEVEL_INSTITUTIONAL);
                });
            }

            return $q->pluck('cod_usuario')->map(fn ($id) => (int) $id)->unique()->values()->all();
        } catch (Throwable $e) {
            Log::warning('Notificador::usuariosDosNiveis falhou', ['erro' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @return list<int>
     */
    public static function usuariosDoProcesso(int $processo, ?int $escolaId = null): array
    {
        $q = DB::table('pmieducar.usuario as u')
            ->select('u.cod_usuario')
            ->distinct()
            ->join('pmieducar.tipo_usuario as tu', 'tu.cod_tipo_usuario', '=', 'u.ref_cod_tipo_usuario')
            ->leftJoin('pmieducar.escola_usuario as eu', 'eu.ref_cod_usuario', '=', 'u.cod_usuario')
            ->leftJoin('pmieducar.menu_tipo_usuario as mtu', function ($j): void {
                $j->on('mtu.ref_cod_tipo_usuario', '=', 'u.ref_cod_tipo_usuario')
                    ->where('mtu.visualiza', 1);
            })
            ->leftJoin('public.menus as m', 'm.id', '=', 'mtu.menu_id')
            ->where('u.ativo', 1)
            ->where(function ($q) use ($processo): void {
                $q->where('tu.nivel', LegacyUserType::LEVEL_ADMIN)
                    ->orWhere('m.process', $processo);
            });

        if ($escolaId !== null) {
            $q->where(function ($w) use ($escolaId): void {
                $w->where('eu.ref_cod_escola', $escolaId)
                    ->orWhere('tu.nivel', '<=', LegacyUserType::LEVEL_INSTITUTIONAL);
            });
        }

        return $q->pluck('cod_usuario')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }
}
