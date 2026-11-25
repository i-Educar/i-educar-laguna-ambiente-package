<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware;

use Closure;
use iEducar\Packages\BuritiLagunaAmbiente\Support\DiretivasModulos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia rotas de módulos desativados na configuração municipal.
 */
final class ExigeModuloAtivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');
        foreach (DiretivasModulos::mapa() as $id => $meta) {
            foreach ($meta['prefixos_rota'] as $prefixo) {
                $prefixo = trim($prefixo, '/');
                if ($prefixo === '') {
                    continue;
                }
                if ($path === $prefixo || str_starts_with($path, $prefixo.'/')) {
                    if (! ModuloConfiguracao::ativo($id)) {
                        abort(403, 'Módulo «'.$id.'» está desativado nas configurações municipais. Peça ao administrador para reativar em Configurações → Módulos.');
                    }
                }
            }
        }

        return $next($request);
    }
}
