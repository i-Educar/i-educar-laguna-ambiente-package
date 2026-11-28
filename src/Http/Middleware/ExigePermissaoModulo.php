<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware;

use Closure;
use iEducar\Packages\BuritiLagunaAmbiente\Support\InformesModulo;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia URL de módulo Buriti/Laguna quando o tipo de usuário não tem ACL
 * (complementa ExigeModuloAtivo, que só olha flag municipal).
 */
final class ExigePermissaoModulo
{
    /** @var list<string> */
    private const IGNORAR_PREFIXOS = [
        'painel',
        'login',
        'logout',
        'password',
        'notificacoes',
        'storage',
        'vendor',
        'livewire',
        'horizon',
        'telescope',
        '_debugbar',
        'sanctum',
        'module',
        'intranet',
    ];

    public function handle(Request $request, Closure $next, ?string $moduloFixo = null): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        $path = trim($request->path(), '/');
        if ($path === '' || $this->deveIgnorar($path)) {
            return $next($request);
        }

        if ($this->ehRestritoAdmin($path)) {
            return $this->negar(
                $request,
                'Sem permissão para configurações administrativas ou Ambiente Laguna.'
            );
        }

        $id = $moduloFixo !== null && $moduloFixo !== ''
            ? $moduloFixo
            : InformesModulo::idDaRequisicao($path);

        if ($id === null) {
            return $next($request);
        }

        if (PermissaoMatriz::usuarioPodeAcessarModulo($user, $id)) {
            return $next($request);
        }

        return $this->negar(
            $request,
            'Seu perfil não tem permissão para o módulo «'.$id.'».'
        );
    }

    private function negar(Request $request, string $mensagem): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $mensagem], 403);
        }

        return redirect('/painel')->with('erro', $mensagem);
    }

    private function ehRestritoAdmin(string $path): bool
    {
        $prefixos = [
            'laguna-ambiente',
            'configuracoes',
            'configuracoes/modulos',
        ];

        foreach ($prefixos as $prefixo) {
            if ($path === $prefixo || str_starts_with($path, $prefixo.'/')) {
                return true;
            }
        }

        return false;
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
