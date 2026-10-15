<?php

/**
 * Configuration générale de l'application OTTIS.
 * Surchargeable par serveur/client via le fichier .env
 * (voir .env.example : CONTACT_EMAIL, HELP_URL).
 *
 * Valeur absente, vide ou invalide : avertissement non bloquant (log)
 * et element masque dans la navigation.
 * L'application continue de fonctionner.
 */

$ottisResolveEnv = function (string $envName, ?int $filter): string {
    // createImmutable() peuple $_ENV mais pas getenv() : lire $_ENV d'abord,
    // getenv() ne couvre que les variables posees dans l'environnement reel
    $value = trim((string) ($_ENV[$envName] ?? getenv($envName)));

    if ($value === '') {
        $problem = 'non definie ou vide';
    } elseif ($filter !== null && filter_var($value, $filter) === false) {
        $problem = 'invalide (' . $value . ')';
    } else {
        return $value;
    }

    try {
        \App\Utils\Logger::warning(
            "config/app.php : {$envName} {$problem}, element masque dans la navigation"
        );
    } catch (\Throwable $e) {
        // Le logger ne doit jamais bloquer l'application
        error_log("config/app.php : {$envName} {$problem} (log indisponible)");
    }

    return '';
};

return [
    // Adresse de contact affichée dans la navigation (lien mailto)
    'contact_email' => $ottisResolveEnv('CONTACT_EMAIL', FILTER_VALIDATE_EMAIL),

    // Lien externe "Aide" de la navigation
    'help_url' => $ottisResolveEnv('HELP_URL', FILTER_VALIDATE_URL),
];
