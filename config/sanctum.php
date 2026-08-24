<?php

use Laravel\Sanctum\Sanctum;

return [
    'stateful' => [],

    'guard' => ['web'],

    // expiração em minutos (exemplo: 30 dias = 43200 minutos)
    'expiration' => 43200,

    // prefixo para identificação de tokens da aplicação
    'token_prefix' => 'rnk_',

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];