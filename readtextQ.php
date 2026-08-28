<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Security/init.php';

// Vérification du token CSRF
if (!verifyCsrfRequest()) {
    http_response_code(403);
    die('CSRF token validation failed');
}

// Security Phase 1: Input validation and XSS prevention
// Validate ROOMID parameter using regex (alphanumeric, underscore, hyphen only)
if (!isset($_POST['ROOMID']) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $_POST['ROOMID'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Access denied';
    exit;
}

$roomid = $_POST['ROOMID'];

// Vérifier le room_token pour l'auditeur (multi-onglets)
$roomToken = $_POST['room_token'] ?? '';
error_log('[CHAT-READ] room_token received: ' . substr($roomToken, 0, 8) . "... for ROOMID=$roomid");
error_log('[CHAT-READ] Token file exists: ' . (file_exists(__DIR__ . '/tmp/room_token_' . basename($roomid)) ? 'YES' : 'NO'));
if (!empty($roomToken)) {
    if (!validateRoomToken($roomid, $roomToken)) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Invalid room token';
        exit;
    }
}

// Security: Ensure the file exists and is readable before accessing
$filePath = 'tmp/LinkQuestions_' . $roomid;

if (file_exists($filePath) && is_file($filePath) && is_readable($filePath)) {
    // Security: Limit file size to prevent DoS (10MB max)
    $fileSize = filesize($filePath);
    if ($fileSize > 10 * 1024 * 1024) {
        header('HTTP/1.1 413 Payload Too Large');
        echo 'File too large';
        exit;
    }
    echo file_get_contents($filePath);
} else {
    echo '';
}
