<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Controllers;

use iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModulosConfigController
{
    public function index(): View
    {
        return view('laguna-ambiente::modulos-config', [
            'modulos' => ModuloConfiguracao::painel(),
            'tenant' => config('database.default'),
        ]);
    }

    public function salvar(Request $request, string $id): RedirectResponse
    {
        $ativo = $request->boolean('ativo');
        $ciente = $request->boolean('ciente_riscos');
        $diretivas = (array) $request->input('diretivas', []);

        foreach ((array) $request->input('diretivas_flags', []) as $chave) {
            $diretivas[(string) $chave] = $request->boolean('diretivas.'.$chave);
        }

        $resultado = ModuloConfiguracao::salvar($id, $ativo, $diretivas, $ciente);

        $redirect = redirect('/configuracoes/modulos#modulo-'.$id);
        if (! $resultado['ok']) {
            return $redirect
                ->with('erro', $resultado['mensagem'])
                ->with('avisos_modulo', $resultado['avisos'])
                ->with('modulo_foco', $id);
        }

        return $redirect
            ->with('ok', $resultado['mensagem'])
            ->with('avisos_modulo', $resultado['avisos'])
            ->with('modulo_foco', $id);
    }
}
