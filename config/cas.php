<?php

// Serveur d'authentification CAS - configuration client via .env.
// CAS_HOST est OBLIGATOIRE si CAS_ENABLED=true : il n'y a pas de serveur CAS
// par defaut (les valeurs de Nantes Universite ne sont pas un defaut generique).
// CAS_PORT / CAS_URL optionnels : defauts generiques 443 et /cas.
// createImmutable() peuple $_ENV mais pas getenv() : lire $_ENV d'abord.
$casResolveEnv = function (string $envName, string $default, ?int $filter = null, ?array $options = null): string {
    $value = trim((string) ($_ENV[$envName] ?? getenv($envName)));
    if ($value === '') {
        return $default;
    }
    if ($filter !== null && filter_var($value, $filter, $options) === false) {
        $problem = 'invalide (' . $value . ')';
        try {
            \App\Utils\Logger::warning(
                "config/cas.php : {$envName} {$problem}, valeur par defaut conservee"
            );
        } catch (\Throwable $e) {
            // Le logger ne doit jamais bloquer l'application
            error_log("config/cas.php : {$envName} {$problem} (log indisponible)");
        }
        return $default;
    }
    return $value;
};

$casEnabled = filter_var(
    trim((string) ($_ENV['CAS_ENABLED'] ?? getenv('CAS_ENABLED'))) ?: false,
    FILTER_VALIDATE_BOOLEAN
);
// NB : FILTER_VALIDATE_DOMAIN est trop permissif (accepte "http://...") :
// regex hostname stricte a la place (labels alphanumeriques, tirets internes).
$casHost = $casResolveEnv(
    'CAS_HOST',
    '',
    FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/']]
);
$casPort = (int) $casResolveEnv('CAS_PORT', '443', FILTER_VALIDATE_INT);
$casUrl = $casResolveEnv('CAS_URL', '/cas');

// CAS active sans serveur configure : avertissement explicite.
// L'authentification echouera (RuntimeException de CasAuthenticator).
if ($casEnabled && $casHost === '') {
    try {
        \App\Utils\Logger::warning(
            "config/cas.php : CAS_ENABLED actif mais CAS_HOST non defini, l'authentification CAS echouera"
        );
    } catch (\Throwable $e) {
        // Le logger ne doit jamais bloquer l'application
        error_log("config/cas.php : CAS_ENABLED actif mais CAS_HOST non defini (log indisponible)");
    }
}

return [
    // Activation pilotée par la variable d'environnement CAS_ENABLED (fichier .env)
    'enabled' => $casEnabled,
    // Serveur CAS : CAS_HOST obligatoire si enabled, CAS_PORT/CAS_URL optionnels
    'host' => $casHost,
    'port' => $casPort,
    'url' => $casUrl,
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
