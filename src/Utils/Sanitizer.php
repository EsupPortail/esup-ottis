<?php

namespace App\Utils;

/**
 * Sanitizer utility class
 *
 * Contains methods for sanitizing output to protect against XSS attacks.
 */
class Sanitizer
{
    /**
     * Sanitize a value for HTML display
     *
     * This is a shortcut for htmlspecialchars with the correct flags.
     * Should be used for all user variables displayed in HTML.
     *
     * @param string $value The value to sanitize
     * @param int $flags Flags for htmlspecialchars (ENT_QUOTES | ENT_SUBSTITUTE by default)
     * @param string|null $encoding The encoding (UTF-8 by default)
     * @param bool $doubleEncode Whether to double-encode
     * @return string The sanitized value
     */
    public static function e(string $value, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, ?string $encoding = null, bool $doubleEncode = true): string
    {
        if ($encoding === null) {
            $encoding = ini_get('default_charset') ?: 'UTF-8';
        }

        return htmlspecialchars($value, $flags, $encoding, $doubleEncode);
    }

    /**
     * Sanitize a value for HTML attribute
     *
     * @param string $value The value to sanitize
     * @return string The sanitized value
     */
    public static function eAttr(string $value): string
    {
        return self::e($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true);
    }

    /**
     * Sanitize a value for JavaScript
     *
     * @param string $value The value to sanitize
     * @return string The sanitized value
     */
    public static function eJs(string $value): string
    {
        // For JavaScript, use json_encode which correctly handles special characters
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Sanitize a URL for display
     *
     * @param string $url The URL to sanitize
     * @return string The sanitized URL
     */
    public static function eUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}
