<?php

return [
    'enabled' => env('REQUEST_LOGGER_ENABLE'),
    'exclude' => [
        'methods' => ['GET', 'HEAD', 'OPTIONS'],
        'paths'   => [

        ],
    ],

    'should_queue'     => false,
    'queue_name'       => null,
    'queue_connection' => null,

    'log_type'      => env('REQUEST_LOGGER_TYPE', 'daily'),
    'log_level'     => 'debug',
    'log_max_files' => 5,
    'log_file'      => storage_path('logs/http.log'),
    'log_format'    => \Bubasuma\RequestLogger\MessageFormatter::DEBUG,
];
