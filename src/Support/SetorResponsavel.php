<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

/**
 * Dados do setor responsável por módulo (SME), gravados nas diretivas Laguna
 * e exibidos nos portais externos quando preenchidos.
 *
 * @phpstan-type Setor array{
 *   nome:string,
 *   endereco:string,
 *   horario:string,
 *   telefone:string,
 *   email:string,
 *   observacao:string,
 *   publicado:bool
 * }
 */
final class SetorResponsavel
{
    /**
     * @return list<array{chave:string,rotulo:string,tipo:string,config:?string,padrao:mixed,ajuda?:string}>
     */
    public static function diretivas(): array
    {
        return [
            [
                'chave' => 'setor_nome',
                'rotulo' => 'Setor responsável (nome)',
                'tipo' => 'string',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Nome do setor da SME que responde por este módulo. Ex.: Setor de Transporte Escolar.',
                'grupo' => 'setor',
                'maxlength' => 120,
                'placeholder' => 'Ex.: Setor de Transporte Escolar',
                'essencial' => true,
            ],
            [
                'chave' => 'setor_endereco',
                'rotulo' => 'Endereço de atendimento (SME)',
                'tipo' => 'textarea',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Endereço físico onde a família ou a escola resolve pendências presenciais.',
                'grupo' => 'setor',
                'maxlength' => 240,
                'placeholder' => 'Rua, número, bairro — CEP 00000-000',
            ],
            [
                'chave' => 'setor_horario',
                'rotulo' => 'Horário de funcionamento',
                'tipo' => 'string',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Use formato legível: dias + intervalos. Evite só “8 às 17”.',
                'grupo' => 'setor',
                'maxlength' => 120,
                'placeholder' => 'Seg a sex, 8h–12h e 13h30–17h',
                'mascara' => 'horario',
            ],
            [
                'chave' => 'setor_telefone',
                'rotulo' => 'Telefone institucional',
                'tipo' => 'tel',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'DDD + número. Canal oficial da SME — não use celular pessoal.',
                'grupo' => 'setor',
                'maxlength' => 20,
                'placeholder' => '(48) 3644-0000',
                'mascara' => 'telefone',
                'essencial' => true,
            ],
            [
                'chave' => 'setor_email',
                'rotulo' => 'E-mail do setor',
                'tipo' => 'email',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Prefira domínio @municipio.gov.br ou institucional.',
                'grupo' => 'setor',
                'maxlength' => 120,
                'placeholder' => 'setor@municipio.gov.br',
                'essencial' => true,
            ],
            [
                'chave' => 'setor_observacao',
                'rotulo' => 'Orientação ao público',
                'tipo' => 'textarea',
                'config' => null,
                'padrao' => '',
                'ajuda' => 'Texto curto no portal: documentos, senha, fila, o que levar.',
                'grupo' => 'setor',
                'maxlength' => 400,
                'placeholder' => 'Traga RG e comprovante de residência. Atendimento por ordem de chegada.',
            ],
        ];
    }

    /**
     * @return Setor
     */
    public static function de(string $moduloId): array
    {
        $d = ModuloConfiguracao::diretivasSalvas($moduloId);
        $nome = trim((string) ($d['setor_nome'] ?? ''));
        $endereco = trim((string) ($d['setor_endereco'] ?? ''));
        $horario = trim((string) ($d['setor_horario'] ?? ''));
        $telefone = trim((string) ($d['setor_telefone'] ?? ''));
        $email = trim((string) ($d['setor_email'] ?? ''));
        $observacao = trim((string) ($d['setor_observacao'] ?? ''));

        $publicado = $nome !== '' || $endereco !== '' || $horario !== '' || $telefone !== '' || $email !== '';

        return [
            'nome' => $nome,
            'endereco' => $endereco,
            'horario' => $horario,
            'telefone' => self::formatarTelefone($telefone),
            'email' => $email,
            'observacao' => $observacao,
            'publicado' => $publicado,
        ];
    }

    private static function formatarTelefone(string $telefone): string
    {
        $digitos = preg_replace('/\D+/', '', $telefone) ?? '';
        if (strlen($digitos) === 11) {
            return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7));
        }
        if (strlen($digitos) === 10) {
            return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6));
        }

        return $telefone;
    }

    /**
     * Anonimiza nome para lista pública (ex.: M**** A**** da S****).
     */
    public static function anonimizarNome(string $nome): string
    {
        $nome = trim(preg_replace('/\s+/u', ' ', $nome) ?? '');
        if ($nome === '') {
            return '—';
        }
        $partes = preg_split('/\s+/u', $nome) ?: [];
        $out = [];
        foreach ($partes as $p) {
            $len = mb_strlen($p);
            if ($len <= 1) {
                $out[] = '*';
                continue;
            }
            $out[] = mb_substr($p, 0, 1).str_repeat('*', min(4, $len - 1));
        }

        return implode(' ', $out);
    }
}
