<?php
/**
 * Input parameters validation
 */

function validateRoomId($roomid) {
    if (!is_string($roomid)) {
        return false;
    }
    if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
        return false;
    }
    return true;
}

function sanitizeText($text, $maxLength = 10000) {
    if (!is_string($text)) {
        return '';
    }

    if (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength);
    }

    $text = strip_tags($text);
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    $text = str_replace(["\r\n", "\n", "\r"], ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return trim($text);
}

function validateLanguage($lang) {
    if (!is_string($lang)) {
        return 'fr';
    }
    if (!preg_match('/^[a-zA-Z0-9_-]{2,15}$/', $lang)) {
        return 'fr';
    }
    return $lang;
}

function validateLineId($lineid) {
    if (!is_numeric($lineid)) {
        return 0;
    }
    return (int)$lineid;
}

function validateTmpFilePath($roomid, $prefix = 'Link') {
    if (!validateRoomId($roomid)) {
        return false;
    }

    $allowedPrefixes = ['Link', 'Translated', 'Verylast', 'Image', 'ImageID', 'LinkQuestions'];
    if (!in_array($prefix, $allowedPrefixes)) {
        return false;
    }

    return 'tmp/' . $prefix . '_' . $roomid;
}

function secureOpenTmpFile($roomid, $prefix, $mode) {
    $filepath = validateTmpFilePath($roomid, $prefix);
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
        log_error("Impossible d'ouvrir le fichier: {filepath}", ['filepath' => $filepath]);
        return false;
    }

    return $fd;
}