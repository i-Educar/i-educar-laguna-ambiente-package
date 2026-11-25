<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

/**
 * Faixa 8899xx — ferramentas admin Laguna (fora dos módulos de domínio).
 */
final class Processos
{
    public const RAIZ = 889900;

    public const PAINEL = 889901;

    public const CHECK = 889910;

    public const SETUP = 889911;

    /** @return array<int, string> */
    public static function titulos(): array
    {
        return [
            self::RAIZ => 'Ambiente Laguna',
            self::PAINEL => 'Painel ambiente',
            self::CHECK => 'Verificar módulos',
            self::SETUP => 'Setup / povoar teste',
        ];
    }
}
