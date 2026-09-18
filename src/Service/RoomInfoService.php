<?php

namespace App\Service;

/**
 * RoomInfoService - Service for managing room-related information
 *
 * This service provides utilities for:
 * - Getting client IP address
 * - Determining user ID
 * - Generating application IDs
 */
class RoomInfoService
{
    /**
     * Get the client IP address
     *
     * Checks various HTTP headers to determine the real client IP address,
     * taking into account proxy servers and load balancers.
     *
     * @return string Client IP address
     */
    public static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get the user ID from server authentication
     *
     * @param string $default Default user ID if not authenticated
     * @return string User ID
     */
    public static function getUserId(string $default = 'anonymous'): string
    {
        $uid = $_SERVER['PHP_AUTH_USER'] ?? '';

        if (empty($uid)) {
            return $default;
        }

        // For non-univ-nantes.fr domains, use anonymous
        if (strpos($_SERVER['SERVER_NAME'] ?? '', 'univ-nantes.fr') === false) {
            return 'anonymous';
        }

        return $uid;
    }

    /**
     * Generate a unique application ID
     *
     * @param string $prefix Optional prefix for the ID
     * @return string Generated application ID
     */
    public static function generateAppId(string $prefix = ''): string
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $uniqueId = md5($timestamp . 'XXX');
        return $prefix . $uniqueId;
    }

    /**
     * Generate a room token
     *
     * @param string $roomId Room identifier
     * @return string Room token
     */
    public static function generateRoomToken(string $roomId): string
    {
        // Use the existing RoomTokenManager if available
        if (class_exists('\App\Security\RoomTokenManager')) {
            return \App\Security\RoomTokenManager::generateRoomToken($roomId);
        }
        // Fallback implementation
        return md5($roomId . '_' . time());
    }

    /**
     * Get all room information at once
     *
     * @param string $roomId Room identifier
     * @param string $defaultUser Default user ID
     * @return array Room information including IP, UID, app ID, and room token
     */
    public static function getRoomInfo(string $roomId, string $defaultUser = 'anonymous'): array
    {
        return [
            'roomId' => $roomId,
            'ip' => self::getClientIp(),
            'uid' => self::getUserId($defaultUser),
            'appId' => self::generateAppId(),
            'roomToken' => self::generateRoomToken($roomId)
        ];
    }
}
