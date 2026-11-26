<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware;

use Closure;
use iEducar\Packages\BuritiLagunaAmbiente\Support\DiretivasModulos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Complementa a autenticação/permissão do usuário: se o módulo estiver
 * desativado na configuração municipal, impede o uso mesmo com URL salva
 * ou atalho antigo.
 */
final class ExigeModuloAtivo
{
    /** @var list<string> */
    private const IGNORAR_PREFIXOS = [
        'painel',
        'login',
        'logout',
        'password',
        'notificacoes',
        'laguna-ambiente',
        'configuracoes',
        'intranet',
        'storage',
        'vendor',
        'livewire',
        'horizon',
        'telescope',
        '_debugbar',
        'sanctum',
    ];

    public function handle(Request $request, Closure $next, ?string $moduloFixo = null): Response
    {
        $id = $moduloFixo !== null && $moduloFixo !== ''
            ? $moduloFixo
            : $this->resolverModulo($request);

        if ($id === null) {
            return $next($request);
        }

        if (ModuloConfiguracao::ativo($id)) {
            return $next($request);
        }

        $mensagem = 'O módulo «'.$id.'» está desativado nas configurações municipais. '
            .'Peça à SME para reativar em Configurações → Módulos.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $mensagem,
                'modulo' => $id,
                'ativo' => false,
            ], 403);
        }

        if ($request->user()) {
            return redirect('/painel')
                ->with('erro', $mensagem);
        }

        abort(403, $mensagem);
    }

    private function resolverModulo(Request $request): ?string
    {
        $path = trim($request->path(), '/');
        if ($path === '' || $this->deveIgnorar($path)) {
            return null;
        }

        $melhor = null;
        $melhorLen = -1;

        foreach (DiretivasModulos::mapa() as $id => $meta) {
            foreach ($meta['prefixos_rota'] ?? [] as $prefixo) {
                $prefixo = trim((string) $prefixo, '/');
                if ($prefixo === '') {
                    continue;
                }
                if ($path === $prefixo || str_starts_with($path, $prefixo.'/')) {
                    $len = strlen($prefixo);
                    if ($len > $melhorLen) {
                        $melhor = (string) $id;
                        $melhorLen = $len;
                    }
                }
            }
        }

        return $melhor;
    }

    private function deveIgnorar(string $path): bool
    {
        foreach (self::IGNORAR_PREFIXOS as $prefixo) {
            if ($path === $prefixo || str_starts_with($path, $prefixo.'/')) {
                return true;
            }
        }

        return false;
    }
}
