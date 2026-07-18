<?php


return [
    'default' => 'file',

    'stores'  => [
        'file' => [
            'type'       => 'File',
            'path'       => '',
            'prefix'     => '',
            'expire'     => 0,
            'tag_prefix' => 'tag:',
            'serialize'  => [],
        ],
        'redis' => [
            'type'       => 'Redis',
            'host'       => env('REDIS_HOST', '127.0.0.1'),
            'port'       => (int) env('REDIS_PORT', 6379),
            'password'   => env('REDIS_PASSWORD', ''),
            'select'     => (int) env('REDIS_SELECT', 0),
            'prefix'     => env('REDIS_PREFIX', 'frp:'),
            'expire'     => 0,
            'timeout'    => 0,
            'serialize'  => [],
            'tag_prefix' => 'tag:',
        ],
    ],
];
