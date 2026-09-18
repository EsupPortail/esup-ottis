<?php

namespace App\Service;

/**
 * Router - Simple PSR-7 compatible router for MVC architecture
 *
 * This router handles HTTP requests and dispatches them to the appropriate
 * controller and action method based on the request URI and method.
 */
class Router
{
    /**
     * @var array Routes configuration: method => [path => [controller, action]]
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    /**
     * @var string Current HTTP method
     */
    private string $method;

    /**
     * @var string Current request URI
     */
    private string $uri;

    /**
     * @var array Route parameters extracted from the URI
     */
    private array $params = [];

    /**
     * @var string Base path for the application
     */
    private string $basePath = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->parseUri();
        $this->basePath = $this->detectBasePath();
    }

    /**
     * Parse the request URI
     *
     * @return string The request URI without base path
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove base path
        $basePath = $this->detectBasePath();
        if (!empty($basePath) && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        // Ensure URI starts with /
        return '/' . ltrim($uri, '/');
    }

    /**
     * Detect the base path of the application
     *
     * @return string The base path
     */
    private function detectBasePath(): string
    {
        // Check if we're in a subdirectory
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = dirname($scriptName);

        // If running from public/index.php, the base path is the directory containing public
        if (str_ends_with($scriptName, '/public/index.php')) {
            return $scriptDir;
        }

        // For root installation
        return '';
    }

    /**
     * Add a GET route
     *
     * @param string $path Route path (e.g., '/', '/conference')
     * @param string $controller Controller class name (e.g., 'HomeController')
     * @param string $action Action method name (e.g., 'index')
     */
    public function get(string $path, string $controller, string $action = 'index'): void
    {
        $this->routes['GET'][$path] = [$controller, $action];
    }

    /**
     * Add a POST route
     *
     * @param string $path Route path
     * @param string $controller Controller class name
     * @param string $action Action method name
     */
    public function post(string $path, string $controller, string $action = 'store'): void
    {
        $this->routes['POST'][$path] = [$controller, $action];
    }

    /**
     * Add a PUT route
     *
     * @param string $path Route path
     * @param string $controller Controller class name
     * @param string $action Action method name
     */
    public function put(string $path, string $controller, string $action = 'update'): void
    {
        $this->routes['PUT'][$path] = [$controller, $action];
    }

    /**
     * Add a DELETE route
     *
     * @param string $path Route path
     * @param string $controller Controller class name
     * @param string $action Action method name
     */
    public function delete(string $path, string $controller, string $action = 'destroy'): void
    {
        $this->routes['DELETE'][$path] = [$controller, $action];
    }

    /**
     * Match the current request to a route
     *
     * @return array|null [controller, action, params] or null if no match
     */
    public function match(): ?array
    {
        $method = $this->method;

        // Try exact match first
        if (isset($this->routes[$method][$this->uri])) {
            return [
                $this->routes[$method][$this->uri][0],
                $this->routes[$method][$this->uri][1],
                []
            ];
        }

        // Try pattern matching with parameters
        foreach ($this->routes[$method] as $path => $handler) {
            $pattern = $this->pathToPattern($path);
            if (preg_match($pattern, $this->uri, $matches)) {
                // Extract parameters (skip full match at index 0)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return [$handler[0], $handler[1], $params];
            }
        }

        // No route matched
        return null;
    }

    /**
     * Convert a route path to a regex pattern
     *
     * @param string $path Route path with parameters like /conference/{roomId}
     * @return string Regex pattern
     */
    private function pathToPattern(string $path): string
    {
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);

        // Ensure pattern matches from start to end
        return '#^' . $pattern . '$#';
    }

    /**
     * Get the current request method
     *
     * @return string HTTP method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the current request URI
     *
     * @return string Request URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get route parameters
     *
     * @return array Route parameters
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Generate a URL for a named route
     *
     * @param string $path Route path
     * @param array $params Parameters to substitute
     * @return string Generated URL
     */
    public function generateUrl(string $path, array $params = []): string
    {
        // Replace {param} placeholders with actual values
        $url = $path;
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', urlencode($value), $url);
        }

        return $this->basePath . $url;
    }

    /**
     * Redirect to a URL
     *
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code (default: 302)
     * @return never
     */
    public function redirect(string $url, int $statusCode = 302): never
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /**
     * Check if current request is AJAX
     *
     * @return bool True if AJAX request
     */
    public function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Get all registered routes (for debugging)
     *
     * @return array All registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
