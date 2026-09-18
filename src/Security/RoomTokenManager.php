<?php

namespace App\Security;

use App\Utils\Validator;

/**
 * Room Token Manager
 *
 * Manages room-specific tokens for multi-tab/multi-device access
 * without relying on PHP sessions.
 */
class RoomTokenManager
{
    /**
     * Token directory path
     */
    private static function getTokenDir(): string
    {
        return __DIR__ . '/../../Security/tmp';
    }

    /**
     * Generate a unique token for a room ID
     *
     * @param string $roomid The room identifier
     * @return string The generated token
     */
    public static function generateRoomToken(string $roomid): string
    {
        $tokenDir = self::getTokenDir();
        $tokenFile = $tokenDir . '/room_token_' . basename($roomid);

        // Create tmp directory if it doesn't exist
        if (!is_dir($tokenDir)) {
            mkdir($tokenDir, 0755, true);
        }

        // If a token already exists for this room, return it
        if (file_exists($tokenFile)) {
            $content = explode('\n', trim(file_get_contents($tokenFile)));
            $existingToken = $content[0];
            if (!empty($existingToken)) {
                error_log("RoomTokenManager: Reusing existing token for ROOMID=$roomid");
                return $existingToken;
            }
        }

        // Otherwise create and save a new token
        $token = bin2hex(random_bytes(16));
        $result = file_put_contents($tokenFile, $token . '\n' . time()); // Store token + timestamp
        if ($result === false) {
            error_log("RoomTokenManager: Failed to write token file for ROOMID=$roomid");
        }
        error_log("RoomTokenManager: Generated new token for ROOMID=$roomid");
        return $token;
    }

    /**
     * Validate a token for a room ID
     *
     * @param string $roomid The room identifier
     * @param string $token The token to validate
     * @return bool True if the token is valid, false otherwise
     */
    public static function validateRoomToken(string $roomid, string $token): bool
    {
        $tokenFile = self::getTokenDir() . '/room_token_' . basename($roomid);

        if (!file_exists($tokenFile)) {
            return false;
        }

        $content = explode('\n', trim(file_get_contents($tokenFile)));
        $storedToken = $content[0];
        $timestamp = (int)($content[1] ?? 0);

        // Invalid token if empty or doesn't match
        if (empty($storedToken) || !hash_equals($storedToken, $token)) {
            return false;
        }

        // Clean up expired tokens (24h by default)
        self::cleanupExpiredTokens();

        return true;
    }

    /**
     * Clean up expired tokens (24h)
     */
    public static function cleanupExpiredTokens(): void
    {
        $tmpDir = self::getTokenDir();
        $now = time();
        $expiry = 24 * 3600; // 24 hours

        if (!is_dir($tmpDir)) {
            return;
        }

        foreach (glob($tmpDir . '/room_token_*') as $file) {
            $content = explode('\n', trim(file_get_contents($file)));
            $timestamp = (int)($content[1] ?? 0);

            if ($now - $timestamp > $expiry) {
                unlink($file);
            }
        }
    }

    /**
     * Clean up the token for a specific room ID
     *
     * @param string $roomid The room identifier
     */
    public static function cleanupRoomToken(string $roomid): void
    {
        $tokenFile = self::getTokenDir() . '/room_token_' . basename($roomid);
        if (file_exists($tokenFile)) {
            unlink($tokenFile);
        }
    }

    /**
     * Check if a valid room token exists
     *
     * @param string $roomid The room identifier
     * @return bool True if a valid token exists
     */
    public static function hasValidRoomToken(string $roomid): bool
    {
        $tokenFile = self::getTokenDir() . '/room_token_' . basename($roomid);
        return file_exists($tokenFile);
    }

    /**
     * Validate room token for a POST request
     * Terminates execution with HTTP 403 if invalid
     *
     * @param string $roomidParam Name of the ROOMID parameter (default: 'ROOMID')
     * @param string $tokenParam Name of the token parameter (default: 'room_token')
     */
    public static function requireRoomToken(string $roomidParam = 'ROOMID', string $tokenParam = 'room_token'): void
    {
        $roomid = $_POST[$roomidParam] ?? '';
        $roomToken = $_POST[$tokenParam] ?? '';

        // Basic validations
        if (!Validator::validateRoomId($roomid)) {
            http_response_code(400);
            die('Invalid ROOMID');
        }

        if (empty($roomToken)) {
            http_response_code(403);
            error_log("Room token missing for ROOMID=$roomid");
            die('Room token missing');
        }

        if (!self::validateRoomToken($roomid, $roomToken)) {
            http_response_code(403);
            error_log("Invalid room token for ROOMID=$roomid");
            die('Invalid room token');
        }
    }
}
