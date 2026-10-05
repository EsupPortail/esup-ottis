<?php

/**
 * Logger wrapper pour Monolog
 *
 * Ce fichier fournit une fonction globale logger() et des helpers pour
 * utiliser Monolog partout dans l'application OMIST.
 *
 * @package OMIST\Security
 */

if (!function_exists('logger')) {
    /**
     * Récupère ou crée l'instance du logger Monolog
     *
     * @return \Monolog\Logger L'instance du logger
     */
    function logger(): \Monolog\Logger
    {
        static $logger = null;

        if ($logger === null) {
            // Charger l'autoloading de Composer pour Monolog
            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require_once __DIR__ . '/../vendor/autoload.php';
            }

            // Charger la configuration de logging
            if (file_exists(__DIR__ . '/../config/logging.php')) {
                try {
                    $logger = require __DIR__ . '/../config/logging.php';
                } catch (Exception $e) {
                    // Si Monolog échoue, utiliser le fallback
                    error_log('Monolog initialization failed: ' . $e->getMessage());
                    $logger = createFallbackLogger();
                }
            } else {
                // Fallback en cas de configuration manquante
                $logger = createFallbackLogger();
            }
        }

        return $logger;
    }

    /**
     * Crée un logger de secours si la configuration est manquante
     *
     * @return \Monolog\Logger|object
     */
    function createFallbackLogger()
    {
        try {
            require_once __DIR__ . '/../vendor/autoload.php';

            $logger = new \Monolog\Logger('OMIST-FALLBACK');

            // Handler de secours vers php://stderr
            $handler = new \Monolog\Handler\StreamHandler('php://stderr', \Monolog\Level::Debug);
            $logger->pushHandler($handler);

            return $logger;
        } catch (Exception $e) {
            // Si même Monolog lui-même échoue, créer un logger minimal qui ne fait rien
            error_log('Fallback logger failed: ' . $e->getMessage());
            // Créer un mock logger pour éviter les erreurs
            $nullLogger = new class () {
                public function __call($name, $arguments)
                {
                    return null;
                }
            };
            return $nullLogger;
        }
    }
}

if (!function_exists('log_error')) {
    /**
     * Log une erreur
     *
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_error(string $message, array $context = []): void
    {
        logger()->error($message, $context);
    }
}

if (!function_exists('log_warning')) {
    /**
     * Log un avertissement
     *
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_warning(string $message, array $context = []): void
    {
        logger()->warning($message, $context);
    }
}

if (!function_exists('log_info')) {
    /**
     * Log une information
     *
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_info(string $message, array $context = []): void
    {
        logger()->info($message, $context);
    }
}

if (!function_exists('log_debug')) {
    /**
     * Log un message de debug
     *
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_debug(string $message, array $context = []): void
    {
        logger()->debug($message, $context);
    }
}

if (!function_exists('log_critical')) {
    /**
     * Log une erreur critique
     *
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_critical(string $message, array $context = []): void
    {
        logger()->critical($message, $context);
    }
}

if (!function_exists('log_notice')) {
    /**
     * Log un notice
     *
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_notice(string $message, array $context = []): void
    {
        logger()->notice($message, $context);
    }
}

if (!function_exists('log_alert')) {
    /**
     * Log une alerte (action immédiate requise)
     *
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_alert(string $message, array $context = []): void
    {
        logger()->alert($message, $context);
    }
}

if (!function_exists('log_emergency')) {
    /**
     * Log une urgence (système inutilisable)
     *
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_emergency(string $message, array $context = []): void
    {
        logger()->emergency($message, $context);
    }
}

// ============================================================================
// Fonctions de log avec canal spécifique
// ============================================================================

if (!function_exists('log_security')) {
    /**
     * Log un événement lié à la sécurité
     *
     * @param string $level Niveau de log (debug, info, warning, error, etc.)
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_security(string $level, string $message, array $context = []): void
    {
        $securityLogger = logger()->withName('security');
        $method = 'log' . ucfirst(strtolower($level));
        if (method_exists($securityLogger, $method)) {
            $securityLogger->$method($message, $context);
        } else {
            $securityLogger->log($level, $message, $context);
        }
    }
}

if (!function_exists('log_api')) {
    /**
     * Log un événement lié à l'API
     *
     * @param string $level Niveau de log
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_api(string $level, string $message, array $context = []): void
    {
        $apiLogger = logger()->withName('api');
        $method = 'log' . ucfirst(strtolower($level));
        if (method_exists($apiLogger, $method)) {
            $apiLogger->$method($message, $context);
        } else {
            $apiLogger->log($level, $message, $context);
        }
    }
}

if (!function_exists('log_database')) {
    /**
     * Log un événement lié à la base de données
     *
     * @param string $level Niveau de log
     * @param string $message Message à logger
     * @param array $context Contexte additionnel
     */
    function log_database(string $level, string $message, array $context = []): void
    {
        $dbLogger = logger()->withName('database');
        $method = 'log' . ucfirst(strtolower($level));
        if (method_exists($dbLogger, $method)) {
            $dbLogger->$method($message, $context);
        } else {
            $dbLogger->log($level, $message, $context);
        }
    }
}
