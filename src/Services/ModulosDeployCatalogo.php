<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use iEducar\Packages\BuritiLagunaAmbiente\Support\CatalogoModulos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao;
use Throwable;

/**
 * Catálogo de módulos instalados/ativos + versão (composer) + data do último deploy CI.
 */
final class ModulosDeployCatalogo
{
    public const string MANIFEST_REL = 'buriti/modules-deploy.json';

    /**
     * @return array{
     *   gerado_em:string,
     *   app_version:string,
     *   total:int,
     *   ativos:int,
     *   modulos:list<array<string,mixed>>
     * }
     */
    public function listar(): array
    {
        $manifest = $this->lerManifesto();
        $modulos = [];

        foreach (CatalogoModulos::todos() as $mod) {
            $id = (string) ($mod['id'] ?? '');
            $path = (string) ($mod['path'] ?? '');
            $composer = (string) ($mod['composer'] ?? '');
            $base = $path !== '' ? base_path($path) : '';
            $instalado = $base !== '' && is_dir($base) && is_file($base.'/composer.json');
            $provider = (string) ($mod['provider'] ?? '');
            $providerOk = $provider === '' || class_exists($provider);
            $ativoCfg = $id !== '' ? ModuloConfiguracao::ativo($id) : true;
            $ativo = $instalado && $providerOk && $ativoCfg;

            $versaoComposer = $instalado ? $this->versaoComposer($base.'/composer.json') : null;
            $infoLocal = $instalado ? $this->lerDeployInfoValido($base.'/deploy-info.json', $path, $composer, $id) : [];
            $infoManifest = $this->entradaManifesto($manifest, $composer, $path, $id);
            if ($infoManifest !== [] && ! $this->infoPertenceAoModulo($infoManifest, $path, $composer, $id)) {
                $infoManifest = [];
            }

            $versao = (string) ($infoLocal['version'] ?? $infoManifest['version'] ?? $versaoComposer ?? '—');
            $deployedAt = (string) ($infoLocal['deployed_at'] ?? $infoManifest['deployed_at'] ?? '');
            $sha = (string) ($infoLocal['sha'] ?? $infoManifest['sha'] ?? '');
            $tag = (string) ($infoLocal['tag'] ?? $infoManifest['tag'] ?? '');
            $fonte = (string) ($infoLocal['source'] ?? $infoManifest['source'] ?? '');

            // Painel CI: só o que está no servidor (instalado). Ativo/off aparece no badge.
            if (! $instalado) {
                continue;
            }

            $modulos[] = [
                'id' => $id,
                'nome' => (string) ($mod['nome'] ?? $id),
                'composer' => $composer,
                'path' => $path,
                'nivel' => (string) ($mod['nivel'] ?? ''),
                'icone' => (string) ($mod['icone'] ?? 'fa-cube'),
                'cor' => (string) ($mod['cor'] ?? '#0f4c5c'),
                'instalado' => $instalado,
                'ativo' => $ativo,
                'provider_ok' => $providerOk,
                'versao' => $versao,
                'versao_composer' => $versaoComposer,
                'deployed_at' => $deployedAt !== '' ? $deployedAt : null,
                'deployed_at_br' => $deployedAt !== '' ? $this->formatarDataBr($deployedAt) : null,
                'sha' => $sha !== '' ? $sha : null,
                'tag' => $tag !== '' ? $tag : null,
                'source' => $fonte !== '' ? $fonte : null,
            ];
        }

        usort($modulos, static fn (array $a, array $b): int => strcmp((string) $a['nome'], (string) $b['nome']));

        $ativos = count(array_filter($modulos, static fn (array $m): bool => ! empty($m['ativo'])));

        return [
            'gerado_em' => now()->toIso8601String(),
            'app_version' => (string) (config('app.version') ?? '2.12'),
            'total' => count($modulos),
            'ativos' => $ativos,
            'modulos' => $modulos,
        ];
    }

