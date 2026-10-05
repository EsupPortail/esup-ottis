<?php

/**
 * Gestionnaire d'erreurs centralisé pour OMIST
 *
 * Ce fichier fournit un gestionnaire centralisé pour toutes les erreurs PHP,
 * exceptions et erreurs fatales. Il intègre Monolog pour le logging et
 * affiche des pages d'erreur personnalisées.
 *
 * @package OMIST\Security
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!defined('APP_VIEWS')) {
    define('APP_VIEWS', APP_ROOT . '/views');
}

/**
 * Vérifie si l'environnement est en production
 *
 * @return bool True si environnement est 'production'
 */
function isProductionEnvironment(): bool
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
 * Récupère l'instance du logger Monolog
 *
 * @return \Monolog\Logger
 */
function getErrorLogger(): \Monolog\Logger
{
    static $logger = null;

    if ($logger === null) {
        if (file_exists(APP_ROOT . '/Security/logger.php')) {
            require_once APP_ROOT . '/Security/logger.php';
            $logger = logger();
        } else {
            // Fallback si logger.php n'est pas disponible
            try {
                require_once APP_ROOT . '/vendor/autoload.php';
                $logger = new \Monolog\Logger('OMIST-ERROR');
                $handler = new \Monolog\Handler\StreamHandler('php://stderr', \Monolog\Level::Error);
                $logger->pushHandler($handler);
            } catch (\Exception $e) {
                error_log('Failed to initialize error logger: ' . $e->getMessage());
                $logger = new class () {
                    public function __call($name, $arguments)
                    {
                        return null;
                    }
                };
            }
        }
    }

    return $logger;
}

/**
 * Affiche une page d'erreur personnalisée
 *
 * @param int $httpCode Code HTTP de l'erreur
 * @param string $title Titre de l'erreur
 * @param string $message Message d'erreur
 * @param array $context Contexte additionnel pour le logging
 * @param bool $logError Si true, log l'erreur
 */
function renderErrorPage(int $httpCode, string $title, string $message, array $context = [], bool $logError = true): void
{
    if ($logError) {
        $context['http_code'] = $httpCode;
        getErrorLogger()->error($title . ': ' . $message, $context);
    }

    http_response_code($httpCode);

    // Vérifier si on est en mode CLI
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, "Error {$httpCode}: {$title}\n{$message}\n");
        exit(1);
    }

    // Vérifier si on peut afficher une page personnalisée
    $errorFile = APP_VIEWS . '/errors/' . $httpCode . '.php';

    if (file_exists($errorFile) && !isProductionEnvironment()) {
        // En dev, afficher la page d'erreur avec détails
        require $errorFile;
    } elseif (file_exists($errorFile)) {
        // En production, afficher la page d'erreur sans détails sensibles
        require $errorFile;
    } else {
        // Fallback si le template n'existe pas
        sendGenericErrorPage($httpCode, $title, $message);
    }

    exit(1);
}

/**
 * Affiche une page d'erreur générique
 *
 * @param int $httpCode Code HTTP
 * @param string $title Titre
 * @param string $message Message
 */
function sendGenericErrorPage(int $httpCode, string $title, string $message): void
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
        <div class="error-footer">OMIST Automatic Translator</div>
    </div>
</body>
</html>
HTML;

    echo $html;
}

/**
 * Gestionnaire d'erreurs PHP (set_error_handler)
 *
 * @param int $errno Niveau de l'erreur
 * @param string $errstr Message d'erreur
 * @param string $errfile Fichier où l'erreur s'est produite
 * @param int $errline Ligne où l'erreur s'est produite
 * @return bool
 */
function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
{
    // Respecter error_reporting
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // Ignorer les suppressions avec @
    if (error_reporting() === 0) {
        return false;
    }

    // Mapper le niveau d'erreur à un code HTTP
    $httpCode = mapErrorLevelToHttpCode($errno);

    // Contexte pour le logging
    $context = [
        'error_type' => 'PHP Error',
        'error_level' => $errno,
        'error_message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'environment' => isProductionEnvironment() ? 'production' : 'development',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'session_id' => function_exists('session_id') && session_status() !== PHP_SESSION_NONE ? session_id() : 'none',
    ];

    // Logger l'erreur
    $logger = getErrorLogger();
    $level = mapErrorLevelToLogLevel($errno);
    $logger->$level($errstr, $context);

    // En production, ne pas afficher les erreurs à l'utilisateur
    if (isProductionEnvironment()) {
        // Afficher une page générique
        renderErrorPage(500, 'Erreur Interne', 'Une erreur interne est survenue. Veuillez réessayer plus tard.');
    }

    // En développement, retourner false pour que PHP gère l'erreur normalement
    return false;
}

