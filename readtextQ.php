<?php 

//include("Security/referer.php");

// Security Phase 1: Input validation and XSS prevention
// Validate ROOMID parameter using regex (alphanumeric, underscore, hyphen only)
if (!isset($_POST["ROOMID"]) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $_POST["ROOMID"])) {
    header('HTTP/1.1 400 Bad Request');
    echo "Access denied";
    exit;
}

$roomid = $_POST["ROOMID"];

// Security: Ensure the file exists and is readable before accessing
$filePath = "tmp/LinkQuestions_" . $roomid;

if (file_exists($filePath) && is_file($filePath) && is_readable($filePath)) {
    // Security: Limit file size to prevent DoS (10MB max)
    $fileSize = filesize($filePath);
    if ($fileSize > 10 * 1024 * 1024) {
        header('HTTP/1.1 413 Payload Too Large');
        echo "File too large";
        exit;
    }
    echo file_get_contents($filePath);
} else {
    echo "";
}

?>
