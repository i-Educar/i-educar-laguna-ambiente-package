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
    | Skins visuais municipais — tokens + paletas + skin-core no i-Educar / i-Diário.
    | Prioridade: settings (laguna-ambiente.tema.skin) > BURITI_TEMA_SKIN > ativo legado.
    | Ids: nenhum | buriti | ebano | horizonte | orbita | litoral
    */
    'tema' => [
        'ativo' => filter_var(env('BURITI_TEMA_ATIVO', false), FILTER_VALIDATE_BOOLEAN),
        'skin' => env('BURITI_TEMA_SKIN', filter_var(env('BURITI_TEMA_ATIVO', false), FILTER_VALIDATE_BOOLEAN) ? 'buriti' : 'nenhum'),
    ],

];
