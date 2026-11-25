<?php

return [

    /*
    | Pacote administrativo — não deve aparecer na home para público externo.
    */
    'public' => [
        'aparecer_na_home' => (bool) env('LAGUNA_AMBIENTE_APARECER_NA_HOME', false),
        'titulo' => 'Ambiente municipal',
        'ordem' => 90,
    ],

    /*
    | Tema visual Buriti (onda 1) — tokens + skin-core no core i-Educar.
    | Prioridade: settings (laguna-ambiente.tema.ativo) > BURITI_TEMA_ATIVO.
    */
    'tema' => [
        'ativo' => filter_var(env('BURITI_TEMA_ATIVO', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
