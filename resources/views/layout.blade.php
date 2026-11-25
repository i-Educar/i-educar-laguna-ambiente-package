<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Ambiente Laguna')</title>
    <link rel="stylesheet" href="{{ url('/intranet/styles/font-awesome.css') }}">
    <style>
        :root {
            --cor: {{ $identidade['cor'] ?? '#16384a' }};
            --destaque: {{ $identidade['destaque'] ?? '#1f4e66' }};
            --clara: {{ $identidade['clara'] ?? '#e8eef2' }};
            --nav: #15222b; --nav-texto: #d5e0e6; --fundo: #e6eef3;
            --tinta: #1a242b; --suave: #5b6b76; --linha: #c9d6df;
            --ok: #17663f; --ok-fundo: #e5f6ec; --erro: #9b2c2c; --erro-fundo: #fdeceb;
            --ambar: #b86e00; --ambar-fundo: #fff6e8; --cor-tela: var(--cor);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; font-family: "Segoe UI", sans-serif; font-size: 14px; color: var(--tinta);
            background:
                radial-gradient(900px 360px at 90% -5%, color-mix(in srgb, var(--clara) 85%, transparent), transparent 55%),
                linear-gradient(180deg, #eef4f8, var(--fundo));
        }
        .app { display: flex; min-height: 100vh; }
        .lateral { width: 248px; flex: none; background: linear-gradient(180deg, #1a3a4a 0%, var(--nav) 40%); color: var(--nav-texto); }
        .marca { display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; padding: 14px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .marca .marca-texto { display: none; }
        .marca .marca-titulo { display: block; font-size: .82rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; line-height: 1.15; }
        .marca .marca-desc { display: block; color: #8aa0ad; font-weight: 400; font-size: .72rem; line-height: 1.25; }
        .marca strong { display: block; }
        .marca small { color: #8aa0ad; }
        .icone { width: 40px; height: 40px; background: linear-gradient(145deg, var(--destaque), var(--cor)); color: #fff; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 18px rgba(22,56,74,.35); }
        .modulo { padding: 8px; }
        .modulo a { display: flex; align-items: center; gap: 10px; color: var(--nav-texto); text-decoration: none; border-radius: 10px; padding: 9px 10px; margin: 4px 0; }
        .modulo a.ativo, .modulo a:hover { background: color-mix(in srgb, var(--cor) 90%, #fff); color: #fff; }
        .miolo { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .topo { background: color-mix(in srgb, #fff 90%, transparent); backdrop-filter: blur(8px); border-bottom: 1px solid var(--linha); padding: 10px 18px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
        .migalha a { color: var(--cor); font-weight: 700; text-decoration: none; }
        main { padding: 18px 20px; flex: 1; }
        .identidade-tela { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .marca-tela { width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; flex: none; background: linear-gradient(145deg, var(--destaque), var(--cor-tela)); color: #fff; font-size: 18px; box-shadow: 0 10px 20px color-mix(in srgb, var(--cor-tela) 28%, transparent); }
        .cabeca-tela { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: -18px -20px 16px; padding: 14px 20px; background: linear-gradient(120deg, color-mix(in srgb, var(--clara) 75%, #fff), #fff 60%); border-bottom: 1px solid var(--linha); border-left: 4px solid var(--cor-tela); }
        .cabeca-tela h1 { margin: 0; font-size: 1.25rem; line-height: 1.2; }
        .cabeca-tela .lead { margin: 2px 0 0; color: var(--suave); }
        .titulo-modulo { margin: 0 0 2px; font-size: .7rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--cor-tela); }
        .acoes-tela { margin-left: auto; display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 8px; }
        .botao { display: inline-flex; align-items: center; gap: 8px; background: var(--cor-tela); color: #fff; border: 0; border-radius: 10px; padding: 8px 12px; font: inherit; text-decoration: none; cursor: pointer; transition: transform .15s ease; }
        .botao:hover { transform: translateY(-1px); }
        .botao.claro { background: #fff; color: var(--cor-tela); border: 1px solid var(--linha); }
        .flash { border-radius: 12px; padding: 12px 14px; margin: 0 0 16px; display: flex; gap: 8px; align-items: flex-start; }
        .flash.ok { background: var(--ok-fundo); color: var(--ok); }
        .flash.erro { background: var(--erro-fundo); color: var(--erro); }
        .bloco { background: #fff; border: 1px solid var(--linha); border-radius: 14px; padding: 14px 16px; margin: 0 0 12px; border-top: 3px solid var(--cor-tela); box-shadow: 0 10px 24px rgba(21,34,43,.04); }
        .bloco h2 { margin: 0 0 10px; display: flex; align-items: center; gap: 8px; }
        .numeros { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin: 0 0 16px; }
        .numeros article {
            --tom: var(--cor-tela); position: relative; overflow: hidden;
            background: linear-gradient(180deg, color-mix(in srgb, var(--tom) 10%, #fff), #fff 68%);
            border: 1px solid color-mix(in srgb, var(--tom) 24%, var(--linha)); border-radius: 14px; padding: 14px;
            box-shadow: 0 8px 20px rgba(21,34,43,.04); animation: sobe .45s ease both;
        }
        .numeros .kpi-ico { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; margin: 0 0 8px; background: #fff; color: var(--tom); border: 1px solid color-mix(in srgb, var(--tom) 22%, #fff); }
        .numeros span { display: block; color: var(--suave); font-size: .78rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .numeros strong { font-size: 1.35rem; letter-spacing: -.03em; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--linha); }
        th { background: var(--clara); color: var(--cor); }
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: .75rem; font-weight: 700; }
        .badge.ok { background: var(--ok-fundo); color: var(--ok); }
        .badge.fail { background: var(--erro-fundo); color: var(--erro); }
        .badge.info { background: var(--clara); color: var(--destaque); }
        .badge.ambar { background: var(--ambar-fundo); color: var(--ambar); }
        .badge.muted { background: #eef2f5; color: #64748b; }
        .grupo-mod-head {
            display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: end; justify-content: space-between;
            margin: 0 0 14px;
        }
        .grupo-mod-head h2 { margin: 0; }
        .sec-kicker-laguna {
            margin: 0 0 2px; font-size: .65rem; font-weight: 800; letter-spacing: .1em;
            text-transform: uppercase; color: var(--destaque);
        }
        .grupo-count {
            font-size: .78rem; font-weight: 700; color: var(--suave);
            padding: 4px 10px; border-radius: 999px; background: #f3f6f8; border: 1px solid var(--linha);
        }
        .modulo-badges { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 12px; }
        .modulo-card .modulo-link.muted { color: var(--suave); text-transform: none; letter-spacing: 0; font-weight: 600; }
        .modulo-card .modulo-link.suave { color: var(--suave); font-weight: 600; text-transform: none; letter-spacing: 0; }
        .modulo-card.no-ui { opacity: .96; }
        .modulo-card.is-atencao { box-shadow: 0 12px 28px rgba(155, 44, 44, .06); }
        .aviso-admin { background: var(--ambar-fundo); color: var(--ambar); padding: 10px 12px; border-radius: 12px; margin: 0 0 12px; font-size: .85rem; display: flex; gap: 8px; align-items: center; border: 1px solid color-mix(in srgb, var(--ambar) 22%, #f0e0c4); }
        .saude-anel {
            --pct: 0; width: 92px; height: 92px; border-radius: 50%; display: grid; place-items: center; flex: none;
            background: conic-gradient(var(--tom, var(--ok)) calc(var(--pct) * 1%), #d9e4eb 0);
            box-shadow: inset 0 0 0 10px #fff, 0 8px 18px rgba(21,34,43,.08);
        }
        .saude-anel span { width: 64px; height: 64px; border-radius: 50%; background: #fff; display: grid; place-items: center; font-weight: 800; font-size: .95rem; }
        .modulo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 14px; }
        .modulo-card {
            --tom: var(--cor);
            position: relative; overflow: hidden;
            background:
                radial-gradient(120% 80% at 100% 0%, color-mix(in srgb, var(--tom) 14%, transparent), transparent 55%),
                linear-gradient(165deg, #fff 0%, color-mix(in srgb, var(--tom) 6%, #f8fafc) 100%);
            border: 1px solid color-mix(in srgb, var(--tom) 22%, var(--linha));
            border-radius: 18px; padding: 16px 16px 14px;
            box-shadow: 0 12px 28px rgba(21,34,43,.06); animation: sobe .45s ease both;
            border-top: 0; transition: transform .18s ease, box-shadow .18s ease;
        }
        .modulo-card::before {
            content: ""; position: absolute; inset: 0 auto 0 0; width: 4px;
            background: linear-gradient(180deg, color-mix(in srgb, var(--tom) 85%, #fff), var(--tom));
        }
        .modulo-card:hover { transform: translateY(-3px); box-shadow: 0 16px 32px rgba(21,34,43,.1); }
        .modulo-card header { display: flex; align-items: flex-start; gap: 12px; margin: 0 0 12px; }
        .modulo-card .ico {
            width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; color: #fff; flex: none;
            font-size: 1.05rem;
            background: linear-gradient(145deg, color-mix(in srgb, var(--tom) 72%, #fff), var(--tom));
            box-shadow: 0 10px 18px color-mix(in srgb, var(--tom) 28%, transparent);
        }
        .modulo-card h3 {
            margin: 0; font-size: .78rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
            color: #0f172a; line-height: 1.25;
        }
        .modulo-card .tr { margin: 4px 0 0; color: var(--suave); font-size: .72rem; letter-spacing: .02em; }
        .modulo-card .modulo-meta { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; }
        .modulo-card .modulo-link {
            display: inline-flex; align-items: center; gap: 6px; font-size: .78rem; font-weight: 700;
            color: var(--tom); text-decoration: none; letter-spacing: .04em; text-transform: uppercase;
        }
        .modulo-card .modulo-link:hover { text-decoration: underline; }
        .atalhos { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .atalhos a {
            display: grid; grid-template-columns: 42px 1fr; gap: 10px; align-items: center; min-height: 64px;
            padding: 10px 12px; border: 1px solid var(--linha); border-radius: 14px; text-decoration: none; color: var(--tinta);
            background: linear-gradient(180deg, #f5f8fa, #fff); font-weight: 700; transition: transform .15s ease;
        }
        .atalhos a:hover { transform: translateY(-2px); }
        .atalhos a i { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; color: #fff; background: var(--destaque); }
        .atalhos a small { display: block; font-weight: 500; color: var(--suave); margin-top: 2px; }
        .passo-lista { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
        .passo-lista li {
            display: grid; grid-template-columns: 36px 1fr; gap: 10px; align-items: start; padding: 10px 12px;
            border: 1px solid var(--linha); border-radius: 12px; background: #f7fafc;
        }
        .passo-lista .n {
            width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; color: #fff;
            background: linear-gradient(145deg, var(--destaque), var(--cor));
        }
        footer { background: var(--nav); color: var(--nav-texto); padding: 12px 18px; font-size: .82rem; border-top: 3px solid var(--destaque); }
        @keyframes sobe { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @media (max-width: 860px) { .lateral { display: none; } }
    </style>
    @include('buriti-ui::estilos')
</head>
<body>
@php $path = request()->path(); @endphp
<div class="app">
    <aside class="lateral">
        <a class="marca" href="{{ url('/laguna-ambiente') }}" title="{{ $identidade['nome'] ?? '' }}">
            <span class="icone"><i class="fa {{ $identidade['icone'] ?? 'fa-heartbeat' }}"></i></span>
        </a>
        <nav class="modulo">
            <a href="{{ url('/laguna-ambiente') }}" class="@if($path === 'laguna-ambiente') ativo @endif"><i class="fa fa-heartbeat"></i> Painel</a>
            <a href="{{ url('/laguna-ambiente/check') }}" class="@if(str_contains($path, 'check')) ativo @endif"><i class="fa fa-stethoscope"></i> Verificar</a>
            <a href="{{ url('/laguna-ambiente/setup') }}" class="@if(str_contains($path, 'setup')) ativo @endif"><i class="fa fa-flask"></i> Setup teste</a>
            <a href="{{ url('/laguna-ambiente/tema') }}" class="@if(str_contains($path, 'tema')) ativo @endif"><i class="fa fa-paint-brush"></i> Tema visual</a>
            <a href="{{ url('/laguna-ambiente/usuarios') }}" class="@if(str_contains($path, 'usuarios')) ativo @endif"><i class="fa fa-user-times"></i> Suspender usuários</a>
            <a href="{{ url('/configuracoes/modulos') }}" class="@if(str_contains($path, 'configuracoes/modulos')) ativo @endif"><i class="fa fa-cubes"></i> Módulos</a>
            <a href="{{ url('/apps-api/configuracao') }}"><i class="fa fa-mobile"></i> Apps API</a>
            <a href="{{ url('/apps-api/governanca') }}"><i class="fa fa-shield"></i> Governança apps</a>
        </nav>
    </aside>
    <div class="miolo">
        @php
            $homeModulo = str_contains($path ?? '', 'configuracoes/modulos')
                ? url('/configuracoes/modulos')
                : url('/laguna-ambiente');
            $nomeModulo = str_contains($path ?? '', 'configuracoes/modulos')
                ? 'Configuração de módulos'
                : ($identidade['nome'] ?? 'Ambiente Laguna');
            $descricaoModulo = str_contains($path ?? '', 'configuracoes/modulos')
                ? 'Flags, diretivas e ativação por módulo'
                : ($identidade['descricao'] ?? null);
            $menuGrupos = [
                ['nome' => 'Operação', 'itens' => [
                    ['chave' => 'painel', 'rotulo' => 'Painel', 'url' => '/laguna-ambiente', 'icone' => 'fa-heartbeat'],
                    ['chave' => 'check', 'rotulo' => 'Verificar', 'url' => '/laguna-ambiente/check', 'icone' => 'fa-stethoscope'],
                    ['chave' => 'setup', 'rotulo' => 'Setup teste', 'url' => '/laguna-ambiente/setup', 'icone' => 'fa-flask'],
                    ['chave' => 'tema', 'rotulo' => 'Tema visual', 'url' => '/laguna-ambiente/tema', 'icone' => 'fa-paint-brush'],
                    ['chave' => 'usuarios', 'rotulo' => 'Suspender usuários', 'url' => '/laguna-ambiente/usuarios', 'icone' => 'fa-user-times'],
                ]],
                ['nome' => 'Administração', 'itens' => [
                    ['chave' => 'modulos', 'rotulo' => 'Módulos', 'url' => '/configuracoes/modulos', 'icone' => 'fa-cubes'],
                    ['chave' => 'apps', 'rotulo' => 'Apps API', 'url' => '/apps-api/configuracao', 'icone' => 'fa-mobile'],
                    ['chave' => 'gov', 'rotulo' => 'Governança', 'url' => '/apps-api/governanca', 'icone' => 'fa-shield'],
                ]],
            ];
        @endphp
        @include('buriti-ui::topo')
        <main>
            <p class="aviso-admin"><i class="fa fa-lock"></i> Área restrita a administradores · PE-77/2026 Laguna</p>
            @if(session('ok')) <p class="flash ok"><i class="fa fa-check-circle"></i> {{ session('ok') }}</p> @endif
            @if(session('erro')) <p class="flash erro"><i class="fa fa-times-circle"></i> {{ session('erro') }}</p> @endif

            <header class="cabeca-tela">
                <div class="identidade-tela">
                    <span class="marca-tela"><i class="fa @yield('icone-tela', $identidade['icone'] ?? 'fa-cube')" aria-hidden="true"></i></span>
                    <div>
                        <h1>@hasSection('titulo-tela')@yield('titulo-tela')@else@yield('migalha', 'Painel')@endif</h1>
                        @hasSection('lead')
                            <p class="lead">@yield('lead')</p>
                        @endif
                    </div>
                </div>
                <div class="acoes-tela">
                    @yield('acoes-topo')
                </div>
            </header>

            @yield('conteudo')
        </main>
        <footer>Ambiente Laguna · painel de saúde do município · {{ $identidade['cor'] ?? '' }}</footer>
    </div>
</div>
</body>
</html>
