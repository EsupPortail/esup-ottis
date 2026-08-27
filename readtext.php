<?php
require __DIR__ . '/vendor/autoload.php';

// Vérifier le room_token
requireRoomToken();

// Vérification du token CSRF
if (!verifyCsrfRequest()) {
    http_response_code(403);
    die('CSRF token validation failed');
}

// Input validation
$ligneID = validateLineId($_POST["lineid"] ?? 0);
$roomid = $_POST["ROOMID"] ?? '';
$order = validateLineId($_POST["ORDER"] ?? 0);

// Validate ROOMID
if (!validateRoomId($roomid)) {
    http_response_code(400);
    die("Invalid ROOMID");
}

$filePath = validateTmpFilePath($roomid, 'Link');
if ($filePath === false) {
    http_response_code(400);
    die("Invalid file path");
}

// Check if file exists
if (!file_exists($filePath)) {
    echo "";
    exit;
}

// Open file for reading
$fd = secureOpenTmpFile($roomid, 'Link', 'r');
if ($fd === false) {
    http_response_code(403);
    die("Access denied");
}

while($ligne=fgets($fd,4096)){
    $T=explode(";",$ligne);
    if ($T[0]*1>$ligneID*1) {
        echo $order.";".$ligne;
    }
}
fclose($fd);

?>
