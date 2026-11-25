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

        return $mapa[$id] ?? [
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
        ];
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
                'diretivas' => [
                    self::ativo(),
                ],
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
                'diretivas' => [
                    self::ativo(),
                ],
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
                'diretivas' => [
                    self::ativo(),
                ],
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
                'diretivas' => [
                    self::ativo(),
                    [
                        'chave' => 'limite_ausencias_alerta',
                        'rotulo' => 'Limite de ausências para alerta',
                        'tipo' => 'int',
                        'config' => 'atendimento.limite_ausencias_alerta',
                        'padrao' => 3,
                        'min' => 1,
                        'max' => 30,
                        'ajuda' => 'Usado pelo serviço de atendimento e pela ponte com Busca Ativa.',
                    ],
                ],
                'impactos_desativar' => [
                    'Fila, agenda e registro de acolhimento ficam inacessíveis.',
                    'Alertas da Busca Ativa deixam de alimentar a fila do atendimento.',
                ],
                'fluxos_comprometidos' => [
                    'Busca Ativa → Atendimento ao estudante',
                    'Equipe multiprofissional na escola',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['atendimento-estudante'],
            ],
            'busca-ativa' => [
                'diretivas' => [
                    self::ativo(),
                    [
                        'chave' => 'faltas_consecutivas_alerta',
                        'rotulo' => 'Faltas consecutivas para alerta',
                        'tipo' => 'int',
                        'config' => 'busca-ativa.limites.faltas_consecutivas_alerta',
                        'padrao' => 3,
                        'min' => 1,
                        'max' => 30,
                    ],
                    [
                        'chave' => 'faltas_mensais_alerta',
                        'rotulo' => 'Faltas no período (mês)',
                        'tipo' => 'int',
                        'config' => 'busca-ativa.limites.faltas_mensais_alerta',
                        'padrao' => 5,
                        'min' => 1,
                        'max' => 60,
                    ],
                    [
                        'chave' => 'frequencia_minima_percentual',
                        'rotulo' => 'Frequência mínima (%)',
                        'tipo' => 'float',
                        'config' => 'busca-ativa.limites.frequencia_minima_percentual',
                        'padrao' => 75,
                        'min' => 0,
                        'max' => 100,
                    ],
                    [
                        'chave' => 'somente_cursos_marcados',
                        'rotulo' => 'Somente cursos com flag Busca Ativa',
                        'tipo' => 'bool',
                        'config' => 'busca-ativa.diretivas.somente_cursos_marcados',
                        'padrao' => true,
                        'ajuda' => 'Espelhado nas regras do módulo Busca Ativa.',
                    ],
                    [
                        'chave' => 'gerar_alerta_atendimento',
                        'rotulo' => 'Gerar alerta no Atendimento ao estudante',
                        'tipo' => 'bool',
                        'config' => 'busca-ativa.atendimento.gerar_alerta',
                        'padrao' => true,
                    ],
                ],
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
                'diretivas' => [
                    self::ativo(),
                ],
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
                'diretivas' => [
                    self::ativo(),
                ],
                'impactos_desativar' => [
                    'Empréstimos e acervo do módulo ficam inacessíveis.',
                ],
                'fluxos_comprometidos' => [],
                'bloqueavel' => true,
                'prefixos_rota' => ['biblioteca-escolar'],
            ],
            'horario' => [
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
                'diretivas' => [
                    self::ativo(),
                ],
                'impactos_desativar' => [
                    'Inscrições e frequência em formação continuada ficam offline.',
                ],
                'fluxos_comprometidos' => [
                    'Certificação de formação',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => ['eventos-formacao'],
            ],
            'act' => [
                'diretivas' => [
                    self::ativo(),
                ],
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
            'apps-api' => [
                'diretivas' => [
                    self::ativo(),
                ],
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
                'diretivas' => [
                    self::ativo(),
                ],
                'impactos_desativar' => [
                    'Extensões do domínio servidor educacional deixam de carregar.',
                ],
                'fluxos_comprometidos' => [
                    'Fluxos que dependem do pacote servidor educacional',
                ],
                'bloqueavel' => true,
                'prefixos_rota' => [],
            ],
            'censo' => [
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
            'setup' => [
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
        ];
    }
}
