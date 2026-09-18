<?php

ini_set('opcache.enable', 0);

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Front Controller - Main entry point for the OMIST application
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

// Load Composer autoloader and Security init FIRST to have error handler available
$autoloadPath = APP_ROOT . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
    try {
        require_once APP_ROOT . '/Security/init.php';
    } catch (Exception $e) {
        error_log('Security/init.php load failed: ' . $e->getMessage());
        // Continue without Security/init.php in case of error
    }
} else {
    // Fallback for development without Composer
    $fallbackAutoload = APP_ROOT . '/Security/load_env.php';
    if (file_exists($fallbackAutoload)) {
        require $fallbackAutoload;
    }
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

// Load environment variables
if (class_exists('Dotenv\Dotenv')) {
    try {
        $dotenv = \Dotenv\Dotenv::createImmutable(APP_ROOT);
        $dotenv->safeLoad();
    } catch (Exception $e) {
        error_log('Dotenv load failed: ' . $e->getMessage());
    }
}

// Initialize security functions
if (file_exists(APP_ROOT . '/Security/load_env.php')) {
    require_once APP_ROOT . '/Security/load_env.php';
}
// Initialize sanitization functions
if (file_exists(APP_ROOT . '/Security/sanitize.php')) {
    require_once APP_ROOT . '/Security/sanitize.php';
}

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
error_log('BASE_PATH: ' . $basePath);
error_log('=== DEBUG REQUEST ===');
error_log('SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET'));
error_log('REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'NOT SET'));
error_log('PATH_INFO: ' . ($_SERVER['PATH_INFO'] ?? 'NOT SET'));
error_log('REQUEST_METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET'));

// Register routes
$routes = [
    // Home page
    '/' => [\App\Controller\HomeController::class, 'index'],

    '/conference' => [\App\Controller\ConferenceController::class, 'index'],
    '/conference/{roomId}' => [\App\Controller\ConferenceController::class, 'show'],

    '/auditor' => [\App\Controller\AuditorController::class, 'index'],
    '/auditor/{roomId}' => [\App\Controller\AuditorController::class, 'show'],

    '/subtitles' => [\App\Controller\SubtitlesController::class, 'index'],

    // Upload endpoints
    '/upload' => [\App\Controller\UploadController::class, 'handle'],
    '/upload/handle' => [\App\Controller\UploadController::class, 'handle'],

    // Text endpoints
    '/text/read' => [\App\Controller\TextController::class, 'read'],
    '/text/read-q' => [\App\Controller\TextController::class, 'readQ'],
    '/text/read-transcript-sub' => [\App\Controller\TextController::class, 'readTranscriptSub'],
    '/image/read' => [\App\Controller\ImageController::class, 'read'],
    '/text/save' => [\App\Controller\TextController::class, 'save'],
    '/text/translate' => [\App\Controller\TextController::class, 'translate'],
    '/text/read-vo' => [\App\Controller\TextController::class, 'readVO'],
    '/text/save-q' => [\App\Controller\TextController::class, 'saveQ'],

    // Save endpoints
    '/image/save' => [\App\Controller\ImageController::class, 'save'],

    // Other endpoints
    '/email/send' => [\App\Controller\EmailController::class, 'send'],
    '/extract/transcription-sub' => [\App\Controller\ExtractController::class, 'transcriptionSub'],
    '/extract/slides-notes' => [\App\Controller\ExtractController::class, 'slidesNotes'],
    '/extract/slides-notes-txt' => [\App\Controller\ExtractController::class, 'slidesNotesTxt'],
    '/extract/slides-notes-apphtml' => [\App\Controller\ExtractController::class, 'slidesNotesAppHTML'],
    '/extract/pptx-images' => [\App\Controller\ExtractController::class, 'pptxImages'],
    '/extract/pptx-notes' => [\App\Controller\ExtractController::class, 'pptxNotes'],
];

