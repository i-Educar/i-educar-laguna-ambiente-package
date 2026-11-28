<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;
use iEducar\Packages\BuritiLagunaAmbiente\Support\Processos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1) Tira Relatórios avançados de process=55 (Escola) / NULL e dá faixa 8840xx.
 * 2) Garante menu i-Diário (884100) para ACL/painel.
 * 3) Reaplica PermissaoMatriz (alimentacao/vagas/relatorios/idiario por tipo).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $this->atribuirProcessosRelatorios();
        $this->garantirMenuIdiario();

        if (class_exists(PermissaoMatriz::class)) {
            PermissaoMatriz::sincronizar();
        }

        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (Throwable) {
            }
        }
    }

    public function down(): void
    {
        //
    }

    private function atribuirProcessosRelatorios(): void
    {
        $rows = DB::table('menus')
            ->where(static function ($q): void {
                $q->where('link', 'ilike', '/relatorios-avancados%')
                    ->orWhere('link', 'ilike', 'relatorios-avancados%');
            })
            ->orderBy('id')
            ->get(['id', 'process']);

        $processo = Processos::RELATORIOS;
        foreach ($rows as $row) {
            DB::table('menus')->where('id', $row->id)->update(['process' => $processo]);
            $processo++;
            if ($processo > 884099) {
                break;
            }
        }
    }

    private function garantirMenuIdiario(): void
    {
        $existente = Menu::query()->where('process', Processos::IDIARIO)->first();
        if ($existente !== null) {
            if (empty($existente->link)) {
                $existente->link = '/idiario/entrar';
                $existente->save();
            }

            return;
        }

        Menu::query()->create([
            'parent_id' => null,
            'title' => 'i-Diário',
            'description' => 'Frequência e lançamentos pedagógicos (SSO)',
            'link' => '/idiario/entrar',
            'icon' => 'fa-book',
            'order' => 35,
            'type' => 1,
            'process' => Processos::IDIARIO,
            'active' => true,
        ]);
    }
};
