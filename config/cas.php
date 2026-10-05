<?php

return [
    'enabled' => true,
    'host' => 'login.univ-nantes.fr',
    'port' => 443,
    'url' => '/cas26m/',
    'server_validation' => false,
    'cert_path' => null,
    'dev_mode' => false,
    'protected_routes' => [
        '/',
        '/conference',
        '/conference/',
    ],
    'public_routes' => [
        '/auditor',
        '/auditor/',
    ],
];
