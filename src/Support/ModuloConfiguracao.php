<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use App\Menu;
use App\Models\NotificationType;
use App\Services\CacheService;
use App\Services\MenuCacheService;
use App\Setting;
use App\SettingCategory;
use App\Support\Tenancy\PackageTenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class ModuloConfiguracao
{
    public const string PREFIXO_SETTING = 'buriti.modulos.';

    public static function settingKey(string $id): string
    {
        return self::PREFIXO_SETTING.$id.'.ativo';
    }

    public static function settingDiretivasKey(string $id): string
    {
        return self::PREFIXO_SETTING.$id.'.diretivas';
    }

    public static function ativo(string $id): bool
    {
        $key = self::settingKey($id);
        $valor = config($key);
        if ($valor === null && Schema::hasTable('settings')) {
            $valor = Setting::query()->where('key', $key)->value('value');
        }
        if ($valor === null) {
            return true;
        }

        return self::truthy($valor);
    }

    public static function ativoPorComposer(string $composer): bool
    {
        foreach (CatalogoModulos::todos() as $mod) {
            if (($mod['composer'] ?? '') === $composer) {
                return self::ativo((string) $mod['id']);
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public static function diretivasSalvas(string $id): array
    {
        $key = self::settingDiretivasKey($id);
        $raw = config($key);
        if ($raw === null && Schema::hasTable('settings')) {
            $raw = Setting::query()->where('key', $key)->value('value');
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }
        if (is_array($raw)) {
            return $raw;
        }

        return [];
    }

    /**
     * Snapshot para a UI.
     *
     * @return list<array<string, mixed>>
     */
    public static function painel(): array
    {
        $itens = [];
        foreach (CatalogoModulos::todos() as $mod) {
            $id = (string) $mod['id'];
            $meta = DiretivasModulos::de($id);
            $salvas = self::diretivasSalvas($id);
            $ativo = self::ativo($id);
            $tenantOk = PackageTenant::allows((string) $mod['composer']);

            $diretivas = [];
            foreach ($meta['diretivas'] as $d) {
                $chave = $d['chave'];
                if ($chave === 'ativo') {
                    $valor = $ativo;
                } elseif (array_key_exists($chave, $salvas)) {
                    $valor = $salvas[$chave];
                } elseif (! empty($d['config'])) {
                    $valor = config((string) $d['config'], $d['padrao']);
                } else {
                    $valor = $d['padrao'];
                }
                $diretivas[] = array_merge($d, ['valor' => $valor]);
            }

            $itens[] = [
                'id' => $id,
                'nome' => $mod['nome'],
                'tr' => $mod['tr'] ?? '',
                'composer' => $mod['composer'],
                'nivel' => $mod['nivel'] ?? 'opcional',
                'cor' => $mod['cor'] ?? '#5b6b76',
                'icone' => $mod['icone'] ?? 'fa-cube',
                'admin_url' => $mod['admin_url'] ?? null,
                'processos' => $mod['processos'] ?? [],
                'ativo' => $ativo,
                'tenant_ok' => $tenantOk,
                'bloqueavel' => (bool) ($meta['bloqueavel'] ?? true),
                'impactos' => $meta['impactos_desativar'],
                'fluxos' => $meta['fluxos_comprometidos'],
                'diretivas' => $diretivas,
                'prefixos_rota' => $meta['prefixos_rota'],
            ];
        }

        return $itens;
    }

    /**
     * @param  array<string, mixed>  $diretivas
     * @return array{ok:bool,mensagem:string,avisos:list<string>}
     */
    public static function salvar(string $id, bool $ativo, array $diretivas, bool $cienteRiscos = false): array
    {
        $mod = null;
        foreach (CatalogoModulos::todos() as $m) {
            if ($m['id'] === $id) {
                $mod = $m;
                break;
            }
        }
        if ($mod === null) {
            return ['ok' => false, 'mensagem' => 'Módulo desconhecido.', 'avisos' => []];
        }

        $meta = DiretivasModulos::de($id);
        if (! ($meta['bloqueavel'] ?? true) && ! $ativo) {
            return [
                'ok' => false,
                'mensagem' => 'Este módulo não pode ser desativado pela cidade (infraestrutura).',
                'avisos' => $meta['impactos_desativar'],
            ];
        }

        $antes = self::ativo($id);
        if ($antes && ! $ativo && ! $cienteRiscos) {
            return [
                'ok' => false,
                'mensagem' => 'Confirme que está ciente dos riscos e impactos para a cidade antes de desativar.',
                'avisos' => array_merge($meta['impactos_desativar'], $meta['fluxos_comprometidos']),
            ];
        }

        if (($mod['nivel'] ?? '') === 'obrigatorio' && ! $ativo && ! $cienteRiscos) {
            return [
                'ok' => false,
                'mensagem' => 'Módulo obrigatório no ensaio Laguna: marque ciência dos riscos para desativar.',
                'avisos' => $meta['impactos_desativar'],
            ];
        }

        self::gravarSetting(self::settingKey($id), $ativo ? '1' : '0', Setting::TYPE_BOOLEAN, 'Módulo '.$mod['nome'].' ativo');

        $limpas = [];
        foreach ($meta['diretivas'] as $d) {
            $chave = $d['chave'];
            if ($chave === 'ativo') {
                continue;
            }
            $valor = $diretivas[$chave] ?? $d['padrao'];
            $valor = match ($d['tipo']) {
                'bool' => self::truthy($valor),
                'int' => (int) $valor,
                'float' => (float) $valor,
                default => $valor,
            };
            if (isset($d['min']) && is_numeric($valor) && $valor < $d['min']) {
                $valor = $d['min'];
            }
            if (isset($d['max']) && is_numeric($valor) && $valor > $d['max']) {
                $valor = $d['max'];
            }
            $limpas[$chave] = $valor;
            if (! empty($d['config'])) {
                Config::set((string) $d['config'], $valor);
            }
        }
        self::gravarSetting(
            self::settingDiretivasKey($id),
            json_encode($limpas, JSON_UNESCAPED_UNICODE),
            Setting::TYPE_STRING,
            'Diretivas do módulo '.$mod['nome']
        );

        Config::set(self::settingKey($id), $ativo);
        Config::set(self::settingDiretivasKey($id), $limpas);

        self::sincronizarMenus($mod, $ativo);
        self::aplicarDiretivasRuntime($id, $limpas);
        self::espelharDominio($id, $ativo, $limpas);

        $avisos = [];
        if ($antes && ! $ativo) {
            $avisos = array_values(array_unique(array_merge($meta['impactos_desativar'], $meta['fluxos_comprometidos'])));
            self::notificarDesativacao($mod, $avisos);
        } elseif (! $antes && $ativo) {
            self::notificarReativacao($mod);
        }

        try {
            CacheService::clearSettings();
        } catch (Throwable) {
        }
        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (Throwable) {
            }
        }

        return [
            'ok' => true,
            'mensagem' => $ativo
                ? 'Módulo «'.$mod['nome'].'» ativo e diretivas salvas.'
                : 'Módulo «'.$mod['nome'].'» desativado. Menus e rotas bloqueados.',
            'avisos' => $avisos,
        ];
    }

    public static function aplicarTodasRuntime(): void
    {
        foreach (CatalogoModulos::todos() as $mod) {
            $id = (string) $mod['id'];
            $salvas = self::diretivasSalvas($id);
            self::aplicarDiretivasRuntime($id, $salvas);
            Config::set(self::settingKey($id), self::ativo($id));
        }
    }

    /**
     * @param  array<string, mixed>  $valores
     */
    public static function aplicarDiretivasRuntime(string $id, array $valores): void
    {
        $meta = DiretivasModulos::de($id);
        foreach ($meta['diretivas'] as $d) {
            if ($d['chave'] === 'ativo' || empty($d['config'])) {
                continue;
            }
            $valor = $valores[$d['chave']] ?? config((string) $d['config'], $d['padrao']);
            Config::set((string) $d['config'], $valor);
        }
    }

    /**
     * @param  array<string, mixed>  $mod
     */
    private static function sincronizarMenus(array $mod, bool $ativo): void
    {
        $processos = $mod['processos'] ?? [];
        if ($processos === [] || ! Schema::hasTable('menus')) {
            return;
        }

        $ids = Menu::query()->whereIn('process', $processos)->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        // Desativa raiz e folhas ligadas aos processos do módulo
        Menu::query()->whereIn('id', $ids)->update(['active' => $ativo]);
        Menu::query()->whereIn('parent_id', $ids)->update(['active' => $ativo]);
    }

    /**
     * @param  array<string, mixed>  $mod
     * @param  list<string>  $avisos
     */
    private static function notificarDesativacao(array $mod, array $avisos): void
    {
        if (! class_exists(Notificador::class)) {
            return;
        }
        $texto = 'Configuração municipal: módulo «'.$mod['nome'].'» foi DESATIVADO. '
            .'Impactos: '.implode(' · ', array_slice($avisos, 0, 4));
        Notificador::paraProcesso(
            889910,
            $texto,
            '/configuracoes/modulos',
            null,
            NotificationType::OTHER,
        );
        // Fallback: processo Ambiente Laguna
        Notificador::paraProcesso(
            889900,
            $texto,
            '/configuracoes/modulos',
            null,
            NotificationType::OTHER,
        );
    }

    /** @param  array<string, mixed>  $mod */
    private static function notificarReativacao(array $mod): void
    {
        if (! class_exists(Notificador::class)) {
            return;
        }
        $texto = 'Configuração municipal: módulo «'.$mod['nome'].'» foi REATIVADO.';
        Notificador::paraProcesso(889910, $texto, '/configuracoes/modulos');
        Notificador::paraProcesso(889900, $texto, '/configuracoes/modulos');
    }

    private static function gravarSetting(string $key, string $value, string $type, string $description): void
    {
        if (! Schema::hasTable('settings')) {
            Config::set($key, $type === Setting::TYPE_BOOLEAN ? self::truthy($value) : $value);

            return;
        }

        $categoriaId = 1;
        try {
            $categoriaId = (int) (SettingCategory::query()->orderBy('id')->value('id') ?: 1);
        } catch (Throwable) {
            $categoriaId = 1;
        }

        $row = Setting::query()->where('key', $key)->first();
        if ($row) {
            $row->value = $value;
            $row->type = $type;
            $row->save();
        } else {
            Setting::query()->create([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'setting_category_id' => $categoriaId,
                'hint' => 'Governança de módulos Buriti / Laguna',
            ]);
        }
    }

    /**
     * Espelha diretivas em schemas de domínio quando existirem (ex.: busca_ativa.configuracao).
     *
     * @param  array<string, mixed>  $limpas
     */
    private static function espelharDominio(string $id, bool $ativo, array $limpas): void
    {
        if ($id === 'busca-ativa'
            && class_exists(\iEducar\Packages\BuritiBuscaAtiva\Services\ConfiguracaoService::class)) {
            try {
                $atual = \iEducar\Packages\BuritiBuscaAtiva\Services\ConfiguracaoService::obter();
                \iEducar\Packages\BuritiBuscaAtiva\Services\ConfiguracaoService::salvar([
                    'faltas_consecutivas_alerta' => $limpas['faltas_consecutivas_alerta'] ?? $atual['faltas_consecutivas_alerta'],
                    'faltas_mensais_alerta' => $limpas['faltas_mensais_alerta'] ?? $atual['faltas_mensais_alerta'],
                    'frequencia_minima_percentual' => $limpas['frequencia_minima_percentual'] ?? $atual['frequencia_minima_percentual'],
                    'dias_sem_contato_escalar' => $atual['dias_sem_contato_escalar'] ?? 7,
                    'gerar_alerta_atendimento' => $limpas['gerar_alerta_atendimento'] ?? $atual['gerar_alerta_atendimento'],
                    'notificar_familia' => $atual['notificar_familia'] ?? true,
                    'somente_cursos_marcados' => $limpas['somente_cursos_marcados'] ?? $atual['somente_cursos_marcados'],
                    'abrir_processo_automatico' => $atual['abrir_processo_automatico'] ?? true,
                    'ativo' => $ativo,
                ]);
            } catch (Throwable) {
            }
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
