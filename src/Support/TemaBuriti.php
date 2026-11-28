<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use App\Services\CacheService;
use App\Setting;
use App\SettingCategory;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Catálogo de skins visuais (i-Educar + i-Diário).
 *
 * Settings:
 *  - laguna-ambiente.tema.skin  (id)
 *  - laguna-ambiente.tema.ativo (bool legado; espelha skin !== nenhum)
 */
final class TemaBuriti
{
    public const string SETTING_KEY = 'laguna-ambiente.tema.ativo';

    public const string SETTING_SKIN = 'laguna-ambiente.tema.skin';

    public const string BODY_CLASS = 'tema-buriti-laguna';

    public const string SKIN_NENHUM = 'nenhum';

    /**
     * @return array<string, array{
     *   id:string,nome:string,conceito:string,descricao:string,
     *   body:string,preview:array{fundo:string,topo:string,lateral:string,acento:string,texto:string},
     *   tokens:array<string,string>
     * }>
     */
    public static function catalogo(): array
    {
        return [
            self::SKIN_NENHUM => [
                'id' => self::SKIN_NENHUM,
                'nome' => 'Padrão Portabilis',
                'conceito' => 'Original',
                'descricao' => 'Chrome nativo do i-Educar e SmartAdmin do i-Diário, sem overlay municipal.',
                'body' => '',
                'preview' => [
                    'fundo' => '#eceff1',
                    'topo' => '#27333B',
                    'lateral' => '#2c3b44',
                    'acento' => '#5bc0de',
                    'texto' => '#ffffff',
                ],
                'tokens' => [],
            ],
            'buriti' => [
                'id' => 'buriti',
                'nome' => 'Buriti Laguna',
                'conceito' => 'Institucional',
                'descricao' => 'Azul suave e teal — rail clara, topo discreto, superfícies planas.',
                'body' => 'tema-buriti-skin tema-buriti tema-buriti-laguna',
                'preview' => [
                    'fundo' => '#f6f8fb',
                    'topo' => '#4a7aa3',
                    'lateral' => '#f3f5f8',
                    'acento' => '#5b9e94',
                    'texto' => '#243447',
                ],
                'tokens' => [
                    '--cor' => '#3d6f98',
                    '--destaque' => '#4f84ad',
                    '--acento' => '#5b9e94',
                    '--nav' => '#f3f5f8',
                    '--nav-borda' => '#e3e8ef',
                    '--nav-texto' => '#243447',
                    '--nav-mute' => '#6a7b8c',
                    '--fundo' => '#f6f8fb',
                    '--clara' => '#eef2f7',
                    '--superficie' => '#ffffff',
                    '--tinta' => '#1e2936',
                    '--suave' => '#5f6f80',
                    '--linha' => '#e6ebf1',
                    '--fonte' => '"Manrope", "Segoe UI", system-ui, sans-serif',
                    '--raio' => '10px',
                ],
            ],
            'ebano' => [
                'id' => 'ebano',
                'nome' => 'Ébano',
                'conceito' => 'Elegante',
                'descricao' => 'Ivory quente e bronze — formal e leve para leitura prolongada.',
                'body' => 'tema-buriti-skin tema-ebano',
                'preview' => [
                    'fundo' => '#f9f6f0',
                    'topo' => '#4a423c',
                    'lateral' => '#f7f3ec',
                    'acento' => '#b89a6e',
                    'texto' => '#2a2420',
                ],
                'tokens' => [
                    '--cor' => '#3f3732',
                    '--destaque' => '#5a4f45',
                    '--acento' => '#b89a6e',
                    '--nav' => '#f7f3ec',
                    '--nav-borda' => '#e8e0d4',
                    '--nav-texto' => '#2a2420',
                    '--nav-mute' => '#7a6f62',
                    '--fundo' => '#f9f6f0',
                    '--clara' => '#f1ebe3',
                    '--superficie' => '#fffcf8',
                    '--tinta' => '#1f1a16',
                    '--suave' => '#6e6458',
                    '--linha' => '#e6ddd1',
                    '--fonte' => '"Libre Baskerville", "Georgia", "Segoe UI", serif',
                    '--raio' => '8px',
                ],
            ],
            'horizonte' => [
                'id' => 'horizonte',
                'nome' => 'Horizonte',
                'conceito' => 'Moderna',
                'descricao' => 'Slate suave e azul — contemporâneo sem glow nem elevação exagerada.',
                'body' => 'tema-buriti-skin tema-horizonte',
                'preview' => [
                    'fundo' => '#f5f7fa',
                    'topo' => '#3d4f66',
                    'lateral' => '#f3f5f8',
                    'acento' => '#5b8fd4',
                    'texto' => '#1e293b',
                ],
                'tokens' => [
                    '--cor' => '#334155',
                    '--destaque' => '#3b6fb5',
                    '--acento' => '#5b8fd4',
                    '--nav' => '#f3f5f8',
                    '--nav-borda' => '#e2e8f0',
                    '--nav-texto' => '#1e293b',
                    '--nav-mute' => '#64748b',
                    '--fundo' => '#f5f7fa',
                    '--clara' => '#e9eef5',
                    '--superficie' => '#ffffff',
                    '--tinta' => '#0f172a',
                    '--suave' => '#64748b',
                    '--linha' => '#e2e8f0',
                    '--fonte' => '"Manrope", "Segoe UI", system-ui, sans-serif',
                    '--raio' => '8px',
                ],
            ],
            'orbita' => [
                'id' => 'orbita',
                'nome' => 'Órbita',
                'conceito' => 'Futurista',
                'descricao' => 'Noite ink com teal calmo — contraste alto, sem brilho neon.',
                'body' => 'tema-buriti-skin tema-orbita',
                'preview' => [
                    'fundo' => '#0b1020',
                    'topo' => '#0e1422',
                    'lateral' => '#0f1524',
                    'acento' => '#2a9f90',
                    'texto' => '#dce8e4',
                ],
                'tokens' => [
                    '--cor' => '#121a2e',
                    '--destaque' => '#2a9f90',
                    '--acento' => '#3db8a8',
                    '--nav' => '#0f1524',
                    '--nav-borda' => '#1e2940',
                    '--nav-texto' => '#dce8e4',
                    '--nav-mute' => '#8aa4b8',
                    '--fundo' => '#0b1020',
                    '--clara' => '#151d32',
                    '--superficie' => '#121a2e',
                    '--tinta' => '#e2ece8',
                    '--suave' => '#8aa4b8',
                    '--linha' => '#1e2940',
                    '--fonte' => '"Manrope", "Segoe UI", system-ui, sans-serif',
                    '--raio' => '6px',
                ],
            ],
            'litoral' => [
                'id' => 'litoral',
                'nome' => 'Litoral',
                'conceito' => 'Elegante municipal',
                'descricao' => 'Azul costeiro e verde-mar — rail clara, calmo para o dia a dia escolar.',
                'body' => 'tema-buriti-skin tema-litoral',
                'preview' => [
                    'fundo' => '#f4f8f7',
                    'topo' => '#3a6f88',
                    'lateral' => '#f2f6f5',
                    'acento' => '#4a9e92',
                    'texto' => '#1a2f3a',
                ],
                'tokens' => [
                    '--cor' => '#2a5f78',
                    '--destaque' => '#3a7a92',
                    '--acento' => '#4a9e92',
                    '--nav' => '#f2f6f5',
                    '--nav-borda' => '#dce6e3',
                    '--nav-texto' => '#1a2f3a',
                    '--nav-mute' => '#5a6f78',
                    '--fundo' => '#f4f8f7',
                    '--clara' => '#e8f0ee',
                    '--superficie' => '#fafcfb',
                    '--tinta' => '#12202c',
                    '--suave' => '#5a6f78',
                    '--linha' => '#d8e2df',
                    '--fonte' => '"Manrope", "Segoe UI", system-ui, sans-serif',
                    '--raio' => '12px',
                ],
            ],
        ];
    }

