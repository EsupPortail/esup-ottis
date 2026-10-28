<?php

// Surcharge client via .env : CAS_HOST, CAS_PORT, CAS_URL.
// Absents, vides ou invalides -> valeurs par defaut ci-dessous (Nantes Universite).
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

return [
    // Activation pilotée par la variable d'environnement CAS_ENABLED (fichier .env)
    // createImmutable() peuple $_ENV mais pas getenv() : lire $_ENV d'abord
    'enabled' => filter_var(
        trim((string) ($_ENV['CAS_ENABLED'] ?? getenv('CAS_ENABLED'))) ?: false,
        FILTER_VALIDATE_BOOLEAN
    ),
    // Serveur d'authentification : surchargeable via CAS_HOST / CAS_PORT / CAS_URL
    // NB : FILTER_VALIDATE_DOMAIN est trop permissif (accepte "http://...") :
    // regex hostname stricte a la place (labels alphanumeriques, tirets internes).
    'host' => $casResolveEnv(
        'CAS_HOST',
        'login.univ-nantes.fr',
        FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/']]
    ),
    'port' => (int) $casResolveEnv('CAS_PORT', '443', FILTER_VALIDATE_INT),
    'url' => $casResolveEnv('CAS_URL', '/cas26m/'),
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
