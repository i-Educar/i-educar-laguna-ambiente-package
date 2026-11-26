<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;
use iEducar\Packages\BuritiLagunaAmbiente\Support\Processos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1) Restaura o menu "Verificar módulos" (processo 889914).
 * 2) Ambiente Laguna + Configurações → Módulos só para Administrador.
 * 3) Reaplica matriz prática de tipos × módulos Buriti.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->restaurarMenuCheck();
        $this->garantirMenuModulos();

        if (class_exists(PermissaoMatriz::class)) {
            $r = PermissaoMatriz::sincronizar();
            $this->concederNucleoSecretario();
            // Qualquer outro tipo ativo fora do catálogo Laguna também perde Ambiente Laguna.
            PermissaoMatriz::revogarForaDe(Processos::todos(), [PermissaoMatriz::TIPO_ADMIN]);
        }

        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (Throwable) {
            }
        }

        unset($r);
    }

    public function down(): void
    {
        //
    }

    private function restaurarMenuCheck(): void
    {
        $raiz = Menu::query()->where('process', Processos::RAIZ)->first();
        if ($raiz === null) {
            return;
        }

        // Se 889910 ainda aponta para /laguna-ambiente/check, move para Configurações → Módulos.
        $conflito = Menu::query()->where('process', Processos::MODULOS)->first();
        if ($conflito && str_contains((string) $conflito->link, 'laguna-ambiente/check')) {
            $conflito->process = Processos::CHECK;
            $conflito->save();
        }

        Menu::query()->updateOrCreate(['process' => Processos::CHECK], [
            'parent_id' => $raiz->getKey(),
            'title' => 'Verificar módulos',
            'description' => 'Auditoria de instalação dos pacotes Laguna',
            'link' => '/laguna-ambiente/check',
            'order' => 2,
            'type' => 3,
            'active' => true,
        ]);
    }

    private function garantirMenuModulos(): void
    {
        $configuracoes = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 25)->orWhere('title', 'Configurações');
            })
            ->first();

        if ($configuracoes === null) {
            return;
        }

        Menu::query()->updateOrCreate(['process' => Processos::MODULOS], [
            'parent_id' => $configuracoes->getKey(),
            'title' => 'Módulos',
            'description' => 'Flags, diretivas e ativação dos módulos municipais',
            'link' => '/configuracoes/modulos',
            'icon' => 'fa-cubes',
            'order' => 3,
            'type' => 2,
            'active' => true,
        ]);
    }

    private function concederNucleoSecretario(): void
    {
        if (! Schema::hasTable('pmieducar.menu_tipo_usuario')) {
            return;
        }

        $governados = PermissaoMatriz::menusGovernados();
        $menus = DB::table('menus')
            ->where('active', true)
            ->whereNotNull('process')
            ->when($governados !== [], fn ($q) => $q->whereNotIn('id', $governados))
            ->pluck('id');

        foreach ($menus as $menuId) {
            DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                [
                    'ref_cod_tipo_usuario' => PermissaoMatriz::TIPO_SECRETARIO,
                    'menu_id' => $menuId,
                ],
                ['visualiza' => 1, 'cadastra' => 1, 'exclui' => 0],
            );
        }
    }
};