    public static function skinId(): string
    {
        $cfg = config('laguna-ambiente.tema.skin');
        if (is_string($cfg) && trim($cfg) !== '') {
            $id = self::normalizar($cfg);
            // Setting legado só com ativo=true e skin ainda "nenhum" no .env
            if ($id === self::SKIN_NENHUM && self::truthy(config('laguna-ambiente.tema.ativo', false))) {
                return 'buriti';
            }

            return $id;
        }

        return self::truthy(config('laguna-ambiente.tema.ativo', false))
            ? 'buriti'
            : self::SKIN_NENHUM;
    }

    public static function ativo(): bool
    {
        return self::skinId() !== self::SKIN_NENHUM;
    }

    public static function bodyClass(): string
    {
        $skin = self::catalogo()[self::skinId()] ?? null;

        return $skin['body'] ?? '';
    }

    /** @return list<string> */
    public static function assetUrls(): array
    {
        if (! self::ativo()) {
            return [];
        }

        $v = '10';
        $temaDir = public_path('vendor/buriti-tema');
        foreach (['skin-core.css'] as $arquivo) {
            $caminho = $temaDir.DIRECTORY_SEPARATOR.$arquivo;
            if (is_file($caminho)) {
                $v = (string) filemtime($caminho);
                break;
            }
        }

        return [
            '/vendor/buriti-tema/tokens.css?v='.$v,
            '/vendor/buriti-tema/skins-palettes.css?v='.$v,
            '/vendor/buriti-tema/skin-core.css?v='.$v,
        ];
    }

