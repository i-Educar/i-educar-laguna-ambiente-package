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
        $valor = self::lerSettingBruto(self::settingKey($id));
        if ($valor === null) {
            $cfg = config(self::settingKey($id));
            if ($cfg === null) {
                return true;
            }

            return self::truthy($cfg);
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
        // Fonte de verdade: coluna bruta no banco (evita cache/accessor/nesting de config).
        $raw = self::lerSettingBruto($key);
        if ($raw === null || $raw === '') {
            $cfg = config($key);
            if (is_array($cfg)) {
                return $cfg;
            }
            $raw = is_string($cfg) ? $cfg : null;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Lê o value cru da tabela settings (sem accessor Eloquent).
     */
    private static function lerSettingBruto(string $key): ?string
    {
        if (! Schema::hasTable('settings')) {
            return null;
        }
        try {
            $valor = DB::table('settings')->where('key', $key)->value('value');
            if ($valor === null) {
                return null;
            }

            return is_string($valor) ? $valor : (string) $valor;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Valor efetivo de uma diretiva (salvas → config → padrão).
     */
    public static function diretiva(string $id, string $chave, mixed $fallback = null): mixed
    {
        $salvas = self::diretivasSalvas($id);
        if (array_key_exists($chave, $salvas)) {
            return $salvas[$chave];
        }
        foreach (DiretivasModulos::de($id)['diretivas'] as $d) {
            if (($d['chave'] ?? '') !== $chave) {
                continue;
            }
            if (! empty($d['config'])) {
                return config((string) $d['config'], $d['padrao'] ?? $fallback);
            }

            return $d['padrao'] ?? $fallback;
        }

        return $fallback;
    }

    public static function portalExterno(string $id): bool
    {
        $meta = DiretivasModulos::de($id);
        $temFlag = false;
        $padrao = false;
        foreach ($meta['diretivas'] as $d) {
            if (($d['chave'] ?? '') === 'portal_externo') {
                $temFlag = true;
                $padrao = self::truthy($d['padrao'] ?? false);
                break;
            }
        }
        if (! $temFlag) {
            return false;
        }

        return self::truthy(self::diretiva($id, 'portal_externo', $padrao));
    }

    public static function aparecerNaHome(string $id): bool
    {
        if (! self::ativo($id) || ! self::portalExterno($id)) {
            return false;
        }

        return self::truthy(self::diretiva($id, 'aparecer_na_home', false));
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
            if (! empty($mod['ocultar_painel']) || ! empty($mod['agregado_em'])) {
                continue;
            }
            $id = (string) $mod['id'];
            $meta = DiretivasModulos::de($id);
            $salvas = self::diretivasSalvas($id);
            $ativo = self::ativo($id);
            $tenantOk = PackageTenant::allows((string) $mod['composer']);

            $diretivas = [];
            $dominio = self::valoresDominio($id);
            $essenciaisPendentes = 0;
            $temEssenciais = false;
            foreach ($meta['diretivas'] as $d) {
                $chave = $d['chave'];
                if ($chave === 'ativo') {
                    $valor = $ativo;
                } elseif (array_key_exists($chave, $salvas)) {
                    $valor = $salvas[$chave];
                } elseif (array_key_exists($chave, $dominio)) {
                    $valor = $dominio[$chave];
                } elseif (! empty($d['config'])) {
                    $valor = config((string) $d['config'], $d['padrao']);
                } else {
                    $valor = $d['padrao'];
                }
                if (! empty($d['essencial'])) {
                    $temEssenciais = true;
                    $vazio = $valor === null || $valor === '' || $valor === false;
                    if ($vazio) {
                        $essenciaisPendentes++;
                    }
                }
                $diretivas[] = array_merge($d, ['valor' => $valor]);
            }

            $contatoOk = ! $temEssenciais || $essenciaisPendentes === 0;

            $camposTotal = 0;
            $camposOk = 0;
            foreach ($diretivas as $d) {
                if (($d['chave'] ?? '') === 'ativo') {
                    continue;
                }
                $camposTotal++;
                $valor = $d['valor'] ?? null;
                if ($valor !== null && $valor !== '') {
                    $camposOk++;
                }
            }
            $percentual = $camposTotal > 0
                ? (int) round(($camposOk / $camposTotal) * 100)
                : ($ativo ? 100 : 0);

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
                'guia' => (string) ($meta['guia'] ?? ''),
                'contato_ok' => $contatoOk,
                'essenciais_pendentes' => $essenciaisPendentes,
                'ja_configurado' => $salvas !== [],
                'percentual' => $percentual,
                'campos_ok' => $camposOk,
                'campos_total' => $camposTotal,
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

        // TYPE_STRING evita cast ambíguo de booleano no LoadSettings / pluck.
        self::gravarSetting(self::settingKey($id), $ativo ? '1' : '0', Setting::TYPE_STRING, 'Módulo '.$mod['nome'].' ativo');

        $limpas = [];
        foreach ($meta['diretivas'] as $d) {
            $chave = $d['chave'];
            if ($chave === 'ativo') {
                continue;
            }
            $valor = array_key_exists($chave, $diretivas) ? $diretivas[$chave] : ($d['padrao'] ?? '');
            $valor = match ($d['tipo']) {
                'bool' => self::truthy($valor),
                'int' => (int) $valor,
                'float' => (float) $valor,
                'email' => is_string($valor) ? trim($valor) : '',
                'tel' => is_string($valor) ? (preg_replace('/[^\d]/', '', trim($valor)) ?? '') : '',
                'select' => is_string($valor) ? trim($valor) : (string) $valor,
                'textarea', 'string', 'url', 'date' => is_string($valor) ? trim($valor) : (string) $valor,
                default => $valor,
            };
            if (($d['tipo'] ?? '') === 'tel' && $valor !== '') {
                // Guarda só dígitos; a UI aplica máscara na exibição.
                $valor = substr($valor, 0, 13);
            }
            if (($d['tipo'] ?? '') === 'select' && ! empty($d['opcoes']) && is_array($d['opcoes'])) {
                $permitidos = [];
                foreach ($d['opcoes'] as $opt) {
                    $permitidos[] = (string) (is_array($opt) ? ($opt['value'] ?? '') : $opt);
                }
                if (! in_array((string) $valor, $permitidos, true)) {
                    $valor = $d['padrao'] ?? ($permitidos[0] ?? '');
                }
            }
            if (in_array($d['tipo'], ['email'], true) && $valor !== '' && ! filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                return [
                    'ok' => false,
                    'mensagem' => 'E-mail inválido em «'.$d['rotulo'].'».',
                    'avisos' => [],
                ];
            }
            if (in_array($d['tipo'], ['url'], true) && $valor !== '' && ! filter_var($valor, FILTER_VALIDATE_URL)) {
                return [
                    'ok' => false,
                    'mensagem' => 'URL inválida em «'.$d['rotulo'].'».',
                    'avisos' => [],
                ];
            }
            if (isset($d['maxlength']) && is_string($valor) && mb_strlen($valor) > (int) $d['maxlength']) {
                $valor = mb_substr($valor, 0, (int) $d['maxlength']);
            }
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
        $json = json_encode($limpas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return [
                'ok' => false,
                'mensagem' => 'Não foi possível serializar as diretivas para gravação.',
                'avisos' => [],
            ];
        }

        self::gravarSetting(
            self::settingDiretivasKey($id),
            $json,
            Setting::TYPE_STRING,
            'Diretivas do módulo '.$mod['nome']
        );

        // Confirma leitura imediata do que acabou de gravar.
        $lido = self::lerSettingBruto(self::settingDiretivasKey($id));
        if ($lido === null || $lido === '') {
            return [
                'ok' => false,
                'mensagem' => 'Falha ao gravar diretivas na tabela settings. Verifique permissões do banco.',
                'avisos' => [],
            ];
        }

        Config::set(self::settingKey($id), $ativo ? '1' : '0');
        Config::set(self::settingDiretivasKey($id), $json);

        self::sincronizarMenus($mod, $ativo);
        // Cura efeitos colaterais de syncs antigos (processos/parent_id compartilhados).
        self::repararMenusDosDemaisModulos($id);
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

        $pendencias = [];
        foreach ($meta['diretivas'] as $d) {
            if (empty($d['essencial'])) {
                continue;
            }
            $v = $limpas[$d['chave']] ?? '';
            if ($v === null || $v === '' || $v === false) {
                $pendencias[] = (string) ($d['rotulo'] ?? $d['chave']);
            }
        }
        if ($pendencias !== [] && $ativo) {
            $avisos[] = 'Contato incompleto: preencha '.implode(', ', $pendencias).' para o público/rede.';
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
     * Ativa/desativa somente menus deste módulo (por prefixo de rota).
     * Nunca cascateia por parent_id nem por process compartilhado com outro pacote
     * (ex.: exportação e biblioteca já colidiram em 881500).
     *
     * @param  array<string, mixed>  $mod
     */
    private static function sincronizarMenus(array $mod, bool $ativo): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $prefixos = self::prefixosMenuDoModulo($mod);
        if ($prefixos === []) {
            return;
        }

        $ids = Menu::query()
            ->where(function ($q) use ($prefixos): void {
                foreach ($prefixos as $i => $prefixo) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method}(function ($w) use ($prefixo): void {
                        $w->where('link', $prefixo)
                            ->orWhere('link', '/'.$prefixo)
                            ->orWhere('link', 'like', $prefixo.'/%')
                            ->orWhere('link', 'like', '/'.$prefixo.'/%');
                    });
                }
            })
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        Menu::query()->whereIn('id', $ids)->update(['active' => $ativo]);
    }

    /**
     * @param  array<string, mixed>  $mod
     * @return list<string>
     */
    private static function prefixosMenuDoModulo(array $mod): array
    {
        $id = (string) ($mod['id'] ?? '');
        $prefixos = [];
        $meta = DiretivasModulos::de($id);
        foreach ($meta['prefixos_rota'] ?? [] as $p) {
            $p = trim((string) $p, '/');
            if ($p !== '') {
                $prefixos[] = $p;
            }
        }
        $admin = trim((string) ($mod['admin_url'] ?? ''), '/');
        if ($admin !== '') {
            // admin_url pode ter querystring
            $path = parse_url('/'.$admin, PHP_URL_PATH) ?: '/'.$admin;
            $path = trim((string) $path, '/');
            if ($path !== '') {
                $prefixos[] = $path;
            }
        }
        foreach ($mod['rotas'] ?? [] as $rota) {
            $path = parse_url((string) $rota, PHP_URL_PATH) ?: (string) $rota;
            $path = trim($path, '/');
            if ($path !== '' && ! str_contains($path, '{')) {
                // só o primeiro segmento + caminho estável sem parâmetros
                $prefixos[] = $path;
            }
        }

        $prefixos = array_values(array_unique($prefixos));
        // Preferir prefixos mais específicos primeiro não é necessário para o WHERE OR.
        // Remover prefixos genéricos demais (vazios já filtrados).
        return array_values(array_filter(
            $prefixos,
            static fn (string $p): bool => $p !== '' && $p !== 'api' && ! str_starts_with($p, 'api/')
        ));
    }

    /**
     * Reativa menus dos demais módulos que continuam ativos — nunca deixa
     * um save de A derrubar menus de B.
     */
    private static function repararMenusDosDemaisModulos(string $excetoId): void
    {
        foreach (CatalogoModulos::todos() as $outro) {
            $oid = (string) ($outro['id'] ?? '');
            if ($oid === '' || $oid === $excetoId) {
                continue;
            }
            if (! self::ativo($oid)) {
                continue;
            }
            self::sincronizarMenus($outro, true);
        }
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
            Processos::MODULOS,
            $texto,
            '/configuracoes/modulos',
            null,
            NotificationType::OTHER,
        );
        Notificador::paraProcesso(
            Processos::RAIZ,
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
        Notificador::paraProcesso(Processos::MODULOS, $texto, '/configuracoes/modulos');
        Notificador::paraProcesso(Processos::RAIZ, $texto, '/configuracoes/modulos');
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
     * Valores já persistidos no domínio (quando o painel ainda não tem diretivas salvas).
     *
     * @return array<string, mixed>
     */
    private static function valoresDominio(string $id): array
    {
        if ($id === 'transporte' && Schema::hasTable('transporte_escolar.configuracao')) {
            try {
                $rows = DB::table('transporte_escolar.configuracao')
                    ->whereIn('chave', ['aquaviario', 'geocerca', 'raio_metros', 'rateio'])
                    ->pluck('valor', 'chave');

                return [
                    'aquaviario' => ($rows['aquaviario'] ?? '0') === '1',
                    'geocerca' => ($rows['geocerca'] ?? '0') === '1',
                    'raio_metros' => (int) ($rows['raio_metros'] ?? 150),
                    'rateio' => (($rows['rateio'] ?? 'km') === 'alunos') ? 'alunos' : 'km',
                ];
            } catch (Throwable) {
                return [];
            }
        }

        if ($id === 'apps-api'
            && class_exists(\iEducar\Packages\BuritiAppsApi\Services\AppsConfigService::class)) {
            try {
                $estado = app(\iEducar\Packages\BuritiAppsApi\Services\AppsConfigService::class)->estadoAtual();
                $features = is_array($estado['features'] ?? null) ? $estado['features'] : [];

                return [
                    'require_login' => (bool) ($estado['require_login'] ?? true),
                    'token_ttl_minutes' => (int) ($estado['token_ttl_minutes'] ?? 43200),
                    'suspension_message' => (string) ($estado['suspension_message'] ?? ''),
                    'feature_familia' => (bool) ($features['familia'] ?? true),
                    'feature_professor' => (bool) ($features['professor'] ?? true),
                    'feature_admin' => (bool) ($features['admin'] ?? true),
                    'feature_exportacao' => (bool) ($features['exportacao'] ?? true),
                    'feature_diario' => (bool) ($features['diario'] ?? true),
                ];
            } catch (Throwable) {
                return [];
            }
        }

        if ($id === 'alimentacao'
            && class_exists(\iEducar\Packages\BuritiAlimentacaoEscolar\Services\ConfigAlimentacao::class)) {
            try {
                $cfg = \iEducar\Packages\BuritiAlimentacaoEscolar\Services\ConfigAlimentacao::class;
                $rt = method_exists($cfg, 'responsavelTecnico') ? ($cfg::responsavelTecnico() ?? []) : [];
                $slots = $cfg::slotsAtivos();

                $out = [
                    'lanches_por_dia' => (int) $cfg::get('lanches_por_dia', 2),
                    'incluir_jantar' => in_array('jantar', $slots, true),
                    'montar_cardapio' => (bool) $cfg::get('montar_cardapio', true),
                    'bandejas_automaticas' => (bool) $cfg::get('bandejas_automaticas', true),
                    'estimar_porcoes_ao_lancar' => (bool) $cfg::get('estimar_porcoes_ao_lancar', true),
                    'unidade_so_registra_fatos' => (bool) $cfg::get('unidade_so_registra_fatos', true),
                    'alerta_horario_turma' => (bool) $cfg::get('alerta_horario_turma', true),
                    'porcoes_padrao' => (int) $cfg::get('porcoes_padrao', 100),
                ];
                // Só espelha RT quando houver valor — senão o painel cai no config/padrão PoC.
                $rtNome = trim((string) ($rt['nome'] ?? ''));
                if ($rtNome !== '') {
                    $out['rt_nome'] = $rtNome;
                    $out['rt_registro'] = (string) ($rt['registro'] ?? '');
                    $out['rt_cargo'] = (string) ($rt['cargo'] ?? 'Nutricionista responsável técnico');
                    $out['rt_email'] = (string) ($rt['email'] ?? '');
                }

                return $out;
            } catch (Throwable) {
                return [];
            }
        }

        if ($id === 'seguranca'
            && class_exists(\iEducar\Packages\BuritiSeguranca\Services\ConfiguracaoService::class)) {
            try {
                return app(\iEducar\Packages\BuritiSeguranca\Services\ConfiguracaoService::class)->obter();
            } catch (Throwable) {
                return [];
            }
        }

        return [];
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

        if ($id === 'apps-api'
            && class_exists(\iEducar\Packages\BuritiAppsApi\Services\AppsConfigService::class)) {
            try {
                $svc = app(\iEducar\Packages\BuritiAppsApi\Services\AppsConfigService::class);
                $antes = $svc->estadoAtual();
                $features = is_array($antes['features'] ?? null) ? $antes['features'] : [];
                foreach (['familia', 'professor', 'admin', 'exportacao', 'diario'] as $feat) {
                    $chave = 'feature_'.$feat;
                    if (array_key_exists($chave, $limpas)) {
                        $features[$feat] = self::truthy($limpas[$chave]);
                    }
                }
                $svc->salvar([
                    'enabled' => $ativo,
                    'require_login' => self::truthy($limpas['require_login'] ?? true),
                    'token_ttl_minutes' => (int) ($limpas['token_ttl_minutes'] ?? 43200),
                    'suspension_message' => (string) ($limpas['suspension_message'] ?? ''),
                    'features' => $features,
                ]);
            } catch (Throwable) {
            }
        }

        if ($id === 'alimentacao'
            && class_exists(\iEducar\Packages\BuritiAlimentacaoEscolar\Services\ConfigAlimentacao::class)) {
            try {
                $lanches = max(1, min(4, (int) ($limpas['lanches_por_dia'] ?? 2)));
                $slots = ['cafe', 'almoco', 'lanche'];
                for ($i = 2; $i <= $lanches; $i++) {
                    $slots[] = 'lanche_'.$i;
                }
                if (self::truthy($limpas['incluir_jantar'] ?? false)) {
                    $slots[] = 'jantar';
                }
                \iEducar\Packages\BuritiAlimentacaoEscolar\Services\ConfigAlimentacao::salvar([
                    'montar_cardapio' => self::truthy($limpas['montar_cardapio'] ?? true),
                    'bandejas_automaticas' => self::truthy($limpas['bandejas_automaticas'] ?? true),
                    'estimar_porcoes_ao_lancar' => self::truthy($limpas['estimar_porcoes_ao_lancar'] ?? true),
                    'unidade_so_registra_fatos' => self::truthy($limpas['unidade_so_registra_fatos'] ?? true),
                    'alerta_horario_turma' => self::truthy($limpas['alerta_horario_turma'] ?? true),
                    'porcoes_padrao' => max(1, (int) ($limpas['porcoes_padrao'] ?? 100)),
                    'lanches_por_dia' => $lanches,
                    'slots_refeicao' => $slots,
                ]);
                if (method_exists(\iEducar\Packages\BuritiAlimentacaoEscolar\Services\ConfigAlimentacao::class, 'salvarResponsavelTecnico')) {
                    \iEducar\Packages\BuritiAlimentacaoEscolar\Services\ConfigAlimentacao::salvarResponsavelTecnico([
                        'nome' => (string) ($limpas['rt_nome'] ?? ''),
                        'registro' => (string) ($limpas['rt_registro'] ?? ''),
                        'cargo' => (string) ($limpas['rt_cargo'] ?? 'Nutricionista responsável técnico'),
                        'email' => (string) ($limpas['rt_email'] ?? ''),
                    ]);
                }
            } catch (Throwable) {
            }
        }

        if ($id === 'biblioteca' && Schema::hasTable('biblioteca.poc_registro')) {
            try {
                $payload = [
                    'limite_simultaneo' => max(1, min(50, (int) ($limpas['limite_simultaneo'] ?? 3))),
                    'dias_padrao' => max(1, min(90, (int) ($limpas['dias_padrao'] ?? 7))),
                    'bloquear_atraso' => self::truthy($limpas['bloquear_atraso'] ?? true),
                    'atualizado_em' => now()->toIso8601String(),
                ];
                $existente = DB::table('biblioteca.poc_registro')->where('tipo', 'config')->orderByDesc('id')->first();
                if ($existente) {
                    DB::table('biblioteca.poc_registro')->where('id', $existente->id)->update([
                        'titulo' => 'Configuração operacional',
                        'meta' => 'ativo',
                        'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    ]);
                } else {
                    DB::table('biblioteca.poc_registro')->insert([
                        'tipo' => 'config',
                        'titulo' => 'Configuração operacional',
                        'meta' => 'ativo',
                        'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                    ]);
                }
            } catch (Throwable) {
            }
        }

        if ($id === 'transporte' && Schema::hasTable('transporte_escolar.configuracao')) {
            try {
                foreach ([
                    'aquaviario' => self::truthy($limpas['aquaviario'] ?? false) ? '1' : '0',
                    'geocerca' => self::truthy($limpas['geocerca'] ?? false) ? '1' : '0',
                    'raio_metros' => (string) max(0, (int) ($limpas['raio_metros'] ?? 150)),
                    'rateio' => (($limpas['rateio'] ?? 'km') === 'alunos') ? 'alunos' : 'km',
                ] as $chave => $valor) {
                    DB::table('transporte_escolar.configuracao')->updateOrInsert(
                        ['chave' => $chave],
                        ['valor' => $valor]
                    );
                }
            } catch (Throwable) {
            }
        }

        if ($id === 'seguranca'
            && class_exists(\iEducar\Packages\BuritiSeguranca\Services\ConfiguracaoService::class)) {
            try {
                app(\iEducar\Packages\BuritiSeguranca\Services\ConfiguracaoService::class)->salvar([
                    'mfa_obrigatorio' => self::truthy($limpas['mfa_obrigatorio'] ?? false),
                    'passkeys_enabled' => self::truthy($limpas['passkeys_enabled'] ?? true),
                    'max_sessoes' => max(1, min(20, (int) ($limpas['max_sessoes'] ?? 3))),
                    'mfa_janela' => max(0, min(5, (int) ($limpas['mfa_janela'] ?? 1))),
                    'sessoes_rastrear' => self::truthy($limpas['sessoes_rastrear'] ?? true),
                    'sessoes_inativar_apos_dias' => max(1, (int) ($limpas['sessoes_inativar_apos_dias'] ?? 30)),
                    'auditoria_enabled' => self::truthy($limpas['auditoria_enabled'] ?? true),
                    'auditoria_reter_dias' => max(7, (int) ($limpas['auditoria_reter_dias'] ?? 90)),
                    'revogar_tokens_ao_suspender' => self::truthy($limpas['revogar_tokens_ao_suspender'] ?? true),
                    'deslogar_ao_alterar_seguranca' => self::truthy($limpas['deslogar_ao_alterar_seguranca'] ?? true),
                    'exigir_motivo_suspensao' => self::truthy($limpas['exigir_motivo_suspensao'] ?? true),
                    'passkeys_rp_name' => (string) ($limpas['passkeys_rp_name'] ?? 'i-Educar'),
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
