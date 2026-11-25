<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use Illuminate\Support\Facades\View;

/**
 * Shell visual compartilhado (barra de ícones + rodapé Buriti/LibreCode).
 */
final class BuritiUi
{
    public static function registrar(): void
    {
        $path = dirname(__DIR__, 2).'/resources/views/ui';
        if (! is_dir($path)) {
            return;
        }
        View::addNamespace('buriti-ui', $path);
    }

    /**
     * Monta itens de CRUD / relatórios / admin a partir dos grupos laterais.
     *
     * @param  list<array{nome:string,itens:list<array{rotulo:string,url:string,icone?:string,chave?:string}>}>  $grupos
     * @return array{crud:list<array{rotulo:string,url:string,icone:string}>,relatorios:list<array{rotulo:string,url:string,icone:string}>,admin:list<array{rotulo:string,url:string,icone:string}>}
     */
    public static function menusDoFluxo(array $grupos, string $homeModulo): array
    {
        $crud = [];
        $relatorios = [];
        $admin = [];

        foreach ($grupos as $grupo) {
            $nome = mb_strtolower((string) ($grupo['nome'] ?? ''));
            foreach ($grupo['itens'] ?? [] as $item) {
                $entrada = [
                    'rotulo' => (string) $item['rotulo'],
                    'url' => (string) $item['url'],
                    'icone' => (string) ($item['icone'] ?? 'fa-circle-o'),
                ];
                $chave = mb_strtolower((string) ($item['chave'] ?? $item['rotulo'] ?? ''));
                $url = mb_strtolower($entrada['url']);

                if (str_contains($nome, 'relat') || str_contains($nome, 'consulta')
                    || str_contains($chave, 'relat') || str_contains($chave, 'historico') || str_contains($chave, 'mapa')
                    || str_contains($url, 'relatorio') || str_contains($url, 'historico') || str_contains($url, '/mapa')) {
                    $relatorios[] = $entrada;
                    continue;
                }
                if (str_contains($nome, 'audit') || str_contains($chave, 'audit') || str_contains($url, 'auditoria')
                    || str_contains($nome, 'admin') || str_contains($chave, 'config') || str_contains($chave, 'equipe')
                    || str_contains($chave, 'demo') || str_contains($url, 'config') || str_contains($url, 'configuracao')
                    || str_contains($url, 'governanca') || str_contains($url, '/equipe') || str_contains($url, '/demo')) {
                    $admin[] = $entrada;
                    continue;
                }
                if (str_contains($nome, 'cadastro') || str_contains($nome, 'opera') || str_contains($nome, 'poc')) {
                    $crud[] = $entrada;
                    continue;
                }
                $crud[] = $entrada;
            }
        }

        // Garante ações CRUD em português no submenu, apontando ao início do módulo quando o fluxo for enxuto.
        if ($crud === []) {
            $crud = [
                ['rotulo' => 'Listar', 'url' => $homeModulo, 'icone' => 'fa-list'],
                ['rotulo' => 'Cadastrar', 'url' => $homeModulo, 'icone' => 'fa-plus'],
            ];
        } else {
            // Prefixa rótulos genéricos se ainda não houver verbos CRUD explícitos.
            $temListar = false;
            foreach ($crud as $c) {
                if (str_starts_with(mb_strtolower($c['rotulo']), 'listar') || str_contains(mb_strtolower($c['rotulo']), 'painel')) {
                    $temListar = true;
                    break;
                }
            }
            if (! $temListar) {
                array_unshift($crud, ['rotulo' => 'Listar / Painel', 'url' => $homeModulo, 'icone' => 'fa-list']);
            }
        }

        if ($relatorios === []) {
            $relatorios[] = ['rotulo' => 'Relatórios', 'url' => $homeModulo, 'icone' => 'fa-bar-chart'];
        }
        if ($admin === []) {
            $admin[] = ['rotulo' => 'Administração', 'url' => $homeModulo, 'icone' => 'fa-cog'];
        }

        return [
            'crud' => self::unicos($crud),
            'relatorios' => self::unicos($relatorios),
            'admin' => self::unicos($admin),
        ];
    }

    /**
     * @param  list<array{rotulo:string,url:string,icone:string}>  $itens
     * @return list<array{rotulo:string,url:string,icone:string}>
     */
    private static function unicos(array $itens): array
    {
        $vistos = [];
        $out = [];
        foreach ($itens as $item) {
            $k = $item['url'].'|'.$item['rotulo'];
            if (isset($vistos[$k])) {
                continue;
            }
            $vistos[$k] = true;
            $out[] = $item;
        }

        return $out;
    }
}
