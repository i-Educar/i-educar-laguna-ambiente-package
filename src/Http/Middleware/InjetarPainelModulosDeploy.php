<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Injeta no rodapé suspenso do /painel o botão admin de módulos/versões/deploy.
 * Evita depender de patch no monorepo — sobe só com o CI do pacote Laguna.
 */
final class InjetarPainelModulosDeploy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if (! $request->isMethod('GET')) {
                return $response;
            }
            $path = trim($request->path(), '/');
            if ($path !== 'painel') {
                return $response;
            }
            $user = $request->user();
            if ($user === null || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
                return $response;
            }
            if (! method_exists($response, 'getContent') || ! method_exists($response, 'setContent')) {
                return $response;
            }
            $html = (string) $response->getContent();
            if ($html === '' || ! str_contains($html, 'rodape-operacao-lista')) {
                return $response;
            }
            if (str_contains($html, 'data-buriti-modulos-deploy')) {
                return $response;
            }

            $urlJson = url('/laguna-ambiente/modulos-deploy');
            $botao = view('laguna-ambiente::partials.rodape-modulos-deploy-botao')->render();
            $painel = view('laguna-ambiente::partials.rodape-modulos-deploy-painel', [
                'urlJson' => $urlJson,
            ])->render();

            $novo = preg_replace(
                '#(<ul\s+class="rodape-operacao-lista"[^>]*>)#i',
                '$1'.$botao,
                $html,
                1,
                $countUl
            );
            if (! is_string($novo) || $countUl < 1) {
                return $response;
            }

            if (str_contains($novo, '</body>')) {
                $novo = str_replace('</body>', $painel.'</body>', $novo);
            } else {
                $novo .= $painel;
            }

            $response->setContent($novo);
        } catch (Throwable) {
            // best-effort
        }

        return $response;
    }
}
