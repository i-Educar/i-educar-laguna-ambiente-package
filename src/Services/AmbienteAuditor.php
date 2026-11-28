<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use App\Support\Tenancy\PackageTenant;
use iEducar\Packages\BuritiLagunaAmbiente\Support\AprovacaoModulo;
use iEducar\Packages\BuritiLagunaAmbiente\Support\CatalogoModulos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AmbienteAuditor
{
    /**
     * @return array{
     *   ok:bool,
     *   resumo:array{obrigatorios_ok:int,obrigatorios_total:int,falhas:int,avisos:int},
     *   nucleo:list<array{chave:string,ok:bool,detalhe:string}>,
     *   modulos:list<array{id:string,tr:string,nome:string,nivel:string,ok:bool,situacao:string,checks:list<array{nome:string,ok:bool,detalhe:string,critico?:bool}>}>
     * }
     */
    public function auditar(): array
    {
        $nucleo = $this->checarNucleo();
        $modulos = [];
        $obrigatoriosOk = 0;
        $obrigatoriosTotal = 0;
        $falhas = 0;
        $avisos = 0;
        $contagem = [
            AprovacaoModulo::STATUS_FUNCIONA => 0,
            AprovacaoModulo::STATUS_RESSALVA => 0,
            AprovacaoModulo::STATUS_REPROVADO => 0,
            AprovacaoModulo::STATUS_TESTAR => 0,
        ];

        foreach (CatalogoModulos::todos() as $mod) {
            if (! empty($mod['ocultar_painel']) || ! empty($mod['agregado_em'])) {
                continue; // horário/ACT/eventos → cards sob RH / Servidores
            }
            $checks = $this->checarModulo($mod);
            $tecnico = AprovacaoModulo::classificarTecnico($checks);
            $ok = $tecnico !== AprovacaoModulo::STATUS_REPROVADO;
            $situacao = AprovacaoModulo::situacaoExibicao((string) $mod['id'], $checks);
            $pctChecks = AprovacaoModulo::percentualChecks($checks);
            $contagem[$situacao['status']] = ($contagem[$situacao['status']] ?? 0) + 1;

            if ($mod['nivel'] === 'obrigatorio') {
                $obrigatoriosTotal++;
                if ($tecnico === AprovacaoModulo::STATUS_FUNCIONA) {
                    $obrigatoriosOk++;
                } elseif ($tecnico === AprovacaoModulo::STATUS_REPROVADO) {
                    $falhas++;
                } else {
                    $avisos++;
                }
            } elseif ($tecnico !== AprovacaoModulo::STATUS_FUNCIONA) {
                $avisos++;
            }

            $adminUrl = $mod['admin_url'] ?? null;
            if (($adminUrl === null || $adminUrl === '') && ! empty($mod['rotas'][0]) && ! str_starts_with((string) $mod['rotas'][0], '/api/')) {
                $adminUrl = $mod['rotas'][0];
            }

            $habilitado = true;
            try {
                $habilitado = ModuloConfiguracao::ativo((string) $mod['id']);
            } catch (\Throwable) {
                $habilitado = true;
            }

            $modulos[] = [
                'id' => $mod['id'],
                'tr' => $mod['tr'],
                'nome' => $mod['nome'],
                'nivel' => $mod['nivel'],
                'composer' => $mod['composer'],
                'ok' => $ok,
                'tecnico' => $tecnico,
                'situacao' => $situacao['status'],
                'situacao_meta' => $situacao,
                'checks' => $checks,
                'percentual' => $pctChecks['percentual'],
                'checks_ok' => $pctChecks['ok'],
                'checks_total' => $pctChecks['total'],
                'cor' => $mod['cor'] ?? '#5b6b76',
                'icone' => $mod['icone'] ?? 'fa-cube',
                'admin_url' => $adminUrl,
                'habilitado' => $habilitado,
            ];
        }

        $nucleoOk = ! in_array(false, array_column($nucleo, 'ok'), true);
        if (! $nucleoOk) {
            $falhas++;
        }

        return [
            'ok' => $nucleoOk && $falhas === 0,
            'resumo' => [
                'obrigatorios_ok' => $obrigatoriosOk,
                'obrigatorios_total' => $obrigatoriosTotal,
                'falhas' => $falhas,
                'avisos' => $avisos,
                'por_situacao' => $contagem,
            ],
            'nucleo' => $nucleo,
            'modulos' => $modulos,
            'legenda' => [
                AprovacaoModulo::meta(AprovacaoModulo::STATUS_FUNCIONA),
                AprovacaoModulo::meta(AprovacaoModulo::STATUS_RESSALVA),
                AprovacaoModulo::meta(AprovacaoModulo::STATUS_REPROVADO),
                AprovacaoModulo::meta(AprovacaoModulo::STATUS_TESTAR),
            ],
        ];
    }

    /**
     * @return list<array{chave:string,ok:bool,detalhe:string}>
     */
    private function checarNucleo(): array
    {
        $checks = [];

        $checks[] = [
            'chave' => 'php',
            'ok' => version_compare(PHP_VERSION, '8.3.0', '>='),
            'detalhe' => 'PHP '.PHP_VERSION.' (exige ≥ 8.3)',
        ];

        $laravel = app()->version();
        $checks[] = [
            'chave' => 'laravel',
            'ok' => version_compare($laravel, '13.0.0', '>='),
            'detalhe' => 'Laravel '.$laravel,
        ];

        try {
            DB::connection()->getPdo();
            $escolas = (int) DB::table('pmieducar.escola')->where('ativo', 1)->count();
            $alunos = (int) DB::table('pmieducar.aluno')->where('ativo', 1)->count();
            $turmas = (int) DB::table('pmieducar.turma')->where('ativo', 1)->count();
            $checks[] = [
                'chave' => 'banco',
                'ok' => true,
                'detalhe' => "Postgres OK · escolas={$escolas} turmas={$turmas} alunos={$alunos}",
            ];
            $checks[] = [
                'chave' => 'rede_minima',
                'ok' => $escolas >= 1 && $turmas >= 1,
                'detalhe' => $escolas >= 1 && $turmas >= 1
                    ? 'Rede mínima presente'
                    : 'Rede vazia — rode laguna:setup para povoar',
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'chave' => 'banco',
                'ok' => false,
                'detalhe' => 'Falha de conexão: '.$e->getMessage(),
            ];
        }

        return $checks;
    }

    /**
     * @param  array<string, mixed>  $mod
     * @return list<array{nome:string,ok:bool,detalhe:string}>
     */
    private function checarModulo(array $mod): array
    {
        $checks = [];
        $base = base_path($mod['path']);
        $pastaExiste = is_dir($base);
        $obrigatorio = ($mod['nivel'] ?? '') === 'obrigatorio';

        $checks[] = [
            'nome' => 'pasta',
            'ok' => $pastaExiste,
            'critico' => $obrigatorio,
            'detalhe' => $pastaExiste ? $mod['path'] : 'ausente: '.$mod['path'],
        ];

        $composerOk = $pastaExiste && is_file($base.'/composer.json');
        $checks[] = [
            'nome' => 'composer.json',
            'ok' => $composerOk || ! $obrigatorio,
            'critico' => $obrigatorio,
            'detalhe' => $composerOk ? $mod['composer'] : 'pacote não encontrado no path',
        ];

        if (! empty($mod['provider'])) {
            $classeOk = class_exists($mod['provider']);
            $registrado = in_array($mod['provider'], config('app.providers', []), true)
                || $this->providerBooted($mod['provider']);
            $arquivoProvider = $pastaExiste && is_file($base.'/src/Providers/'.class_basename($mod['provider']).'.php');
            $checks[] = [
                'nome' => 'provider',
                'ok' => $classeOk && $registrado,
                'critico' => $obrigatorio,
                'detalhe' => $classeOk
                    ? ($registrado ? 'classe + registrado' : 'classe existe mas não está em config/app.php')
                    : ($arquivoProvider
                        ? 'fonte no disco, fora do autoload/composer do app — wire path + provider'
                        : 'classe provider ausente'),
            ];
        }

        if (class_exists(PackageTenant::class)) {
            $allows = PackageTenant::allows($mod['composer']);
            $checks[] = [
                'nome' => 'PackageTenant',
                'ok' => $allows || ! $obrigatorio,
                'critico' => false,
                'detalhe' => $allows
                    ? 'liberado no tenant atual'
                    : 'bloqueado em tenants.packages — inclua '.$mod['composer'],
            ];
        }

        foreach ($mod['schemas'] as $schema) {
            $ok = $this->schemaExiste($schema);
            $checks[] = [
                'nome' => 'schema:'.$schema,
                'ok' => $ok || ! $obrigatorio,
                'critico' => false,
                'detalhe' => $ok ? "schema {$schema} presente" : "schema {$schema} ausente (migrate?)",
            ];
        }

        foreach ($mod['processos'] as $processo) {
            $ok = $this->menuProcessoExiste((int) $processo);
            $checks[] = [
                'nome' => 'menu:'.$processo,
                'ok' => $ok || ! $obrigatorio,
                'critico' => false,
                'detalhe' => $ok ? "processo {$processo} no menus" : "processo {$processo} sem menu (migrate do pacote?)",
            ];
        }

        $comandos = $this->comandosRegistrados();
        foreach ($mod['comandos'] as $cmd) {
            $ok = isset($comandos[$cmd]);
            $checks[] = [
                'nome' => 'cmd:'.$cmd,
                'ok' => $ok || ($mod['nivel'] ?? '') === 'opcional',
                'critico' => false,
                'detalhe' => $ok ? 'comando registrado' : 'comando não listado no Artisan',
            ];
        }

        return $checks;
    }

    private function providerBooted(string $provider): bool
    {
        try {
            $loaded = app()->getLoadedProviders();

            return isset($loaded[$provider]);
        } catch (\Throwable) {
            return false;
        }
    }

    private function schemaExiste(string $schema): bool
    {
        try {
            $n = (int) DB::selectOne(
                'select count(*)::int as c from information_schema.schemata where schema_name = ?',
                [$schema]
            )->c;

            return $n > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    private function menuProcessoExiste(int $processo): bool
    {
        try {
            if (! Schema::hasTable('menus') && ! Schema::hasTable('public.menus')) {
                // tabela menus no i-Educar 2.x
            }

            return DB::table('menus')->where('process', $processo)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array<string, true> */
    private function comandosRegistrados(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $cache = [];
        try {
            foreach (Artisan::all() as $name => $command) {
                $cache[(string) $name] = true;
            }
        } catch (\Throwable) {
            //
        }

        // Em requisição HTTP, muitos providers só chamam $this->commands() em
        // runningInConsole() — Artisan::all() fica incompleto e gera falso negativo.
        if (! app()->runningInConsole()) {
            try {
                // PHP_BINARY sob php-fpm costuma ser o binário FPM (não serve para artisan).
                $php = self::phpCliBinario();
                $artisan = base_path('artisan');
                $lines = [];
                $code = 0;
                exec(
                    escapeshellarg($php).' '.escapeshellarg($artisan).' list --raw 2>/dev/null',
                    $lines,
                    $code
                );
                if ($code === 0) {
                    foreach ($lines as $line) {
                        $name = strtok(trim((string) $line), " \t");
                        if (is_string($name) && $name !== '') {
                            $cache[$name] = true;
                        }
                    }
                }
            } catch (\Throwable) {
                //
            }
        }

        return $cache;
    }

    private static function phpCliBinario(): string
    {
        $bin = (string) (PHP_BINARY ?: '');
        if ($bin !== '' && is_executable($bin) && ! preg_match('/fpm|php-fpm/i', $bin)) {
            return $bin;
        }

        foreach (['/usr/bin/php', '/usr/local/bin/php', 'php'] as $candidato) {
            if ($candidato === 'php') {
                return 'php';
            }
            if (is_executable($candidato)) {
                return $candidato;
            }
        }

        return 'php';
    }
}
