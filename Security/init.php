<?php

/**
 * Initialisation des fonctionnalités de sécurité
 *
 * Ce fichier doit être inclus au début de chaque script PHP
 * pour activer la protection CSRF et les fonctions de sanitization.
 * 
 * Initialisation de la sécurité et du CAS
 *
 * Règle métier:
 * - /, /conference, /conference/{roomId} : protégés par CAS
 * - /auditor, /auditor/{roomId} : publics, sans CAS
 */

// Charger l'autoloading de Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Charger le logger dès que possible (il charge autoload.php lui-même si nécessaire)
require_once __DIR__ . '/logger.php';

// Charger le gestionnaire d'erreurs centralisé
require_once __DIR__ . '/error_handler.php';

// Charger les variables d'environnement avec Dotenv
try {
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }
} catch (Exception $e) {
    error_log('Dotenv load failed: ' . $e->getMessage());
}

// Inclure les fichiers de sécurité
require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/sanitize.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    // Session longue duree (8h) : une conference ne doit pas perdre sa session
    session_set_cookie_params([
        'lifetime' => 28800,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.gc_maxlifetime', 28800);
    session_start();
}

// ==============================
// Configuration CAS
// ==============================

$casConfigPath = __DIR__ . '/../config/cas.php';
$casConfig = [];

if (file_exists($casConfigPath)) {
    $loadedConfig = require $casConfigPath;
    if (is_array($loadedConfig)) {
        $casConfig = $loadedConfig;
    }
}

$casConfig = array_merge([
    'enabled' => false,
    'host' => null,
    'port' => 443,
    'context' => '/cas',
    'server_validation' => false,
    'cert_path' => null,
    'dev_mode' => false,
    'protected_routes' => ['/', '/conference'],
    'public_routes' => ['/auditor'],
], $casConfig);

/**
 * Retourne le chemin courant sans query string.
 */
function ottisCurrentPath(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return parse_url($uri, PHP_URL_PATH) ?: '/';
}

/**
 * Routes protégées par CAS.
 */
function ottisRequiresCasAuth(string $path): bool
{
    return $path === '/' ||
           $path === '/conference' ||
           str_starts_with($path, '/conference/');
}

/**
 * Routes publiques accessibles sans CAS.
 */
function ottisIsAuditorRoute(string $path): bool
{
    return $path === '/auditor' ||
           str_starts_with($path, '/auditor/');
}

/**
 * Vérifie si le compte CAS est bien autorisé à accéder aux zones internes.
 *
 * L’accès interne est autorisé si l’une des affiliations CAS est autorisée.
 * On n’utilise pas uniquement eduPersonPrimaryAffiliation, car le serveur CAS
 * peut renvoyer plusieurs valeurs via eduPersonAffiliation.
 */
function ottisCasUserHasInternalAccess(): bool
{
    $attributes = $_SESSION['cas_attributes'] ?? [];
    $affiliations = $attributes['eduPersonAffiliation'] ?? [];

    if (!is_array($affiliations)) {
        $affiliations = [$affiliations];
    }

    $allowedAffiliations = ['faculty', 'staff', 'employee', 'teacher'];

    foreach ($affiliations as $value) {
        if ($value === null || $value === '') {
            continue;
        }

        $normalizedAffiliation = strtolower(trim((string) $value));

        if (in_array($normalizedAffiliation, $allowedAffiliations, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Initialise phpCAS en fonction des routes et de la config serveur.
 */
function ottisCurrentServiceBaseUrl(): string
{
    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
    if (!empty($forwardedProto)) {
        $scheme = strtolower(trim(explode(',', (string) $forwardedProto)[0]));
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443
            ? 'https'
            : 'http';
    }

    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';

    if ($path === '/') {
        return $scheme . '://' . $host . '/';
    }

    return $scheme . '://' . $host . $path;
}

function ottisInitCas(array $casConfig): void
{
    $path = ottisCurrentPath();

    if (ottisIsAuditorRoute($path)) {
        return;
    }

    if (!ottisRequiresCasAuth($path)) {
        return;
    }

    $appEnv = getenv('APP_ENV') ?: 'production';
    $casEnabled = (bool) ($casConfig['enabled'] ?? false);

    if (!$casEnabled || $appEnv === 'local' || $appEnv === 'dev' || ($casConfig['dev_mode'] ?? false)) {
        $_SESSION['auth_provider'] = 'dev';
        $_SESSION['cas_user'] = $_SESSION['cas_user'] ?? 'ottis-dev-user';
        $_SESSION['cas_attributes'] = $_SESSION['cas_attributes'] ?? ['role' => 'staff'];
        return;
    }

    $casHost = $casConfig['host'] ?? null;
    $casPort = (int) ($casConfig['port'] ?? 443);
    $casUrl = $casConfig['url'] ?? ($casConfig['context'] ?? '/cas');
    $serviceBaseUrl = ottisCurrentServiceBaseUrl();

    if (empty($casHost)) {
        throw new RuntimeException('CAS host is not configured. Please edit config/cas.php.');
    }

    phpCAS::client(
        CAS_VERSION_3_0,
        $casHost,
        $casPort,
        $casUrl,
        $serviceBaseUrl
    );

    if (!empty($casConfig['cert_path'])) {
        phpCAS::setCasServerCACert($casConfig['cert_path']);
    } elseif (($casConfig['server_validation'] ?? false) === false) {
        phpCAS::setNoCasServerValidation();
    }

    phpCAS::handleLogoutRequests(false);

    try {
        if (!phpCAS::isAuthenticated()) {
            // Memoriser l'URL complete (query incluse, sans ticket) pour y revenir apres reconnexion
            if (!isset($_GET['ticket'])) {
                $_SESSION['ottis_return_to'] = $_SERVER['REQUEST_URI'] ?? '/';
            }
            phpCAS::forceAuthentication();
        }

        $_SESSION['cas_user'] = phpCAS::getUser();
        $_SESSION['cas_attributes'] = phpCAS::getAttributes();
        $_SESSION['auth_provider'] = 'cas';
    } catch (Throwable $e) {
        error_log('phpCAS authentication failed: ' . $e->getMessage());

        // Ticket invalide ou deja consomme : reessayer une fois sur la meme URL sans le ticket
        if (isset($_GET['ticket'])) {
            $query = $_GET;
            unset($query['ticket']);
            $clean = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
            if (!empty($query)) {
                $clean .= '?' . http_build_query($query);
            }
            header('Location: ' . $clean, true, 302);
            exit;
        }

        // Sinon, repartir de l'accueil (relance propre du flux CAS), sauf si on y est deja (evite une boucle)
        if (ottisCurrentPath() !== '/') {
            header('Location: /', true, 302);
            exit;
        }

        http_response_code(500);
        echo '<h1>Erreur d’authentification CAS</h1><p>Le service de connexion est indisponible. Merci de réessayer plus tard.</p>';
        exit;
    }

    // Revenir a l'URL d'origine si CAS y est revenu sans la query (ROOMID, lang...)
    $returnTo = $_SESSION['ottis_return_to'] ?? null;
    unset($_SESSION['ottis_return_to']);
    if ($returnTo && $returnTo !== ($_SERVER['REQUEST_URI'] ?? '')) {
        header('Location: ' . $returnTo, true, 302);
        exit;
    }

    if (!ottisCasUserHasInternalAccess()) {
        http_response_code(403);
        echo '<h1>Accès interdit</h1><p>Cette zone est réservée au personnel et aux enseignants.</p>';
        exit;
    }

    // Reconnexion CAS (retour avec ticket) : si l'URL d'origine a ete perdue
    // (session detruite, ottis_return_to absent) et que l'intervenant possede
    // une salle encore recente, y revenir directement pour ne pas perdre la
    // conference et les auditeurs qui y sont lies.
    if (isset($_GET['ticket'])
        && class_exists(\App\Security\RoomTokenManager::class)
        && (ottisCurrentPath() === '/'
            || (ottisCurrentPath() === '/conference' && empty($_GET['ROOMID'])))) {
        $recentRoom = \App\Security\RoomTokenManager::findRecentRoomByOwner(
            (string) ($_SESSION['cas_user'] ?? '')
        );
        if ($recentRoom !== null) {
            header('Location: /conference/' . rawurlencode($recentRoom), true, 302);
            exit;
        }
    }
}

ottisInitCas($casConfig);

// Initialiser la protection CSRF
\App\Utils\Csrf::initProtection();
