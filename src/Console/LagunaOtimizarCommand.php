<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

/**
 * Religa containers Docker necessários e aponta o runtime para a conexão Laguna.
 * Também limpa caches — útil após exportações pesadas (ex.: PDF RH).
 *
 * Se a porta 443 já estiver com outro container (ex.: i-Diário/puma), o nginx
 * sobe com SSL em DOCKER_NGINX_SSL_PORT (padrão 8443) para não falhar o up.
 */
class LagunaOtimizarCommand extends Command
{
    protected $signature = 'laguna:otimizar
                            {--sem-docker : Não mexe em containers}
                            {--so-docker : Só reinicia containers}
                            {--services=postgres,redis,fpm,nginx,horizon,php : Serviços docker compose}
                            {--ssl-port= : Porta host para HTTPS do nginx (default: 8443 se 443 ocupada)}';

    protected $description = 'Laguna — desliga/religa containers, conecta DB Laguna e otimiza caches/runtime';

    public function handle(): int
    {
        $root = base_path();
        $falhas = 0;

        if (! $this->option('sem-docker')) {
            $falhas += $this->religarDocker($root) ? 0 : 1;
        }

        if ($this->option('so-docker')) {
            return $falhas > 0 ? self::FAILURE : self::SUCCESS;
        }

        $falhas += $this->apontarLaguna() ? 0 : 1;
        $falhas += $this->limparCaches() ? 0 : 1;
        $falhas += $this->checarConexao() ? 0 : 1;

        $this->newLine();
        $this->line('URLs locais:');
        $this->line('  artisan serve → http://127.0.0.1:8000  (ou http://laguna.localhost:8000)');
        $http = (string) (env('DOCKER_NGINX_PORT') ?: '80');
        $https = (string) (env('DOCKER_NGINX_SSL_PORT') ?: '443');
        $this->line("  docker nginx  → http://127.0.0.1:{$http}  · https://127.0.0.1:{$https}");
        $this->line('  Evite https://localhost:8000 — o serve não usa TLS.');

        if ($falhas > 0) {
            $this->warn("Otimização terminou com {$falhas} aviso(s).");

            return self::FAILURE;
        }

        $this->info('Laguna otimizada: containers + conexão + caches OK.');

        return self::SUCCESS;
    }

