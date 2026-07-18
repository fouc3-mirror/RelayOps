<?php
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

use think\App;


require __DIR__ . '/../vendor/autoload.php';

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

$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
