<?php

return [
    'name'           => 'PHPSESSID',
    'var_session_id' => '',
    'type'           => 'file',
    'store'          => null,
    'expire'         => 86400,
    'prefix'         => '',
];

ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
