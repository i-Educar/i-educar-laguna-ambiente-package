<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Fallback: injeta no rodapé suspenso do /painel o botão admin de módulos/versões/deploy
 * quando a view do monorepo ainda não inclui o partial nativamente.
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

            $temBotao = str_contains($html, 'data-buriti-modulos-deploy');
            $temPainel = str_contains($html, 'data-buriti-modulos-deploy-painel');
            if ($temBotao && $temPainel) {
                return $response;
            }

            $urlJson = url('/laguna-ambiente/modulos-deploy');
            $novo = $html;

            if (! $temBotao) {
                $botao = view('laguna-ambiente::partials.rodape-modulos-deploy-botao')->render();
                $comBotao = preg_replace(
                    '#(<ul\s+class="rodape-operacao-lista"[^>]*>)#i',
                    '$1'.$botao,
                    $novo,
                    1,
                    $countUl
                );
                if (is_string($comBotao) && $countUl > 0) {
                    $novo = $comBotao;
                }
            }

            if (! $temPainel) {
                $painel = view('laguna-ambiente::partials.rodape-modulos-deploy-painel', [
                    'urlJson' => $urlJson,
                ])->render();
                if (str_contains($novo, '</body>')) {
                    $novo = str_replace('</body>', $painel.'</body>', $novo);
                } else {
                    $novo .= $painel;
                }
            }

            if ($novo !== $html) {
                $response->setContent($novo);
            }
        } catch (Throwable) {
            // best-effort
        }

        return $response;
    }
}
