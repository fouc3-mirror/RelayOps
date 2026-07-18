<?php

use think\facade\Db;

return [
    'host'     => function () {
        $row = Db::name('setting')->where('name', 'redis_host')->find();
        return $row['value'] ?? env('REDIS_HOST', '127.0.0.1');
    },
    'port'     => function () {
        $row = Db::name('setting')->where('name', 'redis_port')->find();
        return (int) ($row['value'] ?? env('REDIS_PORT', 6379));
    },
    'password' => function () {
        $row = Db::name('setting')->where('name', 'redis_password')->find();
        return $row['value'] ?? env('REDIS_PASSWORD', '');
    },
    'select'   => function () {
        $row = Db::name('setting')->where('name', 'redis_select')->find();
        return (int) ($row['value'] ?? env('REDIS_SELECT', 0));
    },
    'prefix'   => function () {
        $row = Db::name('setting')->where('name', 'redis_prefix')->find();
        return $row['value'] ?? env('REDIS_PREFIX', 'frp:');
    },
    'timeout'  => 3,
];
