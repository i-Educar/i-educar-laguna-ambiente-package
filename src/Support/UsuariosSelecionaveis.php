<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use App\Models\LegacyUserType;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Lista pessoas do i-Educar para selects, com escopo do ator e (opcional)
 * filtro por processos/menus — só quem tem permissão de ver o fluxo.
 *
 * @phpstan-type UsuarioSelect array{id:int,nome:string,login:string,meta:string,nivel:?int,nivel_nome:?string}
 */
final class UsuariosSelecionaveis
{
    /**
     * @param  list<int>  $processos  Códigos `menus.process` do módulo (qualquer um com visualiza=1)
     * @return list<UsuarioSelect>
     */
    public static function listar(
        ?User $ator = null,
        array $processos = [],
        int $limite = 250,
        bool $incluirAtor = true,
    ): array {
        $ator ??= Auth::user();

        if (Schema::hasTable('pmieducar.usuario') && Schema::hasTable('portal.funcionario')) {
            return self::viaPortal($ator, $processos, $limite, $incluirAtor);
        }

        return self::viaUsers($limite);
    }

    /**
     * Formato pronto para buriti-ui::select-busca / select-multi / select-usuario.
     *
     * @param  list<int>  $processos
     * @return list<array{id:int,nome:string,meta:string,login?:string}>
     */
    public static function paraSelect(
        ?User $ator = null,
        array $processos = [],
        int $limite = 250,
        bool $incluirAtor = true,
    ): array {
        return array_map(static fn (array $u): array => [
            'id' => $u['id'],
            'nome' => $u['nome'],
            'meta' => $u['meta'],
            'login' => $u['login'],
        ], self::listar($ator, $processos, $limite, $incluirAtor));
    }

