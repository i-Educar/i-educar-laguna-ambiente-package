<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Http\Controllers;

use iEducar\Packages\BuritiLagunaAmbiente\Support\DiretivasModulos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModulosConfigController
{
    public function index(Request $request): View
    {
        // Só reabre o modal quando veio de um save (flash) ou de ?abrir=1 explícito.
        // NÃO usa session persistente — evitava reabrir Transporte em toda visita.
        $abrir = '';
        if ($request->session()->has('modulo_abrir')) {
            $abrir = (string) $request->session()->pull('modulo_abrir', '');
        } elseif ($request->boolean('abrir') && $request->filled('modulo')) {
            $abrir = (string) $request->query('modulo');
        }

        $destaque = (string) $request->query('modulo', '');

        return view('laguna-ambiente::modulos-config', [
            'modulos' => ModuloConfiguracao::painel(),
            'tenant' => config('database.default'),
            'moduloAbrir' => $abrir,
            'moduloDestaque' => $destaque !== '' ? $destaque : $abrir,
            'gruposRotulo' => [
                'geral' => 'Geral',
                'acesso' => 'Acesso externo',
                'libresign' => 'Instância LibreSign',
                'signatarios' => 'Contas signatárias',
                'setor' => 'Setor responsável (SME)',
                'rede_apoio' => 'Rede de apoio / proteção',
                'regras' => 'Regras e limites',
            ],
        ]);
    }

    public function salvar(Request $request, string $id): RedirectResponse
    {
        $meta = DiretivasModulos::de($id);
        $rules = [
            'ativo' => 'nullable|boolean',
            'ciente_riscos' => 'nullable|boolean',
        ];
        foreach ($meta['diretivas'] as $d) {
            $chave = $d['chave'] ?? '';
            if ($chave === '' || $chave === 'ativo') {
                continue;
            }
            $campo = 'diretivas.'.$chave;
            $rules[$campo] = match ($d['tipo'] ?? 'string') {
                'bool' => 'nullable|boolean',
                'int' => 'nullable|integer'
                    .(isset($d['min']) ? '|min:'.$d['min'] : '')
                    .(isset($d['max']) ? '|max:'.$d['max'] : ''),
                'float' => 'nullable|numeric'
                    .(isset($d['min']) ? '|min:'.$d['min'] : '')
                    .(isset($d['max']) ? '|max:'.$d['max'] : ''),
                'email' => 'nullable|email|max:'.((int) ($d['maxlength'] ?? 120)),
                'url' => 'nullable|url|max:'.((int) ($d['maxlength'] ?? 240)),
                'password' => 'nullable|string|max:'.((int) ($d['maxlength'] ?? 240)),
                'tel' => 'nullable|string|max:'.((int) ($d['maxlength'] ?? 20)),
                'date' => 'nullable|date',
                'select' => 'nullable|string|max:64',
                'textarea' => 'nullable|string|max:'.((int) ($d['maxlength'] ?? 800)),
                default => 'nullable|string|max:'.((int) ($d['maxlength'] ?? 240)),
            };
        }

        $request->validate($rules, [], $this->atributos($meta['diretivas']));

        $ativo = $request->boolean('ativo');
        // Checkbox desabilitado (infra) não vai no POST — força ativo.
        if (! ($meta['bloqueavel'] ?? true)) {
            $ativo = true;
        }
        $ciente = $request->boolean('ciente_riscos');
        $diretivas = (array) $request->input('diretivas', []);

        foreach ((array) $request->input('diretivas_flags', []) as $chave) {
            $diretivas[(string) $chave] = $request->boolean('diretivas.'.$chave);
        }

        // Normaliza telefones (máscara → dígitos) antes de gravar.
        foreach ($meta['diretivas'] as $d) {
            if (($d['tipo'] ?? '') !== 'tel') {
                continue;
            }
            $chave = (string) ($d['chave'] ?? '');
            if ($chave === '' || ! array_key_exists($chave, $diretivas)) {
                continue;
            }
            $diretivas[$chave] = preg_replace('/\D+/', '', (string) $diretivas[$chave]) ?? '';
        }

        // Ciência obrigatória só na transição ativo → off (salvar diretivas com módulo já off não exige).
        $antesAtivo = ModuloConfiguracao::ativo($id);
        if ($antesAtivo && ! $ativo && ! $ciente) {
            return redirect('/configuracoes/modulos')
                ->withInput()
                ->with('erro', 'Para desativar o módulo, marque «Estou ciente dos riscos e impactos…».')
                ->with('modulo_abrir', $id);
        }

        $resultado = ModuloConfiguracao::salvar($id, $ativo, $diretivas, $ciente);

        $redirect = redirect('/configuracoes/modulos');
        if (! $resultado['ok']) {
            // Erro: reabre o modal do módulo para corrigir.
            return $redirect
                ->withInput()
                ->with('erro', $resultado['mensagem'])
                ->with('avisos_modulo', $resultado['avisos'])
                ->with('modulo_abrir', $id);
        }

        // Sucesso: recarrega a página limpa, sem reabrir o modal.
        return $redirect
            ->with('ok', $resultado['mensagem'])
            ->with('avisos_modulo', $resultado['avisos']);
    }

    /**
     * @param  list<array<string, mixed>>  $diretivas
     * @return array<string, string>
     */
    private function atributos(array $diretivas): array
    {
        $attrs = [
            'ativo' => 'módulo ativo',
            'ciente_riscos' => 'ciência dos riscos',
        ];
        foreach ($diretivas as $d) {
            $chave = $d['chave'] ?? '';
            if ($chave === '' || $chave === 'ativo') {
                continue;
            }
            $attrs['diretivas.'.$chave] = (string) ($d['rotulo'] ?? $chave);
        }

        return $attrs;
    }
}