    /**
     * Reconstrói modules-deploy.json a partir dos deploy-info.json no disco.
     * Com $inventariar, preenche ausentes/inválidos via composer.json (mtime).
     *
     * @return array{total:int, inventariados:int, com_ci:int, manifest_path:string, modules:array<string,mixed>}
     */
    public function reconstruirManifesto(bool $inventariar = false): array
    {
        $modules = [];
        $inventariados = 0;
        $comCi = 0;

        foreach (CatalogoModulos::todos() as $mod) {
            $id = (string) ($mod['id'] ?? '');
            $path = (string) ($mod['path'] ?? '');
            $composer = (string) ($mod['composer'] ?? '');
            if ($path === '') {
                continue;
            }
            $base = base_path($path);
            if (! is_dir($base) || ! is_file($base.'/composer.json')) {
                continue;
            }

            $infoPath = $base.'/deploy-info.json';
            $info = $this->lerDeployInfoValido($infoPath, $path, $composer, $id);

            if ($info === [] && $inventariar) {
                $info = $this->inventarDeployInfo($base, $path, $composer, $id);
                $this->gravarJson($infoPath, $info);
                $inventariados++;
            }

            if ($info === []) {
                continue;
            }

            if (($info['source'] ?? '') === 'github-actions') {
                $comCi++;
            }

            foreach (array_filter([$composer, $path, $id, basename($path)]) as $chave) {
                $modules[(string) $chave] = $info;
            }
        }

        $updatedAt = null;
        foreach ($modules as $info) {
            $at = (string) ($info['deployed_at'] ?? '');
            if ($at !== '' && ($updatedAt === null || $at > $updatedAt)) {
                $updatedAt = $at;
            }
        }

        $payload = [
            'modules' => $modules,
            'updated_at' => $updatedAt,
        ];
        $manifestPath = storage_path('app/'.self::MANIFEST_REL);
        $this->gravarJson($manifestPath, $payload);

        return [
            'total' => count(array_unique(array_map(
                static fn (array $i): string => (string) ($i['path'] ?? $i['package'] ?? ''),
                $modules
            ))),
            'inventariados' => $inventariados,
            'com_ci' => $comCi,
            'manifest_path' => $manifestPath,
            'modules' => $modules,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function lerManifesto(): array
    {
        $path = storage_path('app/'.self::MANIFEST_REL);
        if (! is_file($path)) {
            return ['modules' => [], 'updated_at' => null];
        }
        try {
            $data = json_decode((string) file_get_contents($path), true);

            return is_array($data) ? $data : ['modules' => []];
        } catch (Throwable) {
            return ['modules' => []];
        }
    }

    /**
     * @param  array<string,mixed>  $manifest
     * @return array<string,mixed>
     */
    private function entradaManifesto(array $manifest, string $composer, string $path, string $id): array
    {
        $mods = is_array($manifest['modules'] ?? null) ? $manifest['modules'] : [];
        foreach ([$composer, $path, $id, basename($path)] as $chave) {
            if ($chave !== '' && isset($mods[$chave]) && is_array($mods[$chave])) {
                return $mods[$chave];
            }
        }

        return [];
    }

    /**
     * @return array<string,mixed>
     */
    private function lerDeployInfo(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }
        try {
            $data = json_decode((string) file_get_contents($path), true);

            return is_array($data) ? $data : [];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function lerDeployInfoValido(string $file, string $path, string $composer, string $id): array
    {
        $info = $this->lerDeployInfo($file);
        if ($info === [] || ! $this->infoPertenceAoModulo($info, $path, $composer, $id)) {
            return [];
        }

        return $info;
    }

    /**
     * @param  array<string,mixed>  $info
     */
    private function infoPertenceAoModulo(array $info, string $path, string $composer, string $id): bool
    {
        $infoPath = (string) ($info['path'] ?? '');
        $infoComposer = (string) ($info['composer'] ?? '');
        $infoPackage = (string) ($info['package'] ?? '');
        $base = basename($path);

        if ($infoPath !== '' && $infoPath === $path) {
            return true;
        }
        if ($infoComposer !== '' && $infoComposer === $composer) {
            return true;
        }
        if ($infoPackage !== '' && in_array($infoPackage, [$id, $base, $composer], true)) {
            return true;
        }

        // Sem âncora: só aceita se não contradiz o path atual.
        if ($infoPath === '' && $infoComposer === '' && $infoPackage === '') {
            return isset($info['deployed_at']) || isset($info['version']);
        }

        return false;
    }

    /**
     * @return array<string,mixed>
     */
    private function inventarDeployInfo(string $base, string $path, string $composer, string $id): array
    {
        $composerFile = $base.'/composer.json';
        $mtime = is_file($composerFile) ? (int) filemtime($composerFile) : time();
        $deployedAt = gmdate('Y-m-d\TH:i:s\Z', $mtime);

        return [
            'package' => $base !== '' ? basename($path) : $id,
            'composer' => $composer,
            'version' => (string) ($this->versaoComposer($composerFile) ?? ''),
            'sha' => '',
            'full_sha' => '',
            'deployed_at' => $deployedAt,
            'path' => $path,
            'source' => 'inventario-servidor',
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function gravarJson(string $path, array $data): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
        );
    }

    private function versaoComposer(string $path): ?string
    {
        try {
            $data = json_decode((string) file_get_contents($path), true);
            $v = trim((string) ($data['version'] ?? ''));

            return $v !== '' ? $v : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function formatarDataBr(string $iso): string
    {
        try {
            return \Illuminate\Support\Carbon::parse($iso)
                ->timezone(config('app.timezone', 'America/Sao_Paulo'))
                ->format('d/m/Y H:i');
        } catch (Throwable) {
            return $iso;
        }
    }
}
