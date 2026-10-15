<?php

namespace App\Error;

use App\Utils\Logger;
use Monolog\Level;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

if (!defined('APP_VIEWS')) {
    define('APP_VIEWS', APP_ROOT . '/views');
}

/**
 * Gestionnaire d'erreurs centralisé pour OTTIS
 *
 * Enregistre les handlers PHP (erreurs, exceptions, erreurs fatales),
 * logge via App\Utils\Logger et affiche les pages d'erreur personnalisées
 * (views/errors/). Les méthodes publiques sont référencées comme callbacks
 * PHP (set_error_handler, etc.) : les renommer casse l'initialisation.
 */
class ErrorHandler
{
    /**
     * Initialise le gestionnaire d'erreurs centralisé
     */
    public static function init(): void
    {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        $initialized = true;

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        // Désactiver l'affichage des erreurs en production
        if (self::isProduction()) {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
        } else {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
        }

        error_reporting(E_ALL);
    }

    /**
     * Vérifie si l'environnement est en production
     */
    public static function isProduction(): bool
    {
        $env = getenv('APP_ENV');
        if ($env === false && file_exists(APP_ROOT . '/.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(APP_ROOT);
            $dotenv->load();
            $env = getenv('APP_ENV');
        }
        return $env === 'production';
    }

    /**
     * Gestionnaire d'erreurs PHP (set_error_handler)
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Respecter error_reporting
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Ignorer les suppressions avec @
        if (error_reporting() === 0) {
            return false;
        }

        // Contexte pour le logging
        $context = [
            'error_type' => 'PHP Error',
            'error_level' => $errno,
            'error_message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'environment' => self::isProduction() ? 'production' : 'development',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'session_id' => function_exists('session_id') && session_status() !== PHP_SESSION_NONE ? session_id() : 'none',
        ];

        Logger::get()->log(self::mapErrorLevelToLogLevel($errno), $errstr, $context);

        // En production, afficher une page générique
        if (self::isProduction()) {
            self::renderErrorPage(500, 'Erreur Interne', 'Une erreur interne est survenue. Veuillez réessayer plus tard.');
        }

        // En développement, retourner false pour que PHP gère l'erreur normalement
        return false;
    }

    /**
     * Gestionnaire d'exceptions (set_exception_handler)
     */
    public static function handleException(\Throwable $exception): void
    {
        $httpCode = $exception->getCode() >= 400 && $exception->getCode() < 600
            ? $exception->getCode()
            : 500;

        $context = [
            'error_type' => 'Exception',
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'environment' => self::isProduction() ? 'production' : 'development',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'session_id' => function_exists('session_id') && session_status() !== PHP_SESSION_NONE ? session_id() : 'none',
        ];

        Logger::get()->error($exception->getMessage(), $context);

        if (self::isProduction()) {
            self::renderErrorPage($httpCode, 'Erreur Interne', 'Une erreur interne est survenue. Veuillez réessayer plus tard.');
        } else {
            $title = 'Exception: ' . get_class($exception);
            $message = $exception->getMessage() . "\n\nFichier: {$exception->getFile()}:{$exception->getLine()}";
            self::renderErrorPage($httpCode, $title, $message, $context);
        }
    }

    /**
     * Gestionnaire de shutdown pour les erreurs fatales
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        // Vérifier si c'est une erreur fatale
        $fatalErrors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

        if (in_array($error['type'], $fatalErrors, true)) {
            $context = [
                'error_type' => 'Fatal Error',
                'error_level' => $error['type'],
                'error_message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'environment' => self::isProduction() ? 'production' : 'development',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'session_id' => function_exists('session_id') && session_status() !== PHP_SESSION_NONE ? session_id() : 'none',
            ];

            Logger::get()->critical($error['message'], $context);

            if (!headers_sent()) {
                self::renderErrorPage(500, 'Erreur Fatale', 'Une erreur fatale est survenue. L\'application ne peut pas continuer.', $context);
            } else {
                // Si les headers ont déjà été envoyés, afficher un message simple
                echo '<div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin: 20px;">';
                echo '<h2>Erreur Fatale</h2>';
                echo '<p>Une erreur fatale est survenue. L\'application ne peut pas continuer.</p>';
                if (!self::isProduction()) {
                    echo '<p><small>Fichier: ' . htmlspecialchars($error['file'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ':' . htmlspecialchars((string)$error['line'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</small></p>';
                    echo '<p><small>Message: ' . htmlspecialchars($error['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</small></p>';
                }
                echo '</div>';
            }
        }
    }

    /**
     * Affiche une page d'erreur personnalisée
     */
    public static function renderErrorPage(int $httpCode, string $title, string $message, array $context = [], bool $logError = true): void
    {
        if ($logError) {
            $context['http_code'] = $httpCode;
            Logger::get()->error($title . ': ' . $message, $context);
        }

        http_response_code($httpCode);

        // Vérifier si on est en mode CLI
        if (php_sapi_name() === 'cli') {
            fwrite(STDERR, "Error {$httpCode}: {$title}\n{$message}\n");
            exit(1);
        }

        // Afficher la page d'erreur personnalisée
        $errorFile = APP_VIEWS . '/errors/' . $httpCode . '.php';

        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            // Fallback si le template n'existe pas
            self::sendGenericErrorPage($httpCode, $title, $message);
        }

        exit(1);
    }

    /**
     * Affiche une page d'erreur générique
     */
    private static function sendGenericErrorPage(int $httpCode, string $title, string $message): void
    {
        http_response_code($httpCode);

        header('Content-Type: text/html; charset=UTF-8');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$httpCode} - {$title}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .error-container { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 40px; text-align: center; max-width: 500px; width: 90%; }
        .error-code { font-size: 72px; font-weight: bold; color: #d9534f; margin: 0 0 20px 0; }
        .error-title { font-size: 24px; margin: 0 0 15px 0; color: #333; }
        .error-message { font-size: 16px; color: #666; line-height: 1.5; }
        .error-footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 14px; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">{$httpCode}</div>
        <h1 class="error-title">{$title}</h1>
        <p class="error-message">{$message}</p>
        <div class="error-footer">OTTIS Automatic Translator</div>
    </div>
</body>
</html>
HTML;

        echo $html;
    }

    /**
     * Mappe le niveau d'erreur PHP à un niveau Monolog
     */
    private static function mapErrorLevelToLogLevel(int $errno): Level
    {
        return match ($errno) {
            E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => Level::Critical,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => Level::Warning,
            E_NOTICE, E_USER_NOTICE, E_STRICT => Level::Notice,
            E_DEPRECATED, E_USER_DEPRECATED => Level::Info,
            default => Level::Error,
        };
    }
}
