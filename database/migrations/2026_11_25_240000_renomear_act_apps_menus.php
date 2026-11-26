<?php

use App\Menu;
use App\Services\MenuCacheService;
use Illuminate\Database\Migrations\Migration;

/**
 * Identidade visual: ACT → Contratações · Apps API → APIs (menus do núcleo).
 */
return new class extends Migration
{
    public function up(): void
    {
        Menu::query()
            ->where(function ($q): void {
                $q->where('title', 'Contratações temporárias')
                    ->orWhere('title', 'ACT')
                    ->orWhere('title', 'like', 'ACT %');
            })
            ->update(['title' => 'Contratações']);

        Menu::query()
            ->whereNull('parent_id')
            ->where('link', '/contratacoes-temporarias')
            ->update(['title' => 'Contratações']);

        Menu::query()
            ->where(function ($q): void {
                $q->where('title', 'Apps API')
                    ->orWhere('title', 'APPS')
                    ->orWhere('title', 'Apps');
            })
            ->update(['title' => 'APIs']);

        Menu::query()
            ->where('link', '/apps-api/configuracao')
            ->where(function ($q): void {
                $q->whereNull('parent_id')->orWhere('title', 'like', 'App%');
            })
            ->update(['title' => 'APIs']);

        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (\Throwable) {
            }
        }
    }

    public function down(): void
    {
        Menu::query()
            ->where('title', 'Contratações')
            ->where(function ($q): void {
                $q->where('link', '/contratacoes-temporarias')->orWhere('process', 882000);
            })
            ->update(['title' => 'Contratações temporárias']);

        Menu::query()
            ->where('title', 'APIs')
            ->where(function ($q): void {
                $q->where('link', 'like', '/apps-api%')->orWhere('process', 882101);
            })
            ->update(['title' => 'Apps API']);
    }
};
