<?php
/**
 * Validation centralisée du room-token
 * À inclure dans tous les endpoints qui nécessitent une validation
 */

require_once __DIR__ . '/room_tokens.php';
require_once __DIR__ . '/validate.php';

/**
 * Valide le room_token pour une requête POST
 * Termine l'exécution avec HTTP 403 si invalide
 *
 * @param string $roomidParam Nom du paramètre ROOMID (défaut: 'ROOMID')
 * @param string $tokenParam Nom du paramètre token (défaut: 'room_token')
 */
function requireRoomToken($roomidParam = 'ROOMID', $tokenParam = 'room_token') {
    $roomid = $_POST[$roomidParam] ?? '';
    $roomToken = $_POST[$tokenParam] ?? '';

    // Validations de base
    if (!validateRoomId($roomid)) {
        http_response_code(400);
        die("Invalid ROOMID");
    }

    if (empty($roomToken)) {
        http_response_code(403);
        log_security('warning', 'Room token missing for ROOMID={roomid}', ['roomid' => $roomid]);
        die("Room token missing");
    }

    if (!validateRoomToken($roomid, $roomToken)) {
        http_response_code(403);
        log_security('warning', 'Invalid room token for ROOMID={roomid}', ['roomid' => $roomid]);
        die("Invalid room token");
    }
}
