<?php
require_once __DIR__ . '/Security/room_tokens.php';
require_once __DIR__ . '/Security/validate_room_token.php';
require_once __DIR__ . '/Security/csrf.php';

// Vérifier le room_token
requireRoomToken();

// Vérification du token CSRF
if (!verifyCsrfRequest()) {
    http_response_code(403);
    die('CSRF token validation failed');
}

$random = $_POST["random"];
if (file_exists("tmp/ImageID_".$roomid)) {
    $randomid = file_get_contents("tmp/ImageID_".$roomid);
    if ($random != $randomid) {
      echo $randomid."|||".file_get_contents("tmp/Image_".$roomid);
    }
}

?>
