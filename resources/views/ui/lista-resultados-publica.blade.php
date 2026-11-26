{{--
  Lista pública de resultados (transparência), com identificação anonimizada.
  @var string $titulo
  @var string|null $lead
  @var list<string> $cabecalhos
  @var list<list<string>> $linhas
  @var string|null $avisoLegal
--}}
@php
    $titulo = $titulo ?? 'Resultados publicados';
    $cabecalhos = $cabecalhos ?? [];
    $linhas = $linhas ?? [];
    $avisoLegal = $avisoLegal ?? 'Nomes parcialmente ocultos em cumprimento à LGPD, mantendo a publicidade exigida pela administração pública.';
@endphp
<section class="buriti-lista-publica" aria-label="{{ $titulo }}">
    <header>
        <h2>{{ $titulo }}</h2>
        @if(!empty($lead))
            <p>{{ $lead }}</p>
        @endif
    </header>
    <div class="tabela-wrap">
        <table>
            @if($cabecalhos !== [])
                <thead>
                    <tr>
                        @foreach($cabecalhos as $c)
                            <th scope="col">{{ $c }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($linhas as $linha)
                    <tr>
                        @foreach($linha as $cel)
                            <td>{{ $cel }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ max(1, count($cabecalhos)) }}">Nenhum resultado publicado neste momento.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="aviso-legal">{{ $avisoLegal }}</p>
</section>
<style>
.buriti-lista-publica{margin:0 0 8px;padding:18px 18px 14px;border:1px solid #dde5eb;border-radius:16px;background:#fff}
.buriti-lista-publica > header{margin:0 0 12px;padding:0;background:transparent;border:0}
.buriti-lista-publica h2{margin:0 0 6px;font-size:1.08rem;font-weight:700;color:#16232b}
.buriti-lista-publica header p{margin:0;color:#445560;font-size:.92rem;line-height:1.45;max-width:52rem}
.buriti-lista-publica .tabela-wrap{overflow-x:auto;border:1px solid #e4ebf0;border-radius:12px}
.buriti-lista-publica table{width:100%;border-collapse:collapse;font-size:.9rem;background:#fff}
.buriti-lista-publica th,.buriti-lista-publica td{text-align:left;padding:10px 12px;border-bottom:1px solid #e6edf2;color:#1a242b;vertical-align:top}
.buriti-lista-publica tbody tr:last-child td{border-bottom:0}
.buriti-lista-publica th{font-size:.7rem;letter-spacing:.05em;text-transform:uppercase;font-weight:750;color:#445560;background:#f3f7f9}
.buriti-lista-publica td:nth-child(4){font-weight:700}
.buriti-lista-publica .aviso-legal{margin:12px 0 0;font-size:.8rem;color:#556570;line-height:1.4}
</style>
