<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Ambiente Laguna')</title>
    <link rel="stylesheet" href="{{ url('/intranet/styles/font-awesome.css') }}">
    <style>
        :root {
            --cor: {{ $identidade['cor'] ?? '#16384a' }};
            --destaque: {{ $identidade['destaque'] ?? '#1f4e66' }};
            --clara: {{ $identidade['clara'] ?? '#e8eef2' }};
            --nav: #15222b; --nav-texto: #d5e0e6; --fundo: #e7ebef;
            --tinta: #1a242b; --suave: #5b6b76; --linha: #d3dbe2;
            --ok: #17663f; --ok-fundo: #e5f6ec; --erro: #9b2c2c; --erro-fundo: #fdeceb;
            --cor-tela: var(--cor);
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", sans-serif; font-size: 14px; background: var(--fundo); color: var(--tinta); }
        .app { display: flex; min-height: 100vh; }
        .lateral { width: 240px; flex: none; background: var(--nav); color: var(--nav-texto); }
        .marca { display: flex; gap: 10px; align-items: center; color: #fff; text-decoration: none; padding: 14px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .icone { width: 36px; height: 36px; background: var(--destaque); color: #fff; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; }
        .modulo a { display: flex; align-items: center; gap: 10px; color: var(--nav-texto); text-decoration: none; border-radius: 4px; padding: 7px 10px; margin: 4px 8px; }
        .modulo a.ativo, .modulo a:hover { background: var(--cor); color: #fff; }
        .miolo { flex: 1; min-width: 0; }
        .topo { background: #fff; border-bottom: 1px solid var(--linha); padding: 10px 18px; display: flex; justify-content: space-between; align-items: center; }
        .migalha a { color: var(--cor); font-weight: 700; text-decoration: none; }
        main { padding: 18px 20px; }
        .cabeca-tela { display: flex; align-items: center; gap: 12px; margin: -18px -20px 16px; padding: 12px 20px; background: #fff; border-bottom: 1px solid var(--linha); border-left: 4px solid var(--cor-tela); }
        .marca-tela { width: 40px; height: 40px; border-radius: 4px; display: grid; place-items: center; background: var(--cor-tela); color: #fff; }
        .botao { display: inline-flex; align-items: center; gap: 8px; background: var(--cor-tela); color: #fff; border: 0; border-radius: 4px; padding: 8px 12px; font: inherit; text-decoration: none; cursor: pointer; }
        .botao.claro { background: #fff; color: var(--cor-tela); border: 1px solid var(--linha); }
        .flash { border-radius: 8px; padding: 12px 14px; margin: 0 0 16px; }
        .flash.ok { background: var(--ok-fundo); color: var(--ok); }
        .flash.erro { background: var(--erro-fundo); color: var(--erro); }
        .bloco { background: #fff; border: 1px solid var(--linha); border-radius: 4px; padding: 12px 14px; margin: 0 0 12px; border-top: 3px solid var(--cor-tela); }
        .numeros { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; margin: 0 0 16px; }
        .numeros article { background: #fff; border: 1px solid var(--linha); border-radius: 4px; padding: 12px; border-top: 3px solid var(--cor-tela); }
        .numeros span { display: block; color: var(--suave); font-size: .8rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--linha); }
        th { background: var(--clara); color: var(--cor); }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: .75rem; font-weight: 700; }
        .badge.ok { background: var(--ok-fundo); color: var(--ok); }
        .badge.fail { background: var(--erro-fundo); color: var(--erro); }
        .aviso-admin { background: #fff6e8; color: #b86e00; padding: 8px 12px; border-radius: 4px; margin: 0 0 12px; font-size: .85rem; }
        footer { background: var(--nav); color: var(--nav-texto); padding: 12px 18px; font-size: .82rem; border-top: 3px solid var(--destaque); }
        @media (max-width: 860px) { .lateral { display: none; } }
    </style>
</head>
<body>
@php $path = request()->path(); @endphp
<div class="app">
    <aside class="lateral">
        <a class="marca" href="{{ url('/laguna-ambiente') }}">
            <span class="icone"><i class="fa {{ $identidade['icone'] ?? 'fa-heartbeat' }}"></i></span>
            <span><strong>{{ $identidade['nome'] ?? 'Ambiente Laguna' }}</strong><br><small>Admin only</small></span>
        </a>
        <nav class="modulo">
            <a href="{{ url('/laguna-ambiente') }}" class="@if($path === 'laguna-ambiente') ativo @endif"><i class="fa fa-dashboard"></i> Painel</a>
            <a href="{{ url('/laguna-ambiente/check') }}" class="@if(str_contains($path, 'check')) ativo @endif"><i class="fa fa-check-square-o"></i> Verificar</a>
            <a href="{{ url('/laguna-ambiente/setup') }}" class="@if(str_contains($path, 'setup')) ativo @endif"><i class="fa fa-cogs"></i> Setup teste</a>
        </nav>
    </aside>
    <div class="miolo">
        <header class="topo">
            <p class="migalha"><a href="{{ url('/') }}">i-Educar</a> / <a href="{{ url('/laguna-ambiente') }}">Laguna</a> / <strong>@yield('migalha', 'Painel')</strong></p>
            <div>@yield('acoes-topo')</div>
        </header>
        <main>
            <p class="aviso-admin"><i class="fa fa-lock"></i> Área restrita a administradores · PE-77/2026 Laguna</p>
            @if(session('ok')) <p class="flash ok">{{ session('ok') }}</p> @endif
            @if(session('erro')) <p class="flash erro">{{ session('erro') }}</p> @endif
            @yield('conteudo')
        </main>
        <footer>Ambiente Laguna · só admin · {{ $identidade['cor'] ?? '' }}</footer>
    </div>
</div>
</body>
</html>
