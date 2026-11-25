<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Controllers;

use iEducar\Packages\BuritiLagunaAmbiente\Services\AmbienteAuditor;
use iEducar\Packages\BuritiLagunaAmbiente\Services\LagunaSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class AmbienteController
{
    public function index(AmbienteAuditor $auditor): View
    {
        return view('laguna-ambiente::painel', [
            'relatorio' => $auditor->auditar(),
        ]);
    }

    public function check(AmbienteAuditor $auditor): View
    {
        return view('laguna-ambiente::check', [
            'relatorio' => $auditor->auditar(),
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
}
