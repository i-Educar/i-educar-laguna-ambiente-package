<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aprovação PoC por módulo (persistida em settings).
 *
 * Status manuais: funciona | ressalva | reprovado | testar
 */
final class AprovacaoModulo
{
    public const STATUS_FUNCIONA = 'funciona';

    public const STATUS_RESSALVA = 'ressalva';

    public const STATUS_REPROVADO = 'reprovado';

    public const STATUS_TESTAR = 'testar';

    private const SETTINGS_KEY = 'laguna.aprovacao_modulos';

    /** @return list<string> */
    public static function statusValidos(): array
    {
        return [
            self::STATUS_FUNCIONA,
            self::STATUS_RESSALVA,
            self::STATUS_REPROVADO,
            self::STATUS_TESTAR,
        ];
    }

    /**
     * @return array{status:string,rotulo:string,tom:string,icone:string,ajuda:string}
     */
    public static function meta(string $status): array
    {
        return match ($status) {
            self::STATUS_FUNCIONA => [
                'status' => self::STATUS_FUNCIONA,
                'rotulo' => 'Aprovado · funciona',
                'tom' => 'ok',
                'icone' => 'fa-check-circle',
                'ajuda' => 'PoC aprovada e operação demonstrável sem bloqueios.',
            ],
            self::STATUS_RESSALVA => [
                'status' => self::STATUS_RESSALVA,
                'rotulo' => 'Aprovado · com ressalvas',
                'tom' => 'warn',
                'icone' => 'fa-exclamation-circle',
                'ajuda' => 'Aceito na PoC, mas há avisos ou limitações conhecidas.',
            ],
            self::STATUS_REPROVADO => [
                'status' => self::STATUS_REPROVADO,
                'rotulo' => 'Não aprovado',
                'tom' => 'fail',
                'icone' => 'fa-times-circle',
                'ajuda' => 'Falhas críticas ou decisão explícita de reprovação.',
            ],
            default => [
                'status' => self::STATUS_TESTAR,
                'rotulo' => 'Precisa ser testado',
                'tom' => 'test',
                'icone' => 'fa-flask',
                'ajuda' => 'Ainda sem decisão de aprovação — execute o ensaio e registre o resultado.',
            ],
        };
    }

    /**
     * @return array<string, array{status:string,nota:string,em:?string,por:?string}>
     */
    public static function todas(): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }
        $raw = DB::table('settings')->where('key', self::SETTINGS_KEY)->value('value');
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * @return array{status:string,nota:string,em:?string,por:?string}
     */
    public static function de(string $moduloId): array
    {
        $todas = self::todas();
        $row = $todas[$moduloId] ?? null;
        if (! is_array($row)) {
            return [
                'status' => self::STATUS_TESTAR,
                'nota' => '',
                'em' => null,
                'por' => null,
            ];
        }

        $status = (string) ($row['status'] ?? self::STATUS_TESTAR);
        if (! in_array($status, self::statusValidos(), true)) {
            $status = self::STATUS_TESTAR;
        }

        return [
            'status' => $status,
            'nota' => (string) ($row['nota'] ?? ''),
            'em' => isset($row['em']) ? (string) $row['em'] : null,
            'por' => isset($row['por']) ? (string) $row['por'] : null,
        ];
    }

    public static function gravar(string $moduloId, string $status, string $nota = ''): void
    {
        if (! in_array($status, self::statusValidos(), true)) {
            $status = self::STATUS_TESTAR;
        }
        $todas = self::todas();
        $user = Auth::user();
        $todas[$moduloId] = [
            'status' => $status,
            'nota' => mb_substr(trim($nota), 0, 500),
            'em' => now()->toIso8601String(),
            'por' => $user?->name ?? $user?->email ?? 'admin',
        ];

        if (! Schema::hasTable('settings')) {
            return;
        }

        $payload = json_encode($todas, JSON_UNESCAPED_UNICODE);
        $exists = DB::table('settings')->where('key', self::SETTINGS_KEY)->exists();
        if ($exists) {
            DB::table('settings')->where('key', self::SETTINGS_KEY)->update([
                'value' => $payload,
                'type' => 'string',
                'updated_at' => now(),
            ]);
        } else {
            $row = [
                'key' => self::SETTINGS_KEY,
                'value' => $payload,
                'type' => 'string',
                'description' => 'Aprovação PoC Laguna por módulo',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('settings', 'setting_category_id')) {
                $row['setting_category_id'] = 1;
            }
            DB::table('settings')->insert($row);
        }
    }

    /**
     * Situação de exibição = combinação saúde técnica + aprovação manual.
     *
     * @param  list<array{nome:string,ok:bool,detalhe:string,critico?:bool}>  $checks
     * @return array{status:string,rotulo:string,tom:string,icone:string,ajuda:string,tecnico:string,aprovacao:array{status:string,nota:string,em:?string,por:?string}}
     */
    public static function situacaoExibicao(string $moduloId, array $checks): array
    {
        $tecnico = self::classificarTecnico($checks);
        $aprovacao = self::de($moduloId);

        $status = match (true) {
            $tecnico === self::STATUS_REPROVADO => self::STATUS_REPROVADO,
            $aprovacao['status'] === self::STATUS_REPROVADO => self::STATUS_REPROVADO,
            $aprovacao['status'] === self::STATUS_RESSALVA => self::STATUS_RESSALVA,
            $aprovacao['status'] === self::STATUS_FUNCIONA && $tecnico === self::STATUS_RESSALVA => self::STATUS_RESSALVA,
            $aprovacao['status'] === self::STATUS_FUNCIONA && $tecnico === self::STATUS_FUNCIONA => self::STATUS_FUNCIONA,
            $aprovacao['status'] === self::STATUS_TESTAR => self::STATUS_TESTAR,
            default => $tecnico === self::STATUS_FUNCIONA ? self::STATUS_TESTAR : $tecnico,
        };

        return self::meta($status) + [
            'tecnico' => $tecnico,
            'aprovacao' => $aprovacao,
        ];
    }

    /**
     * @param  list<array{nome:string,ok:bool,detalhe:string,critico?:bool}>  $checks
     */
    public static function classificarTecnico(array $checks): string
    {
        $temCritico = false;
        $temMole = false;
        foreach ($checks as $c) {
            if (! empty($c['ok'])) {
                continue;
            }
            if (! empty($c['critico'])) {
                $temCritico = true;
            } else {
                $temMole = true;
            }
        }
        if ($temCritico) {
            return self::STATUS_REPROVADO;
        }
        if ($temMole) {
            return self::STATUS_RESSALVA;
        }

        return self::STATUS_FUNCIONA;
    }

    /**
     * Percentual de checks técnicos OK (0–100).
     *
     * @param  list<array{nome?:string,ok?:bool,detalhe?:string,critico?:bool}>  $checks
     * @return array{percentual:int,ok:int,total:int}
     */
    public static function percentualChecks(array $checks): array
    {
        $total = count($checks);
        if ($total === 0) {
            return ['percentual' => 0, 'ok' => 0, 'total' => 0];
        }
        $ok = 0;
        foreach ($checks as $c) {
            if (! empty($c['ok'])) {
                $ok++;
            }
        }

        return [
            'percentual' => (int) round(($ok / $total) * 100),
            'ok' => $ok,
            'total' => $total,
        ];
    }
}
