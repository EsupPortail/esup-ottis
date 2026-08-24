<?php
/**
 * Initialisation des fonctionnalités de sécurité
 * 
 * Ce fichier doit être inclus au début de chaque script PHP
 * pour activer la protection CSRF et les fonctions de sanitization.
 */

// Charger les variables d'environnement avec Dotenv
try {
    if (file_exists(__DIR__ . '/../.env')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::create(__DIR__ . '/..');
        $dotenv->load();
    }
} catch (Exception $e) {
    error_log('Dotenv load failed: ' . $e->getMessage());
}

// Inclure les fichiers de sécurité
require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/sanitize.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialiser la protection CSRF
initCsrfProtection();
