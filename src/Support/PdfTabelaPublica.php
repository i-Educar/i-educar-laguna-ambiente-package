<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Gera PDF de tabela pública com identificação parcial (LGPD).
 * Usa Dompdf quando disponível; caso contrário, PDF texto mínimo embutido.
 */
final class PdfTabelaPublica
{
    /**
     * @param  list<string>  $cabecalhos
     * @param  list<list<string>>  $linhas
     */
    public static function baixar(
        string $titulo,
        array $cabecalhos,
        array $linhas,
        string $nomeArquivo,
        ?string $subtitulo = null,
        ?string $avisoLegal = null,
    ): Response {
        $avisoLegal ??= 'Nomes parcialmente ocultos em cumprimento à LGPD, mantendo a publicidade exigida pela administração pública.';
        $municipio = (string) config('app.name', 'Rede municipal');
        $emitidoEm = now()->format('d/m/Y H:i');

        $html = view('buriti-ui::pdf-tabela-publica', [
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'cabecalhos' => $cabecalhos,
            'linhas' => $linhas,
            'avisoLegal' => $avisoLegal,
            'municipio' => $municipio,
            'emitidoEm' => $emitidoEm,
        ])->render();

        $nomeArquivo = preg_replace('/[^\w\-.\p{L}]+/u', '_', $nomeArquivo) ?: 'resultado.pdf';
        if (! str_ends_with(strtolower($nomeArquivo), '.pdf')) {
            $nomeArquivo .= '.pdf';
        }

        $binario = null;
        try {
            if (class_exists(\Dompdf\Dompdf::class)) {
                $options = new \Dompdf\Options();
                $options->set('isRemoteEnabled', false);
                $options->set('isHtml5ParserEnabled', true);
                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->setPaper('a4', 'portrait');
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->render();
                $binario = $dompdf->output();
            }
        } catch (Throwable $e) {
            report($e);
        }

        if ($binario === null || $binario === '') {
            $texto = self::linhasTexto($titulo, $subtitulo, $municipio, $emitidoEm, $cabecalhos, $linhas, $avisoLegal);
            $binario = self::pdfDasLinhas($texto);
        }

        return response($binario, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$nomeArquivo.'"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    /**
     * @param  list<string>  $cabecalhos
     * @param  list<list<string>>  $linhas
     * @return list<string>
     */
    private static function linhasTexto(
        string $titulo,
        ?string $subtitulo,
        string $municipio,
        string $emitidoEm,
        array $cabecalhos,
        array $linhas,
        string $avisoLegal,
    ): array {
        $out = [
            $titulo,
            $municipio.($subtitulo ? ' — '.$subtitulo : ''),
            'Emitido em '.$emitidoEm,
            '',
            implode(' | ', $cabecalhos),
            str_repeat('-', 72),
        ];

        if ($linhas === []) {
            $out[] = 'Nenhum registro publicado para este processo no momento.';
        } else {
            foreach ($linhas as $linha) {
                $out[] = implode(' | ', array_map(static fn ($c) => (string) $c, $linha));
            }
        }

        $out[] = '';
        $out[] = $avisoLegal;

        return $out;
    }

    /** @param  list<string>  $linhas */
    private static function pdfDasLinhas(array $linhas): string
    {
        $win = static function (string $texto): string {
            $map = [
                'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
                'é' => 'e', 'ê' => 'e', 'è' => 'e',
                'í' => 'i', 'ì' => 'i',
                'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
                'ú' => 'u', 'ü' => 'u',
                'ç' => 'c', 'ñ' => 'n',
                'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
                'É' => 'E', 'Ê' => 'E',
                'Í' => 'I',
                'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
                'Ú' => 'U',
                'Ç' => 'C',
            ];

            return strtr($texto, $map);
        };
        $pdfString = static function (string $texto): string {
            return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
        };

        $y = 800;
        $ops = ["BT /F1 9 Tf 40 {$y} Td"];
        $i = 0;
        foreach ($linhas as $linha) {
            $safe = $pdfString($win(mb_substr($linha, 0, 110)));
            $ops[] = $i === 0 ? "({$safe}) Tj" : "0 -11 Td ({$safe}) Tj";
            $i++;
            if ($i > 68) {
                break;
            }
        }
        $ops[] = 'ET';
        $stream = implode("\n", $ops);

        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objs[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objs[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>';
        $objs[4] = '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream";
        $objs[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objs as $n => $body) {
            $offsets[$n] = strlen($pdf);
            $pdf .= "{$n} 0 obj\n{$body}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objs) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($n = 1; $n <= count($objs); $n++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$n]);
        }
        $pdf .= "trailer\n<< /Size ".(count($objs) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref}\n%%EOF\n";

        return $pdf;
    }
}
