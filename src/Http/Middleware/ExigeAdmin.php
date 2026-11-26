<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ambiente Laguna e flags de módulos: só o tipo Administrador (cod 1).
 * Outros perfis com nivel poli-institucional (ex.: Secretário SME) não entram.
 */
final class ExigeAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tipo = $user !== null ? (int) ($user->ref_cod_tipo_usuario ?? 0) : 0;
        if ($user === null || $tipo !== 1) {
            abort(403, 'Ambiente Laguna: acesso restrito ao tipo Administrador.');
        }

        return $next($request);
    }
}
