<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Controllers;

use App\User;
use iEducar\Packages\BuritiLagunaAmbiente\Services\AmbienteAuditor;
use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaModuloTestesService;
use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaSetupService;
use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaSimulacaoPessoasService;
use iEducar\Packages\BuritiLagunaAmbiente\Support\AprovacaoModulo;
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
        ]);
    }

    public function check(AmbienteAuditor $auditor, LagunaModuloTestesService $testes): View
    {
        $relatorio = $auditor->auditar();
        $ultimoTeste = $testes->ultimo();
        $testePorId = [];
        foreach ($ultimoTeste['itens'] ?? [] as $item) {
            $testePorId[(string) ($item['id'] ?? '')] = $item;
        }

        return view('laguna-ambiente::check', [
            'relatorio' => $relatorio,
            'appsApi' => $this->snapshotAppsApi($relatorio),
            'statusAprovacao' => AprovacaoModulo::statusValidos(),
            'ultimoTeste' => $ultimoTeste,
            'testePorId' => $testePorId,
            'pendencias' => $testes->pendencias($relatorio),
        ]);
    }

    public function testarModulos(Request $request, LagunaModuloTestesService $testes): RedirectResponse
    {
        $data = $request->validate([
            'modulo_id' => 'nullable|string|max:80',
        ]);

        $moduloId = trim((string) ($data['modulo_id'] ?? ''));
        if ($moduloId !== '') {
            $ids = array_column(CatalogoModulos::todos(), 'id');
            if (! in_array($moduloId, $ids, true)) {
                return redirect('/laguna-ambiente/check')
                    ->with('erro', 'Módulo inválido para teste.');
            }
        }

        @set_time_limit(0);
        $resultado = $testes->executar($moduloId !== '' ? $moduloId : null);
        $resumo = $resultado['resumo'] ?? ['ok' => 0, 'falha' => 0, 'skip' => 0, 'total' => 0];

        $msg = $moduloId !== ''
            ? 'Testes de «'.$moduloId.'»: '.$resumo['ok'].' ok, '.$resumo['falha'].' falha(s), '.$resumo['skip'].' sem script.'
            : 'Testes atualizados: '.$resumo['ok'].' ok, '.$resumo['falha'].' falha(s), '.$resumo['skip'].' sem script ('.$resumo['total'].' módulos).';

        $redirect = $moduloId !== ''
            ? '/laguna-ambiente/check?mod='.$moduloId.'#pendencias'
            : '/laguna-ambiente/check#pendencias';

        return redirect($redirect)
            ->with(($resumo['falha'] ?? 0) > 0 ? 'erro' : 'ok', $msg)
            ->with('teste_resumo', $resumo);
    }

    public function aprovarModulo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'modulo_id' => 'required|string|max:80',
            'status' => 'required|string|in:'.implode(',', AprovacaoModulo::statusValidos()),
            'nota' => 'nullable|string|max:500',
        ]);

        $ids = array_column(CatalogoModulos::todos(), 'id');
        if (! in_array($data['modulo_id'], $ids, true)) {
            return redirect('/laguna-ambiente/check')
                ->with('erro', 'Módulo inválido para aprovação.');
        }

        AprovacaoModulo::gravar(
            $data['modulo_id'],
            $data['status'],
            (string) ($data['nota'] ?? ''),
        );

        $meta = AprovacaoModulo::meta($data['status']);

        return redirect('/laguna-ambiente/check?mod='.$data['modulo_id'])
            ->with('ok', 'Aprovação de «'.$data['modulo_id'].'» atualizada: '.$meta['rotulo'].'.');
    }

    public function setupForm(LagunaSimulacaoPessoasService $sim): View
    {
        return view('laguna-ambiente::setup', [
            'simulacao' => $sim->ultimoLote(),
        ]);
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
            comSimulacaoPessoas: ! $request->boolean('skip_simulacao'),
        );

        $ok = count(array_filter($passos, static fn ($p) => $p['ok'])) === count($passos);

        return redirect('/laguna-ambiente/simulacao')
            ->with($ok ? 'ok' : 'erro', $ok
                ? 'Setup + simulação executados. Dados persistidos para verificação.'
                : 'Setup parcial — confira os passos e a simulação abaixo.')
            ->with('passos', $passos);
    }

    public function simulacaoForm(LagunaSimulacaoPessoasService $sim): View
    {
        return view('laguna-ambiente::simulacao', [
            'dados' => $sim->ultimoLote(),
            'passos' => session('passos', []),
        ]);
    }

    public function simulacaoRun(Request $request, LagunaSimulacaoPessoasService $sim): RedirectResponse
    {
        if (App::environment('production') && ! $request->boolean('force_production')) {
            return redirect('/laguna-ambiente/simulacao')
                ->with('erro', 'Recusado em production. Marque force ou use homologação.');
        }

        $qtd = max(1, min(40, (int) $request->input('qtd', 20)));
        $resultado = $sim->executar($qtd);

        return redirect('/laguna-ambiente/simulacao')
            ->with($resultado['ok'] ? 'ok' : 'erro', $resultado['saida'])
            ->with('resultado', $resultado);
    }

    public function temaForm(): View
    {
        return view('laguna-ambiente::tema', [
            'ativo' => TemaBuriti::ativo(),
            'skinAtual' => TemaBuriti::skinId(),
            'skins' => TemaBuriti::catalogo(),
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
        $skin = (string) $request->input('skin', '');
        if ($skin === '' && $request->has('ativo')) {
            $skin = $request->boolean('ativo') ? 'buriti' : TemaBuriti::SKIN_NENHUM;
        }
        TemaBuriti::salvar($skin !== '' ? $skin : TemaBuriti::SKIN_NENHUM);
        $nome = TemaBuriti::catalogo()[TemaBuriti::skinId()]['nome'] ?? TemaBuriti::skinId();

        return redirect('/laguna-ambiente/tema')
            ->with('ok', TemaBuriti::ativo()
                ? "Skin «{$nome}» aplicada no i-Educar e no i-Diário. Recarregue as telas abertas."
                : 'Skin municipal desativada — chrome padrão Portabilis restaurado.');
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

}
