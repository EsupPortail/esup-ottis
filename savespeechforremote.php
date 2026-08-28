<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Security/init.php';

requireRoomToken();

// Vérification du token CSRF
if (!verifyCsrfRequest()) {
    http_response_code(403);
    die('CSRF token validation failed');
}

$speech = $_POST['SPEECH'];
$roomid = $_POST['ROOMID'];

if ($speech == 'read') {
    if (file_exists('tmp/remotespeech_' . $roomid)) {
        $content = file_get_contents('tmp/remotespeech_' . $roomid);
        echo $content;
    } else {
        echo ':|:READY !:|:';
    }
} else {
    $fd = fopen('tmp/remotespeech_' . $roomid, 'w');
    fputs($fd, $speech);
    fclose($fd);
    echo 'OK !';
}
