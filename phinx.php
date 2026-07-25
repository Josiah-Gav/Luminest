<?php

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'luminest',
            'user' => 'root',
            'pass' => '',
            'port' => '3309',
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'luminest',
            'user' => 'root',
            'pass' => '',
            'port' => '3309',
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'luminest',
            'user' => 'root',
            'pass' => '',
            'port' => '3309',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
