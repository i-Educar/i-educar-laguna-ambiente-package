<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

/**
 * Flags, diretivas e impactos por módulo do catálogo PE-77.
 *
 * @phpstan-type Diretiva array{
 *   chave:string,
 *   rotulo:string,
 *   tipo:string,
 *   config:?string,
 *   padrao:mixed,
 *   ajuda?:string,
 *   min?:float|int,
 *   max?:float|int
 * }
 */
final class DiretivasModulos
{
    /**
     * @return array{
     *   diretivas:list<Diretiva>,
     *   impactos_desativar:list<string>,
     *   fluxos_comprometidos:list<string>,
     *   bloqueavel:bool,
     *   prefixos_rota:list<string>
     * }
     */
    public static function de(string $id): array
    {
        $mapa = self::mapa();
        $padrao = [
            'diretivas' => [
                [
                    'chave' => 'ativo',
                    'rotulo' => 'Módulo ativo para a cidade',
                    'tipo' => 'bool',
                    'config' => null,
                    'padrao' => true,
                    'ajuda' => 'Controla menus, rotas e PackageTenant deste módulo.',
                ],
            ],
            'impactos_desativar' => [
                'Funcionalidades do módulo deixam de estar disponíveis na rede.',
                'Usuários com permissão deixam de acessar as telas correspondentes.',
            ],
            'fluxos_comprometidos' => [],
            'bloqueavel' => true,
            'prefixos_rota' => [],
            'guia' => 'Ative o módulo e revise as opções antes de publicar para a rede.',
        ];

        $meta = $mapa[$id] ?? $padrao;
        $meta['guia'] = $meta['guia'] ?? $padrao['guia'];

        return $meta;
    }

