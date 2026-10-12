<?php

return [
    // Activation pilotée par la variable d'environnement CAS_ENABLED (fichier .env)
    'enabled' => filter_var(getenv('CAS_ENABLED') ?: false, FILTER_VALIDATE_BOOLEAN),
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
