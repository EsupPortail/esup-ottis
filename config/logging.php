<?php

/**
 * Configuration de Monolog pour le logging
 *
 * Ce fichier configure le système de logging pour l'application OMIST.
 * Il gère différents environnements (development, staging, production) et
 * différents niveaux de log (DEBUG, INFO, WARNING, ERROR, CRITICAL).
 */

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

// Récupérer l'environnement de l'application
$appEnv = $_ENV['APP_ENV'] ?? (getenv('APP_ENV') ?: 'development');

// Format des logs : "2026-08-27 14:30:45.123456 [WARNING] security: Invalid room ID"
$dateFormat = 'Y-m-d H:i:s.u';
$output = "%datetime% [%level_name%] %channel%.%context%: %message%\n";
$formatter = new LineFormatter($output, $dateFormat);

// Créer le logger principal
$logger = new Logger('OMIST');

// ============================================================================
// Configuration commune à tous les environnements
// ============================================================================

// Handler pour les erreurs PHP fatales (toujours actif)
$errorLogHandler = new ErrorLogHandler(
    ErrorLogHandler::OPERATING_SYSTEM,
    Level::Critical
);
$logger->pushHandler($errorLogHandler);

// ============================================================================
// Configuration par environnement
// ============================================================================

switch ($appEnv) {
    case 'production':
        // ==================== PRODUCTION ====================
        // En production :
        // - Seuil de log : WARNING (ne log que les warnings et au-dessus)
        // - Fichier unique pour les logs principaux
        // - Fichier séparé pour les erreurs

        // Fichier principal
        $prodHandler = new StreamHandler(
            __DIR__ . '/../logs/app.log',
            Level::Warning
        );
        $prodHandler->setFormatter($formatter);
        $logger->pushHandler($prodHandler);

        // Fichier d'erreurs
        $errorHandler = new StreamHandler(
            __DIR__ . '/../logs/errors.log',
            Level::Error
        );
        $errorHandler->setFormatter($formatter);
        $logger->pushHandler($errorHandler);

        break;

    case 'staging':
        // ==================== STAGING ====================
        // En staging :
        // - Seuil de log : INFO

        $stagingHandler = new StreamHandler(
            __DIR__ . '/../logs/staging.log',
            Level::Info
        );
        $stagingHandler->setFormatter($formatter);
        $logger->pushHandler($stagingHandler);

        // Handler séparé pour les erreurs
        $stagingErrorHandler = new StreamHandler(
            __DIR__ . '/../logs/staging-errors.log',
            Level::Error
        );
        $stagingErrorHandler->setFormatter($formatter);
        $logger->pushHandler($stagingErrorHandler);

        break;

    case 'development':
    default:
        // ==================== DEVELOPMENT ====================
        // En développement :
        // - Seuil de log : DEBUG (tout logger)
        // - Affichage dans la console

        // Console (pour le développement local)
        if (php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg') {
            $consoleHandler = new StreamHandler(
                'php://stdout',
                Level::Debug
            );
            $consoleHandler->setFormatter($formatter);
            $logger->pushHandler($consoleHandler);
        }

        // Fichier de développement
        $devHandler = new StreamHandler(
            __DIR__ . '/../logs/dev.log',
            Level::Debug
        );
        $devHandler->setFormatter($formatter);
        $logger->pushHandler($devHandler);

        // Handler séparé pour les erreurs en développement
        $devErrorHandler = new StreamHandler(
            __DIR__ . '/../logs/dev-errors.log',
            Level::Error
        );
        $devErrorHandler->setFormatter($formatter);
        $logger->pushHandler($devErrorHandler);

        break;
}

// Retourner le logger configuré
return $logger;
