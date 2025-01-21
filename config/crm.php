<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-tenant Configuration
    |--------------------------------------------------------------------------
    |
    | Configure multi-tenant settings for the CRM
    |
    */
    'multi_tenant' => [
        'enabled' => env('CRM_MULTI_TENANT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Configuration
    |--------------------------------------------------------------------------
    |
    | Configure limits and validation rules for contacts
    |
    */
    'contacts' => [
        'limits' => [
            'phones' => env('CRM_MAX_PHONES', 10),
            'emails' => env('CRM_MAX_EMAILS', 10),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Call Configuration
    |--------------------------------------------------------------------------
    |
    | Configure call-related settings and valid statuses
    |
    */
    'calls' => [
        'statuses' => [
            'initiated',
            'successful',
            'busy',
            'failed'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Phone Number Configuration
    |--------------------------------------------------------------------------
    |
    | Configure supported countries and validation rules
    |
    */
    'phone_numbers' => [
        'supported_countries' => [
            'AU' => [
                'code' => '61',
                'mobile_prefixes' => ['4'],
                'landline_prefixes' => ['2', '3', '7', '8'],
            ],
            'NZ' => [
                'code' => '64',
                'mobile_prefixes' => ['2'],
                'landline_prefixes' => ['3', '4', '6', '7', '9'],
            ],
        ],
    ],
]; 