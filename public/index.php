<?php

ini_set('opcache.enable', 0);

error_reporting(E_ALL);
ini_set('display_errors', 0);

/**
 * Front Controller - Main entry point for the OTTIS application
 *
 * This file serves as the single entry point for all HTTP requests.
 * It initializes the application, sets up autoloading, and dispatches
 * requests to the appropriate controller based on the URL.
 */

// Define application constants
define('APP_ROOT', dirname(__DIR__));
define('APP_PUBLIC', __DIR__);
define('APP_VIEWS', APP_ROOT . '/views');

// Ensure no output before headers
ob_start();

// Load Composer autoloader and bootstrap the application
$autoloadPath = APP_ROOT . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
    try {
        \App\Core\Bootstrap::run();
    } catch (\Exception $e) {
        error_log('Bootstrap failed: ' . $e->getMessage());
        // Continue en cas d'erreur (ex. CAS non configure : la barriere
        // de controle CAS plus bas renverra 403 sur les routes protegees)
    }
} else {
    // Fallback for development without Composer
}

// Set default timezone
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Europe/Paris');
}

// Set content type header
header('Content-Type: text/html; charset=UTF-8');

// Prevent caching for dynamic content
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');

$casConfigPath = APP_ROOT . '/config/cas.php';
$casConfig = [];
if (file_exists($casConfigPath)) {
    $loadedConfig = require $casConfigPath;
    if (is_array($loadedConfig)) {
        $casConfig = $loadedConfig;
    }
}
$casEnabled = (bool) ($casConfig['enabled'] ?? false);

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isProtectedRoute = $currentPath === '/' || $currentPath === '/conference' || str_starts_with($currentPath, '/conference/');
$isAuditorRoute = $currentPath === '/auditor' || str_starts_with($currentPath, '/auditor/');

if ($casEnabled && $isProtectedRoute) {
    $casUser = $_SESSION['cas_user'] ?? null;
    $casAttributes = $_SESSION['cas_attributes'] ?? [];

    $affiliations = $casAttributes['eduPersonAffiliation'] ?? ($casAttributes['eduPersonPrimaryAffiliation'] ?? []);
    if (!is_array($affiliations)) {
        $affiliations = [$affiliations];
    }

    $allowedAffiliations = ['faculty', 'staff', 'employee', 'teacher'];
    $hasAuthorizedAffiliation = false;

    foreach ($affiliations as $affiliation) {
        if ($affiliation === null || $affiliation === '') {
            continue;
        }

        $normalizedAffiliation = strtolower(trim((string) $affiliation));
        if (in_array($normalizedAffiliation, $allowedAffiliations, true)) {
            $hasAuthorizedAffiliation = true;
            break;
        }
    }

    if (empty($casUser) || !$hasAuthorizedAffiliation) {
        http_response_code(403);
        echo '<h1>Accès interdit</h1><p>Cette zone est réservée au personnel et aux enseignants.</p>';
        exit;
    }
}

if ($casEnabled && $isAuditorRoute) {
    // Route publique : accès libre, sans CAS.
}

// Fix for path preservation through Apache rewrite using PATH_INFO
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
if (!empty($pathInfo)) {
    $_SERVER['REQUEST_URI'] = $pathInfo;
}

// Create router instance
$router = new \App\Service\Router();

// Calculate base path for JavaScript
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = dirname($scriptName);
$basePath = '';
if (str_ends_with($scriptName, '/public/index.php')) {
    $basePath = '/'; // Base path is root, not /public
}

// Expose base path to JavaScript - ALWAYS define BASE_PATH
$basePathScript = "<script>window.BASE_PATH = '" . addslashes($basePath) . "';</script>";

// Logs de debug uniquement en environnement de développement
$isDevEnvironment = in_array(strtolower(getenv('APP_ENV') ?: 'development'), ['development', 'dev', 'local'], true);
if ($isDevEnvironment) {
    error_log('BASE_PATH: ' . $basePath);
    error_log('=== DEBUG REQUEST ===');
    error_log('SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET'));
    error_log('REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'NOT SET'));
    error_log('PATH_INFO: ' . ($_SERVER['PATH_INFO'] ?? 'NOT SET'));
    error_log('REQUEST_METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET'));
}

