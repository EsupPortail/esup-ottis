<?php

namespace App\Core;

use App\Error\ErrorHandler;
use App\Security\CasAuthenticator;
use App\Utils\Csrf;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

/**
 * Bootstrap de l'application OTTIS
 *
 * Séquence : gestionnaire d'erreurs, environnement (.env), session (8h,
 * une conférence ne doit pas perdre sa session), CAS, protection CSRF.
 */
class Bootstrap
{
    public static function run(): void
    {
        ErrorHandler::init();

        // Charger les variables d'environnement avec Dotenv
        try {
            if (file_exists(APP_ROOT . '/.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(APP_ROOT);
                $dotenv->load();
            }
        } catch (\Exception $e) {
            error_log('Dotenv load failed: ' . $e->getMessage());
        }

        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 28800,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            ini_set('session.gc_maxlifetime', 28800);
            session_start();
        }

        CasAuthenticator::init(CasAuthenticator::loadConfig());

        // Initialiser la protection CSRF
        Csrf::initProtection();
    }
}