// Register GET routes
foreach ($routes as $path => $handler) {
    $router->get($path, $handler[0], $handler[1]);
}

// For POST requests, register the same handlers
// First, register explicit POST routes with specific action names
$router->post('/extract/transcription-sub', \App\Controller\ExtractController::class, 'transcriptionSub');
$router->post('/extract/slides-notes', \App\Controller\ExtractController::class, 'slidesNotes');
$router->post('/extract/slides-notes-txt', \App\Controller\ExtractController::class, 'slidesNotesTxt');
$router->post('/extract/slides-notes-apphtml', \App\Controller\ExtractController::class, 'slidesNotesAppHTML');
$router->post('/extract/pptx-images', \App\Controller\ExtractController::class, 'pptxImages');
$router->post('/extract/pptx-notes', \App\Controller\ExtractController::class, 'pptxNotes');
$router->post('/image/read', \App\Controller\ImageController::class, 'read');
$router->post('/text/read', \App\Controller\TextController::class, 'read');
$router->post('/text/read-q', \App\Controller\TextController::class, 'readQ');
$router->post('/text/read-transcript-sub', \App\Controller\TextController::class, 'readTranscriptSub');
$router->post('/text/read-vo', \App\Controller\TextController::class, 'readVO');
$router->post('/image/save', \App\Controller\ImageController::class, 'save');
$router->post('/text/save-q', \App\Controller\TextController::class, 'saveQ');
$router->post('/text/save', \App\Controller\TextController::class, 'save');
$router->post('/text/translate', \App\Controller\TextController::class, 'translate');
$router->post('/email/send', \App\Controller\EmailController::class, 'send');

foreach ($routes as $path => $handler) {
    // Skip routes we already registered explicitly
    if (in_array($path, ['/extract/transcription-sub', '/image/read', '/text/read', '/text/read-q', '/text/read-transcript-sub', '/text/read-vo', '/text/save-q', '/text/save', '/text/translate', '/image/save', '/email/send', '/extract/slides-notes', '/extract/slides-notes-txt', '/extract/slides-notes-apphtml', '/extract/pptx-images', '/extract/pptx-notes'])) {
        continue;
    }
    // Use 'store' as the action for POST save routes
    $action = in_array($path, [
        '/text/save', '/text/save-q',
        '/image/save',
        '/email/send'
    ]) ? 'store' : 'handle';
    $router->post($path, $handler[0], $action);
}

// DEBUG: Log des routes POST enregistrées
error_log('=== POST ROUTES ===');
foreach ($router->getRoutes()['POST'] as $path => $handler) {
    error_log("POST $path => " . $handler[0] . '::' . $handler[1]);
}

// Try to match and dispatch the route
$match = $router->match();
error_log('[Router] URI: ' . ($router->getUri()) . ', Method: ' . ($router->getMethod()));
error_log('[Router] Match result: ' . var_export($match, true));

if ($match !== null) {
    [$controllerClass, $action, $params] = $match;

    // Validate that we have a valid controller class
    if (empty($controllerClass)) {
        $match = null;
    }
}

if ($match !== null) {
    // Check if controller exists and is instantiable
    if (class_exists($controllerClass)) {
        try {
            $controller = new $controllerClass($router);

            // Call the action method if it exists
            if (method_exists($controller, $action)) {
                // Clear output buffer before controller output
                ob_clean();

                // Inject BASE_PATH script only for HTML responses (not for API endpoints)
                $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                $isApiRequest = str_starts_with($requestUri, '/text/') ||
                                str_starts_with($requestUri, '/extract/') ||
                                str_starts_with($requestUri, '/email/') ||
                                str_starts_with($requestUri, '/image/') ||
                                str_starts_with($requestUri, '/upload/') ||
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
            if (function_exists('log_error')) {
                log_error('Controller Error: {error}', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            } else {
                error_log('Controller Error: ' . $e->getMessage());
            }

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

// Clean output buffer
ob_end_flush();
