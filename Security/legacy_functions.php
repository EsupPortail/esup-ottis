<?php
/**
 * Legacy Functions Wrapper
 *
 * This file provides backward compatibility by defining the old global functions
 * as wrappers around the new PSR-4 classes.
 * Include this file in your existing code until full migration is complete.
 */

// Load Composer autoloader if not already loaded
if (!class_exists('App\\Utils\\Sanitizer')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Sanitize functions
if (!function_exists('e')) {
    /**
     * @deprecated Use App\Utils\Sanitizer::e() instead
     */
    function e(string $value, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, ?string $encoding = null, bool $doubleEncode = true): string
    {
        return App\Utils\Sanitizer::e($value, $flags, $encoding, $doubleEncode);
    }
}

if (!function_exists('e_attr')) {
    /**
     * @deprecated Use App\Utils\Sanitizer::eAttr() instead
     */
    function e_attr(string $value): string
    {
        return App\Utils\Sanitizer::eAttr($value);
    }
}

if (!function_exists('e_js')) {
    /**
     * @deprecated Use App\Utils\Sanitizer::eJs() instead
     */
    function e_js(string $value): string
    {
        return App\Utils\Sanitizer::eJs($value);
    }
}

if (!function_exists('e_url')) {
    /**
     * @deprecated Use App\Utils\Sanitizer::eUrl() instead
     */
    function e_url(string $url): string
    {
        return App\Utils\Sanitizer::eUrl($url);
    }
}

// CSRF functions
if (!function_exists('generateCsrfToken')) {
    /**
     * @deprecated Use App\Utils\Csrf::generateToken() instead
     */
    function generateCsrfToken(): string
    {
        return App\Utils\Csrf::generateToken();
    }
}

if (!function_exists('validateCsrfToken')) {
    /**
     * @deprecated Use App\Utils\Csrf::validateToken() instead
     */
    function validateCsrfToken(string $token): bool
    {
        return App\Utils\Csrf::validateToken($token);
    }
}

if (!function_exists('csrfField')) {
    /**
     * @deprecated Use App\Utils\Csrf::field() instead
     */
    function csrfField(): string
    {
        return App\Utils\Csrf::field();
    }
}

if (!function_exists('verifyCsrfRequest')) {
    /**
     * @deprecated Use App\Utils\Csrf::verifyRequest() instead
     */
    function verifyCsrfRequest(): bool
    {
        return App\Utils\Csrf::verifyRequest();
    }
}

if (!function_exists('initCsrfProtection')) {
    /**
     * @deprecated Use App\Utils\Csrf::initProtection() instead
     */
    function initCsrfProtection(): void
    {
        App\Utils\Csrf::initProtection();
    }
}

// Validator functions
if (!function_exists('validateRoomId')) {
    /**
     * @deprecated Use App\Utils\Validator::validateRoomId() instead
     */
    function validateRoomId($roomid): bool
    {
        return App\Utils\Validator::validateRoomId($roomid);
    }
}

if (!function_exists('sanitizeText')) {
    /**
     * @deprecated Use App\Utils\Validator::sanitizeText() instead
     */
    function sanitizeText($text, $maxLength = 10000): string
    {
        return App\Utils\Validator::sanitizeText($text, $maxLength);
    }
}

if (!function_exists('validateLanguage')) {
    /**
     * @deprecated Use App\Utils\Validator::validateLanguage() instead
     */
    function validateLanguage($lang): string
    {
        return App\Utils\Validator::validateLanguage($lang);
    }
}

if (!function_exists('validateLineId')) {
    /**
     * @deprecated Use App\Utils\Validator::validateLineId() instead
     */
    function validateLineId($lineid): int
    {
        return App\Utils\Validator::validateLineId($lineid);
    }
}

if (!function_exists('validateTmpFilePath')) {
    /**
     * @deprecated Use App\Utils\Validator::validateTmpFilePath() instead
     */
    function validateTmpFilePath($roomid, $prefix = 'Link')
    {
        return App\Utils\Validator::validateTmpFilePath($roomid, $prefix);
    }
}

if (!function_exists('secureOpenTmpFile')) {
    /**
     * @deprecated Use App\Utils\Validator::secureOpenTmpFile() instead
     */
    function secureOpenTmpFile($roomid, $prefix, $mode)
    {
        return App\Utils\Validator::secureOpenTmpFile($roomid, $prefix, $mode);
    }
}

// Room Token functions
if (!function_exists('generateRoomToken')) {
    /**
     * @deprecated Use App\Security\RoomTokenManager::generateRoomToken() instead
     */
    function generateRoomToken($roomid): string
    {
        return App\Security\RoomTokenManager::generateRoomToken($roomid);
    }
}

if (!function_exists('validateRoomToken')) {
    /**
     * @deprecated Use App\Security\RoomTokenManager::validateRoomToken() instead
     */
    function validateRoomToken($roomid, $token): bool
    {
        return App\Security\RoomTokenManager::validateRoomToken($roomid, $token);
    }
}

if (!function_exists('cleanupExpiredTokens')) {
    /**
     * @deprecated Use App\Security\RoomTokenManager::cleanupExpiredTokens() instead
     */
    function cleanupExpiredTokens(): void
    {
        App\Security\RoomTokenManager::cleanupExpiredTokens();
    }
}

if (!function_exists('cleanupRoomToken')) {
    /**
     * @deprecated Use App\Security\RoomTokenManager::cleanupRoomToken() instead
     */
    function cleanupRoomToken($roomid): void
    {
        App\Security\RoomTokenManager::cleanupRoomToken($roomid);
    }
}

if (!function_exists('hasValidRoomToken')) {
    /**
     * @deprecated Use App\Security\RoomTokenManager::hasValidRoomToken() instead
     */
    function hasValidRoomToken($roomid): bool
    {
        return App\Security\RoomTokenManager::hasValidRoomToken($roomid);
    }
}

if (!function_exists('requireRoomToken')) {
    /**
     * @deprecated Use App\Security\RoomTokenManager::requireRoomToken() instead
     */
    function requireRoomToken($roomidParam = 'ROOMID', $tokenParam = 'room_token'): void
    {
        App\Security\RoomTokenManager::requireRoomToken($roomidParam, $tokenParam);
    }
}
