<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\App;

// [ 应用入口文件 ]

require __DIR__ . '/../vendor/autoload.php';

// 兼容宝塔面板 nginx ?s= 格式的 rewrite 规则
// 宝塔默认 rewrite: rewrite ^(.*)$ /index.php?s=$1 last;
// ThinkPHP 8 使用 PATH_INFO，需要将 ?s= 转换为 PATH_INFO
if (!empty($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $qs);
    if (isset($qs['s'])) {
        $pathInfo = '/' . ltrim($qs['s'], '/');
        $_SERVER['PATH_INFO'] = $pathInfo;
        $_SERVER['REQUEST_URI'] = $pathInfo;
        unset($qs['s']);
        $_SERVER['QUERY_STRING'] = http_build_query($qs);
    }
}

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