    /**
     * @param  list<int>  $processos
     * @return list<UsuarioSelect>
     */
    private static function viaPortal(?User $ator, array $processos, int $limite, bool $incluirAtor): array
    {
        try {
            $q = DB::table('pmieducar.usuario as u')
                ->join('portal.funcionario as f', 'f.ref_cod_pessoa_fj', '=', 'u.cod_usuario')
                ->join('pmieducar.tipo_usuario as tu', 'tu.cod_tipo_usuario', '=', 'u.ref_cod_tipo_usuario')
                ->leftJoin('cadastro.pessoa as p', 'p.idpes', '=', 'u.cod_usuario')
                ->where('u.ativo', 1)
                ->where('f.ativo', 1);

            if ($ator !== null) {
                self::aplicarEscopo($q, $ator);
                if (! $incluirAtor) {
                    $q->where('u.cod_usuario', '<>', (int) $ator->getKey());
                }
            }

            if ($processos !== [] && Schema::hasTable('menus') && Schema::hasTable('pmieducar.menu_tipo_usuario')) {
                $menuIds = DB::table('menus')
                    ->whereIn('process', array_map('intval', $processos))
                    ->pluck('id')
                    ->map(static fn ($id) => (int) $id)
                    ->all();

                if ($menuIds === []) {
                    return [];
                }

                $q->whereExists(function ($sub) use ($menuIds): void {
                    $sub->select(DB::raw(1))
                        ->from('pmieducar.menu_tipo_usuario as mtu')
                        ->whereColumn('mtu.ref_cod_tipo_usuario', 'u.ref_cod_tipo_usuario')
                        ->whereIn('mtu.menu_id', $menuIds)
                        ->where('mtu.visualiza', 1);
                });
            }

            $rows = $q->orderBy('p.nome')
                ->orderBy('f.matricula')
                ->limit($limite)
                ->get([
                    'u.cod_usuario as id',
                    'f.matricula as login',
                    'p.nome',
                    'tu.nivel',
                    'tu.nm_tipo as nivel_nome',
                ]);
        } catch (Throwable) {
            return self::viaUsers($limite);
        }

        $ids = $rows->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $escolas = self::escolasDosUsuarios($ids);

        $out = [];
        foreach ($rows as $r) {
            $id = (int) $r->id;
            $login = (string) ($r->login ?? '');
            $nome = trim((string) ($r->nome ?? ''));
            if ($nome === '') {
                $nome = $login !== '' ? $login : ('Usuário #'.$id);
            }
            $nivelNome = isset($r->nivel_nome) ? (string) $r->nivel_nome : null;
            $escolaTxt = implode(', ', array_slice($escolas[$id] ?? [], 0, 2));
            $metaParts = array_values(array_filter([
                $login !== '' ? $login : null,
                $nivelNome,
                $escolaTxt !== '' ? $escolaTxt : null,
            ]));

            $out[] = [
                'id' => $id,
                'nome' => $nome,
                'login' => $login,
                'meta' => implode(' · ', $metaParts),
                'nivel' => isset($r->nivel) ? (int) $r->nivel : null,
                'nivel_nome' => $nivelNome,
            ];
        }

        return $out;
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $q
     */
    private static function aplicarEscopo($q, User $ator): void
    {
        if (method_exists($ator, 'isAdmin') && $ator->isAdmin()) {
            return;
        }

        if (method_exists($ator, 'isInstitutional') && $ator->isInstitutional()) {
            $q->whereIn('tu.nivel', [LegacyUserType::LEVEL_SCHOOLING, LegacyUserType::LEVEL_LIBRARY]);

            return;
        }

        if (method_exists($ator, 'isSchooling') && $ator->isSchooling()) {
            $q->whereIn('tu.nivel', [LegacyUserType::LEVEL_SCHOOLING, LegacyUserType::LEVEL_LIBRARY]);
            $escolas = self::idsEscolasDoUsuario((int) $ator->getKey());
            if ($escolas === []) {
                $q->whereRaw('1 = 0');

                return;
            }
            $q->whereExists(function ($sub) use ($escolas): void {
                $sub->select(DB::raw(1))
                    ->from('pmieducar.escola_usuario as eu')
                    ->whereColumn('eu.ref_cod_usuario', 'u.cod_usuario')
                    ->whereIn('eu.ref_cod_escola', $escolas);
            });

            return;
        }

        // Biblioteca / outros níveis: restringe à própria escola quando houver vínculo
        $escolas = self::idsEscolasDoUsuario((int) $ator->getKey());
        if ($escolas === []) {
            return;
        }
        $q->whereExists(function ($sub) use ($escolas): void {
            $sub->select(DB::raw(1))
                ->from('pmieducar.escola_usuario as eu')
                ->whereColumn('eu.ref_cod_usuario', 'u.cod_usuario')
                ->whereIn('eu.ref_cod_escola', $escolas);
        });
    }

    /** @return list<int> */
    private static function idsEscolasDoUsuario(int $usuarioId): array
    {
        if (! Schema::hasTable('pmieducar.escola_usuario')) {
            return [];
        }
        try {
            return DB::table('pmieducar.escola_usuario')
                ->where('ref_cod_usuario', $usuarioId)
                ->pluck('ref_cod_escola')
                ->map(static fn ($id) => (int) $id)
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param  list<int>  $usuarioIds
     * @return array<int, list<string>>
     */
    private static function escolasDosUsuarios(array $usuarioIds): array
    {
        if ($usuarioIds === [] || ! Schema::hasTable('pmieducar.escola_usuario')) {
            return [];
        }

        try {
            $rows = DB::table('pmieducar.escola_usuario as eu')
                ->leftJoin('pmieducar.escola as e', 'e.cod_escola', '=', 'eu.ref_cod_escola')
                ->leftJoin('cadastro.pessoa as pe', 'pe.idpes', '=', 'e.ref_idpes')
                ->whereIn('eu.ref_cod_usuario', $usuarioIds)
                ->get(['eu.ref_cod_usuario', 'pe.nome', 'e.cod_escola']);
        } catch (Throwable) {
            return [];
        }

        $map = [];
        foreach ($rows as $r) {
            $uid = (int) $r->ref_cod_usuario;
            $nome = trim((string) ($r->nome ?? ''));
            $label = $nome !== '' ? $nome : ('Escola #'.(int) $r->cod_escola);
            $map[$uid] ??= [];
            if (! in_array($label, $map[$uid], true)) {
                $map[$uid][] = $label;
            }
        }

        return $map;
    }

    /** @return list<UsuarioSelect> */
    private static function viaUsers(int $limite): array
    {
        try {
            if (! Schema::hasTable('users')) {
                return [];
            }
            $q = DB::table('users')->orderBy('name')->limit($limite);
            if (Schema::hasColumn('users', 'active')) {
                $q->where('active', true);
            }

            return $q->get(['id', 'name', 'email'])
                ->map(static function ($u): array {
                    $nome = (string) ($u->name ?: $u->email ?: 'Usuário #'.$u->id);
                    $login = (string) ($u->email ?? '');

                    return [
                        'id' => (int) $u->id,
                        'nome' => $nome,
                        'login' => $login,
                        'meta' => $login,
                        'nivel' => null,
                        'nivel_nome' => null,
                    ];
                })
                ->all();
        } catch (Throwable) {
            return [];
        }
    }
}
