<?php
require_once __DIR__ . '/Security/validate.php';
require_once __DIR__ . '/Security/room_tokens.php';
require_once __DIR__ . '/Security/csrf.php';

// Vérification du token CSRF
if (!verifyCsrfRequest()) {
    http_response_code(403);
    die('CSRF token validation failed');
}

// Security Phase 1: Input validation and XSS prevention

// Validate ROOMID parameter using regex (alphanumeric, underscore, hyphen only)
if (!isset($_POST["ROOMID"]) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $_POST["ROOMID"])) {
    header('HTTP/1.1 400 Bad Request');
    echo "Access denied";
    exit;
}

$roomid = $_POST["ROOMID"];

// Vérifier le room_token pour l'auditeur (multi-onglets)
$roomToken = $_POST["room_token"] ?? '';
error_log("[CHAT-SAVE] room_token received: " . substr($roomToken, 0, 8) . "... for ROOMID=$roomid");
error_log("[CHAT-SAVE] Token file exists: " . (file_exists(__DIR__ . '/tmp/room_token_' . basename($roomid)) ? "YES" : "NO"));
if (!empty($roomToken)) {
    if (!validateRoomToken($roomid, $roomToken)) {
        header('HTTP/1.1 403 Forbidden');
        echo "Invalid room token";
        exit;
    }
}

// Validate and sanitize lineid (limit length and allow only safe characters)
if (!isset($_POST["lineid"])) {
    header('HTTP/1.1 400 Bad Request');
    echo "Missing lineid";
    exit;
}
$ligneID = substr($_POST["lineid"], 0, 256);
$ligneID = preg_replace('/[^a-zA-Z0-9_\-:\s]/u', '', $ligneID);

// Validate and sanitize language (limit length and allow only safe characters)
if (!isset($_POST["inputlanguage"])) {
    header('HTTP/1.1 400 Bad Request');
    echo "Missing inputlanguage";
    exit;
}
$lang = substr($_POST["inputlanguage"], 0, 16);
$lang = preg_replace('/[^a-zA-Z0-9_-]/', '', $lang);

// Validate and sanitize text (prevent XSS and limit length)
if (!isset($_POST["text"])) {
    header('HTTP/1.1 400 Bad Request');
    echo "Missing text";
    exit;
}
$text = $_POST["text"];

// Security: Limit text length to prevent DoS (10KB max)
if (strlen($text) > 10240) {
    $text = substr($text, 0, 10240);
}

// Security: Remove HTML tags and encode special characters to prevent XSS
$text = strip_tags($text);
//$text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);

// Security: Ensure the directory exists and is writable
$tmpDir = "tmp";
if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "Server configuration error";
    exit;
}

// Security Phase 5: Rate Limiting (Phase 5.2)
// Max 1 message per second per user, Max 10 messages per minute per user

// Rate limit configuration
define('MAX_MESSAGES_PER_SECOND', 1);
define('MAX_MESSAGES_PER_MINUTE', 10);
define('RATE_LIMIT_WINDOW_SECOND', 1);
define('RATE_LIMIT_WINDOW_MINUTE', 60);

/**
 * Check and update rate limits for a user
 * @param string $userId User identifier (lineid)
 * @param string $roomid Room identifier
 * @return bool True if message is allowed, false if rate limited
 */
function checkRateLimit($userId, $roomid) {
    $rateLimitFile = "tmp/ratelimit_{$roomid}.json";
    $now = time();
    
    // Initialize or load rate limit data
    $rateData = [];
    if (file_exists($rateLimitFile)) {
        $json = file_get_contents($rateLimitFile);
        if ($json !== false) {
            $rateData = json_decode($json, true);
            if (!is_array($rateData)) {
                $rateData = [];
            }
        }
    }
    
    // Initialize user entry if not exists
    if (!isset($rateData[$userId])) {
        $rateData[$userId] = [
            'timestamps' => [],
            'last_check' => $now
        ];
    }
    
    // Clean up old timestamps (older than 1 minute)
    $rateData[$userId]['timestamps'] = array_filter(
        $rateData[$userId]['timestamps'],
        function($ts) use ($now) {
            return ($now - $ts) < RATE_LIMIT_WINDOW_MINUTE;
        }
    );
    
    // Check rate limits
    $recentCount = count($rateData[$userId]['timestamps']);
    
    // Check per-minute limit
    if ($recentCount >= MAX_MESSAGES_PER_MINUTE) {
        return false;
    }
    
    // Check per-second limit
    $recentSecond = array_filter(
        $rateData[$userId]['timestamps'],
        function($ts) use ($now) {
            return ($now - $ts) < RATE_LIMIT_WINDOW_SECOND;
        }
    );
    
    if (count($recentSecond) >= MAX_MESSAGES_PER_SECOND) {
        return false;
    }
    
    // Add current timestamp
    $rateData[$userId]['timestamps'][] = $now;
    $rateData[$userId]['last_check'] = $now;
    
    // Save updated rate data
    file_put_contents($rateLimitFile, json_encode($rateData, JSON_PRETTY_PRINT), LOCK_EX);
    
    return true;
}

// Apply rate limiting
if (!checkRateLimit($ligneID, $roomid)) {
    header('HTTP/1.1 429 Too Many Requests');
    echo "Rate limit exceeded: Maximum " . MAX_MESSAGES_PER_SECOND . " message(s) per second and " .
         MAX_MESSAGES_PER_MINUTE . " message(s) per minute per user";
    exit;
}

$fd=fopen("tmp/LinkQuestions_".$roomid,"a");
fputs($fd,$ligneID.";".$lang.";".$text."\n");
fclose($fd);

?>
