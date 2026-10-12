<?php

namespace App\Security;

use App\Utils\Validator;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

/**
 * Room Token Manager
 *
 * Manages room-specific tokens for multi-tab/multi-device access
 * without relying on PHP sessions.
 */
class RoomTokenManager
{
    /**
     * Fenetre (s) pendant laquelle une salle d'un intervenant reste
     * recuperable apres sa derniere activite (reconnexion CAS).
     */
    public const ROOM_RECOVERY_WINDOW = 900;

    /**
     * Token directory path
     */
    private static function getTokenDir(): string
    {
        $dir = APP_ROOT . '/tmp';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Si le répertoire existe mais n'est pas accessible en écriture, utiliser sys temp dir
        if (!is_writable($dir)) {
            $dir = sys_get_temp_dir() . '/ottis_tokens/';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }

        return $dir;
    }

    /**
     * Read the room token data from disk.
     *
     * Supports both the legacy format (token\n<timestamp>) and the extended
     * format (token\n<timestamp>\n<ownerUid>).
     *
     * @param string $roomid
     * @return array{token:string,timestamp:int,ownerUid:string}
     */
    private static function readRoomTokenData(string $roomid): array
    {
        $tokenFile = self::getTokenDir() . '/room_token_' . basename($roomid);
        if (!file_exists($tokenFile)) {
            return ['token' => '', 'timestamp' => 0, 'ownerUid' => ''];
        }

        $content = trim((string) file_get_contents($tokenFile));
        if ($content === '') {
            return ['token' => '', 'timestamp' => 0, 'ownerUid' => ''];
        }

        $segments = preg_split('/\r\n|\n|\\n/', $content);
        $segments = array_values(array_filter($segments, static fn ($segment) => $segment !== ''));

        $token = trim((string) ($segments[0] ?? ''));
        $timestamp = (int) ($segments[1] ?? 0);
        $ownerUid = trim((string) ($segments[2] ?? ''));

        return ['token' => $token, 'timestamp' => $timestamp, 'ownerUid' => $ownerUid];
    }

    /**
     * Generate a unique token for a room ID
     *
     * @param string $roomid The room identifier
     * @param string|null $ownerUid Optional owner uid for the room
     * @return string The generated token
     */
    public static function generateRoomToken(string $roomid, ?string $ownerUid = null): string
    {
        $tokenDir = self::getTokenDir();
        $tokenFile = $tokenDir . '/room_token_' . basename($roomid);

        // Create tmp directory if it doesn't exist
        if (!is_dir($tokenDir)) {
            mkdir($tokenDir, 0755, true);
        }

        // If a token already exists for this room, reuse it and update owner if provided.
        if (file_exists($tokenFile)) {
            $existing = self::readRoomTokenData($roomid);
            $existingToken = $existing['token'];
            if (!empty($existingToken)) {
                if (!empty($ownerUid) && empty($existing['ownerUid'])) {
                    self::setRoomOwnerUid($roomid, $ownerUid);
                }
                error_log("RoomTokenManager: Reusing existing token for ROOMID=$roomid");
                return $existingToken;
            }
        }

        // Otherwise create and save a new token
        $token = bin2hex(random_bytes(16));
        $payload = $token . "\n" . time();
        if (!empty($ownerUid)) {
            $payload .= "\n" . trim($ownerUid);
        }
        $result = file_put_contents($tokenFile, $payload);
        if ($result === false) {
            error_log("RoomTokenManager: Failed to write token file for ROOMID=$roomid");
        }
        error_log("RoomTokenManager: Generated new token for ROOMID=$roomid");
        return $token;
    }

    /**
     * Persist the room owner uid for a room ID.
     *
     * @param string $roomid
     * @param string $ownerUid
     * @return void
     */
    public static function setRoomOwnerUid(string $roomid, string $ownerUid): void
    {
        $tokenFile = self::getTokenDir() . '/room_token_' . basename($roomid);
        if (!is_dir(self::getTokenDir())) {
            mkdir(self::getTokenDir(), 0755, true);
        }

        $current = self::readRoomTokenData($roomid);
        $token = $current['token'];
        if ($token === '') {
            $token = self::generateRoomToken($roomid, $ownerUid);
            $current = self::readRoomTokenData($roomid);
            $token = $current['token'];
        }

        $timestamp = $current['timestamp'] ?: time();
        $payload = $token . "\n" . $timestamp . "\n" . trim($ownerUid);
        file_put_contents($tokenFile, $payload);
    }

    /**
     * Return the room owner uid for the given room.
     *
     * @param string $roomid
     * @return string
     */
    public static function getRoomOwnerUid(string $roomid): string
    {
        $data = self::readRoomTokenData($roomid);
        return (string) $data['ownerUid'];
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

        $data = self::readRoomTokenData($roomid);
        $storedToken = $data['token'];
        $timestamp = (int) $data['timestamp'];

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
            // basename($file, 'room_token_') ne retire pas le prefixe (suffixe en fin de nom) :
            // l'ancien code lisait un fichier inexistant et supprimait TOUS les tokens.
            $roomid = substr(basename($file), strlen('room_token_'));
            $data = self::readRoomTokenData($roomid);
            $timestamp = (int) $data['timestamp'];

            if ($now - $timestamp > $expiry) {
                unlink($file);
            }
        }
    }

    /**
     * Rafraichir la date d'activite du token d'une salle (mtime du fichier).
     * Appele par le heartbeat de la conference : une salle active reste
     * "recente" tant que la page conference est ouverte.
     *
     * @param string $roomid
     * @return void
     */
    public static function touchRoomToken(string $roomid): void
    {
        $tokenFile = self::getTokenDir() . '/room_token_' . basename($roomid);
        if (file_exists($tokenFile)) {
            touch($tokenFile);
        }
    }

    /**
     * Salle la plus recemment active possedee par un intervenant.
     * Retourne l'ID de salle, ou null si aucune activite recente
     * (fenetre ROOM_RECOVERY_WINDOW apres la derniere activite).
     *
     * @param string $ownerUid
     * @return string|null
     */
    public static function findRecentRoomByOwner(string $ownerUid): ?string
    {
        if ($ownerUid === '') {
            return null;
        }

        $now = time();
        $bestRoom = null;
        $bestMtime = 0;
        foreach (glob(self::getTokenDir() . '/room_token_*') ?: [] as $file) {
            $mtime = filemtime($file);
            if ($mtime === false || ($now - $mtime) > self::ROOM_RECOVERY_WINDOW) {
                continue;
            }
            $roomid = substr(basename($file), strlen('room_token_'));
            if (self::readRoomTokenData($roomid)['ownerUid'] !== $ownerUid) {
                continue;
            }
            if ($mtime > $bestMtime) {
                $bestMtime = $mtime;
                $bestRoom = $roomid;
            }
        }

        return $bestRoom;
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
