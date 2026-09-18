<?php

/**
 * Initialisation des fonctionnalités de sécurité
 *
 * Ce fichier doit être inclus au début de chaque script PHP
 * pour activer la protection CSRF et les fonctions de sanitization.
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
    // Ne pas logger ici car Monolog peut ne pas être initialisé
    error_log('Dotenv load failed: ' . $e->getMessage());
}

// Inclure les fichiers de sécurité
require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/sanitize.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialiser la protection CSRF
\App\Utils\Csrf::initProtection();
