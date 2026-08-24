<?php
require_once __DIR__ . '/Security/validate.php';
require_once __DIR__ . '/Security/validate_room_token.php';
require_once __DIR__ . '/Security/csrf.php';
requireRoomToken();

// Vérification du token CSRF
if (!verifyCsrfRequest()) {
    http_response_code(403);
    die('CSRF token validation failed');
}

// Input validation
$random = $_POST["random"] ?? '';
$roomid = $_POST["ROOMID"] ?? '';

// Validate ROOMID
if (!validateRoomId($roomid)) {
    http_response_code(400);
    die("Invalid ROOMID");
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$fileType = $_FILES['image']['type'] ?? '';
if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    die("Unauthorized file type: " . $fileType);
}

// Check file size (5MB max)
$maxSize = 5 * 1024 * 1024;
if ($_FILES['image']['size'] > $maxSize) {
    http_response_code(413);
    die("File too large");
}

// Check upload errors
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    die("Upload error: " . $_FILES['image']['error']);
}

// Validate actual MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedTypes)) {
    @unlink($_FILES['image']['tmp_name']);
    http_response_code(400);
    die("Unauthorized MIME type: " . $mime);
}

// Read file content
$fileContent = file_get_contents($_FILES['image']['tmp_name']);
if ($fileContent === false) {
    @unlink($_FILES['image']['tmp_name']);
    http_response_code(400);
    die("Unable to read file");
}

// Encode to base64
$url = "data:" . $mime . ";base64," . base64_encode($fileContent);

// Save image
$imageFile = validateTmpFilePath($roomid, 'Image');
if ($imageFile === false) {
    @unlink($_FILES['image']['tmp_name']);
    http_response_code(400);
    die("Invalid file path");
}

$fd = fopen($imageFile, "w");
if ($fd === false) {
    @unlink($_FILES['image']['tmp_name']);
    http_response_code(500);
    die("Unable to create file");
}
fputs($fd, $url);
fclose($fd);

// Save image ID
$imageIdFile = validateTmpFilePath($roomid, 'ImageID');
if ($imageIdFile !== false) {
    $fd = fopen($imageIdFile, "w");
    if ($fd !== false) {
        fputs($fd, $random);
        fclose($fd);
    }
}

// Nettoyer le fichier temporaire
@unlink($_FILES['image']['tmp_name']);

?>