    private function religarDocker(string $root): bool
    {
        $services = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('services')))));
        if ($services === []) {
            $services = ['postgres', 'redis', 'fpm', 'nginx', 'horizon', 'php'];
        }

        $env = $this->composeEnv($root);

        $this->info('Docker · parando serviços i-Educar: '.implode(', ', $services));
        $down = $this->compose($root, array_merge(['stop'], $services), $env);
        if ($down !== 0) {
            $this->warn('  stop retornou código '.$down.' — tentando up mesmo assim.');
        }

        // Remove container nginx “Created” preso de tentativa anterior
        $this->compose($root, ['rm', '-f', 'nginx'], $env);

        $this->info('Docker · subindo serviços…');
        if (! empty($env['DOCKER_NGINX_SSL_PORT']) && (string) $env['DOCKER_NGINX_SSL_PORT'] !== '443') {
            $this->line('  443 ocupada (provável i-Diário) → nginx HTTPS em :'.$env['DOCKER_NGINX_SSL_PORT']);
        }

        $up = $this->compose($root, array_merge(['up', '-d', '--remove-orphans'], $services), $env);
        if ($up !== 0) {
            // Fallback: sobe sem nginx e avisa (artisan serve / fpm ainda úteis)
            $semNginx = array_values(array_filter($services, static fn (string $s): bool => $s !== 'nginx'));
            $this->warn('  falha no up completo — tentando sem nginx: '.implode(', ', $semNginx));
            $up2 = $this->compose($root, array_merge(['up', '-d', '--remove-orphans'], $semNginx), $env);
            if ($up2 !== 0) {
                $this->error('  falha ao subir containers (código '.$up2.').');

                return false;
            }
            $this->warn('  nginx não subiu. Use: php artisan serve --host=127.0.0.1 --port=8000');
        }

        $this->line('  containers religados.');

        return true;
    }

    /** @return array<string, string> */
    private function composeEnv(string $root): array
    {
        $env = $_ENV + $_SERVER;
        $env = array_filter($env, static fn ($v) => is_string($v) || is_numeric($v));
        $env = array_map(static fn ($v) => (string) $v, $env);

        $sslOpt = trim((string) $this->option('ssl-port'));
        $sslEnv = trim((string) (env('DOCKER_NGINX_SSL_PORT') ?: ''));
        $ssl = $sslOpt !== '' ? $sslOpt : $sslEnv;

        if ($ssl === '' || $ssl === '443') {
            if ($this->portaOcupada(443)) {
                $ssl = '8443';
            } else {
                $ssl = '443';
            }
        }

        $http = trim((string) (env('DOCKER_NGINX_PORT') ?: ''));
        if ($http === '') {
            $http = $this->portaOcupada(80) ? '8080' : '80';
        }

        $env['DOCKER_NGINX_PORT'] = $http;
        $env['DOCKER_NGINX_SSL_PORT'] = $ssl;

        // Reflete no processo atual para a mensagem final de URLs
        putenv('DOCKER_NGINX_PORT='.$http);
        putenv('DOCKER_NGINX_SSL_PORT='.$ssl);
        $_ENV['DOCKER_NGINX_PORT'] = $http;
        $_ENV['DOCKER_NGINX_SSL_PORT'] = $ssl;

        return $env;
    }

    private function portaOcupada(int $porta): bool
    {
        $process = new Process(['bash', '-lc', "ss -lptn 'sport = :{$porta}' 2>/dev/null | grep -q LISTEN"]);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * @param  list<string>  $args
     * @param  array<string, string>  $env
     */
    private function compose(string $root, array $args, array $env = []): int
    {
        $bin = $this->composeBin();
        $cmd = array_merge($bin, $args);
        $process = new Process($cmd, $root, $env === [] ? null : $env, null, 180);
        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        return $process->getExitCode() ?? 1;
    }

    /** @return list<string> */
    private function composeBin(): array
    {
        $v2 = new Process(['docker', 'compose', 'version']);
        $v2->run();
        if ($v2->isSuccessful()) {
            return ['docker', 'compose'];
        }

        return ['docker-compose'];
    }

    private function apontarLaguna(): bool
    {
        $this->info('Conexão · forçando runtime Laguna');
        try {
            config(['database.default' => 'laguna']);
            DB::purge();
            DB::setDefaultConnection('laguna');
            $this->line('  default=laguna · db='.(string) config('database.connections.laguna.database'));

            return true;
        } catch (\Throwable $e) {
            $this->error('  '.$e->getMessage());

            return false;
        }
    }

    private function limparCaches(): bool
    {
        $this->info('Caches · optimize:clear + package:discover');
        $ok = true;
        foreach (['optimize:clear', 'package:discover'] as $cmd) {
            try {
                $code = Artisan::call($cmd);
                $this->line('  '.$cmd.($code === 0 ? ' OK' : ' código '.$code));
                if ($code !== 0) {
                    $ok = false;
                }
            } catch (\Throwable $e) {
                $this->warn('  '.$cmd.': '.$e->getMessage());
                $ok = false;
            }
        }

        return $ok;
    }

    private function checarConexao(): bool
    {
        $this->info('Postgres · ping Laguna');
        try {
            $one = DB::connection('laguna')->select('select 1 as ok');
            $ok = isset($one[0]->ok) && (int) $one[0]->ok === 1;
            $this->line($ok ? '  ping OK' : '  ping sem retorno esperado');

            return $ok;
        } catch (\Throwable $e) {
            $this->error('  '.$e->getMessage());

            return false;
        }
    }
}
