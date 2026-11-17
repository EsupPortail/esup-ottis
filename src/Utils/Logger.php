<?php

namespace App\Utils;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;

/**
 * Logger utility class
 *
 * Fournit un accès statique à l'instance Monolog de l'application,
 * configurée dans config/logging.php, avec un fallback stderr.
 */
class Logger
{
    private static ?MonologLogger $logger = null;

    /**
     * Get the shared Monolog instance (created on first use).
     */
    public static function get(): MonologLogger
    {
        if (self::$logger === null) {
            self::$logger = self::createLogger();
        }

        return self::$logger;
    }

    private static function createLogger(): MonologLogger
    {
        $configFile = (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/logging.php';

        if (file_exists($configFile)) {
            try {
                $configured = require $configFile;
                if ($configured instanceof MonologLogger) {
                    return $configured;
                }
                error_log('Logging config did not return a Monolog logger, using fallback');
            } catch (\Exception $e) {
                error_log('Monolog initialization failed: ' . $e->getMessage());
            }
        }

        return self::createFallbackLogger();
    }

    private static function createFallbackLogger(): MonologLogger
    {
        $logger = new MonologLogger('OTTIS-FALLBACK');
        $logger->pushHandler(new StreamHandler('php://stderr', Level::Debug));

        return $logger;
    }

    /**
     * Loggue une erreur d'exécution
     *
     * @param array<string, mixed> $context Données contextuelles pour le log
     */
    public static function error(string $message, array $context = []): void
    {
        self::get()->error($message, $context);
    }

    /**
     * Loggue un avertissement
     *
     * @param array<string, mixed> $context Données contextuelles pour le log
     */
    public static function warning(string $message, array $context = []): void
    {
        self::get()->warning($message, $context);
    }

    /**
     * Loggue un message d'information
     *
     * @param array<string, mixed> $context Données contextuelles pour le log
     */
    public static function info(string $message, array $context = []): void
    {
        self::get()->info($message, $context);
    }

    /**
     * Loggue un message de débogage
     *
     * @param array<string, mixed> $context Données contextuelles pour le log
     */
    public static function debug(string $message, array $context = []): void
    {
        self::get()->debug($message, $context);
    }

    /**
     * Loggue une anomalie bloquante
     *
     * @param array<string, mixed> $context Données contextuelles pour le log
     */
    public static function critical(string $message, array $context = []): void
    {
        self::get()->critical($message, $context);
    }

    /**
     * Loggue un événement notable mais normal
     *
     * @param array<string, mixed> $context Données contextuelles pour le log
     */
    public static function notice(string $message, array $context = []): void
    {
        self::get()->notice($message, $context);
    }

    /**
     * Loggue une alerte demandant une action immédiate
     *
     * @param array<string, mixed> $context Données contextuelles pour le log
     */
    public static function alert(string $message, array $context = []): void
    {
        self::get()->alert($message, $context);
    }

    /**
     * Loggue une urgence rendant le système inutilisable
     *
     * @param array<string, mixed> $context Données contextuelles pour le log
     */
    public static function emergency(string $message, array $context = []): void
    {
        self::get()->emergency($message, $context);
    }
}