/**
 * Gestionnaire d'exceptions (set_exception_handler)
 *
 * @param \Throwable $exception L'exception à gérer
 */
function handleException(\Throwable $exception): void
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
        'environment' => isProductionEnvironment() ? 'production' : 'development',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'session_id' => function_exists('session_id') && session_status() !== PHP_SESSION_NONE ? session_id() : 'none',
    ];

    // Logger l'exception
    $logger = getErrorLogger();
    $logger->error($exception->getMessage(), $context);

    // Afficher la page d'erreur
    if (isProductionEnvironment()) {
        renderErrorPage($httpCode, 'Erreur Interne', 'Une erreur interne est survenue. Veuillez réessayer plus tard.');
    } else {
        // En développement, afficher plus de détails
        $title = 'Exception: ' . get_class($exception);
        $message = $exception->getMessage() . "\n\nFichier: {$exception->getFile()}:{$exception->getLine()}";
        renderErrorPage($httpCode, $title, $message, $context);
    }
}

/**
 * Gestionnaire de shutdown pour les erreurs fatales
 */
function handleShutdown(): void
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
            'environment' => isProductionEnvironment() ? 'production' : 'development',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'session_id' => function_exists('session_id') && session_status() !== PHP_SESSION_NONE ? session_id() : 'none',
        ];

        // Logger l'erreur fatale
        $logger = getErrorLogger();
        $logger->critical($error['message'], $context);

        // Afficher la page d'erreur
        if (!headers_sent()) {
            renderErrorPage(500, 'Erreur Fatale', 'Une erreur fatale est survenue. L\'application ne peut pas continuer.', $context);
        } else {
            // Si les headers ont déjà été envoyés, afficher un message simple
            echo '<div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin: 20px;">';
            echo '<h2>Erreur Fatale</h2>';
            echo '<p>Une erreur fatale est survenue. L\'application ne peut pas continuer.</p>';
            if (!isProductionEnvironment()) {
                echo '<p><small>Fichier: ' . e($error['file']) . ':' . e((string)$error['line']) . '</small></p>';
                echo '<p><small>Message: ' . e($error['message']) . '</small></p>';
            }
            echo '</div>';
        }
    }
}

/**
 * Mappe le niveau d'erreur PHP à un niveau de log Monolog
 *
 * @param int $errno Niveau d'erreur PHP
 * @return string Niveau de log Monolog
 */
function mapErrorLevelToLogLevel(int $errno): string
{
    switch ($errno) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            return 'critical';
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            return 'warning';
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_STRICT:
            return 'notice';
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            return 'deprecated';
        default:
            return 'error';
    }
}

/**
 * Mappe le niveau d'erreur PHP à un code HTTP
 *
 * @param int $errno Niveau d'erreur PHP
 * @return int Code HTTP
 */
function mapErrorLevelToHttpCode(int $errno): int
{
    switch ($errno) {
        case E_USER_ERROR:
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
            return 500;
        case E_USER_WARNING:
        case E_WARNING:
            return 500;
        case E_USER_NOTICE:
        case E_NOTICE:
            return 400;
        default:
            return 500;
    }
}

/**
 * Initialise le gestionnaire d'erreurs centralisé
 *
 * Cette fonction doit être appelée au début de chaque script.
 */
function initErrorHandler(): void
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $initialized = true;

    // Définir le gestionnaire d'erreurs
    set_error_handler('handleError');

    // Définir le gestionnaire d'exceptions
    set_exception_handler('handleException');

    // Définir le gestionnaire de shutdown pour les erreurs fatales
    register_shutdown_function('handleShutdown');

    // Désactiver l'affichage des erreurs en production
    if (isProductionEnvironment()) {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
    } else {
        // En développement, afficher les erreurs mais avec notre gestionnaire
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
    }

    // Configurer le niveau de rapport d'erreurs
    error_reporting(E_ALL);
}

// Initialiser automatiquement si ce fichier est inclus
initErrorHandler();