    /**
     * @return array<string, array{
     *   diretivas:list<Diretiva>,
     *   impactos_desativar:list<string>,
     *   fluxos_comprometidos:list<string>,
     *   bloqueavel:bool,
     *   prefixos_rota:list<string>
     * }>
     */
    public static function mapa(): array
    {
        return [
            'transporte' => [
                'guia' => '1) Confirme se o módulo fica ativo. 2) Ajuste regras (aquaviário, cerca, rateio). 3) Preencha o setor da SME (nome, telefone e e-mail) — é o contato público da família.',
                'diretivas' => array_merge([
                    self::ativo(),
                    self::portalExterno(true, 'Portal da família / carteirinhas externas'),
                    self::aparecerNaHome(false),
                    [
                        'chave' => 'aquaviario',
                        'rotulo' => 'Município com transporte aquaviário',
                        'tipo' => 'bool',
                        'config' => null,
                        'padrao' => false,
                        'grupo' => 'regras',
                        'ajuda' => 'Ligue só se houver travessia fluvial/marítima escolar. Libera categorias e custo aquaviário.',
                    ],
                    [
                        'chave' => 'geocerca',
                        'rotulo' => 'Marcar embarque fora da cerca do ponto',
                        'tipo' => 'bool',
                        'config' => null,
                        'padrao' => false,
                        'grupo' => 'regras',
                        'ajuda' => 'Quando ligado, o app sinaliza se o embarque ocorreu longe do ponto cadastrado.',
                    ],
                    [
                        'chave' => 'raio_metros',
                        'rotulo' => 'Raio da cerca do ponto (metros)',
                        'tipo' => 'int',
                        'config' => null,
                        'padrao' => 150,
                        'min' => 0,
                        'max' => 5000,
                        'grupo' => 'regras',
                        'ajuda' => 'Distância aceitável em torno do ponto (0–5000). Padrão 150 m.',
                        'placeholder' => '150',
                        'sufixo' => 'm',
                    ],
                    [
                        'chave' => 'rateio',
                        'rotulo' => 'Rateio do salário do motorista/monitor',
                        'tipo' => 'select',
                        'config' => null,
                        'padrao' => 'km',
                        'grupo' => 'regras',
                        'ajuda' => 'Como a SME divide o custo entre rotas: por km rodado ou por alunos embarcados.',
                        'opcoes' => [
                            ['value' => 'km', 'label' => 'Por quilometragem'],
                            ['value' => 'alunos', 'label' => 'Por alunos embarcados'],
                        ],
                    ],
                ], SetorResponsavel::diretivas()),
                'impactos_desativar' => [
                    'Cadastro de rotas, veículos e embarque/desembarque fica inacessível.',
                    'Portal da família e carteirinhas deixam de operar.',
                    'Exportações SETE / relatórios de transporte ficam indisponíveis.',
                ],
                'fluxos_comprometidos' => [
                    'Matrícula × transporte (vínculo aluno–rota)',
                    'Apps API de transporte, se habilitada',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['transporte-escolar'],
            ],
            'alimentacao' => [
                'guia' => 'Ajuste lanches/jantar, o responsável técnico (CRN) e o setor da SME. O cardápio e o botão «i» de informes usam estes dados.',
                'diretivas' => array_merge([
                    self::ativo(),
                    [
                        'chave' => 'lanches_por_dia',
                        'rotulo' => 'Lanches por dia',
                        'tipo' => 'int',
                        'config' => 'alimentacao.lanches_por_dia',
                        'padrao' => 2,
                        'min' => 1,
                        'max' => 4,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'incluir_jantar',
                        'rotulo' => 'Incluir jantar no cardápio',
                        'tipo' => 'bool',
                        'config' => 'alimentacao.incluir_jantar',
                        'padrao' => false,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'montar_cardapio',
                        'rotulo' => 'Montar cardápio na SME',
                        'tipo' => 'bool',
                        'config' => 'alimentacao.montar_cardapio',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'bandejas_automaticas',
                        'rotulo' => 'Gerar bandejas automaticamente',
                        'tipo' => 'bool',
                        'config' => 'alimentacao.bandejas_automaticas',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'estimar_porcoes_ao_lancar',
                        'rotulo' => 'Estimar porções ao lançar',
                        'tipo' => 'bool',
                        'config' => 'alimentacao.estimar_porcoes_ao_lancar',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'unidade_so_registra_fatos',
                        'rotulo' => 'Unidade só registra fatos (não planeja)',
                        'tipo' => 'bool',
                        'config' => 'alimentacao.unidade_so_registra_fatos',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'alerta_horario_turma',
                        'rotulo' => 'Alertar horário de turma × refeição',
                        'tipo' => 'bool',
                        'config' => 'alimentacao.alerta_horario_turma',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'porcoes_padrao',
                        'rotulo' => 'Porções padrão por refeição',
                        'tipo' => 'int',
                        'config' => 'alimentacao.porcoes_padrao',
                        'padrao' => 100,
                        'min' => 1,
                        'max' => 5000,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'rt_nome',
                        'rotulo' => 'RT — nome',
                        'tipo' => 'string',
                        'config' => 'alimentacao.rt_nome',
                        'padrao' => 'Nutricionista responsável (PoC Laguna)',
                        'maxlength' => 120,
                        'grupo' => 'setor',
                        'ajuda' => 'Nutricionista responsável técnico pela alimentação escolar.',
                        'placeholder' => 'Nome completo do RT',
                        'essencial' => true,
                    ],
                    [
                        'chave' => 'rt_registro',
                        'rotulo' => 'RT — registro (CRN)',
                        'tipo' => 'string',
                        'config' => 'alimentacao.rt_registro',
                        'padrao' => 'CRN-10 0000',
                        'maxlength' => 40,
                        'grupo' => 'setor',
                        'ajuda' => 'Número do Conselho Regional de Nutricionistas (ex.: CRN-10 1234).',
                        'placeholder' => 'CRN-10 0000',
                        'essencial' => true,
                    ],
                    [
                        'chave' => 'rt_cargo',
                        'rotulo' => 'RT — cargo',
                        'tipo' => 'string',
                        'config' => 'alimentacao.rt_cargo',
                        'padrao' => 'Nutricionista responsável técnico',
                        'maxlength' => 120,
                        'grupo' => 'setor',
                        'placeholder' => 'Nutricionista responsável técnico',
                    ],
                    [
                        'chave' => 'rt_email',
                        'rotulo' => 'RT — e-mail',
                        'tipo' => 'email',
                        'config' => 'alimentacao.rt_email',
                        'padrao' => 'alimentacao@laguna.sc.gov.br',
                        'maxlength' => 120,
                        'grupo' => 'setor',
                        'placeholder' => 'nutricao@municipio.gov.br',
                    ],
                ], array_map(static function (array $d): array {
                    $padroes = [
                        'setor_nome' => 'Setor de Alimentação Escolar / Nutrição',
                        'setor_endereco' => 'SME Laguna — Nutrição / Alimentação Escolar',
                        'setor_horario' => 'Seg a sex, 8h–12h e 13h30–17h',
                        'setor_telefone' => '(48) 3644-4000',
                        'setor_email' => 'alimentacao@laguna.sc.gov.br',
                        'setor_observacao' => 'Dúvidas de cardápio e restrições alimentares: procure o setor de Nutrição com laudo médico.',
                    ];
                    if (isset($padroes[$d['chave']])) {
                        $d['padrao'] = $padroes[$d['chave']];
                    }

                    return $d;
                }, SetorResponsavel::diretivas())),
                'impactos_desativar' => [
                    'Cardápios, remessa e controle de merenda ficam offline.',
                    'Escolas deixam de registrar consumo no módulo.',
                ],
                'fluxos_comprometidos' => [
                    'Planejamento nutricional municipal',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['alimentacao-escolar'],
            ],
            'almoxarifado' => [
                'guia' => 'Módulo operacional de estoque. Em geral basta mantê-lo ativo; pedidos e assinaturas ficam nas telas do próprio almoxarifado. Informe o setor responsável para aparecer o botão «i» de informes.',
                'diretivas' => array_merge([
                    self::ativo(),
                ], array_map(static function (array $d): array {
                    $padroes = [
                        'setor_nome' => 'Setor de Almoxarifado Escolar',
                        'setor_endereco' => 'SME Laguna — Almoxarifado / Suprimentos',
                        'setor_horario' => 'Seg a sex, 8h–12h e 13h30–17h',
                        'setor_telefone' => '(48) 3644-4100',
                        'setor_email' => 'almoxarifado@laguna.sc.gov.br',
                        'setor_observacao' => 'Pedidos pela unidade escolar. Retirada/entrega conforme protocolo do pedido.',
                    ];
                    if (isset($padroes[$d['chave']])) {
                        $d['padrao'] = $padroes[$d['chave']];
                    }

                    return $d;
                }, SetorResponsavel::diretivas())),
                'impactos_desativar' => [
                    'Pedidos de material e movimentação de estoque ficam bloqueados.',
                    'Pedidos já abertos não avançam no fluxo de assinaturas.',
                ],
                'fluxos_comprometidos' => [
                    'Suprimento escolar (carrinho → entrega → conferência)',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['almoxarifado-escolar'],
            ],
            'atendimento' => [
                'guia' => '1) Defina se o acolhimento é na SME, em cada escola ou híbrido. 2) Preencha o setor municipal. 3) Cadastre Conselho Tutelar, CRAS/CREAS e demais parceiros da rede de proteção.',
                'diretivas' => array_merge([
                    self::ativo(),
                    [
                        'chave' => 'limite_ausencias_alerta',
                        'rotulo' => 'Limite de ausências para alerta',
                        'tipo' => 'int',
                        'config' => 'atendimento.limite_ausencias_alerta',
                        'padrao' => 3,
                        'min' => 1,
                        'grupo' => 'regras',
                        'max' => 30,
                        'ajuda' => 'Quantidade de ausências que dispara alerta no atendimento e na ponte com Busca Ativa (1–30).',
                        'placeholder' => '3',
                    ],
                    [
                        'chave' => 'ponto_atendimento',
                        'rotulo' => 'Onde ocorre o atendimento',
                        'tipo' => 'select',
                        'config' => null,
                        'padrao' => 'hibrido',
                        'grupo' => 'regras',
                        'ajuda' => 'Escolas = polo na unidade; SME = só na secretaria; híbrido = ambos (mais comum).',
                        'opcoes' => [
                            ['value' => 'sme', 'label' => 'Somente na SME / sede'],
                            ['value' => 'escola', 'label' => 'Polo em cada escola'],
                            ['value' => 'hibrido', 'label' => 'SME e escolas (híbrido)'],
                        ],
                    ],
                    [
                        'chave' => 'orientacao_escola',
                        'rotulo' => 'Orientação aos polos escolares',
                        'tipo' => 'textarea',
                        'config' => null,
                        'padrao' => '',
                        'grupo' => 'regras',
                        'maxlength' => 500,
                        'ajuda' => 'Texto padrão para a escola quando for ponto de atendimento (sala, horário, responsável).',
                        'placeholder' => 'Atendimento na sala da coordenação, terças e quintas, 9h–11h. Encaminhar pela ficha do aluno.',
                        'wide' => true,
                    ],
                ], SetorResponsavel::diretivas(), RedeApoio::diretivas()),
                'impactos_desativar' => [
                    'Fila, agenda e registro de acolhimento ficam inacessíveis.',
                    'Alertas da Busca Ativa deixam de alimentar a fila do atendimento.',
                ],
                'fluxos_comprometidos' => [
                    'Busca Ativa → Atendimento ao estudante',
                    'Equipe multiprofissional na escola',
                    'Encaminhamentos à rede de proteção',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['atendimento-estudante'],
            ],
            'busca-ativa' => [
                'guia' => '1) Ajuste os limiares de faltas/frequência. 2) Informe o setor da SME. 3) Complete a rede de apoio para encaminhamentos fora da escola.',
                'diretivas' => array_merge([
                    self::ativo(),
                    [
                        'chave' => 'faltas_consecutivas_alerta',
                        'rotulo' => 'Faltas consecutivas para alerta',
                        'tipo' => 'int',
                        'config' => 'busca-ativa.limites.faltas_consecutivas_alerta',
                        'padrao' => 3,
                        'min' => 1,
                        'max' => 30,
                        'grupo' => 'regras',
                        'ajuda' => 'Dispara alerta após N faltas seguidas (1–30).',
                        'placeholder' => '3',
                    ],
                    [
                        'chave' => 'faltas_mensais_alerta',
                        'rotulo' => 'Faltas no período (mês)',
                        'tipo' => 'int',
                        'config' => 'busca-ativa.limites.faltas_mensais_alerta',
                        'padrao' => 5,
                        'min' => 1,
                        'max' => 60,
                        'grupo' => 'regras',
                        'ajuda' => 'Limite de faltas acumuladas no mês letivo (1–60).',
                        'placeholder' => '5',
                    ],
                    [
                        'chave' => 'frequencia_minima_percentual',
                        'rotulo' => 'Frequência mínima (%)',
                        'tipo' => 'float',
                        'config' => 'busca-ativa.limites.frequencia_minima_percentual',
                        'padrao' => 75,
                        'min' => 0,
                        'max' => 100,
                        'grupo' => 'regras',
                        'ajuda' => 'Percentual mínimo aceitável (0–100). Abaixo disso entra na fila.',
                        'placeholder' => '75',
                        'sufixo' => '%',
                    ],
                    [
                        'chave' => 'somente_cursos_marcados',
                        'rotulo' => 'Somente cursos com flag Busca Ativa',
                        'tipo' => 'bool',
                        'config' => 'busca-ativa.diretivas.somente_cursos_marcados',
                        'padrao' => true,
                        'grupo' => 'regras',
                        'ajuda' => 'Restringe a detecção aos cursos marcados no cadastro escolar.',
                    ],
                    [
                        'chave' => 'gerar_alerta_atendimento',
                        'rotulo' => 'Gerar alerta no Atendimento ao estudante',
                        'tipo' => 'bool',
                        'config' => 'busca-ativa.atendimento.gerar_alerta',
                        'padrao' => true,
                        'grupo' => 'regras',
                        'ajuda' => 'Cria item na fila do atendimento quando o aluno entra em risco de evasão.',
                    ],
                ], SetorResponsavel::diretivas(), RedeApoio::diretivas()),
                'impactos_desativar' => [
                    'Detecção de infrequência e processos de reintegração param.',
                    'API familiar e tokens deixam de funcionar.',
                    'Gestores deixam de receber alertas de risco de evasão deste módulo.',
                ],
                'fluxos_comprometidos' => [
                    'TR 5.3.158 / 5.3.173 / 5.3.204 (faltas e evasão)',
                    'Ponte com Atendimento ao estudante',
                    'Acompanhamento familiar externo',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['busca-ativa'],
            ],
            'solicitacao' => [
                'guia' => 'Informe o setor da SME que recebe e classifica os chamados da rede (escola → secretaria).',
                'diretivas' => array_merge([
                    self::ativo(),
                ], SetorResponsavel::diretivas()),
                'impactos_desativar' => [
                    'Abertura e trâmite de chamados da rede ficam indisponíveis.',
                    'SLA e auditoria de pedidos deixam de ser consultáveis pelo módulo.',
                ],
                'fluxos_comprometidos' => [
                    'Central de serviços / chamados SME ↔ escola',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['solicitacao-servicos'],
            ],
            'biblioteca' => [
                'guia' => 'Defina limite de empréstimos, prazo em dias e se atraso bloqueia novo empréstimo.',
                'diretivas' => [
                    self::ativo(),
                    [
                        'chave' => 'limite_simultaneo',
                        'rotulo' => 'Empréstimos simultâneos por leitor',
                        'tipo' => 'int',
                        'config' => 'biblioteca.limite_simultaneo',
                        'padrao' => 3,
                        'min' => 1,
                        'max' => 50,
                        'grupo' => 'regras',
                        'ajuda' => 'Quantos títulos o leitor pode ter ao mesmo tempo (1–50).',
                        'placeholder' => '3',
                    ],
                    [
                        'chave' => 'dias_padrao',
                        'rotulo' => 'Prazo padrão de empréstimo (dias)',
                        'tipo' => 'int',
                        'config' => 'biblioteca.dias_padrao',
                        'padrao' => 7,
                        'min' => 1,
                        'max' => 90,
                        'grupo' => 'regras',
                        'ajuda' => 'Dias corridos até a devolução (1–90).',
                        'placeholder' => '7',
                        'sufixo' => 'dias',
                    ],
                    [
                        'chave' => 'bloquear_atraso',
                        'rotulo' => 'Bloquear novo empréstimo com atraso',
                        'tipo' => 'bool',
                        'config' => 'biblioteca.bloquear_atraso',
                        'padrao' => true,
                        'grupo' => 'regras',
                        'ajuda' => 'Impede novo empréstimo enquanto houver título em atraso.',
                    ],
                ],
                'impactos_desativar' => [
                    'Empréstimos e acervo do módulo ficam inacessíveis.',
                ],
                'fluxos_comprometidos' => [],
                'bloqueavel' => true,
                'prefixos_rota' => ['biblioteca-escolar'],
            ],
            'horario' => [
                'guia' => 'Agregado ao RH/Servidores no painel. Aqui só a chave de ativação do motor de horários.',
                'diretivas' => [
                    self::ativo(),
                ],
                'impactos_desativar' => [
                    'Geração e edição do quadro de horários param.',
                    'Propagação “fazer valer” deixa de estar disponível.',
                ],
                'fluxos_comprometidos' => [
                    'Alocação de professores × turmas',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['horario-automatico'],
            ],
            'eventos' => [
                'guia' => 'Portal público + frequência mínima para certificado + setor da SME que organiza a formação.',
                'diretivas' => array_merge([
                    self::ativo(),
                    self::portalExterno(true, 'Portal público de inscrição, presença e certificados (sem login)'),
                    self::aparecerNaHome(true),
                    [
                        'chave' => 'frequencia_minima_certificado',
                        'rotulo' => 'Frequência mínima para certificado (%)',
                        'tipo' => 'float',
                        'config' => 'eventos.frequencia_minima_certificado',
                        'padrao' => 75,
                        'min' => 0,
                        'max' => 100,
                        'grupo' => 'regras',
                        'ajuda' => 'Percentual exigido para emitir certificado (0–100).',
                        'placeholder' => '75',
                        'sufixo' => '%',
                    ],
                ], SetorResponsavel::diretivas()),
                'impactos_desativar' => [
                    'Inscrições e frequência em formação continuada ficam offline.',
                    'Portal público de eventos deixa de aceitar inscrição, presença e certificado.',
                ],
                'fluxos_comprometidos' => [
                    'Certificação de formação',
                    'Acesso externo (hot site de eventos)',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['eventos-formacao'],
            ],
            'act' => [
                'guia' => 'Ative o portal do candidato e informe o setor de RH/contratações que responde dúvidas do edital.',
                'diretivas' => array_merge([
                    self::ativo(),
                    self::portalExterno(true, 'Portal do candidato ACT (inscrição e acompanhamento sem conta i-Educar)'),
                    self::aparecerNaHome(true),
                ], SetorResponsavel::diretivas()),
                'impactos_desativar' => [
                    'Processos seletivos ACT, inscrição e admissão ficam bloqueados.',
                    'Candidatos no portal público deixam de conseguir acompanhar editais.',
                ],
                'fluxos_comprometidos' => [
                    'Contratação temporária → vínculo servidor/professor',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['contratacoes-temporarias'],
            ],
            'vagas' => [
                'guia' => 'Portal da família (pré-matrícula) + setor de vagas da SME (endereço, telefone e orientação de documentos).',
                'diretivas' => array_merge([
                    self::ativo(),
                    self::portalExterno(true, 'Pré-matrícula / consulta pública de vagas para a família'),
                    self::aparecerNaHome(true),
                ], SetorResponsavel::diretivas()),
                'impactos_desativar' => [
                    'Pré-matrícula / oferta de vagas deixa de operar para a família e para a rede.',
                ],
                'fluxos_comprometidos' => [
                    'Inscrição familiar × alocação de vagas',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['pre-matricula', 'pre-matricula-digital'],
            ],
            'apps-api' => [
                'guia' => 'Governança dos apps (Família/Professor): login, validade do token, features e contato do setor de TI/SME.',
                'diretivas' => array_merge([
                    self::ativo(),
                    [
                        'chave' => 'require_login',
                        'rotulo' => 'Exigir login (Sanctum) nas rotas dos apps',
                        'tipo' => 'bool',
                        'config' => 'apps-api.require_login',
                        'padrao' => true,
                        'grupo' => 'acesso',
                        'ajuda' => 'Mantém as APIs autenticadas. Desligar só em homologação controlada.',
                    ],
                    [
                        'chave' => 'token_ttl_minutes',
                        'rotulo' => 'Validade do token (minutos)',
                        'tipo' => 'int',
                        'config' => 'apps-api.token_ttl_minutes',
                        'padrao' => 43200,
                        'min' => 5,
                        'max' => 525600,
                        'grupo' => 'acesso',
                        'ajuda' => 'Padrão 43200 = 30 dias. Mínimo 5; máximo 1 ano.',
                        'placeholder' => '43200',
                    ],
                    [
                        'chave' => 'suspension_message',
                        'rotulo' => 'Mensagem ao suspender apps no município',
                        'tipo' => 'textarea',
                        'config' => 'apps-api.suspension_message',
                        'padrao' => '',
                        'maxlength' => 400,
                        'grupo' => 'acesso',
                        'ajuda' => 'Texto exibido no app quando o município suspende o acesso.',
                        'placeholder' => 'Acesso temporariamente suspenso pela SME. Em caso de dúvida, procure a secretaria.',
                    ],
                    [
                        'chave' => 'feature_familia',
                        'rotulo' => 'App Família (TR 5.13)',
                        'tipo' => 'bool',
                        'config' => 'apps-api.features.familia',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'feature_professor',
                        'rotulo' => 'App Professor (TR 5.14)',
                        'tipo' => 'bool',
                        'config' => 'apps-api.features.professor',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'feature_admin',
                        'rotulo' => 'API Admin / governança remota',
                        'tipo' => 'bool',
                        'config' => 'apps-api.features.admin',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'feature_exportacao',
                        'rotulo' => 'Exportação (formatos TR 4.x)',
                        'tipo' => 'bool',
                        'config' => 'apps-api.features.exportacao',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                    [
                        'chave' => 'feature_diario',
                        'rotulo' => 'Health i-Diário',
                        'tipo' => 'bool',
                        'config' => 'apps-api.features.diario',
                        'padrao' => true,
                        'grupo' => 'regras',
                    ],
                ], SetorResponsavel::diretivas()),
                'impactos_desativar' => [
                    'Apps mobile e integrações Sanctum deixam de autenticar.',
                    'Governança de tokens e suspensão via apps ficam inacessíveis.',
                ],
                'fluxos_comprometidos' => [
                    'Canais externos (família/professor) dependentes da Apps API',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['apps-api', 'api/apps'],
            ],
            'exportacao' => [
                'guia' => 'Pacote de exportações oficiais. Em geral permanece ativo; não há contato público.',
                'diretivas' => [
                    self::ativo(),
                ],
                'impactos_desativar' => [
                    'Exportações unificadas da rede ficam indisponíveis.',
                ],
                'fluxos_comprometidos' => [
                    'Entregas oficiais / pacotes de dados',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => [],
            ],
            'escolar' => [
                'guia' => 'Aderência escolar (ondas Laguna). Ative para expor as ferramentas na rede.',
                'diretivas' => [
                    self::ativo(),
                ],
                'impactos_desativar' => [
                    'Ferramentas de aderência escolar (ondas Laguna) ficam offline.',
                ],
                'fluxos_comprometidos' => [],
                'bloqueavel' => true,
                'prefixos_rota' => [],
            ],
            'servidor' => [
                'guia' => 'RH congrega horário, ACT e eventos (ocultos como cartões separados). Mantenha ativo se a cidade usa o pacote RH.',
                'diretivas' => array_merge([
                    self::ativo(),
                ], SetorResponsavel::diretivas()),
                'impactos_desativar' => [
                    'Extensões do domínio servidor educacional deixam de carregar.',
                ],
                'fluxos_comprometidos' => [
                    'Fluxos que dependem do pacote servidor educacional',
                    'Horário automático / ACT / eventos agregados',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => [],
            ],
            'censo' => [
                'guia' => 'Importação Educacenso. Ative na janela de carga; não exige setor público.',
                'diretivas' => [
                    self::ativo(),
                ],
                'impactos_desativar' => [
                    'Importação Educacenso fica indisponível.',
                ],
                'fluxos_comprometidos' => [
                    'Carga inicial / sincronização Educacenso',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['educacenso'],
            ],
            'seguranca' => [
                'guia' => 'Autenticação avançada (MFA, passkeys), sessões, auditoria e governança de suspensão.',
                'diretivas' => [
                    self::ativo(),
                    [
                        'chave' => 'mfa_obrigatorio',
                        'rotulo' => 'Exigir MFA (autenticação em dois fatores)',
                        'tipo' => 'bool',
                        'config' => 'seguranca.mfa.obrigatorio',
                        'padrao' => false,
                        'grupo' => 'acesso',
                        'ajuda' => 'Quando ligado, todos os usuários precisam configurar e usar MFA no login.',
                    ],
                    [
                        'chave' => 'mfa_janela',
                        'rotulo' => 'Janela MFA (dias sem revalidar)',
                        'tipo' => 'int',
                        'config' => 'seguranca.mfa.janela',
                        'padrao' => 1,
                        'min' => 0,
                        'max' => 5,
                        'grupo' => 'acesso',
                        'ajuda' => 'Dias em que o segundo fator não é pedido de novo no mesmo dispositivo (0–5). 0 = sempre pedir.',
                        'placeholder' => '1',
                        'sufixo' => 'dias',
                    ],
                    [
                        'chave' => 'passkeys_enabled',
                        'rotulo' => 'Permitir passkeys (WebAuthn)',
                        'tipo' => 'bool',
                        'config' => 'seguranca.passkeys.enabled',
                        'padrao' => true,
                        'grupo' => 'acesso',
                        'ajuda' => 'Habilita registro e login com chaves de acesso (biometria / dispositivo).',
                    ],
                    [
                        'chave' => 'passkeys_rp_name',
                        'rotulo' => 'Nome do Relying Party (passkeys)',
                        'tipo' => 'string',
                        'config' => 'seguranca.passkeys.rp_name',
                        'padrao' => 'i-Educar',
                        'maxlength' => 120,
                        'grupo' => 'acesso',
                        'ajuda' => 'Nome exibido pelo navegador/dispositivo ao registrar a passkey.',
                        'placeholder' => 'i-Educar',
                    ],
                    [
                        'chave' => 'max_sessoes',
                        'rotulo' => 'Máximo de sessões simultâneas',
                        'tipo' => 'int',
                        'config' => 'seguranca.sessoes.max_simultaneas',
                        'padrao' => 3,
                        'min' => 1,
                        'max' => 20,
                        'grupo' => 'acesso',
                        'ajuda' => 'Quantas sessões ativas um usuário pode manter ao mesmo tempo (1–20).',
                        'placeholder' => '3',
                    ],
                    [
                        'chave' => 'sessoes_rastrear',
                        'rotulo' => 'Rastrear sessões e dispositivos',
                        'tipo' => 'bool',
                        'config' => 'seguranca.sessoes.rastrear',
                        'padrao' => true,
                        'grupo' => 'acesso',
                        'ajuda' => 'Registra IP, user-agent e última atividade para listagem e revogação.',
                    ],
                    [
                        'chave' => 'sessoes_inativar_apos_dias',
                        'rotulo' => 'Inativar sessões após (dias)',
                        'tipo' => 'int',
                        'config' => 'seguranca.sessoes.inativar_apos_dias',
                        'padrao' => 30,
                        'min' => 1,
                        'max' => 365,
                        'grupo' => 'acesso',
                        'ajuda' => 'Sessões sem atividade são marcadas inativas após N dias.',
                        'placeholder' => '30',
                        'sufixo' => 'dias',
                    ],
                    [
                        'chave' => 'auditoria_enabled',
                        'rotulo' => 'Registrar auditoria de segurança',
                        'tipo' => 'bool',
                        'config' => 'seguranca.auditoria.enabled',
                        'padrao' => true,
                        'grupo' => 'regras',
                        'ajuda' => 'Grava eventos de login, MFA, suspensão e alterações sensíveis.',
                    ],
                    [
                        'chave' => 'auditoria_reter_dias',
                        'rotulo' => 'Retenção de auditoria (dias)',
                        'tipo' => 'int',
                        'config' => 'seguranca.auditoria.reter_dias',
                        'padrao' => 90,
                        'min' => 7,
                        'max' => 3650,
                        'grupo' => 'regras',
                        'ajuda' => 'Por quanto tempo os eventos de auditoria são mantidos.',
                        'placeholder' => '90',
                        'sufixo' => 'dias',
                    ],
                    [
                        'chave' => 'revogar_tokens_ao_suspender',
                        'rotulo' => 'Revogar tokens ao suspender usuário',
                        'tipo' => 'bool',
                        'config' => 'seguranca.governanca.revogar_tokens_ao_suspender',
                        'padrao' => true,
                        'grupo' => 'regras',
                        'ajuda' => 'Invalida tokens Sanctum/API quando a conta é suspensa.',
                    ],
                    [
                        'chave' => 'deslogar_ao_alterar_seguranca',
                        'rotulo' => 'Deslogar ao alterar segurança da conta',
                        'tipo' => 'bool',
                        'config' => 'seguranca.governanca.deslogar_ao_alterar_seguranca',
                        'padrao' => true,
                        'grupo' => 'regras',
                        'ajuda' => 'Encerra outras sessões após troca de senha, MFA ou passkeys.',
                    ],
                    [
                        'chave' => 'exigir_motivo_suspensao',
                        'rotulo' => 'Exigir motivo na suspensão',
                        'tipo' => 'bool',
                        'config' => 'seguranca.governanca.exigir_motivo_suspensao',
                        'padrao' => true,
                        'grupo' => 'regras',
                        'ajuda' => 'Obriga informar justificativa ao suspender um usuário.',
                    ],
                ],
                'impactos_desativar' => [
                    'MFA, passkeys e políticas de senha ficam inativos.',
                    'Rastreamento de sessões e dispositivos para.',
                    'Auditoria e regras de governança de suspensão deixam de aplicar.',
                ],
                'fluxos_comprometidos' => [
                    'Segurança de autenticação',
                    'Governança de contas e tokens',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['seguranca'],
            ],
            'setup' => [
                'guia' => 'Infraestrutura de bootstrap. Não pode ser desligado pela cidade.',
                'diretivas' => [
                    self::ativo(),
                ],
                'impactos_desativar' => [
                    'Comandos e telas de setup municipal deixam de operar.',
                ],
                'fluxos_comprometidos' => [
                    'Bootstrap de ambiente (homologação)',
                ],
                'bloqueavel' => false,
                'prefixos_rota' => [],
            ],
        ];
    }

    /** @return Diretiva */
    private static function ativo(): array
    {
        return [
            'chave' => 'ativo',
            'rotulo' => 'Módulo ativo para a cidade',
            'tipo' => 'bool',
            'config' => null,
            'padrao' => true,
            'ajuda' => 'Desligar oculta menus, bloqueia rotas e registra risco operacional.',
            'grupo' => 'geral',
        ];
    }

    /** @return Diretiva */
    private static function portalExterno(bool $padrao = false, string $ajuda = ''): array
    {
        return [
            'chave' => 'portal_externo',
            'rotulo' => 'Permitir acesso externo (portal público)',
            'tipo' => 'bool',
            'config' => null,
            'padrao' => $padrao,
            'ajuda' => $ajuda !== '' ? $ajuda : 'Quando desligado, páginas públicas do módulo ficam indisponíveis.',
            'grupo' => 'acesso',
        ];
    }

    /** @return Diretiva */
    private static function aparecerNaHome(bool $padrao = false): array
    {
        return [
            'chave' => 'aparecer_na_home',
            'rotulo' => 'Exibir na tela de login / home pública',
            'tipo' => 'bool',
            'config' => null,
            'padrao' => $padrao,
            'ajuda' => 'Mostra o atalho do portal na página inicial para quem não está autenticado.',
            'grupo' => 'acesso',
        ];
    }
}
