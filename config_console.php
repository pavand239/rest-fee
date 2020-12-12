<?php


$config = [
    'id' => 'restFee-console',
    // basePath (базовый путь) приложения будет каталог `rest-fee`
    'basePath' => __DIR__,
    // это пространство имен где приложение будет искать все контроллеры
    'controllerNamespace' => 'restFee\console\controllers',
    'aliases' => [
        '@restFee' => __DIR__,
    ],
    'bootstrap' => ['log','config'],
    'components' => [
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
            'dsn' => 'mysql:host=db;dbname=restFee',
            'username' => 'restFee',
            'password' => 'P@ssw0rd',
            'charset' => 'utf8',
        ],
        'config' => [
            'class' => 'restFee\components\Config',
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