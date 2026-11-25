<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Controllers;

use App\User;
use iEducar\Packages\BuritiLagunaAmbiente\Services\AmbienteAuditor;
use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaSetupService;
use iEducar\Packages\BuritiLagunaAmbiente\Support\CatalogoModulos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\TemaBuriti;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class AmbienteController
{
    public function index(AmbienteAuditor $auditor): View
    {
        $relatorio = $auditor->auditar();

        return view('laguna-ambiente::painel', [
            'relatorio' => $relatorio,
            'appsApi' => $this->snapshotAppsApi($relatorio),
            'atalhosModulos' => $this->atalhosModulos(),
        ]);
    }

    public function check(AmbienteAuditor $auditor): View
    {
        return view('laguna-ambiente::check', [
            'relatorio' => $auditor->auditar(),
            'appsApi' => $this->snapshotAppsApi(),
        ]);
    }

    public function setupForm(): View
    {
        return view('laguna-ambiente::setup');
    }

    public function setupRun(Request $request, LagunaSetupService $setup): RedirectResponse
    {
        if (App::environment('production') && ! $request->boolean('force_production')) {
            return redirect('/laguna-ambiente/setup')
                ->with('erro', 'Recusado em production. Marque force ou use homologação.');
        }

        $passos = $setup->executar(
            comSetupMunicipal: ! $request->boolean('skip_municipal'),
            comMatriculas: ! $request->boolean('skip_matriculas'),
        );

        $ok = count(array_filter($passos, static fn ($p) => $p['ok'])) === count($passos);

        return redirect('/laguna-ambiente/check')
            ->with($ok ? 'ok' : 'erro', $ok
                ? 'Setup executado. Confira a auditoria abaixo.'
                : 'Setup parcial — veja falhas no histórico do comando ou rode laguna:setup no terminal.')
            ->with('passos', $passos);
    }

    public function temaForm(): View
    {
        return view('laguna-ambiente::tema', [
            'ativo' => TemaBuriti::ativo(),
        ]);
    }

    public function usuarios(Request $request): View
    {
        $usuariosAtivos = [];
        $suspensos = [];
        $historico = [];
        $resumo = ['tokens' => 0, 'suspensos' => 0, 'eventos' => 0, 'apps_on' => false];

        /** @var User|null $ator */
        $ator = $request->user();

        if (class_exists(\iEducar\Packages\BuritiAppsApi\Services\AppsUsuarioEscopoService::class)) {
            $usuariosAtivos = app(\iEducar\Packages\BuritiAppsApi\Services\AppsUsuarioEscopoService::class)
                ->usuariosSelecionaveis($ator, $request->query('q'));
        }
        if (class_exists(\iEducar\Packages\BuritiAppsApi\Services\AppsMonitorService::class)) {
            $monitor = app(\iEducar\Packages\BuritiAppsApi\Services\AppsMonitorService::class);
            $suspensos = $monitor->usuariosSuspensos();
            $historico = $monitor->historicoBloqueios(40);
            $resumo = $monitor->resumo();
        }

        return view('laguna-ambiente::usuarios', [
            'usuariosAtivos' => $usuariosAtivos,
            'suspensos' => $suspensos,
            'historico' => $historico,
            'resumo' => $resumo,
            'buscaUsuarios' => (string) $request->query('q', ''),
        ]);
    }

    public function temaSalvar(Request $request): RedirectResponse
    {
        $ativo = $request->boolean('ativo');
        TemaBuriti::salvar($ativo);

        return redirect('/laguna-ambiente/tema')
            ->with('ok', $ativo
                ? 'Tema Buriti ativado no i-Educar e no i-Diário. Recarregue as telas abertas.'
                : 'Tema Buriti desativado no i-Educar e no i-Diário.');
    }

    /**
     * @param  array<string, mixed>|null  $relatorio
     * @return array{disponivel:bool,ok:?bool,enabled:?bool,tokens:?int,suspensos:?int,url:string,governanca_url:string}
     */
    private function snapshotAppsApi(?array $relatorio = null): array
    {
        $base = [
            'disponivel' => false,
            'ok' => null,
            'enabled' => null,
            'tokens' => null,
            'suspensos' => null,
            'url' => url('/apps-api/configuracao'),
            'governanca_url' => url('/apps-api/governanca'),
        ];

        if ($relatorio !== null) {
            foreach ($relatorio['modulos'] ?? [] as $m) {
                if (($m['id'] ?? '') === 'apps-api') {
                    $base['ok'] = (bool) ($m['ok'] ?? false);
                    break;
                }
            }
        }

        if (! class_exists(\iEducar\Packages\BuritiAppsApi\Services\AppsConfigService::class)) {
            return $base;
        }

        try {
            $estado = app(\iEducar\Packages\BuritiAppsApi\Services\AppsConfigService::class)->estadoAtual();
            $resumo = class_exists(\iEducar\Packages\BuritiAppsApi\Services\AppsMonitorService::class)
                ? app(\iEducar\Packages\BuritiAppsApi\Services\AppsMonitorService::class)->resumo()
                : ['tokens' => $estado['tokens_ativos'] ?? 0, 'suspensos' => 0];

            return [
                'disponivel' => true,
                'ok' => $base['ok'] ?? (bool) ($estado['apps_habilitados'] ?? false),
                'enabled' => (bool) ($estado['enabled'] ?? false),
                'tokens' => (int) ($resumo['tokens'] ?? $estado['tokens_ativos'] ?? 0),
                'suspensos' => (int) ($resumo['suspensos'] ?? 0),
                'url' => url('/apps-api/configuracao'),
                'governanca_url' => url('/apps-api/governanca'),
            ];
        } catch (\Throwable) {
            return $base;
        }
    }

    /**
     * @return list<array{nome:string,url:string,icone:string,meta:string}>
     */
    private function atalhosModulos(): array
    {
        $icones = [
            'apps-api' => 'fa-mobile',
            'alimentacao' => 'fa-cutlery',
            'transporte' => 'fa-bus',
            'almoxarifado' => 'fa-archive',
            'atendimento' => 'fa-heartbeat',
            'eventos' => 'fa-calendar',
            'biblioteca' => 'fa-book',
            'horario' => 'fa-clock-o',
            'solicitacao' => 'fa-ticket',
            'act' => 'fa-briefcase',
        ];

        $out = [];
        foreach (CatalogoModulos::todos() as $mod) {
            $url = $mod['admin_url'] ?? (($mod['rotas'][0] ?? null) && ! str_starts_with((string) $mod['rotas'][0], '/api/')
                ? $mod['rotas'][0]
                : null);
            if ($url === null || $url === '') {
                continue;
            }
            if (! in_array($mod['id'], ['apps-api', 'alimentacao', 'transporte', 'almoxarifado', 'atendimento', 'eventos', 'biblioteca', 'horario', 'solicitacao', 'act'], true)
                && empty($mod['admin_url'])) {
                continue;
            }
            $out[] = [
                'nome' => $mod['nome'],
                'url' => url($url),
                'icone' => $icones[$mod['id']] ?? 'fa-cube',
                'meta' => 'TR '.$mod['tr'],
            ];
        }

        // Apps API sempre em destaque no painel Laguna
        $temApps = false;
        foreach ($out as $a) {
            if (str_contains($a['url'], '/apps-api')) {
                $temApps = true;
                break;
            }
        }
        if (! $temApps) {
            array_unshift($out, [
                'nome' => 'Apps API',
                'url' => url('/apps-api/configuracao'),
                'icone' => 'fa-mobile',
                'meta' => 'TR 5.13+5.14',
            ]);
        }

        return $out;
    }
}
