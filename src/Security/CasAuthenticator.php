<?php

namespace App\Security;

use RuntimeException;

/**
 * Authentification CAS pour OTTIS
 *
 * Règle métier:
 * - /, /conference, /conference/{roomId} : protégés par CAS
 * - /auditor, /auditor/{roomId} : publics, sans CAS
 *
 * L'accès interne est autorisé si l'une des affiliations CAS est autorisée.
 * On n'utilise pas uniquement eduPersonPrimaryAffiliation, car le serveur CAS
 * peut renvoyer plusieurs valeurs via eduPersonAffiliation.
 */
class CasAuthenticator
{
    /**
     * Charge la configuration CAS (config/cas.php) avec les valeurs par défaut
     */
    public static function loadConfig(): array
    {
        $configPath = (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/cas.php';
        $casConfig = [];

        if (file_exists($configPath)) {
            $loadedConfig = require $configPath;
            if (is_array($loadedConfig)) {
                $casConfig = $loadedConfig;
            }
        }

        return array_merge([
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
    }

    /**
     * Initialise phpCAS en fonction des routes et de la config serveur
     */
    public static function init(array $casConfig): void
    {
        $path = self::currentPath();

        if (self::isAuditorRoute($path)) {
            return;
        }

        if (!self::requiresCasAuth($path)) {
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
        $serviceBaseUrl = self::currentServiceBaseUrl();

        if (empty($casHost)) {
            throw new RuntimeException('CAS host is not configured. Please edit config/cas.php.');
        }

        \phpCAS::client(
            \CAS_VERSION_3_0,
            $casHost,
            $casPort,
            $casUrl,
            $serviceBaseUrl
        );

        if (!empty($casConfig['cert_path'])) {
            \phpCAS::setCasServerCACert($casConfig['cert_path']);
        } elseif (($casConfig['server_validation'] ?? false) === false) {
            \phpCAS::setNoCasServerValidation();
        }

        \phpCAS::handleLogoutRequests(false);

        try {
            if (!\phpCAS::isAuthenticated()) {
                // Mémoriser l'URL complète (query incluse, sans ticket) pour y revenir après reconnexion
                if (!isset($_GET['ticket'])) {
                    $_SESSION['ottis_return_to'] = $_SERVER['REQUEST_URI'] ?? '/';
                }
                \phpCAS::forceAuthentication();
            }

            $_SESSION['cas_user'] = \phpCAS::getUser();
            $_SESSION['cas_attributes'] = \phpCAS::getAttributes();
            $_SESSION['auth_provider'] = 'cas';
        } catch (\Throwable $e) {
            error_log('phpCAS authentication failed: ' . $e->getMessage());

            // Ticket invalide ou déjà consommé : réessayer une fois sur la même URL sans le ticket
            if (isset($_GET['ticket'])) {
                $query = $_GET;
                unset($query['ticket']);
                $clean = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?') ?: '/';
                if (!empty($query)) {
                    $clean .= '?' . http_build_query($query);
                }
                header('Location: ' . $clean, true, 302);
                exit;
            }

            // Sinon, repartir de l'accueil (relance propre du flux CAS), sauf si on y est déjà (évite une boucle)
            if (self::currentPath() !== '/') {
                header('Location: /', true, 302);
                exit;
            }

            http_response_code(500);
            echo '<h1>Erreur d\'authentification CAS</h1><p>Le service de connexion est indisponible. Merci de réessayer plus tard.</p>';
            exit;
        }

        // Revenir à l'URL d'origine si CAS y est revenu sans la query (ROOMID, lang...)
        $returnTo = $_SESSION['ottis_return_to'] ?? null;
        unset($_SESSION['ottis_return_to']);
        if ($returnTo && $returnTo !== ($_SERVER['REQUEST_URI'] ?? '')) {
            header('Location: ' . $returnTo, true, 302);
            exit;
        }

        if (!self::casUserHasInternalAccess()) {
            http_response_code(403);
            echo '<h1>Accès interdit</h1><p>Cette zone est réservée au personnel et aux enseignants.</p>';
            exit;
        }

        // Reconnexion CAS (retour avec ticket) : si l'URL d'origine a été perdue
        // (session détruite, ottis_return_to absent) et que l'intervenant possède
        // une salle encore récente, y revenir directement pour ne pas perdre la
        // conférence et les auditeurs qui y sont liés.
        if (isset($_GET['ticket'])
            && class_exists(RoomTokenManager::class)
            && (self::currentPath() === '/'
                || (self::currentPath() === '/conference' && empty($_GET['ROOMID'])))) {
            $recentRoom = RoomTokenManager::findRecentRoomByOwner(
                (string) ($_SESSION['cas_user'] ?? '')
            );
            if ($recentRoom !== null) {
                header('Location: /conference/' . rawurlencode($recentRoom), true, 302);
                exit;
            }
        }
    }

    /**
     * Retourne le chemin courant sans query string
     */
    private static function currentPath(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        return parse_url($uri, PHP_URL_PATH) ?: '/';
    }

    /**
     * Routes protégées par CAS
     */
    private static function requiresCasAuth(string $path): bool
    {
        return $path === '/' ||
               $path === '/conference' ||
               str_starts_with($path, '/conference/');
    }

    /**
     * Routes publiques accessibles sans CAS
     */
    private static function isAuditorRoute(string $path): bool
    {
        return $path === '/auditor' ||
               str_starts_with($path, '/auditor/');
    }

    /**
     * Vérifie si le compte CAS est bien autorisé à accéder aux zones internes
     */
    private static function casUserHasInternalAccess(): bool
    {
        $rawAttributes = $_SESSION['cas_attributes'] ?? [];
        $attributes = is_array($rawAttributes) ? $rawAttributes : [];
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
     * URL de service courante (base pour phpCAS)
     */
    private static function currentServiceBaseUrl(): string
    {
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        if (!empty($forwardedProto)) {
            $scheme = strtolower(trim(explode(',', (string) $forwardedProto)[0]));
        } else {
            $https = (string) ($_SERVER['HTTPS'] ?? '');
            $serverPort = (int) ($_SERVER['SERVER_PORT'] ?? 80);
            $scheme = ($https !== '' && $https !== 'off') || $serverPort === 443 ? 'https' : 'http';
        }

        $host = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if ($path === '/') {
            return $scheme . '://' . $host . '/';
        }

        return $scheme . '://' . $host . $path;
    }
}
