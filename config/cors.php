<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Permite qualquer origem (ideal para lidar com IPs dinâmicos do WSL)
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Cart-Session-Id', 'Location'],

    'max_age' => 0,

    // IMPORTANTE: Deve ser false quando allowed_origins for ['*']
    'supports_credentials' => false, 
];