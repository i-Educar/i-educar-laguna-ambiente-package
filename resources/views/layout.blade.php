<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Ambiente Laguna')</title>
    <link rel="stylesheet" href="{{ url('/intranet/styles/font-awesome.css') }}">
    <link rel="stylesheet" href="/vendor/buriti-tema/tokens.css?v=9">
    <style>
        :root {
            /* Só marca do módulo — superfícies/tipografia vêm de tokens.css (claro/escuro) */
            --cor: {{ $identidade['cor'] ?? '#1a5f9e' }};
            --destaque: {{ $identidade['destaque'] ?? '#2f7ec4' }};
            --clara-marca: {{ $identidade['clara'] ?? '#eef2f7' }};
            --cor-tela: var(--cor);
        }
        /* Pastel da marca só no claro; no escuro --clara vem dos tokens */
        html[data-buriti-modo="claro"] {
            --clara: var(--clara-marca, #eef2f7);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; font-family: var(--fonte, "Segoe UI", sans-serif); font-size: 14px; color: var(--tinta);
            background: var(--fundo);
        }
        .app { display: flex; min-height: 100vh; }
        .lateral { width: 248px; flex: none; background: var(--nav); color: var(--nav-texto); }
        .marca { display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; padding: 14px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .marca .marca-texto { display: none; }
        .marca .marca-titulo { display: block; font-size: .82rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; line-height: 1.15; }
        .marca .marca-desc { display: block; color: #8aa0ad; font-weight: 400; font-size: .72rem; line-height: 1.25; }
        .marca strong { display: block; }
        .marca small { color: #8aa0ad; }
        .icone { width: 38px; height: 38px; background: var(--cor); color: #fff; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; box-shadow: none; }
        .modulo { padding: 8px; }
        .modulo a { display: flex; align-items: center; gap: 10px; color: var(--nav-texto); text-decoration: none; border-radius: 10px; padding: 9px 10px; margin: 4px 0; }
        .modulo a:hover { background: color-mix(in srgb, var(--nav-texto) 8%, transparent); color: var(--nav-texto); }
        .modulo a.ativo { background: color-mix(in srgb, var(--destaque, var(--cor)) 16%, transparent); color: #fff; font-weight: 700; box-shadow: inset 2px 0 0 var(--destaque, var(--cor)); }
        .miolo { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .topo { background: var(--superficie, #fff); border-bottom: 1px solid var(--linha); padding: 10px 18px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
        .migalha a { color: var(--cor); font-weight: 600; text-decoration: none; }
        main { padding: 18px 20px; flex: 1; }
        .identidade-tela { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .marca-tela { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; flex: none; background: var(--cor-tela); color: #fff; font-size: 17px; box-shadow: none; }
        .cabeca-tela { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: -18px -20px 16px; padding: 14px 20px; background: var(--superficie, #fff); border-bottom: 1px solid var(--linha); border-left: 3px solid var(--cor-tela); }
        .cabeca-tela h1 { margin: 0; font-size: 1.2rem; line-height: 1.25; font-weight: 700; }
        .cabeca-tela .lead { margin: 2px 0 0; color: var(--suave); }
        .titulo-modulo { margin: 0 0 2px; font-size: .7rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--cor-tela); }
        .acoes-tela { margin-left: auto; display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 8px; }
        .botao { display: inline-flex; align-items: center; gap: 8px; background: var(--cor-tela); color: #fff; border: 0; border-radius: 8px; padding: 8px 12px; font: inherit; font-weight: 600; text-decoration: none; cursor: pointer; }
        .botao:hover { filter: brightness(1.04); }
        .botao.claro { background: var(--superficie, #fff); color: var(--cor-tela); border: 1px solid var(--linha); }
        .botao.informes {
            width: 40px; min-width: 40px; height: 40px; min-height: 40px;
            padding: 0; gap: 0; justify-content: center; border-radius: 10px;
            background: #1d6fd8; color: #fff; border: 0;
        }
        .botao.informes i { margin: 0; font-size: 1.05rem; }
        .flash { border-radius: 10px; padding: 12px 14px; margin: 0 0 16px; display: flex; gap: 8px; align-items: flex-start; }
        .flash.ok { background: var(--ok-fundo); color: var(--ok); }
        .flash.erro { background: var(--erro-fundo); color: var(--erro); }
        .bloco { background: var(--superficie, #fff); border: 1px solid var(--linha); border-radius: 12px; padding: 14px 16px; margin: 0 0 12px; border-top: 2px solid var(--cor-tela); box-shadow: none; }
        .bloco h2 { margin: 0 0 10px; display: flex; align-items: center; gap: 8px; }
        .numeros { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin: 0 0 16px; }
        .numeros article {
            --tom: var(--cor-tela); position: relative; overflow: hidden;
            background: var(--superficie, #fff);
            border: 1px solid var(--linha); border-radius: 12px; padding: 14px;
            box-shadow: none;
        }
        .numeros .kpi-ico { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; margin: 0 0 8px; background: var(--superficie-alta, var(--superficie, #fff)); color: var(--tom); border: 1px solid color-mix(in srgb, var(--tom) 22%, var(--linha)); }
        .numeros span { display: block; color: var(--suave); font-size: .78rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .numeros strong { font-size: 1.35rem; letter-spacing: -.03em; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--linha); }
        th { background: var(--clara); color: var(--cor); }
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: .75rem; font-weight: 700; }
        .badge.ok { background: var(--ok-fundo); color: var(--ok); }
        .badge.fail { background: var(--erro-fundo); color: var(--erro); }
        .badge.info { background: var(--clara); color: var(--destaque); }
        .badge.ambar, .badge.warn { background: var(--ambar-fundo); color: var(--ambar); }
        .badge.muted { background: var(--muted-fundo, #eef2f5); color: var(--muted-tinta, #64748b); }
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
            padding: 4px 10px; border-radius: 999px; background: var(--clara, #f3f6f8); border: 1px solid var(--linha);
        }
        .modulo-badges { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 12px; }
        .modulo-card.no-ui { opacity: .96; }
        .modulo-card.is-atencao { border-color: color-mix(in srgb, #c74634 28%, var(--linha)); }
        .modulo-card.sit-funciona { --tom: #17663f; }
        .modulo-card.sit-ressalva { --tom: #a85f00; }
        .modulo-card.sit-reprovado { --tom: #c74634; }
        .modulo-card.sit-testar { --tom: #0369a1; }
        .badge.test { background: #e0f2fe; color: #0369a1; }
        .legenda-aprovacao {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin: 0 0 14px;
        }
        .legenda-aprovacao article {
            display: grid; grid-template-columns: 28px 1fr; gap: 10px; align-items: start;
            padding: 10px 12px; border-radius: 12px; border: 1px solid var(--linha); background: var(--superficie, #fff);
        }
        .legenda-aprovacao .dot {
            width: 28px; height: 28px; border-radius: 9px; display: grid; place-items: center; color: #fff; font-size: .85rem;
        }
        .legenda-aprovacao .dot.ok { background: #17663f; }
        .legenda-aprovacao .dot.warn { background: #a85f00; }
        .legenda-aprovacao .dot.fail { background: #c74634; }
        .legenda-aprovacao .dot.test { background: #0369a1; }
        .legenda-aprovacao strong { display: block; font-size: .82rem; }
        .legenda-aprovacao span { display: block; font-size: .74rem; color: var(--suave); line-height: 1.35; margin-top: 2px; }
        .aprovacao-form {
            margin-top: 12px; padding: 12px; border-radius: 12px;
            background: color-mix(in srgb, var(--clara, #f3f6f8) 80%, var(--superficie, #fff));
            border: 1px solid var(--linha);
        }
        .aprovacao-form .form-grid {
            margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 10px; align-items: end;
        }
        .aprovacao-form label { display: grid; gap: 4px; font-size: .78rem; font-weight: 700; color: var(--suave); }
        .aprovacao-form input, .aprovacao-form select {
            min-height: 36px; padding: 6px 8px; border: 1px solid var(--linha); border-radius: 8px; font: inherit; background: var(--superficie, #fff);
        }
        .aprovacao-form .col-wide { grid-column: 1 / -1; }
        .aprovacao-form .acoes { grid-column: 1 / -1; display: flex; justify-content: flex-end; }
        .teste-resumo-linha { margin: 0 0 12px; font-size: .84rem; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .pendencia-item {
            margin: 0 0 10px; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--linha);
            background: var(--superficie, #fff);
        }
        .pendencia-item.grav-fail { border-color: color-mix(in srgb, #c74634 35%, var(--linha)); background: color-mix(in srgb, #c74634 6%, var(--superficie, #fff)); }
        .pendencia-item.grav-warn { border-color: color-mix(in srgb, #a85f00 30%, var(--linha)); background: color-mix(in srgb, #a85f00 5%, var(--superficie, #fff)); }
        .pendencia-item header { display: flex; justify-content: space-between; gap: 10px; align-items: center; margin-bottom: 6px; }
        .pendencia-item header strong { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; font-size: .92rem; }
        .pendencia-item header a { color: inherit; text-decoration: underline; text-underline-offset: 2px; }
        .pendencia-item ul { margin: 0; padding-left: 18px; font-size: .82rem; color: var(--texto, #1f2933); line-height: 1.45; }
        .pendencia-vazia { margin: 0; font-size: .9rem; display: flex; gap: 8px; align-items: center; }
        .mod-nao-pronto {
            margin: 0 0 10px; padding: 10px 12px; border-radius: 10px;
            border: 1px solid color-mix(in srgb, #c74634 28%, var(--linha));
            background: color-mix(in srgb, #c74634 7%, var(--superficie, #fff));
            font-size: .82rem;
        }
        .mod-nao-pronto strong { display: block; margin-bottom: 4px; }
        .mod-nao-pronto ul { margin: 0; padding-left: 18px; line-height: 1.4; }
        .mod-acoes-rapidas {
            display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin: 10px 0 0;
        }
        .teste-mod-linha { margin: 10px 0 0; font-size: .78rem; color: var(--suave); }
        .modulo-card.is-clicavel { cursor: pointer; text-align: left; width: 100%; font: inherit; color: inherit; appearance: none; }
        .modulo-card.is-clicavel:focus-visible { outline: 2px solid var(--destaque); outline-offset: 2px; }
        .modulo-card[aria-expanded="true"] {
            box-shadow: none;
            border-color: color-mix(in srgb, var(--tom) 32%, var(--linha));
        }
        .modulo-saude {
            display: none; margin-top: 12px; padding-top: 12px;
            border-top: 1px dashed color-mix(in srgb, var(--tom) 28%, var(--linha));
        }
        .modulo-card[aria-expanded="true"] .modulo-saude { display: block; }
        .modulo-saude-lista { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
        .modulo-saude-lista li {
            display: grid; grid-template-columns: 18px 1fr; gap: 8px; align-items: start;
            font-size: .8rem; line-height: 1.35; color: var(--tinta);
        }
        .modulo-saude-lista li i { margin-top: 2px; }
        .modulo-saude-lista .ok { color: var(--ok); }
        .modulo-saude-lista .fail { color: var(--erro); }
        .modulo-hint {
            margin: 8px 0 0; font-size: .72rem; font-weight: 700; letter-spacing: .04em;
            text-transform: uppercase; color: var(--suave);
        }
        .modulo-card[aria-expanded="true"] .modulo-hint .fa-chevron-down { transform: rotate(180deg); }
        .modulo-hint .fa-chevron-down { transition: transform .15s ease; }
        .aviso-admin { background: var(--ambar-fundo); color: var(--ambar); padding: 10px 12px; border-radius: 12px; margin: 0 0 12px; font-size: .85rem; display: flex; gap: 8px; align-items: center; border: 1px solid color-mix(in srgb, var(--ambar) 22%, #f0e0c4); }
        .saude-anel {
            --pct: 0; width: 88px; height: 88px; border-radius: 50%; display: grid; place-items: center; flex: none;
            background: conic-gradient(var(--tom, var(--ok)) calc(var(--pct) * 1%), color-mix(in srgb, var(--linha) 70%, var(--clara)) 0);
            box-shadow: inset 0 0 0 10px var(--superficie, #fff);
        }
        .saude-anel span { width: 62px; height: 62px; border-radius: 50%; background: var(--superficie, #fff); color: var(--tinta); display: grid; place-items: center; font-weight: 700; font-size: .92rem; }
        .modulo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }
        .modulo-card {
            --tom: var(--cor);
            position: relative; overflow: hidden;
            background: var(--superficie, #fff);
            border: 1px solid var(--linha);
            border-radius: 12px; padding: 14px 14px 12px;
            box-shadow: none;
            border-left: 3px solid var(--tom);
            transition: border-color .15s ease, background .15s ease;
        }
        .modulo-card::before { display: none; }
        .modulo-card:hover { background: color-mix(in srgb, var(--clara, #eef2f7) 45%, var(--superficie, #fff)); border-color: color-mix(in srgb, var(--tom) 22%, var(--linha)); }
        .modulo-card header { display: flex; align-items: flex-start; gap: 12px; margin: 0 0 12px; }
        .modulo-card header > div { flex: 1; min-width: 0; }
        .modulo-pct {
            --pct: 0; --tom: var(--ok, #17663f);
            width: 46px; height: 46px; border-radius: 50%; flex: none; display: grid; place-items: center;
            background: conic-gradient(var(--tom) calc(var(--pct) * 1%), color-mix(in srgb, var(--linha) 70%, var(--clara)) 0);
            box-shadow: inset 0 0 0 6px var(--superficie, #fff);
        }
        .modulo-pct span {
            width: 32px; height: 32px; border-radius: 50%; background: var(--superficie, #fff);
            color: var(--tom); display: grid; place-items: center;
            font-size: .68rem; font-weight: 800; letter-spacing: -.02em;
        }
        .modulo-card .ico {
            width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; color: #fff; flex: none;
            font-size: 1rem;
            background: var(--tom);
            box-shadow: none;
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
            display: grid; grid-template-columns: 40px 1fr; gap: 10px; align-items: center; min-height: 60px;
            padding: 10px 12px; border: 1px solid var(--linha); border-radius: 10px; text-decoration: none; color: var(--tinta);
            background: var(--superficie, #fff); font-weight: 600;
        }
        .atalhos a:hover { background: color-mix(in srgb, var(--clara) 50%, var(--superficie, #fff)); }
        .atalhos a i { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; color: #fff; background: var(--destaque); }
        .atalhos a small { display: block; font-weight: 500; color: var(--suave); margin-top: 2px; }
        .cmd-tabela {
            display: grid; gap: 8px;
        }
        .cmd-tabela article {
            display: grid; grid-template-columns: minmax(180px, 280px) 1fr; gap: 10px 14px;
            padding: 10px 12px; border-radius: 10px; border: 1px solid var(--linha);
            background: var(--superficie, #fff); align-items: start;
        }
        .cmd-tabela code {
            font-size: .78rem; word-break: break-word;
        }
        .cmd-tabela span {
            font-size: .82rem; color: var(--texto, #1f2933); line-height: 1.4;
        }
        @media (max-width: 720px) {
            .cmd-tabela article { grid-template-columns: 1fr; }
        }
        .passo-lista { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
        .passo-lista li {
            display: grid; grid-template-columns: 36px 1fr; gap: 10px; align-items: start; padding: 10px 12px;
            border: 1px solid var(--linha); border-radius: 10px; background: var(--superficie, #fff);
        }
        .passo-lista .n {
            width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; font-weight: 700; color: #fff;
            background: var(--destaque);
        }
        footer { background: var(--nav); color: var(--nav-texto); padding: 12px 18px; font-size: .82rem; border-top: 2px solid var(--destaque); }
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
            <a href="{{ url('/laguna-ambiente/setup') }}" class="@if(str_contains($path, 'setup') && !str_contains($path, 'simulacao')) ativo @endif"><i class="fa fa-flask"></i> Setup teste</a>
            <a href="{{ url('/laguna-ambiente/simulacao') }}" class="@if(str_contains($path, 'simulacao')) ativo @endif"><i class="fa fa-users"></i> Simulação</a>
            <a href="{{ url('/laguna-ambiente/tema') }}" class="@if(str_contains($path, 'tema')) ativo @endif"><i class="fa fa-paint-brush"></i> Skins da escola</a>
            <a href="{{ url('/laguna-ambiente/usuarios') }}" class="@if(str_contains($path, 'usuarios')) ativo @endif"><i class="fa fa-user-times"></i> Suspender usuários</a>
            <a href="{{ url('/configuracoes/modulos') }}" class="@if(str_contains($path, 'configuracoes/modulos')) ativo @endif"><i class="fa fa-cubes"></i> Módulos</a>
            <a href="{{ url('/apps-api/configuracao') }}"><i class="fa fa-mobile"></i> APIs</a>
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
                    ['chave' => 'simulacao', 'rotulo' => 'Simulação', 'url' => '/laguna-ambiente/simulacao', 'icone' => 'fa-users'],
                    ['chave' => 'tema', 'rotulo' => 'Skins da escola', 'url' => '/laguna-ambiente/tema', 'icone' => 'fa-paint-brush'],
                    ['chave' => 'usuarios', 'rotulo' => 'Suspender usuários', 'url' => '/laguna-ambiente/usuarios', 'icone' => 'fa-user-times'],
                ]],
                ['nome' => 'Administração', 'itens' => [
                    ['chave' => 'modulos', 'rotulo' => 'Módulos', 'url' => '/configuracoes/modulos', 'icone' => 'fa-cubes'],
                    ['chave' => 'apps', 'rotulo' => 'APIs', 'url' => '/apps-api/configuracao', 'icone' => 'fa-mobile'],
                    ['chave' => 'gov', 'rotulo' => 'Governança', 'url' => '/apps-api/governanca', 'icone' => 'fa-shield'],
                ]],
            ];
        @endphp
        @include('buriti-ui::topo')
        <main>
            <p class="aviso-admin"><i class="fa fa-lock"></i> Área restrita a administradores · PE-77/2026 Laguna</p>
            @if(session('ok')) <p class="flash ok"><i class="fa fa-check-circle"></i> {{ session('ok') }}</p> @endif
            @if(session('erro')) <p class="flash erro"><i class="fa fa-times-circle"></i> {{ session('erro') }}</p> @endif

            @php
                $tituloCabeca = trim($__env->yieldContent('titulo-tela'));
                if ($tituloCabeca === '') {
                    $tituloCabeca = trim($__env->yieldContent('migalha'));
                }
                if ($tituloCabeca === '') {
                    $tituloCabeca = 'Painel';
                }
                $iconeCabeca = trim($__env->yieldContent('icone-tela'));
                if ($iconeCabeca === '') {
                    $iconeCabeca = (string) ($identidade['icone'] ?? 'fa-heartbeat');
                }
            @endphp
            <header class="cabeca-tela">
                <div class="identidade-tela">
                    <span class="marca-tela"><i class="fa {{ $iconeCabeca }}" aria-hidden="true"></i></span>
                    <div>
                        <h1>{{ $tituloCabeca }}</h1>
                        @hasSection('lead')
                            <p class="lead">@yield('lead')</p>
                        @endif
                    </div>
                </div>
                <div class="acoes-tela">
                    @yield('acoes-topo')
                    @includeWhen(View::exists('buriti-ui::informes-modulo'), 'buriti-ui::informes-modulo')
                </div>
            </header>

            @hasSection('foco')
                @yield('foco')
            @endif
            @hasSection('trilho')
                <nav class="buriti-trilho" aria-label="Ações da tela">@yield('trilho')</nav>
            @endif

            @yield('conteudo')
        </main>
        @include('buriti-ui::rodape', ['moduloRodape' => 'Ambiente Laguna · saúde do município'])
    </div>
</div>
</body>
</html>
