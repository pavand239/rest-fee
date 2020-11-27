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
            'class' => 'yii\caching\FileCache',
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