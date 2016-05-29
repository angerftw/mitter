<?php

return [

    'route' => [
        'useMitterRoutes' => true,
        'routeGroupConfig' => [
            'middleware' => 'web',
            'prefix' => 'admin',
            'namespace' => '\Yaim\Mitter',
        ],
    ],

    'models' => [

        'aliases' => [
            "users" => \App\User::class,
        ],

    ],

    'views' => [
        'index' => 'index',
        'form' => 'form',
    ],

    'api' => [
        'useMitterRoutes' => true,
        'usePanelModelsAliases' => true,
        'defaultAction' => 'get',
        'useBasicDataFromModel' => true,
        'routeGroupConfig' => [
            'middleware' => 'web',
            'prefix' => 'api'
        ],
        'aliases' => [
            "user" => \App\User::class,
        ],
    ],
];
