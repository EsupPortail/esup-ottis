<?php

namespace App\Utils;

/**
 * Input Validator utility class
 *
 * Contains methods for validating input parameters.
 */
class Validator
{
    /**
     * Validate a room ID
     *
     * @param mixed $roomid The room ID to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateRoomId($roomid): bool
    {
        if (!is_string($roomid)) {
            return false;
        }
        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            return false;
        }
        return true;
    }

    /**
     * Sanitize text input
     *
     * @param string $text The text to sanitize
     * @param int $maxLength Maximum length (default: 10000)
     * @return string The sanitized text
     */
    public static function sanitizeText(string $text, int $maxLength = 10000): string
    {
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength);
        }

        $text = strip_tags($text);
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
        $text = str_replace(["\r\n", "\n", "\r"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Validate a language code
     *
     * @param mixed $lang The language code to validate
     * @return string The validated language code (defaults to 'fr' if invalid)
     */
    public static function validateLanguage($lang): string
    {
        if (!is_string($lang)) {
            return 'fr';
        }
        if (!preg_match('/^[a-zA-Z0-9_-]{2,15}$/', $lang)) {
            return 'fr';
        }
        return $lang;
    }

    /**
     * Validate a line ID
     *
     * @param mixed $lineid The line ID to validate
     * @return int The validated line ID (0 if invalid)
     */
    public static function validateLineId($lineid): int
    {
        if (!is_numeric($lineid)) {
            return 0;
        }
        return (int)$lineid;
    }

    /**
     * Validate a temporary file path
     *
     * @param string $roomid The room ID
     * @param string $prefix The file prefix
     * @return string|false The validated path or false if invalid
     */
    public static function validateTmpFilePath(string $roomid, string $prefix = 'Link')
    {
        if (!self::validateRoomId($roomid)) {
            return false;
        }

        $allowedPrefixes = ['Link', 'Translated', 'Verylast', 'Image', 'ImageID', 'LinkQuestions'];
        if (!in_array($prefix, $allowedPrefixes)) {
            return false;
        }

        return 'tmp/' . $prefix . '_' . $roomid;
    }

    /**
     * Securely open a temporary file
     *
     * @param string $roomid The room ID
     * @param string $prefix The file prefix
     * @param string $mode The file mode
     * @return resource|false The file handle or false on failure
     */
    public static function secureOpenTmpFile(string $roomid, string $prefix, string $mode)
    {
        $filepath = self::validateTmpFilePath($roomid, $prefix);
        if ($filepath === false) {
            return false;
        }

        if (strpos($filepath, 'tmp/') !== 0) {
            return false;
        }

        if (strpos($filepath, '..') !== false || strpos($filepath, '/') !== 3) {
            return false;
        }

        $fd = @fopen($filepath, $mode);
        if ($fd === false) {
            error_log("Impossible d'ouvrir le fichier: " . $filepath);
            return false;
        }

        return $fd;
    }
}
