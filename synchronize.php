<?php
require_once __DIR__ . '/Security/validate.php';
require_once __DIR__ . '/Security/validate_room_token.php';
include("Security/referer.php");

// Validate HTTP method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("");
}
// Vérifier le room_token (en plus du pincode pour la synchronisation)
requireRoomToken();

$pincode = $_POST["pincode"] ?? '';
$roomid = "";

// Validate pincode (must be numeric, 5-10 digits)
if (!preg_match('/^[0-9]{5,10}$/', $pincode)) {
    http_response_code(400);
    die("");
}

$pincodesFile = __DIR__ . '/tmp/pincodes.txt';

// Check if pincodes file exists
if (!file_exists($pincodesFile)) {
    http_response_code(404);
    die("");
}

$fd = fopen($pincodesFile, "r");
if ($fd === false) {
    http_response_code(403);
    die("");
}

while($ligne = fgets($fd, 4096)) {
    $T = explode(";", $ligne);
    if (count($T) >= 3 && $T[1] == $pincode) {
        // Validate roomid from pincodes file
        if (validateRoomId($T[2])) {
            $roomid = $T[2];
        }
        break;
    }
}
fclose($fd);

echo $roomid;

?>
