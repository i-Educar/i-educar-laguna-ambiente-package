<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Services;

use iEducar\Packages\BuritiLagunaAmbiente\Support\CatalogoModulos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Roda `tests/run.php` de cada pacote do catálogo e guarda o último resultado (cache).
 */
final class LagunaModuloTestesService
{
    private const CACHE_KEY = 'laguna.ambiente.ultimo_teste_modulos';

    private const TIMEOUT = 90;

    /**
     * @return array{
     *   em:string,
     *   todos:bool,
     *   resumo:array{ok:int,falha:int,skip:int,total:int},
     *   itens:list<array{id:string,nome:string,tr:string,status:string,ok:int,falha:int,saida:string,script:?string}>
     * }
     */
    public function executar(?string $moduloId = null): array
    {
        $mods = CatalogoModulos::todos();
        if ($moduloId !== null && $moduloId !== '') {
            $mods = array_values(array_filter(
                $mods,
                static fn (array $m): bool => (string) ($m['id'] ?? '') === $moduloId
            ));
        } else {
            // Mesmos cards da tela Verificar (sem agregados ocultos)
            $mods = array_values(array_filter(
                $mods,
                static fn (array $m): bool => empty($m['ocultar_painel']) && empty($m['agregado_em'])
            ));
        }

        $itens = [];
        $ok = 0;
        $falha = 0;
        $skip = 0;

        foreach ($mods as $mod) {
            $resultado = $this->rodarPacote($mod);
            $itens[] = $resultado;

            match ($resultado['status']) {
                'ok' => $ok++,
                'falha' => $falha++,
                default => $skip++,
            };
        }

        $payload = [
            'em' => now()->toIso8601String(),
            'todos' => $moduloId === null || $moduloId === '',
            'modulo_foco' => $moduloId,
            'resumo' => [
                'ok' => $ok,
                'falha' => $falha,
                'skip' => $skip,
                'total' => count($itens),
            ],
            'itens' => $itens,
        ];

        $atual = $this->ultimo();
        if ($moduloId && $atual !== []) {
            $mapa = [];
            foreach ($atual['itens'] ?? [] as $ant) {
                $mapa[(string) $ant['id']] = $ant;
            }
            foreach ($itens as $novo) {
                $mapa[(string) $novo['id']] = $novo;
            }
            $payload['itens'] = array_values($mapa);
            $payload['todos'] = false;
            $payload['resumo'] = $this->recontar($payload['itens']);
        }

        Cache::put(self::CACHE_KEY, $payload, now()->addDay());

        return $payload;
    }

    /** @return array<string, mixed> */
    public function ultimo(): array
    {
        $v = Cache::get(self::CACHE_KEY);

        return is_array($v) ? $v : [];
    }

    /**
     * Pendências a partir do relatório de auditoria + config + últimos testes.
     *
     * @param  array<string, mixed>  $relatorio
     * @return list<array{id:string,nome:string,tr:string,motivos:list<string>,gravidade:string}>
     */
    public function pendencias(array $relatorio): array
    {
        $testes = [];
        foreach ($this->ultimo()['itens'] ?? [] as $t) {
            $testes[(string) ($t['id'] ?? '')] = $t;
        }

        $cfgMap = [];
        try {
            foreach (ModuloConfiguracao::painel() as $c) {
                $cfgMap[(string) $c['id']] = $c;
            }
        } catch (Throwable) {
            $cfgMap = [];
        }

        $lista = [];
        foreach ($relatorio['modulos'] ?? [] as $m) {
            $id = (string) ($m['id'] ?? '');
            $motivos = [];

            foreach ($m['checks'] ?? [] as $chk) {
                if (empty($chk['ok'])) {
                    $crit = ! empty($chk['critico']) ? ' [crítico]' : '';
                    $motivos[] = 'Check'.$crit.': '.($chk['nome'] ?? 'item').' — '.($chk['detalhe'] ?? '');
                }
            }

            $cfg = $cfgMap[$id] ?? null;
            if ($cfg !== null) {
                if (empty($cfg['contato_ok']) && ! empty($cfg['ativo'])) {
                    $motivos[] = 'Config: contato essencial incompleto (setor/RT)';
                }
                $pct = (int) ($cfg['percentual'] ?? 100);
                if ($pct < 80 && ((int) ($cfg['campos_total'] ?? 0)) > 0) {
                    $motivos[] = 'Config: '.$pct.'% dos campos ('.$cfg['campos_ok'].'/'.$cfg['campos_total'].')';
                }
            }

            $t = $testes[$id] ?? null;
            if ($t !== null && ($t['status'] ?? '') === 'falha') {
                $motivos[] = 'Teste run.php: '.($t['falha'] ?? 0).' falha(s) — '.mb_substr((string) ($t['saida'] ?? ''), 0, 160);
            }

            if ($motivos === []) {
                continue;
            }

            $gravidade = 'warn';
            foreach ($m['checks'] ?? [] as $chk) {
                if (empty($chk['ok']) && ! empty($chk['critico'])) {
                    $gravidade = 'fail';
                    break;
                }
            }
            if (($t['status'] ?? '') === 'falha') {
                $gravidade = 'fail';
            }

            $lista[] = [
                'id' => $id,
                'nome' => (string) ($m['nome'] ?? $id),
                'tr' => (string) ($m['tr'] ?? ''),
                'motivos' => $motivos,
                'gravidade' => $gravidade,
            ];
        }

        usort($lista, static function (array $a, array $b): int {
            $peso = static fn (string $g): int => $g === 'fail' ? 0 : 1;

            return $peso($a['gravidade']) <=> $peso($b['gravidade'])
                ?: strcmp($a['nome'], $b['nome']);
        });

        return $lista;
    }

