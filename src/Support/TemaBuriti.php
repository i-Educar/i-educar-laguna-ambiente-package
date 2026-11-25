<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use App\Services\CacheService;
use App\Setting;
use App\SettingCategory;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Tema visual Buriti Laguna (onda 1) — flag env/config + settings.
 */
final class TemaBuriti
{
    public const string SETTING_KEY = 'laguna-ambiente.tema.ativo';

    public const string BODY_CLASS = 'tema-buriti-laguna';

    public static function ativo(): bool
    {
        return self::truthy(config('laguna-ambiente.tema.ativo', false));
    }

    public static function bodyClass(): string
    {
        return self::ativo() ? self::BODY_CLASS : '';
    }

    /** @return list<string> */
    public static function assetUrls(): array
    {
        if (! self::ativo()) {
            return [];
        }

        // Caminhos absolutos a partir da raiz (funcionam com artisan serve e proxy).
        return [
            '/vendor/buriti-tema/tokens.css',
            '/vendor/buriti-tema/skin-core.css',
        ];
    }

    public static function salvar(bool $ativo): void
    {
        $valor = $ativo ? '1' : '0';

        if (! Schema::hasTable('settings')) {
            config([self::SETTING_KEY => $ativo]);
            self::sincronizarIdiario($ativo);

            return;
        }

        $categoriaId = 1;
        try {
            $categoriaId = (int) (SettingCategory::query()->orderBy('id')->value('id') ?: 1);
        } catch (Throwable) {
            $categoriaId = 1;
        }

        $row = Setting::query()->where('key', self::SETTING_KEY)->first();
        if ($row) {
            $row->value = $valor;
            $row->type = Setting::TYPE_BOOLEAN;
            $row->save();
        } else {
            Setting::query()->create([
                'key' => self::SETTING_KEY,
                'value' => $valor,
                'type' => Setting::TYPE_BOOLEAN,
                'description' => 'Tema visual Buriti Laguna (core i-Educar + i-Diário)',
                'setting_category_id' => $categoriaId,
                'hint' => 'Alinha header, lateral e tipografia aos módulos municipais e ao i-Diário',
            ]);
        }

        config([self::SETTING_KEY => $ativo]);

        try {
            CacheService::clearSettings();
        } catch (Throwable) {
            //
        }

        self::sincronizarIdiario($ativo);
    }

    /**
     * Propaga o tema ao i-Diário (flag em disco + ENV no .env do pacote).
     */
    public static function sincronizarIdiario(bool $ativo): void
    {
        $diarioRoot = base_path('packages/portabilis/i-diario');
        if (! is_dir($diarioRoot)) {
            return;
        }

        $flag = $diarioRoot.'/.laguna-tema-buriti';
        try {
            if ($ativo) {
                file_put_contents($flag, "1\n");
            } elseif (is_file($flag)) {
                @unlink($flag);
            }
        } catch (Throwable) {
            //
        }

        $envPath = $diarioRoot.'/.env';
        if (! is_file($envPath) || ! is_writable($envPath)) {
            return;
        }

        try {
            $env = (string) file_get_contents($envPath);
            $linha = 'BURITI_TEMA_ATIVO='.($ativo ? 'true' : 'false');
            if (preg_match('/^BURITI_TEMA_ATIVO=.*/m', $env)) {
                $env = preg_replace('/^BURITI_TEMA_ATIVO=.*/m', $linha, $env, 1);
            } else {
                $env = rtrim($env)."\n{$linha}\n";
            }
            file_put_contents($envPath, $env);
        } catch (Throwable) {
            //
        }
    }

    private static function truthy(mixed $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }
        if (is_int($valor) || is_float($valor)) {
            return (int) $valor === 1;
        }
        $s = strtolower(trim((string) $valor));

        return in_array($s, ['1', 'true', 'on', 'yes', 'sim'], true);
    }
}
