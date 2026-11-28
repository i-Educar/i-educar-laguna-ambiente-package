<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Informações complementares do módulo (setor SME / rede de apoio)
 * para leitura no botão Informes ao lado do Download.
 */
final class InformesModulo
{
    /** @var array<string, true> */
    private static array $renderizado = [];

    private static bool $ajudaRenderizada = false;

    private const array GRUPOS = ['setor', 'rede_apoio'];

    private const array ROTULOS_GRUPO = [
        'setor' => 'Setor responsável (SME)',
        'rede_apoio' => 'Rede de apoio',
    ];

    public static function temComplementares(string $moduloId): bool
    {
        foreach (DiretivasModulos::de($moduloId)['diretivas'] ?? [] as $d) {
            if (! is_array($d)) {
                continue;
            }
            $grupo = (string) ($d['grupo'] ?? '');
            if (in_array($grupo, self::GRUPOS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve o id do catálogo a partir da URL atual (prefixos_rota).
     */
    public static function idDaRequisicao(?string $path = null): ?string
    {
        $path = trim((string) ($path ?? (request()?->path() ?? '')), '/');
        if ($path === '') {
            return null;
        }

        foreach (DiretivasModulos::mapa() as $id => $meta) {
            foreach ($meta['prefixos_rota'] ?? [] as $prefixo) {
                $prefixo = trim((string) $prefixo, '/');
                if ($prefixo === '') {
                    continue;
                }
                if ($path === $prefixo || str_starts_with($path, $prefixo.'/')) {
                    return (string) $id;
                }
            }
        }

        return null;
    }

    public static function podeAlterar(mixed $user = null): bool
    {
        $user ??= Auth::user();
        if ($user === null) {
            return false;
        }
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return (int) ($user->ref_cod_tipo_usuario ?? 0) === 1;
    }

    public static function urlConfiguracao(string $moduloId): string
    {
        $path = '/configuracoes/modulos?modulo='.rawurlencode($moduloId).'&abrir=1';
        try {
            return url($path);
        } catch (\Throwable) {
            return $path;
        }
    }

    public static function nomeModulo(string $moduloId): string
    {
        foreach (CatalogoModulos::todos() as $m) {
            if (($m['id'] ?? '') === $moduloId) {
                return (string) ($m['nome'] ?? $moduloId);
            }
        }

        return $moduloId;
    }

    /**
     * Seções prontas para o modal (somente campos de setor / rede).
     *
     * @return list<array{grupo:string,titulo:string,itens:list<array{rotulo:string,valor:string}>}>
     */
    public static function secoes(string $moduloId): array
    {
        $salvas = ModuloConfiguracao::diretivasSalvas($moduloId);
        $porGrupo = [];

        foreach (DiretivasModulos::de($moduloId)['diretivas'] ?? [] as $d) {
            if (! is_array($d)) {
                continue;
            }
            $grupo = (string) ($d['grupo'] ?? '');
            if (! in_array($grupo, self::GRUPOS, true)) {
                continue;
            }
            $chave = (string) ($d['chave'] ?? '');
            if ($chave === '') {
                continue;
            }
            $valor = trim((string) ($salvas[$chave] ?? ''));
            if ($valor === '') {
                continue;
            }
            $porGrupo[$grupo][] = [
                'rotulo' => (string) ($d['rotulo'] ?? $chave),
                'valor' => $valor,
            ];
        }

        $secoes = [];
        foreach (self::GRUPOS as $grupo) {
            if (empty($porGrupo[$grupo])) {
                continue;
            }
            $secoes[] = [
                'grupo' => $grupo,
                'titulo' => self::ROTULOS_GRUPO[$grupo] ?? $grupo,
                'itens' => $porGrupo[$grupo],
            ];
        }

        return $secoes;
    }

    public static function jaRenderizado(string $moduloId): bool
    {
        return isset(self::$renderizado[$moduloId]);
    }

    public static function marcarRenderizado(string $moduloId): void
    {
        self::$renderizado[$moduloId] = true;
    }

    /** Botão Ajuda (?) do cabeçalho — uma vez por resposta. */
    public static function ajudaJaRenderizada(): bool
    {
        return self::$ajudaRenderizada;
    }

    public static function marcarAjudaRenderizada(): void
    {
        self::$ajudaRenderizada = true;
    }
}
