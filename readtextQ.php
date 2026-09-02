<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Security/init.php';

// 1. Vérifier ROOM_TOKEN d'abord (pour le multi-appareil)
$roomid = $_POST['ROOMID'] ?? '';
$roomToken = $_POST['room_token'] ?? '';

// Valider ROOMID format en premier
if (!empty($roomid) && !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid ROOMID format';
    exit;
}

// Valider ROOM_TOKEN si fourni
$roomTokenValid = false;
if (!empty($roomToken) && !empty($roomid)) {
    $roomTokenValid = validateRoomToken($roomid, $roomToken);
    error_log('[CHAT-READ] room_token received: ' . substr($roomToken, 0, 8) . "... for ROOMID=$roomid");
    error_log('[CHAT-READ] Token file exists: ' . (file_exists(__DIR__ . '/tmp/room_token_' . basename($roomid)) ? 'YES' : 'NO'));
}

// 2. Si ROOM_TOKEN valide → autoriser (le token de salle est suffisant pour le chat multi-appareil)
if ($roomTokenValid) {
    // Accès autorisé, passer à la suite
}
// 3. Sinon → vérifier CSRF (pour la compatibilité)
elseif (!verifyCsrfRequest()) {
    http_response_code(403);
    die('CSRF token validation failed');
}

// Si on arrive ici, soit ROOM_TOKEN est valide, soit CSRF est valide

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
