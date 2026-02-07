<?php

return [
    'setup' => true,
    'key' => base64_encode('62A2AY-3297ZX-1Z6XX3-ZX4Y60'),
    'name' => 'SysCorp84',
    'version' => "1.0",
    'path' => BASE_PATH,
    'date' => 'D M j Y H:i:s',
    'timestamp' => 'Y-m-d H:i:s',
    'unix_timestamp' => 'D M j H:i:s Y',
    'email' => 'root@hacklab',
    'public' => BASE_PATH . '/public/',
    'views' =>  BASE_PATH . '/resources/views/',
    'database' => BASE_PATH . '/database/',
    'timezone' => 'America/Los_Angeles',
    'memory_limit' => '256M',
    'errors' => [
        'ignore_repeated_errors' => true,
        'display_errors' => true,
        'log_errors' => false,
        'error_log' => BASE_PATH . '/storage/logs/errors.log'
    ],
    'music' => [
        'public/sound/80s_pop.mp3',
        'public/sound/80s_pad.mp3',
        'public/sound/80s_disco.mp3',
        'public/sound/80s_synth.mp3'
    ],
    'db' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'syscorp84',
        'username' => 'root',
        'password' => 'mysql',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => ''
    ],
    'cache' => [
        'enable' => false,
        'ttl' => 30,
        'path'   => BASE_PATH . '/storage/cache', // Make sure this directory exists
    ],
    'whitelist' => [
        '194.45.79.27'
    ]
];