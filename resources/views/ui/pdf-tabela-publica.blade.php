<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a242b; margin: 28px; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #556570; margin: 0 0 16px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cfd8df; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #eef3f6; font-size: 10px; text-transform: uppercase; letter-spacing: .03em; }
        .aviso { margin-top: 16px; font-size: 9px; color: #556570; line-height: 1.35; }
        .vazio { padding: 12px; border: 1px solid #cfd8df; }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    <p class="meta">
        {{ $municipio }}
        @if(!empty($subtitulo)) — {{ $subtitulo }}@endif
        <br>Emitido em {{ $emitidoEm }}
    </p>

    @if(($linhas ?? []) === [])
        <p class="vazio">Nenhum registro publicado para este processo no momento.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach(($cabecalhos ?? []) as $c)
                        <th>{{ $c }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($linhas as $linha)
                    <tr>
                        @foreach($linha as $cel)
                            <td>{{ $cel }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="aviso">{{ $avisoLegal }}</p>
</body>
</html>
