<?php
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

if (is_file($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
    return false;
} else {
    $_SERVER["SCRIPT_FILENAME"] = __DIR__ . '/index.php';

    require __DIR__ . "/index.php";
}
