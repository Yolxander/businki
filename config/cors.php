<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'register',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*',],

    'allowed_origins_patterns' => [
        '^https:\/\/[a-z0-9\-]+\.lite\.vusercontent\.net$',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
