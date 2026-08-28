<?php

/**
 * Front Controller - Main entry point for the OMIST application
 *
 * This file serves as the single entry point for all HTTP requests.
 * It initializes the application, sets up autoloading, and dispatches
 * requests to the appropriate controller based on the URL.
 *
 * For backward compatibility, direct access to old PHP files is still supported.
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

// Create router instance
$router = new \App\Service\Router();

// Register routes
$routes = [
    // Home page
    '/' => [\App\Controller\HomeController::class, 'index'],
    '/index.php' => [\App\Controller\HomeController::class, 'index'],

    // Conference pages
    '/conference.php' => [\App\Controller\ConferenceController::class, 'index'],
    '/conference' => [\App\Controller\ConferenceController::class, 'index'],
    '/conference/{roomId}' => [\App\Controller\ConferenceController::class, 'show'],

    // Auditor page
    '/auditor.php' => [\App\Controller\AuditorController::class, 'index'],
    '/auditor' => [\App\Controller\AuditorController::class, 'index'],
    '/auditor/{roomId}' => [\App\Controller\AuditorController::class, 'show'],

    // Remote control
    '/remote.php' => [\App\Controller\RemoteController::class, 'index'],
    '/remote' => [\App\Controller\RemoteController::class, 'index'],

    // Subtitles
    '/subtitles.php' => [\App\Controller\SubtitlesController::class, 'index'],
    '/subtitles' => [\App\Controller\SubtitlesController::class, 'index'],

    // API endpoints for uploads
    '/upload_handler.php' => [\App\Controller\UploadController::class, 'handle'],
    '/readupload.php' => [\App\Controller\UploadController::class, 'read'],
    '/readtext.php' => [\App\Controller\TextController::class, 'read'],
    '/readtextQ.php' => [\App\Controller\TextController::class, 'readQ'],
    '/readtextTranscriptSub.php' => [\App\Controller\TextController::class, 'readTranscriptSub'],
    '/readimage.php' => [\App\Controller\ImageController::class, 'read'],

    // Save endpoints
    '/savetext.php' => [\App\Controller\TextController::class, 'save'],
    '/savetextQ.php' => [\App\Controller\TextController::class, 'saveQ'],
    '/saveimage.php' => [\App\Controller\ImageController::class, 'save'],
    '/saveremoteorder.php' => [\App\Controller\RemoteController::class, 'saveOrder'],
    '/savespeechforremote.php' => [\App\Controller\RemoteController::class, 'saveSpeech'],

    // Other endpoints
    '/sendamail.php' => [\App\Controller\EmailController::class, 'send'],

    // Extract endpoints
    '/ExtractSlidesNotes.php' => [\App\Controller\ExtractController::class, 'slidesNotes'],
    '/ExtractSlidesNotesAppHTML.php' => [\App\Controller\ExtractController::class, 'slidesNotesAppHTML'],
    '/ExtractSlidesNotesTxt.php' => [\App\Controller\ExtractController::class, 'slidesNotesTxt'],
    '/ExtractSlidesNotes_PPTX_images.php' => [\App\Controller\ExtractController::class, 'pptxImages'],
    '/PPTXNotesExtractor.php' => [\App\Controller\ExtractController::class, 'pptxNotes'],
];

// Register GET routes
foreach ($routes as $path => $handler) {
    $router->get($path, $handler[0], $handler[1]);
}

// For POST requests, register the same handlers
foreach ($routes as $path => $handler) {
    // Use 'store' or 'handle' as the action for POST
    $action = in_array($path, [
        '/upload_handler.php', '/savetext.php', '/savetextQ.php',
        '/saveimage.php', '/saveremoteorder.php', '/savespeechforremote.php',
        '/sendamail.php'
    ]) ? 'store' : 'handle';
    $router->post($path, $handler[0], $action);
}

// Try to match and dispatch the route
$match = $router->match();

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

                // Call the controller action
                $controller->$action(...array_values($params));
                exit;
            } else {
                // Action method doesn't exist, try fallback
                handleLegacyRequest();
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

            // Return 500 error code (error pages handled by infrastructure)
            http_response_code(500);
            exit;
        }
    } else {
        // Controller class doesn't exist, try fallback
        handleLegacyRequest();
    }
} else {
    // No route matched, try fallback to legacy system
    handleLegacyRequest();
}

/**
 * Fallback handler for requests that don't match MVC routes
 * This ensures backward compatibility with the old codebase
 */
