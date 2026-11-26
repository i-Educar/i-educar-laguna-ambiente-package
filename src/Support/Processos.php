<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

/**
 * Faixa 8899xx — ferramentas admin Laguna (fora dos módulos de domínio).
 */
final class Processos
{
    public const RAIZ = 889900;

    public const PAINEL = 889901;

    /** Configurações → Módulos (flags municipais). */
    public const MODULOS = 889910;

    public const SETUP = 889911;

    public const TEMA = 889912;

    public const USUARIOS = 889913;

    /** Verificar instalação / auditoria de módulos. */
    public const CHECK = 889914;

    /** @return array<int, string> */
    public static function titulos(): array
    {
        return [
            self::RAIZ => 'Ambiente Laguna',
            self::PAINEL => 'Painel ambiente',
            self::MODULOS => 'Configuração de módulos',
            self::SETUP => 'Setup / povoar teste',
            self::TEMA => 'Skins da escola',
            self::USUARIOS => 'Suspender usuários',
            self::CHECK => 'Verificar módulos',
        ];
    }

    /** @return list<int> */
    public static function todos(): array
    {
        return [
            self::RAIZ,
            self::PAINEL,
            self::CHECK,
            self::SETUP,
            self::TEMA,
            self::USUARIOS,
            self::MODULOS,
        ];
    }
}
