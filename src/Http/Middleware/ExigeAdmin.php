<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ambiente Laguna é só para administrador do i-Educar.
 */
final class ExigeAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403, 'Ambiente Laguna: acesso restrito a administradores.');
        }

        return $next($request);
    }
}
