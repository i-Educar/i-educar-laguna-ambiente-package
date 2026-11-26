@extends('laguna-ambiente::layout')

@section('titulo', 'Suspender usuários')
@section('migalha', 'Usuários')

@section('icone-tela', 'fa-user-times')
@section('titulo-tela', 'Suspender usuários')
@section('lead')
    Desativa um ou mais usuários, desconecta apps e bloqueia novo acesso — com auditoria de quem executou.
@endsection

@section('acoes-topo')
    <a class="botao claro" href="{{ url('/apps-api/governanca') }}"><i class="fa fa-shield"></i> Governança completa</a>
@endsection

@section('conteudo')
    <div class="numeros">
        <article><span>Suspensos</span><strong>{{ $resumo['suspensos'] ?? 0 }}</strong></article>
        <article><span>Tokens ativos</span><strong>{{ $resumo['tokens'] ?? 0 }}</strong></article>
        <article><span>Eventos</span><strong>{{ $resumo['eventos'] ?? 0 }}</strong></article>
        <article>
            <span>Apps</span>
            <strong>{{ !empty($resumo['apps_on']) ? 'ON' : 'OFF' }}</strong>
        </article>
    </div>

    <div class="bloco" id="suspender">
        <h2><i class="fa fa-ban" style="color:var(--erro)"></i> Desativar usuários</h2>
        <p style="margin:0 0 10px;color:var(--suave)">
            A lista respeita o seu nível: admin vê a rede; institucional vê escola/biblioteca; escolar vê a mesma escola.
            Ao desativar: tokens Sanctum revogados, sessão web invalidada e login bloqueado.
        </p>
        <form method="get" action="{{ url('/laguna-ambiente/usuarios') }}" style="display:flex;gap:8px;flex-wrap:wrap;margin:0 0 12px">
            <input type="search" name="q" value="{{ $buscaUsuarios ?? '' }}" placeholder="Buscar nome, matrícula ou ID"
                   style="min-width:220px;flex:1;min-height:36px;padding:6px 8px;border:1px solid var(--linha);border-radius:8px;font:inherit">
            <button class="botao claro" type="submit"><i class="fa fa-search"></i> Filtrar</button>
        </form>
        <form method="post" action="{{ url('/apps-api/governanca/suspender') }}"
              onsubmit="return confirm('Suspender os selecionados? Eles serão desconectados de todos os apps e do i-Educar.');"
              style="display:grid;gap:12px">
            @csrf
            <input type="hidden" name="_redirect" value="{{ url('/laguna-ambiente/usuarios') }}">
            @include('buriti-ui::select-multi', [
                'campo' => 'usuario_ids[]',
                'rotulo' => 'Usuários a desativar',
                'icone' => 'fa-users',
                'opcoes' => collect($usuariosAtivos ?? [])->map(fn ($u) => [
                    'id' => $u['id'],
                    'nome' => ($u['nome'] ?: '—').' · '.($u['login'] ?? ''),
                    'meta' => trim(($u['nivel_nome'] ?? '').' '.implode(', ', array_slice($u['escolas'] ?? [], 0, 2))),
                ])->all(),
                'selecionados' => [],
                'placeholder' => 'Digite o nome ou a matrícula…',
                'ajuda' => 'Marque uma ou mais pessoas. Digite para filtrar.',
                'tamanho' => 12,
            ])
            <label style="display:grid;gap:4px;font-size:.8rem;font-weight:700;color:var(--suave)">Motivo
                <input type="text" name="motivo" maxlength="255" placeholder="Suspenso pela SME"
                       style="min-height:36px;padding:6px 8px;border:1px solid var(--linha);border-radius:8px;font:inherit">
            </label>
            <div style="display:flex;justify-content:flex-end">
                <button class="botao" type="submit" style="background:var(--erro)" @if(empty($usuariosAtivos)) disabled @endif>
                    <i class="fa fa-ban"></i> Desativar selecionados
                </button>
            </div>
        </form>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-user-secret" style="color:var(--destaque)"></i> Suspensos (auditoria)</h2>
        @if(empty($suspensos))
            <p style="margin:0;color:var(--suave)">Nenhum usuário inativo.</p>
        @else
            <table>
                <thead>
                <tr><th>ID</th><th>Login</th><th>Nome</th><th>Motivo</th><th>Por</th><th>Quando</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($suspensos as $u)
                    <tr>
                        <td>{{ $u['id'] }}</td>
                        <td>{{ $u['login'] }}</td>
                        <td>{{ $u['nome'] ?? '—' }}</td>
                        <td>{{ $u['motivo'] ?? '—' }}</td>
                        <td>{{ $u['suspenso_por'] ?? '—' }}</td>
                        <td>{{ \iEducar\Packages\BuritiLagunaAmbiente\Support\DataHora::formatar($u['suspenso_em'] ?? $u['atualizado'] ?? null) }}</td>
                        <td>
                            <form method="post" action="{{ url('/apps-api/governanca/usuarios/'.$u['id'].'/reativar') }}">
                                @csrf
                                <input type="hidden" name="_redirect" value="{{ url('/laguna-ambiente/usuarios') }}">
                                <button class="botao" type="submit" style="padding:4px 8px;font-size:.78rem"><i class="fa fa-unlock"></i> Reativar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="bloco">
        <h2><i class="fa fa-history" style="color:var(--destaque)"></i> Histórico recente</h2>
        @if(empty($historico))
            <p style="margin:0;color:var(--suave)">Sem eventos ainda.</p>
        @else
            <table>
                <thead>
                <tr><th>Quando</th><th>Ação</th><th>Ator</th><th>Alvo</th><th>Detalhe</th></tr>
                </thead>
                <tbody>
                @foreach($historico as $h)
                    <tr>
                        <td>{{ \iEducar\Packages\BuritiLagunaAmbiente\Support\DataHora::formatar($h['created_at'] ?? null) }}</td>
                        <td><span class="badge {{ str_contains($h['acao'], 'suspender') || str_contains($h['acao'], 'bloquear') || str_contains($h['acao'], 'revogar') ? 'fail' : 'ok' }}">{{ $h['acao'] }}</span></td>
                        <td>{{ $h['ator_login'] ?? '—' }}</td>
                        <td>{{ $h['alvo_login'] ?? '—' }}</td>
                        <td>{{ $h['detalhe'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
