<?php

namespace App\Controller;

use App\Service\Router;
use App\Utils\Sanitizer;

/**
 * BaseController - Abstract base controller for all MVC controllers
 *
 * Provides common functionality for all controllers including:
 * - View rendering
 * - Request/Response handling
 * - Helper methods for common web operations
 */
abstract class BaseController
{
    /**
     * @var Router The router instance
     */
    protected Router $router;

    /**
     * @var string Base path for views
     */
    protected string $viewPath;

    /**
     * @var string|false|null Layout to use (default: 'default', false for no layout)
     */
    protected string|false|null $layout = 'default';

    /**
     * @var array Data to pass to the view
     */
    protected array $viewData = [];

    /**
     * @var string Current language
     */
    protected string $language = 'fr';

    /**
     * Constructor
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->viewPath = __DIR__ . '/../../views/';
        $this->initialize();
    }

    /**
     * Initialize controller - override in child classes for setup
     */
    protected function initialize(): void
    {
        // Initialize session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set language from GET parameter or session
        $this->language = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';

        // Send no-cache headers for all pages
        $this->sendNoCacheHeaders();
    }

    /**
     * Send HTTP headers to prevent caching
     * This is useful for pages that should always load fresh data
     */
    protected function sendNoCacheHeaders(): void
    {
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
    }

    /**
     * Render a view with optional layout
     *
     * @param string $view View file name (without .php extension)
     * @param array $data Data to pass to the view
     * @param string|false|null $layout Layout to use (null to use default, false for no layout)
     */
    protected function render(string $view, array $data = [], string|false|null $layout = null): void
    {
        // Merge provided data with view data
        $this->viewData = array_merge($this->viewData, $data);

        // Add common variables
        $this->viewData['router'] = $this->router;
        $this->viewData['language'] = $this->language;
        $this->viewData['_view'] = $view;

        // Load translations
        $this->loadTranslations();

        // Determine layout
        $useLayout = $layout ?? $this->layout;

        if ($useLayout === false) {
            // Render view without layout
            $this->renderView($view);
        } else {
            // Render view with layout
            $this->renderWithLayout($view, $useLayout);
        }
    }

    /**
     * Render view with layout
     *
     * @param string $view View file name
     * @param string $layout Layout file name
     */
    protected function renderWithLayout(string $view, string $layout): void
    {
        // Extract view data for use in layout
        extract($this->viewData);

        // Start output buffering
        ob_start();

        // Include the view
        $this->renderView($view);
        $content = ob_get_clean();

        // Include the layout
        $layoutPath = $this->viewPath . 'layouts/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            // Fallback: just output the view content
            echo $content;
        }
    }

    /**
     * Render a view file
     *
     * @param string $view View file name (without .php extension)
     */
    protected function renderView(string $view): void
    {
        $viewPath = $this->viewPath . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewPath)) {
            extract($this->viewData);
            include $viewPath;
        } else {
            throw new \RuntimeException('View file not found: ' . $viewPath);
        }
    }

    /**
     * Render a partial view (without layout)
     *
     * @param string $partial Partial view name
     * @param array $data Data to pass to the partial
     */
    protected function renderPartial(string $partial, array $data = []): void
    {
        $this->viewData = array_merge($this->viewData, $data);
        $this->render($partial, $this->viewData, false);
    }

    /**
     * Load translations for the current language
     */
    protected function loadTranslations(): void
    {
        $langFile = __DIR__ . '/../../public/lang/' . $this->language . '.json';
        if (file_exists($langFile)) {
            $translations = json_decode(file_get_contents($langFile), true);
            $this->viewData['translations'] = $translations ?? [];
        } else {
            $this->viewData['translations'] = [];
        }
    }

    /**
     * Redirect to a route or URL
     *
     * @param string $url URL or route path
     * @param int $statusCode HTTP status code
     * @return never
     */
    protected function redirect(string $url, int $statusCode = 302): never
    {
        $this->router->redirect($url, $statusCode);
    }

    /**
     * Get a GET parameter
     *
     * @param string $key Parameter key
     * @param mixed $default Default value if not set
     * @return mixed Parameter value or default
     */
    protected function getParam(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get a POST parameter
     *
     * @param string $key Parameter key
     * @param mixed $default Default value if not set
     * @return mixed Parameter value or default
     */
    protected function postParam(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get request parameter (GET or POST)
     *
     * @param string $key Parameter key
     * @param mixed $default Default value if not set
     * @return mixed Parameter value or default
     */
    protected function requestParam(string $key, mixed $default = null): mixed
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**
     * Get all GET parameters
     *
     * @return array All GET parameters
     */
    protected function getAllGetParams(): array
    {
        return $_GET;
    }

    /**
     * Get all POST parameters
     *
     * @return array All POST parameters
     */
    protected function getAllPostParams(): array
    {
        return $_POST;
    }

    /**
     * Set a session value
     *
     * @param string $key Session key
     * @param mixed $value Value to set
     */
    protected function setSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     *
     * @param string $key Session key
     * @param mixed $default Default value if not set
     * @return mixed Session value or default
     */
    protected function getSession(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if current request is AJAX
     *
     * @return bool True if AJAX request
     */
    protected function isAjax(): bool
    {
        return $this->router->isAjax();
    }

    /**
     * Send a JSON response
     *
     * @param mixed $data Data to send as JSON
     * @param int $statusCode HTTP status code
     */
    protected function jsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Send a plain text response
     *
     * @param string $text Text to send
     * @param int $statusCode HTTP status code
     */
    protected function textResponse(string $text, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: text/plain');
        echo $text;
        exit;
    }

    /**
     * Send an error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code (default: 404)
     */
    protected function errorResponse(string $message, int $statusCode = 404): void
    {
        http_response_code($statusCode);

        if ($this->isAjax()) {
            $this->jsonResponse(['error' => $message], $statusCode);
        }

        // For non-AJAX requests, render error view
        $this->render('errors/error', [
            'message' => $message,
            'statusCode' => $statusCode
        ], false);
        exit;
    }

    /**
     * Generate a URL for a route
     *
     * @param string $path Route path
     * @param array $params Route parameters
     * @return string Generated URL
     */
    protected function url(string $path, array $params = []): string
    {
        return $this->router->generateUrl($path, $params);
    }

    /**
     * Sanitize output for HTML
     *
     * @param string $value Value to sanitize
     * @return string Sanitized value
     */
    protected function escape(string $value): string
    {
        return Sanitizer::e($value);
    }
}
