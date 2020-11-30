<?php

$config = [
    'id' => 'rest-fee',
    // basePath (базовый путь) приложения будет каталог `rest-fee`
    'basePath' => __DIR__,
    // это пространство имен где приложение будет искать все контроллеры
    'controllerNamespace' => 'restFee\controllers',
    'aliases' => [
        '@restFee' => __DIR__,
    ],
    'defaultRoute' => 'fee/index',
    'bootstrap' => ['log','config'],
    'components' => [
        'user' => [
            'identityClass' => 'restFee\models\User',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '832768d91e74c39c6a8218eb3de250ba622b11e39078c730ac6e4a49eb8431ae',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\DbCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=restFee',
            'username' => 'pavand239',
            'password' => 'P@ssw0rd',
            'charset' => 'utf8',
        ],
        'config' => [
            'class' => 'restFee\components\Config',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'fee/index',
                'recommended' => 'fee/recommended',
                'load' => 'fee/load',
                'mempool-weight' => 'fee/mempool-weight',
                'blocks-min-fee' => 'fee/blocks-min-fee',
            ],
        ]
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;