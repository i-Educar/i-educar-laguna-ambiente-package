{{--
  Seletor de pessoa do i-Educar (único) — busca por nome, login, escola.
  Aceita $opcoes (padrão select-busca) ou $usuarios (id/nome/login/meta).
--}}
@php
    $campo = $campo ?? 'user_id';
    $rotulo = $rotulo ?? 'Pessoa';
    $usuarios = $usuarios ?? [];
    $opcoes = $opcoes ?? collect($usuarios)->map(static function ($u) {
        $u = (array) $u;
        $meta = trim((string) ($u['meta'] ?? ''));
        if ($meta === '' && ! empty($u['login'])) {
            $meta = (string) $u['login'];
        }

        return [
            'id' => $u['id'] ?? $u['value'] ?? '',
            'nome' => $u['nome'] ?? $u['name'] ?? $u['label'] ?? '',
            'meta' => $meta,
            'login' => $u['login'] ?? '',
            'data' => (array) ($u['data'] ?? []),
            'data_id' => $u['data_id'] ?? null,
        ];
    })->all();
    $ajuda = $ajuda ?? (
        count($opcoes) === 0
            ? 'Nenhuma pessoa elegível neste fluxo (verifique permissões e vínculo com a escola).'
            : 'Digite o nome ou matrícula. Só aparecem pessoas com acesso a este fluxo · '.count($opcoes).' opção(ões).'
    );
@endphp
@include('buriti-ui::select-busca', [
    'campo' => $campo,
    'id' => $id ?? null,
    'rotulo' => $rotulo,
    'icone' => $icone ?? 'fa-user',
    'opcoes' => $opcoes,
    'selecionado' => $selecionado ?? null,
    'placeholder' => $placeholder ?? 'Buscar por nome ou matrícula…',
    'vazio' => $vazio ?? '— escolher na lista —',
    'ajuda' => (! empty($compacto) || ! empty($filtro)) ? null : $ajuda,
    'obrigatorio' => $obrigatorio ?? false,
    'onchange' => $onchange ?? null,
    'attrs' => $attrs ?? null,
    'colWide' => $colWide ?? (empty($compacto) && empty($filtro)),
    'compacto' => ! empty($compacto) || ! empty($filtro),
    'tamanho' => $tamanho ?? min(8, max(5, count($opcoes) ?: 5)),
])
