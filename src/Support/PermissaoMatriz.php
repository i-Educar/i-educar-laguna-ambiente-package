<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use App\Models\LegacyUserType;

/**
 * ACL padrão por nível (tela Tipos de usuário / menu_tipo_usuario).
 *
 * @phpstan-type Acl array{visualiza:int,cadastra:int,exclui:int}
 */
final class PermissaoMatriz
{
    /**
     * @return Acl
     */
    public static function paraNivel(int $nivel, ?int $codTipo = null): array
    {
        // Professor (cod 5 Laguna): só visualiza, mesmo em nível escolar.
        if ($codTipo === 5) {
            return ['visualiza' => 1, 'cadastra' => 0, 'exclui' => 0];
        }

        return match ($nivel) {
            LegacyUserType::LEVEL_ADMIN => [
                'visualiza' => 1,
                'cadastra' => 1,
                'exclui' => ($codTipo !== null && $codTipo <= 2) ? 1 : 0,
            ],
            LegacyUserType::LEVEL_INSTITUTIONAL => [
                'visualiza' => 1,
                'cadastra' => 1,
                'exclui' => 0,
            ],
            LegacyUserType::LEVEL_SCHOOLING,
            LegacyUserType::LEVEL_LIBRARY => [
                'visualiza' => 1,
                'cadastra' => 0,
                'exclui' => 0,
            ],
            default => ['visualiza' => 1, 'cadastra' => 0, 'exclui' => 0],
        };
    }

    /**
     * Concede ACL só aos tipos listados (ou a todos ativos se $tiposCodigos for null).
     *
     * @param  list<int>  $processos
     * @param  list<int>|null  $tiposCodigos
     * @param  list<int>  $processosSomenteVisualiza  força cadastra/exclui=0
     */
    public static function conceder(
        array $processos,
        ?array $tiposCodigos = null,
        array $processosSomenteVisualiza = [],
    ): int {
        $q = \Illuminate\Support\Facades\DB::table('pmieducar.tipo_usuario')->where('ativo', 1);
        if ($tiposCodigos !== null) {
            $q->whereIn('cod_tipo_usuario', $tiposCodigos);
        }
        $tipos = $q->get(['cod_tipo_usuario', 'nivel']);
        $n = 0;

        foreach ($tipos as $tipo) {
            $base = self::paraNivel((int) $tipo->nivel, (int) $tipo->cod_tipo_usuario);
            foreach ($processos as $processo) {
                $menuId = \App\Menu::query()->where('process', $processo)->value('id');
                if ($menuId === null) {
                    continue;
                }
                $acl = $base;
                if (in_array($processo, $processosSomenteVisualiza, true)) {
                    $acl['cadastra'] = 0;
                    $acl['exclui'] = 0;
                }
                \Illuminate\Support\Facades\DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                    [
                        'ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario,
                        'menu_id' => $menuId,
                    ],
                    $acl,
                );
                $n++;
            }
        }

        return $n;
    }

    /**
     * Remove ACL do módulo nos tipos que não devem herdar acesso automático.
     *
     * @param  list<int>  $processos
     * @param  list<int>  $excetoTipos
     */
    public static function revogarForaDe(array $processos, array $excetoTipos): int
    {
        $menuIds = \App\Menu::query()->whereIn('process', $processos)->pluck('id');
        if ($menuIds->isEmpty()) {
            return 0;
        }

        return \Illuminate\Support\Facades\DB::table('pmieducar.menu_tipo_usuario')
            ->whereIn('menu_id', $menuIds)
            ->whereNotIn('ref_cod_tipo_usuario', $excetoTipos)
            ->delete();
    }
}
