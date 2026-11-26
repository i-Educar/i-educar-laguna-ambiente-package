<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Datas/horas no padrão brasileiro para exibição ao usuário.
 *
 * - data: 26/11/2026
 * - absoluto / dataHora: 26/11/2026 14:30
 * - completo: 26/11/2026 14:30:05
 * - formatar (default UI): hoje às 14:30 · 26/11/2026 14:30
 *
 * Persistência/API continua em ISO (Y-m-d / Y-m-d H:i:s).
 */
final class DataHora
{
    public const FORMATO = 'd/m/Y H:i';

    public const FORMATO_DATA = 'd/m/Y';

    public const FORMATO_COMPLETO = 'd/m/Y H:i:s';

    /** Humanizado + absoluto (padrão de telas). */
    public static function formatar(mixed $valor, bool $humanizado = true): string
    {
        $c = self::parse($valor);
        if ($c === null) {
            return '—';
        }

        $c = $c->copy()->timezone(self::fuso());
        $absoluto = $c->format(self::FORMATO);

        if (! $humanizado) {
            return $absoluto;
        }

        $rel = self::rotuloRelativo($c);
        if ($rel === null) {
            return $absoluto;
        }

        return $rel.' · '.$absoluto;
    }

    /** Só absoluto DD/MM/YYYY HH:mm. */
    public static function absoluto(mixed $valor): string
    {
        return self::formatar($valor, false);
    }

    /** Alias de absoluto. */
    public static function dataHora(mixed $valor): string
    {
        return self::absoluto($valor);
    }

    /** Só data DD/MM/YYYY (sem hora). */
    public static function data(mixed $valor): string
    {
        $c = self::parse($valor);
        if ($c === null) {
            return '—';
        }

        return $c->copy()->timezone(self::fuso())->format(self::FORMATO_DATA);
    }

    /** DD/MM/YYYY HH:mm:ss (auditoria / logs). */
    public static function completo(mixed $valor): string
    {
        $c = self::parse($valor);
        if ($c === null) {
            return '—';
        }

        return $c->copy()->timezone(self::fuso())->format(self::FORMATO_COMPLETO);
    }

    /** Como formatar(), mas se vazio retorna $fallback (ex.: "Nunca"). */
    public static function ou(mixed $valor, string $fallback = '—', bool $humanizado = true): string
    {
        if ($valor === null || $valor === '' || $valor === '—') {
            return $fallback;
        }

        $out = self::formatar($valor, $humanizado);

        return $out === '—' ? $fallback : $out;
    }

    public static function garantirLocaleCarbon(): void
    {
        try {
            Carbon::setLocale('pt_BR');
        } catch (\Throwable) {
            try {
                Carbon::setLocale('pt');
            } catch (\Throwable) {
                //
            }
        }
    }

    private static function fuso(): string
    {
        try {
            $tz = (string) config('app.timezone', 'America/Sao_Paulo');
            if ($tz === '' || strtoupper($tz) === 'UTC') {
                $legacy = (string) config('legacy.app.locale.timezone', 'America/Sao_Paulo');

                return $legacy !== '' ? $legacy : 'America/Sao_Paulo';
            }

            return $tz;
        } catch (\Throwable) {
            return 'America/Sao_Paulo';
        }
    }

    private static function rotuloRelativo(CarbonInterface $c): ?string
    {
        $agora = Carbon::now(self::fuso());
        if ($c->greaterThan($agora->copy()->addMinutes(2))) {
            return null;
        }

        $diffMin = (int) $c->diffInMinutes($agora, true);
        if ($diffMin < 1) {
            return 'agora';
        }
        if ($diffMin < 60) {
            return 'há '.$diffMin.' min';
        }

        $diffH = (int) $c->diffInHours($agora, true);
        if ($diffH < 24 && $c->isToday()) {
            return 'hoje às '.$c->format('H:i');
        }
        if ($c->isYesterday()) {
            return 'ontem às '.$c->format('H:i');
        }
        if ($diffH < 48) {
            return 'há '.$diffH.' h';
        }

        $diffD = (int) $c->diffInDays($agora, true);
        if ($diffD < 7) {
            return 'há '.$diffD.' dia'.($diffD === 1 ? '' : 's');
        }

        return null;
    }

    private static function parse(mixed $valor): ?CarbonInterface
    {
        if ($valor === null || $valor === '' || $valor === '—') {
            return null;
        }
        if ($valor instanceof CarbonInterface) {
            return $valor;
        }
        try {
            $s = trim((string) $valor);
            // Já em BR d/m/Y[ H:i[:s]] — evita parse americano
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?$/', $s, $m)) {
                return Carbon::create(
                    (int) $m[3],
                    (int) $m[2],
                    (int) $m[1],
                    (int) ($m[4] ?? 0),
                    (int) ($m[5] ?? 0),
                    (int) ($m[6] ?? 0),
                    self::fuso()
                );
            }

            return Carbon::parse($s);
        } catch (\Throwable) {
            return null;
        }
    }
}
