<?php

return [
    'multi_tenant' => [
        'enabled' => env('CRM_MULTI_TENANT', true),
    ],
    'contacts' => [
        'limits' => [
            'phones' => env('CRM_MAX_PHONES', 10),
            'emails' => env('CRM_MAX_EMAILS', 10),
        ],
    ],
    'database' => [
        'connection' => env('DB_CONNECTION', 'pgsql'),
    ],
    'jwt' => [
        'secret' => env('JWT_SECRET'),
        'ttl' => env('JWT_TTL', 60), // minutes
    ]
]; 