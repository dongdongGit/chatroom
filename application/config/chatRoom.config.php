<?php

return [
    'db' => [//数据库信息组
        'HOST'    => '127.0.0.1',
        'USER'    => 'root',
        'PWD'     => '',
        'NAME'    => 'userdb',
        'TYPE'    => 'mysql',
        'PORT'    => '3306',
        'CHARSET' => 'utf8'
    ],
    'app' => [//应用程序组
        'DEFAULT_PLATFORM' => 'chatRoom',
    ],
    'chatRoom' => [//前台组
        'DEFAULT_CONTROLLER' => 'View',
        'DEFAULT_ACTION'     => 'sLogin',
    ],
    'front' => [],
    'back'  => [],
    'info'  => [
        'DEFAULT_KEY'      => 'test',
        'DEFAULT_TIMEZONE' => 'Asia/shanghai',
    ],
    'captcha' => [
        'fontfile'       => TOOL_PATH . 'captcha/fonts/MyriadPro-Bold.otf',
        'pixel'          => 50,
        'line'           => 3,
        'length'         => 4,
        'distortionFlag' => true,
    ],
];
