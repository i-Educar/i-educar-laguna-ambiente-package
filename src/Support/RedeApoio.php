<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

/**
 * Instituições da rede de proteção / apoio (Conselho Tutelar, CRAS, CREAS etc.)
 * gravadas nas diretivas do módulo — usadas sobretudo em Atendimento e Busca Ativa.
 */
final class RedeApoio
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function diretivas(): array
    {
        return [
            [
                'chave' => 'rede_conselho_nome',
                'rotulo' => 'Conselho Tutelar — nome / polo',
                'tipo' => 'string',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Ex.: Conselho Tutelar de Laguna — Polo Centro.',
                'grupo' => 'rede_apoio',
                'maxlength' => 120,
                'placeholder' => 'Conselho Tutelar — Polo Centro',
            ],
            [
                'chave' => 'rede_conselho_endereco',
                'rotulo' => 'Conselho Tutelar — endereço',
                'tipo' => 'textarea',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Rua, número, bairro e referência para encaminhamento.',
                'grupo' => 'rede_apoio',
                'maxlength' => 240,
                'placeholder' => 'Rua Exemplo, 100 — Centro',
            ],
            [
                'chave' => 'rede_conselho_telefone',
                'rotulo' => 'Conselho Tutelar — telefone',
                'tipo' => 'tel',
                'config' => null,
                'padrao' => '',
                'grupo' => 'rede_apoio',
                'maxlength' => 20,
                'mascara' => 'telefone',
                'placeholder' => '(48) 3644-0000',
            ],
            [
                'chave' => 'rede_conselho_email',
                'rotulo' => 'Conselho Tutelar — e-mail',
                'tipo' => 'email',
                'config' => null,
                'padrao' => '',
                'grupo' => 'rede_apoio',
                'maxlength' => 120,
                'placeholder' => 'conselho.tutelar@municipio.gov.br',
            ],
            [
                'chave' => 'rede_cras_nome',
                'rotulo' => 'CRAS — nome / referência',
                'tipo' => 'string',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Centro de Referência de Assistência Social da rede.',
                'grupo' => 'rede_apoio',
                'maxlength' => 120,
                'placeholder' => 'CRAS Centro',
            ],
            [
                'chave' => 'rede_cras_telefone',
                'rotulo' => 'CRAS — telefone',
                'tipo' => 'tel',
                'config' => null,
                'padrao' => '',
                'grupo' => 'rede_apoio',
                'maxlength' => 20,
                'mascara' => 'telefone',
                'placeholder' => '(48) 3644-0000',
            ],
            [
                'chave' => 'rede_cras_endereco',
                'rotulo' => 'CRAS — endereço',
                'tipo' => 'textarea',
                'config' => null,
                'padrao' => '',
                'grupo' => 'rede_apoio',
                'maxlength' => 240,
            ],
            [
                'chave' => 'rede_creas_nome',
                'rotulo' => 'CREAS — nome / referência',
                'tipo' => 'string',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Para casos de média complexidade (violência, descumprimento etc.).',
                'grupo' => 'rede_apoio',
                'maxlength' => 120,
                'placeholder' => 'CREAS Municipal',
            ],
            [
                'chave' => 'rede_creas_telefone',
                'rotulo' => 'CREAS — telefone',
                'tipo' => 'tel',
                'config' => null,
                'padrao' => '',
                'grupo' => 'rede_apoio',
                'maxlength' => 20,
                'mascara' => 'telefone',
                'placeholder' => '(48) 3644-0000',
            ],
            [
                'chave' => 'rede_outras',
                'rotulo' => 'Outras instituições da rede',
                'tipo' => 'textarea',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Uma por linha: nome — telefone — endereço (Promotoria, saúde mental, ONGs parceiras).',
                'grupo' => 'rede_apoio',
                'maxlength' => 800,
                'placeholder' => "Promotoria da Infância — (48) 0000-0000\nCAPS Infantojuvenil — (48) 0000-0000",
            ],
        ];
    }
}
