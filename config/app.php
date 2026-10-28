<?php

/**
 * Configuration générale de l'application OTTIS.
 * Surchargeable par serveur/client via le fichier .env
 * (voir .env.example : CONTACT_EMAIL, HELP_URL, MATOMO_URL, MATOMO_SITE_ID).
 *
 * Valeur absente, vide ou invalide : avertissement non bloquant (log)
 * et degradation documentee par cle (voir chaque entree ci-dessous).
 * L'application continue de fonctionner.
 */

$ottisResolveEnv = function (
    string $envName,
    ?int $filter,
    string $absence = 'element masque dans la navigation'
): string {
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
            "config/app.php : {$envName} {$problem}, {$absence}"
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

    // Matomo : URL du tracker et identifiant du site du client.
    // Absents, vides ou invalides : Matomo desactive (aucun script emis,
    // aucune collecte, domaine absent de la CSP).
    'matomo_url' => $ottisResolveEnv('MATOMO_URL', FILTER_VALIDATE_URL, 'Matomo desactive'),
    'matomo_site_id' => $ottisResolveEnv('MATOMO_SITE_ID', FILTER_VALIDATE_INT, 'Matomo desactive'),
];