// Register routes from the centralized configuration (config/routes.php)
$routeConfig = require APP_ROOT . '/config/routes.php';
foreach ($routeConfig as $path => $definition) {
    $methods = $definition['methods'] ?? ['GET'];
    foreach ($methods as $httpMethod) {
        $httpMethod = strtolower((string) $httpMethod);
        if (in_array($httpMethod, ['get', 'post', 'put', 'delete'], true)) {
            $router->{$httpMethod}($path, $definition['controller'], $definition['action']);
        }
    }
}

// DEBUG: Log des routes POST enregistrées (développement uniquement)
if ($isDevEnvironment) {
    error_log('=== POST ROUTES ===');
    foreach ($router->getRoutes()['POST'] as $path => $handler) {
        error_log("POST $path => " . $handler[0] . '::' . $handler[1]);
    }
}

// Try to match and dispatch the route
$match = $router->match();
if ($isDevEnvironment) {
    error_log('[Router] URI: ' . ($router->getUri()) . ', Method: ' . ($router->getMethod()));
    error_log('[Router] Match result: ' . var_export($match, true));
}

if ($match !== null) {
    [$controllerClass, $action, $params] = $match;

    // Check that we have a valid controller class
    if (!empty($controllerClass) && class_exists($controllerClass)) {
        try {
            $controller = new $controllerClass($router);

            // Call the action method if it exists
            if (method_exists($controller, $action)) {
                // Clear output buffer before controller output
                ob_clean();

                // Inject BASE_PATH script only for HTML responses (not for API endpoints)
                $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                $isApiRequest = str_starts_with($requestUri, '/keepalive') ||
                                str_starts_with($requestUri, '/text/') ||
                                str_starts_with($requestUri, '/extract/') ||
                                str_starts_with($requestUri, '/email/') ||
                                str_starts_with($requestUri, '/image/') ||
                                str_starts_with($requestUri, '/upload') ||
                                str_starts_with($requestUri, '/auditor/') ||
                                str_starts_with($requestUri, '/conference/') ||
                                str_starts_with($requestUri, '/subtitles/') ||
                                // API endpoints that return JSON
                                str_starts_with($requestUri, '/text/save') ||
                                str_starts_with($requestUri, '/text/save-q') ||
                                str_starts_with($requestUri, '/text/read') ||
                                str_starts_with($requestUri, '/text/read-q') ||
                                str_starts_with($requestUri, '/text/read-transcript-sub') ||
                                str_starts_with($requestUri, '/image/save') ||
                                str_starts_with($requestUri, '/image/read') ||
                                str_starts_with($requestUri, '/email/send') ||
                                str_starts_with($requestUri, '/text/translate') ||
                                str_starts_with($requestUri, '/extract/transcription-sub') ||
                                str_starts_with($requestUri, '/extract/slides-notes') ||
                                str_starts_with($requestUri, '/text/read-vo');

                if (!$isApiRequest) {
                    echo $basePathScript;
                }

                // Call the controller action
                $controller->$action(...array_values($params));
                exit;
            } else {
                // Action method doesn't exist
                http_response_code(404);
                exit;
            }
        } catch (\Throwable $e) {
            // Log error with Monolog
            \App\Utils\Logger::error('Controller Error: {error}', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Display error if display_errors is enabled (for debugging)
            if (ini_get('display_errors')) {
                echo '<h1>Internal Server Error</h1>';
                echo '<pre>Message: ' . htmlspecialchars($e->getMessage()) . '</pre>';
                echo '<pre>File: ' . $e->getFile() . ':' . $e->getLine() . '</pre>';
                echo '<pre>Trace: ' . $e->getTraceAsString() . '</pre>';
            }

            // Return 500 error code (error pages handled by infrastructure)
            http_response_code(500);
            exit;
        }
    } else {
        // Controller class doesn't exist
        http_response_code(404);
        exit;
    }
} else {
    // No route matched
    http_response_code(404);
    exit;
}
