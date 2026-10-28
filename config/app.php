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

// Resolution d'une frequence de traduction intermediaire : 'none'
// (insensible a la casse) ou entier >= 0 ; absente ou invalide -> '5'.
$ottisResolvePartialFreq = function (string $envName): string {
    $value = trim((string) ($_ENV[$envName] ?? getenv($envName)));
    if ($value === '') {
        return '5';
    }
    if (strtolower($value) === 'none') {
        return 'none';
    }
    $int = filter_var($value, FILTER_VALIDATE_INT);
    if ($int !== false && $int >= 0) {
        return (string) $int;
    }
    try {
        \App\Utils\Logger::warning(
            "config/app.php : {$envName} invalide ({$value}), valeur par defaut 5 conservee"
        );
    } catch (\Throwable $e) {
        // Le logger ne doit jamais bloquer l'application
        error_log("config/app.php : {$envName} invalide ({$value}) (log indisponible)");
    }
    return '5';
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

    // Frequence des traductions intermediaires, cote conference (intervenant) :
    // 'none' = aucune traduction intermediaire (aucun texte non traduit affiche,
    // seul le texte final est traduit), 0 = chaque partiel traduit,
    // N = 1 appel DeepL tous les N partiels. Absente : 5.
    'conference_translation_partial_freq' => $ottisResolvePartialFreq('CONFERENCE_TRANSLATION_PARTIAL_FREQ'),

    // Idem cote auditor (l'auditeur ne voit jamais de texte non traduit
    // en mode 'none'). Absente : 5.
    'auditor_translation_partial_freq' => $ottisResolvePartialFreq('AUDITOR_TRANSLATION_PARTIAL_FREQ'),
];
