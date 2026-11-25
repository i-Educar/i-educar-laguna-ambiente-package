<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use App\Support\Tenancy\PackageTenant;
use iEducar\Packages\BuritiLagunaAmbiente\Support\CatalogoModulos;
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
     *   modulos:list<array{id:string,tr:string,nome:string,nivel:string,ok:bool,checks:list<array{nome:string,ok:bool,detalhe:string}>}>
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

        foreach (CatalogoModulos::todos() as $mod) {
            $checks = $this->checarModulo($mod);
            $ok = ! in_array(false, array_column($checks, 'ok'), true);
            if ($mod['nivel'] === 'obrigatorio') {
                $obrigatoriosTotal++;
                if ($ok) {
                    $obrigatoriosOk++;
                } else {
                    $falhas++;
                }
            } elseif (! $ok) {
                $avisos++;
            }

            $modulos[] = [
                'id' => $mod['id'],
                'tr' => $mod['tr'],
                'nome' => $mod['nome'],
                'nivel' => $mod['nivel'],
                'composer' => $mod['composer'],
                'ok' => $ok,
                'checks' => $checks,
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
            ],
            'nucleo' => $nucleo,
            'modulos' => $modulos,
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
        $checks[] = [
            'nome' => 'pasta',
            'ok' => $pastaExiste,
            'detalhe' => $pastaExiste ? $mod['path'] : 'ausente: '.$mod['path'],
        ];

        $composerOk = $pastaExiste && is_file($base.'/composer.json');
        $checks[] = [
            'nome' => 'composer.json',
            'ok' => $composerOk || $mod['nivel'] === 'opcional',
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
                'ok' => $allows || $mod['nivel'] !== 'obrigatorio',
                'detalhe' => $allows
                    ? 'liberado no tenant atual'
                    : 'bloqueado em tenants.packages — inclua '.$mod['composer'],
            ];
        }

        foreach ($mod['schemas'] as $schema) {
            $ok = $this->schemaExiste($schema);
            $checks[] = [
                'nome' => 'schema:'.$schema,
                'ok' => $ok || $mod['nivel'] !== 'obrigatorio',
                'detalhe' => $ok ? "schema {$schema} presente" : "schema {$schema} ausente (migrate?)",
            ];
        }

        foreach ($mod['processos'] as $processo) {
            $ok = $this->menuProcessoExiste((int) $processo);
            $checks[] = [
                'nome' => 'menu:'.$processo,
                'ok' => $ok || $mod['nivel'] !== 'obrigatorio',
                'detalhe' => $ok ? "processo {$processo} no menus" : "processo {$processo} sem menu (migrate do pacote?)",
            ];
        }

        $comandos = $this->comandosRegistrados();
        foreach ($mod['comandos'] as $cmd) {
            $ok = isset($comandos[$cmd]);
            $checks[] = [
                'nome' => 'cmd:'.$cmd,
                'ok' => $ok || $mod['nivel'] === 'opcional',
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
                $cache[$name] = true;
            }
        } catch (\Throwable) {
            //
        }

        return $cache;
    }
}