function handleLegacyRequest(): void
{
    ob_clean();

    // Get the requested path
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // Map URIs to old PHP files
    $legacyFiles = [
        '/' => '/index.php',
        '/index.php' => '/index.php',
        '/conference' => '/conference.php',
        '/conference.php' => '/conference.php',
        '/auditor' => '/auditor.php',
        '/auditor.php' => '/auditor.php',
        '/remote' => '/remote.php',
        '/remote.php' => '/remote.php',
        '/subtitles' => '/subtitles.php',
        '/subtitles.php' => '/subtitles.php',
        '/upload_handler.php' => '/upload_handler.php',
        '/readupload.php' => '/readupload.php',
        '/readtext.php' => '/readtext.php',
        '/readtextQ.php' => '/readtextQ.php',
        '/readtextTranscriptSub.php' => '/readtextTranscriptSub.php',
        '/readimage.php' => '/readimage.php',
        '/savetext.php' => '/savetext.php',
        '/savetextQ.php' => '/savetextQ.php',
        '/saveimage.php' => '/saveimage.php',
        '/saveremoteorder.php' => '/saveremoteorder.php',
        '/savespeechforremote.php' => '/savespeechforremote.php',
        '/sendamail.php' => '/sendamail.php',
    ];

    // Check if the request matches a legacy file
    $requestPath = parse_url($uri, PHP_URL_PATH);

    // Debug: Log the request path for troubleshooting
    error_log('DEBUG: REQUEST_URI = ' . $_SERVER['REQUEST_URI']);
    error_log('DEBUG: requestPath = ' . $requestPath);

    // Handle query strings
    if (isset($legacyFiles[$requestPath])) {
        $legacyFile = APP_ROOT . $legacyFiles[$requestPath];
        if (file_exists($legacyFile)) {
            // Preserve query string
            $queryString = parse_url($uri, PHP_URL_QUERY);
            if (!empty($queryString)) {
                $_SERVER['QUERY_STRING'] = $queryString;
                parse_str($queryString, $_GET);
            }

            // Include the legacy file
            require $legacyFile;
            exit;
        }
    }

    // Try direct file mapping (e.g., /conference.php -> conference.php)
    $basename = basename($requestPath);
    if (str_ends_with($basename, '.php')) {
        $legacyFile = APP_ROOT . '/' . $basename;
        if (file_exists($legacyFile)) {
            require $legacyFile;
            exit;
        }
    }

    // If we're in the public directory, check if the file exists directly
    $publicFile = APP_PUBLIC . $requestPath;
    if (file_exists($publicFile) && is_file($publicFile)) {
        if (str_ends_with($publicFile, '.php')) {
            require $publicFile;
            exit;
        }

        // For non-PHP files, serve them directly
        $mimeTypes = [
            '.css' => 'text/css',
            '.js' => 'application/javascript',
            '.png' => 'image/png',
            '.jpg' => 'image/jpeg',
            '.jpeg' => 'image/jpeg',
            '.gif' => 'image/gif',
            '.svg' => 'image/svg+xml',
            '.woff' => 'font/woff',
            '.woff2' => 'font/woff2',
            '.ttf' => 'font/ttf',
        ];

        $extension = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
            readfile($publicFile);
            exit;
        }
    }

    // No match found - 404 (error pages handled by infrastructure)
    error_log('DEBUG: Reached 404 handler for requestPath = /anything-not-found');
    http_response_code(404);
    exit;
}

// Clean output buffer
ob_end_flush();
