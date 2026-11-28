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
            $infoLocal = $instalado ? $this->lerDeployInfo($base.'/deploy-info.json') : [];
            $infoManifest = $this->entradaManifesto($manifest, $composer, $path, $id);

            $versao = (string) ($infoLocal['version'] ?? $infoManifest['version'] ?? $versaoComposer ?? '—');
            $deployedAt = (string) ($infoLocal['deployed_at'] ?? $infoManifest['deployed_at'] ?? '');
            $sha = (string) ($infoLocal['sha'] ?? $infoManifest['sha'] ?? '');
            $tag = (string) ($infoLocal['tag'] ?? $infoManifest['tag'] ?? '');

            if (! $instalado && ! $ativo) {
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