    public static function salvar(bool|string $skinOuAtivo): void
    {
        if (is_bool($skinOuAtivo)) {
            $skinId = $skinOuAtivo ? 'buriti' : self::SKIN_NENHUM;
        } else {
            $skinId = self::normalizar($skinOuAtivo);
        }

        if (! isset(self::catalogo()[$skinId])) {
            $skinId = self::SKIN_NENHUM;
        }

        $ativo = $skinId !== self::SKIN_NENHUM;

        if (! Schema::hasTable('settings')) {
            config([
                self::SETTING_KEY => $ativo,
                self::SETTING_SKIN => $skinId,
                'laguna-ambiente.tema.ativo' => $ativo,
                'laguna-ambiente.tema.skin' => $skinId,
            ]);
            self::sincronizarIdiario($skinId);

            return;
        }

        $categoriaId = 1;
        try {
            $categoriaId = (int) (SettingCategory::query()->orderBy('id')->value('id') ?: 1);
        } catch (Throwable) {
            $categoriaId = 1;
        }

        self::upsertSetting(
            self::SETTING_SKIN,
            $skinId,
            Setting::TYPE_STRING,
            'Skin visual municipal (i-Educar + i-Diário)',
            'buriti | ebano | horizonte | orbita | litoral | nenhum',
            $categoriaId
        );
        self::upsertSetting(
            self::SETTING_KEY,
            $ativo ? '1' : '0',
            Setting::TYPE_BOOLEAN,
            'Tema visual Buriti Laguna (legado: espelha skin ativa)',
            'Mantido por compatibilidade; preferir laguna-ambiente.tema.skin',
            $categoriaId
        );

        config([
            self::SETTING_KEY => $ativo,
            self::SETTING_SKIN => $skinId,
            'laguna-ambiente.tema.ativo' => $ativo,
            'laguna-ambiente.tema.skin' => $skinId,
        ]);

        try {
            CacheService::clearSettings();
        } catch (Throwable) {
            //
        }

        self::sincronizarIdiario($skinId);
    }

    /**
     * Propaga a skin ao i-Diário (flag em disco + ENV no .env do pacote).
     */
    /** Normaliza id de skin (público para smoke tests). */
    public static function normalizarId(string $id): string
    {
        return self::normalizar($id);
    }

    public static function sincronizarIdiario(bool|string $skinOuAtivo): void
    {
        $skinId = is_bool($skinOuAtivo)
            ? ($skinOuAtivo ? 'buriti' : self::SKIN_NENHUM)
            : self::normalizar((string) $skinOuAtivo);
        if (! isset(self::catalogo()[$skinId])) {
            $skinId = self::SKIN_NENHUM;
        }
        $ativo = $skinId !== self::SKIN_NENHUM;

        $diarioRoot = self::diarioRoot();
        if ($diarioRoot === null) {
            return;
        }

        $flagLegado = $diarioRoot.'/.laguna-tema-buriti';
        $flagSkin = $diarioRoot.'/.laguna-tema-skin';
        try {
            if ($ativo) {
                file_put_contents($flagLegado, "1\n");
                file_put_contents($flagSkin, $skinId."\n");
            } else {
                if (is_file($flagLegado)) {
                    @unlink($flagLegado);
                }
                if (is_file($flagSkin)) {
                    @unlink($flagSkin);
                }
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
            $pares = [
                'BURITI_TEMA_ATIVO' => $ativo ? 'true' : 'false',
                'BURITI_TEMA_SKIN' => $skinId,
            ];
            foreach ($pares as $chave => $valor) {
                $linha = $chave.'='.$valor;
                if (preg_match('/^'.preg_quote($chave, '/').'=.*/m', $env)) {
                    $env = preg_replace('/^'.preg_quote($chave, '/').'=.*/m', $linha, $env, 1) ?? $env;
                } else {
                    $env = rtrim($env)."\n{$linha}\n";
                }
            }
            file_put_contents($envPath, $env);
        } catch (Throwable) {
            //
        }
    }

    private static function upsertSetting(
        string $key,
        string $valor,
        string $type,
        string $description,
        string $hint,
        int $categoriaId
    ): void {
        $row = Setting::query()->where('key', $key)->first();
        if ($row) {
            $row->value = $valor;
            $row->type = $type;
            $row->description = $description;
            $row->hint = $hint;
            $row->save();

            return;
        }

        Setting::query()->create([
            'key' => $key,
            'value' => $valor,
            'type' => $type,
            'description' => $description,
            'setting_category_id' => $categoriaId,
            'hint' => $hint,
        ]);
    }

    private static function normalizar(string $id): string
    {
        $id = strtolower(trim($id));
        if ($id === '' || in_array($id, ['0', 'false', 'off', 'none', 'null'], true)) {
            return self::SKIN_NENHUM;
        }
        if (in_array($id, ['1', 'true', 'on', 'yes', 'sim', 'buriti-laguna', 'tema-buriti-laguna'], true)) {
            return 'buriti';
        }

        return $id;
    }

    private static function diarioRoot(): ?string
    {
        if (function_exists('base_path')) {
            try {
                $viaApp = base_path('packages/portabilis/i-diario');
                if (is_dir($viaApp)) {
                    return $viaApp;
                }
            } catch (Throwable) {
                //
            }
        }

        // Support/ → src → pacote → buriti → packages
        $fallback = dirname(__DIR__, 4).'/portabilis/i-diario';

        return is_dir($fallback) ? $fallback : null;
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
