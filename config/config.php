<?php

use GuzzleHttp\RequestOptions;

return [
    'default' => 'default',

    'clients' => [
        'default' => [
            'SignType' => env('YNJSPX_SIGN_TYPE', 'RSA2'),
            'AppId' => env('YNJSPX_APP_ID'),
            'PrivateKey' => env('YNJSPX_PRIVATE_KEY'),

            'Http' => [
                'base_uri' => env('YNJSPX_HTTP_BASE_URI'),
                RequestOptions::TIMEOUT => 10.0,
                RequestOptions::CONNECT_TIMEOUT => 2.0,
                RequestOptions::READ_TIMEOUT => 20.0,
                RequestOptions::HEADERS => ['User-Agent' => null],
            ],
        ],
    ],
];