    /**
     * @param  array<string, mixed>  $mod
     * @return array{id:string,nome:string,tr:string,status:string,ok:int,falha:int,saida:string,script:?string}
     */
    private function rodarPacote(array $mod): array
    {
        $id = (string) ($mod['id'] ?? '');
        $nome = (string) ($mod['nome'] ?? $id);
        $tr = (string) ($mod['tr'] ?? '');
        $pathRel = (string) ($mod['path'] ?? '');
        $base = $pathRel !== '' ? base_path($pathRel) : '';
        $script = $base !== '' ? $base.'/tests/run.php' : '';

        // Fallbacks conhecidos (servidor → RH / pacote legado)
        if ($script === '' || ! is_file($script)) {
            $alternativos = match ($id) {
                'servidor' => [
                    base_path('packages/buriti/i-educar-rh-educacional-package/tests/run.php'),
                    base_path('packages/buriti/i-educar-servidor-educacional-package/tests/run.php'),
                ],
                default => [],
            };
            foreach ($alternativos as $alt) {
                if (is_file($alt)) {
                    $script = $alt;
                    break;
                }
            }
        }

        if ($script === '' || ! is_file($script)) {
            return [
                'id' => $id,
                'nome' => $nome,
                'tr' => $tr,
                'status' => 'skip',
                'ok' => 0,
                'falha' => 0,
                'saida' => 'Sem tests/run.php neste pacote',
                'script' => null,
            ];
        }

        try {
            $result = Process::timeout(self::TIMEOUT)
                ->path(dirname($script))
                ->run([PHP_BINARY, $script]);

            $saida = trim($result->output()."\n".$result->errorOutput());
            [$oks, $fails] = $this->parseContagem($saida);
            $passou = $result->successful() && $fails === 0;

            return [
                'id' => $id,
                'nome' => $nome,
                'tr' => $tr,
                'status' => $passou ? 'ok' : 'falha',
                'ok' => $oks,
                'falha' => $fails > 0 ? $fails : ($passou ? 0 : 1),
                'saida' => mb_substr($saida !== '' ? $saida : ('exit '.$result->exitCode()), 0, 2000),
                'script' => str_replace(base_path().'/', '', $script),
            ];
        } catch (Throwable $e) {
            return [
                'id' => $id,
                'nome' => $nome,
                'tr' => $tr,
                'status' => 'falha',
                'ok' => 0,
                'falha' => 1,
                'saida' => $e->getMessage(),
                'script' => str_replace(base_path().'/', '', $script),
            ];
        }
    }

    /** @return array{0:int,1:int} */
    private function parseContagem(string $saida): array
    {
        if (preg_match('/Resultado:\s*(\d+)\s*ok,\s*(\d+)\s*falhas?/iu', $saida, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }
        if (preg_match('/(\d+)\s*ok,\s*(\d+)\s*falhas?/iu', $saida, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        return [0, 0];
    }

    /**
     * @param  list<array{status:string}>  $itens
     * @return array{ok:int,falha:int,skip:int,total:int}
     */
    private function recontar(array $itens): array
    {
        $ok = $falha = $skip = 0;
        foreach ($itens as $i) {
            match ($i['status'] ?? 'skip') {
                'ok' => $ok++,
                'falha' => $falha++,
                default => $skip++,
            };
        }

        return ['ok' => $ok, 'falha' => $falha, 'skip' => $skip, 'total' => count($itens)];
    }
}
