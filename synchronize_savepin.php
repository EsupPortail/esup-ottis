<?php
// Security includes
require_once __DIR__ . '/Security/validate.php';
require_once __DIR__ . '/Security/validate_room_token.php';
include("Security/referer.php");

// Validate HTTP method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("");
}

// Require room token validation
requireRoomToken('roomid', 'room_token');

// Retrieve and validate pincode
$pincode = $_POST["pincode"] ?? '';
$roomid = $_POST["roomid"] ?? '';

// Validate pincode (must be numeric, 5-10 digits)
if (!preg_match('/^[0-9]{5,10}$/', $pincode)) {
    http_response_code(400);
    die("Invalid pincode format");
}

// Validate roomid
if (!validateRoomId($roomid)) {
    http_response_code(400);
    die("Invalid ROOMID");
}

// Filter pins: remove expired pins (older than 12 hours) and pins for other rooms
$valid_pin = [];
$today = gmdate("U");

// Open pincodes file for reading
$pincodesFile = __DIR__ . '/tmp/pincodes.txt';

if (file_exists($pincodesFile)) {
  $fd = fopen($pincodesFile, "r");
  if ($fd === false) {
      http_response_code(403);
      die("Cannot read pincodes file");
  }
  while($ligne = fgets($fd, 4096)) {
    $ligne = trim($ligne);
    $T = explode(";", $ligne);
    // Keep pins that are not expired (less than 12 hours old) and not for the current room
    if (count($T) >= 3 && ($today - $T[0]) < 3600*12 && $T[2] != $roomid) {
      $valid_pin[] = $ligne;
    }
  }
  fclose($fd);
}
// Add new pin
$valid_pin[] = $today.";".$pincode.";".$roomid;

// Write all valid pins back to file (use file locking to prevent race conditions)
$fd = fopen($pincodesFile, "w");
if ($fd === false) {
    http_response_code(403);
    die("Cannot write to pincodes file");
}

// Acquire exclusive lock to prevent race conditions
if (flock($fd, LOCK_EX) === false) {
    fclose($fd);
    http_response_code(500);
    die("Cannot acquire file lock");
}

for($i = 0; $i < count($valid_pin); $i++) {
  fputs($fd, $valid_pin[$i]."\n");
}
flock($fd, LOCK_UN);
fclose($fd);


?>
