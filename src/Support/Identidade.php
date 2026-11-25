<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

final class Identidade
{
    public const COR = '#16384a';

    public const DESTAQUE = '#1f4e66';

    public const COR_CLARA = '#e8eef2';

    public const ICONE = 'fa-heartbeat';

    public const NOME = 'Ambiente Laguna';

    public const DESCRICAO = 'Saúde e configuração do ensaio da rede';

    /** @return array{cor:string,destaque:string,clara:string,icone:string,nome:string,descricao:string} */
    public static function tokens(): array
    {
        return [
            'cor' => self::COR,
            'destaque' => self::DESTAQUE,
            'clara' => self::COR_CLARA,
            'icone' => self::ICONE,
            'nome' => self::NOME,
            'descricao' => self::DESCRICAO,
        ];
    }
}
