<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::table('menus')
                ->where('link', '/laguna-ambiente/tema')
                ->update(['title' => 'Skins da escola']);
        } catch (Throwable) {
            //
        }
    }

    public function down(): void
    {
        try {
            DB::table('menus')
                ->where('link', '/laguna-ambiente/tema')
                ->update(['title' => 'Tema visual Buriti']);
        } catch (Throwable) {
            //
        }
    }
};
