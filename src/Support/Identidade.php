<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

final class Identidade
{
    public const COR = '#1a5f9e';

    public const DESTAQUE = '#2f7ec4';

    public const COR_CLARA = '#e8eef8';

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
