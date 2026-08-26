<?php

namespace App\Utils;

/**
 * CSRF Protection utility class
 *
 * Contains methods for generating and validating CSRF tokens
 * to protect the application against Cross-Site Request Forgery attacks.
 */
class Csrf
{
    /**
     * Generate a unique CSRF token and store it in session
     *
     * @return string The generated CSRF token
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token against the one stored in session
     *
     * @param string $token The token to validate
     * @return bool True if the token is valid, false otherwise
     */
    public static function validateToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Render a hidden CSRF field for forms
     *
     * @return string The HTML hidden input field containing the CSRF token
     */
    public static function field(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }

    /**
     * Check if a POST request contains a valid CSRF token
     *
     * @return bool True if the token is valid, false otherwise
     */
    public static function verifyRequest(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // Not a POST request, no CSRF needed
        }

        $token = $_POST['csrf_token'] ?? '';

        if (!self::validateToken($token)) {
            error_log('CSRF token validation failed for IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            return false;
        }

        return true;
    }

    /**
     * Initialize CSRF protection for a page
     * Should be called at the beginning of scripts that use forms
     */
    public static function initProtection(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Ensure token is generated
        self::generateToken();
    }
}
