<?php

namespace App\Controller;

use App\Service\TokenConfigService;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

/**
 * TextController - Handles text reading and saving operations
 */
class TextController extends BaseController
{
    /** Duree maximale d'une connexion SSE du tchat (s) avant cycle de reconnexion */
    private const CHAT_STREAM_MAX_DURATION = 60;

    /** Intervalle de verification de nouveaux messages (microsecondes) */
    private const CHAT_STREAM_TICK_US = 300000;

    /** Intervalle entre les heartbeats SSE (s) */
    private const CHAT_STREAM_HEARTBEAT = 15;

    /** Padding : mod_proxy_fcgi met en tampon tant que AP_MIN_BYTES_TO_WRITE (8000 o) n'est pas atteint */
    private const CHAT_STREAM_FLUSH_PADDING_BYTES = 8200;

    /**
     * Read text content
     *
     * Reads text content from tmp/Link file based on request parameters.
     * Used by readtext.php legacy functionality.
     *
     * @return void
     */
    public function read(): void
    {
        // Validate CSRF token
        if (!\App\Utils\Csrf::verifyRequest()) {
            $this->textResponse('');
            return;
        }

        // Get and validate parameters
        $ligneID = (int)($this->postParam('lineid', 0));
        $roomid = $this->postParam('ROOMID', '');
        $order = (int)($this->postParam('ORDER', 0));

        // Validate ROOMID format
        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->textResponse('');
        }

        // Build file path
        $filePath = APP_ROOT . '/tmp/Link_' . $roomid;

        // Check if file exists
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->textResponse('');
        }

        // Open and read file
        $fd = @fopen($filePath, 'r');
        if ($fd === false) {
            $this->textResponse('');
        }

        // Read lines and return matching ones
        $output = '';
        while ($ligne = fgets($fd, 4096)) {
            $T = explode(';', $ligne);
            // phpstan-ignore-next-line - explode always returns at least one element
            if ((int)$T[0] >= $ligneID) {
                error_log('readtext - Ligne: ' . trim($ligne));
                $output .= $order . ';' . $ligne;
            }
        }
        fclose($fd);

        $this->textResponse($output);
    }

    /**
     * Read text with queue
     *
     * Reads text content from tmp/LinkQuestions file for a specific room.
     * Used by readtextQ.php legacy functionality.
     *
     * @return void
     */
    public function readQ(): void
    {
        // Get parameters
        $roomid = $this->postParam('ROOMID', '');
        $roomToken = $this->postParam('room_token', '');

        // Validate ROOMID format
        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            header('HTTP/1.1 400 Bad Request');
            $this->textResponse('Invalid ROOMID format');
        }

        // Validate room token if provided
        $roomTokenValid = false;
        if (!empty($roomToken) && !empty($roomid)) {
            $roomTokenValid = \App\Security\RoomTokenManager::validateRoomToken($roomid, $roomToken);
            error_log('[CHAT-READ] room_token received: ' . substr($roomToken, 0, 8) . "... for ROOMID=$roomid");
            error_log('[CHAT-READ] Token file exists: ' . (file_exists(APP_ROOT . '/tmp/room_token_' . basename($roomid)) ? 'YES' : 'NO'));
        }

        // If room token valid, allow access
        if ($roomTokenValid) {
            // Access granted
        }
        // Otherwise check CSRF
        elseif (!\App\Utils\Csrf::verifyRequest()) {
            $this->textResponse('');
        }

        // Build file path
        $filePath = APP_ROOT . '/tmp/LinkQuestions_' . $roomid;

        // Security: Ensure the file exists and is readable
        if (file_exists($filePath) && is_file($filePath) && is_readable($filePath)) {
            // Security: Limit file size to prevent DoS (10MB max)
            $fileSize = filesize($filePath);
            if ($fileSize > 10 * 1024 * 1024) {
                header('HTTP/1.1 413 Payload Too Large');
                $this->textResponse('File too large');
            }
            $this->textResponse(file_get_contents($filePath));
        } else {
            $this->textResponse('');
        }
    }

    /**
     * Chat SSE stream
     *
     * Server-Sent Events stream for the classroom chat (tmp/LinkQuestions file).
     * Replaces client-side polling: one persistent GET connection per client,
     * closed after CHAT_STREAM_MAX_DURATION seconds (the browser reconnects
     * automatically with the Last-Event-ID header, so no message is lost).
     *
     * Security: the room token is mandatory (GET request, no CSRF possible);
     * the PHP session lock is released immediately (session_write_close) so
     * the long-lived stream does not block the user's other requests.
     *
     * @return void
     */
    public function chatStream(): void
    {
        $roomid = $this->getParam('ROOMID', '');
        $roomToken = $this->getParam('room_token', '');

        if (!is_string($roomid) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['error' => 'Invalid ROOMID format'], 400);
            return;
        }

        if (!is_string($roomToken) || $roomToken === ''
            || !\App\Security\RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->jsonResponse(['error' => 'Room token validation failed'], 403);
            return;
        }

        // CRITICAL: release the session lock before the long-lived stream,
        // otherwise this connection blocks all other requests of the user.
        session_write_close();

        // Resume: Last-Event-ID header (browser reconnection) > last_id param
        $lastId = 0;
        $headerLastId = $_SERVER['HTTP_LAST_EVENT_ID'] ?? '';
        if (is_string($headerLastId) && preg_match('/^\d+$/', $headerLastId) === 1) {
            $lastId = (int) $headerLastId;
        } else {
            $paramLastId = $this->getParam('last_id', '0');
            if (is_string($paramLastId) && preg_match('/^\d+$/', $paramLastId) === 1) {
                $lastId = (int) $paramLastId;
            }
        }

        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', 'off');
        ignore_user_abort(true);
        set_time_limit(self::CHAT_STREAM_MAX_DURATION + 10);

        // Close and flush all output buffers so events reach the client immediately
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        echo "retry: 3000\n\n";
        echo ": stream open\n\n";
        $this->flushChatStream();
        ob_implicit_flush(true);

        $filePath = APP_ROOT . '/tmp/LinkQuestions_' . $roomid;
        $startTime = time();
        $lastHeartbeat = $startTime;
        $offset = 0;   // byte offset deja servi
        $served = 0;   // nombre de lignes deja servies (ids SSE base 0)
        $skipInitial = ($lastId > 0);

        while (!connection_aborted() && (time() - $startTime) < self::CHAT_STREAM_MAX_DURATION) {
            clearstatcache();
            $fd = @fopen($filePath, 'r');
            if ($fd !== false) {
                if ($skipInitial) {
                    // Reprendre a l'id demande (lignes deja recues par le client)
                    while ($served < $lastId) {
                        $line = fgets($fd);
                        if ($line === false) {
                            break;
                        }
                        if (trim($line) !== '') {
                            $served++;
                        }
                        $tell = ftell($fd);
                        if ($tell !== false) {
                            $offset = $tell;
                        }
                    }
                    $skipInitial = false;
                } elseif ($offset > 0) {
                    @fseek($fd, $offset);
                }
                while (($line = fgets($fd)) !== false) {
                    $tell = ftell($fd);
                    if ($tell !== false) {
                        $offset = $tell;
                    }
                    if (trim($line) === '') {
                        continue;
                    }
                    $this->sendChatStreamEvent($served, $line);
                    $served++;
                }
                fclose($fd);
            }

            if ((time() - $lastHeartbeat) >= self::CHAT_STREAM_HEARTBEAT) {
                echo ": hb\n\n";
                $this->flushChatStream();
                $lastHeartbeat = time();
            }

            usleep(self::CHAT_STREAM_TICK_US);
        }

        // Fin de cycle : le navigateur reconnecte seul avec Last-Event-ID
        echo "event: bye\ndata: cycle\n\n";
        flush();
    }

    /**
     * Send one chat line as an SSE event
     *
     * @param int $index Line index (SSE event id)
     * @param string $line Raw line from the LinkQuestions file
     * @return void
     */
    private function sendChatStreamEvent(int $index, string $line): void
    {
        $data = str_replace(["\r", "\n"], ' ', rtrim($line));
        if ($data === '') {
            return;
        }
        echo 'id: ' . $index . "\n";
        echo 'data: ' . $data . "\n\n";
        $this->flushChatStream();
    }

    /**
     * Flush the SSE stream to the client.
     *
     * Apache (mod_proxy_fcgi) buffers backend writes until AP_MIN_BYTES_TO_WRITE
     * (8000 bytes) or EOF on keepalive connections : pad each write above that
     * threshold so every event reaches the browser immediately.
     *
     * @return void
     */
    private function flushChatStream(): void
    {
        echo ': ', str_pad('', self::CHAT_STREAM_FLUSH_PADDING_BYTES, '*'), "\n\n";
        flush();
    }

    /**
     * Read transcript subtitles history
     *
     * Retrieves the history of finalized subtitle lines for a room and
     * language. Reads sys_get_temp_dir()/ottis/FinalSub_{roomId}_{lang}:
     * finalized lines only (no partials), in the requested language.
     * Used to replay the transcript history to an auditor joining
     * mid-conference, without calling the translation API.
     *
     * @return void
     */
    public function readTranscriptSub(): void
    {
        // Validate CSRF token
        \App\Utils\Csrf::verifyRequest();

        // Get and validate parameters
        $roomid = $this->postParam('ROOMID', '');
        $roomToken = $this->postParam('room_token', '');
        $lang = strtolower(substr((string) $this->postParam('lang', ''), 0, 2));

        // Validate room token
        if (!\App\Security\RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->jsonResponse(['error' => 'Token invalide.'], 403);
        }

        if (empty($roomid)) {
            $this->textResponse('ERREUR: ROOMID manquant.');
        }

        if (!preg_match('/^[a-z]{2}$/', $lang)) {
            $this->textResponse('');
        }

        $filename = sys_get_temp_dir() . '/ottis/FinalSub_' . $roomid . '_' . $lang;

        if (!file_exists($filename) || !is_readable($filename)) {
            // Aucune ligne finale publiee dans cette langue : historique vide.
            $this->textResponse('');
        }

        $content = file_get_contents($filename);
        $lines = explode("\n", trim((string) $content));
        $result = '';
        $lineNumber = 0;
        foreach ($lines as $line) {
            // Prefixe lineid (auditeur) ou C (conference) : retirer,
            // la numerotation sequentielle est refaite ici.
            $sep = strpos($line, ';');
            $text = $sep !== false ? substr($line, $sep + 1) : $line;
            if (trim($text) !== '') {
                $result .= $lineNumber . ';' . $text . "\n";
                $lineNumber++;
            }
        }

        $this->textResponse(trim($result));
    }

    /**
     * Read the last partial subtitle line
     *
     * Returns the last non-empty line of tmp/VerylastSub_{ROOMID}: the partial
     * subtitle (already in the conference subtitle language) published by the
     * conference page while the speaker is still talking. Used by the auditor
     * page to display live subtitles without waiting for the end of the line.
     *
     * @return void
     */
    public function readLastSub(): void
    {
        $roomid = (string) $this->postParam('ROOMID', '');
        $roomToken = (string) $this->postParam('room_token', '');

        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->textResponse('');
        }

        if (!\App\Security\RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->jsonResponse(['error' => 'Token invalide.'], 403);
        }

        // ExtractController::transcriptionSub ecrit les partiels dans
        // sys_get_temp_dir()/ottis/ : lire au meme endroit.
        $filename = sys_get_temp_dir() . '/ottis/VerylastSub_' . $roomid;
        if (!file_exists($filename) || !is_readable($filename)) {
            $this->textResponse('');
        }

        $last = '';
        $fd = @fopen($filename, 'r');
        if ($fd !== false) {
            // Lecture depuis la fin : le fichier grossit a chaque partiel publie
            $size = (int) filesize($filename);
            $window = 65536;
            $start = max(0, $size - $window);
            @fseek($fd, $start);
            $chunk = (string) @fread($fd, $size - $start);
            fclose($fd);
            $lines = explode("\n", $chunk);
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if ($line !== '') {
                    $last = $line;
                    break;
                }
            }
        }

        $this->textResponse($last);
    }

    /**
     * Save text content
     *
     * Validates CSRF token and saves text content to tmp/Link file.
     * Used by savetext.php legacy functionality.
     * Returns the line ID that was saved.
     *
     * @throws \RuntimeException When CSRF validation fails
     * @return void
     */
    public function roomMetadata(): void
    {
        $roomid = trim((string) ($this->getParam('ROOMID', '') ?: $this->getParam('roomId', '')));

        if ($roomid === '' || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['error' => 'ROOMID invalide'], 400);
        }

        $ownerUid = \App\Security\RoomTokenManager::getRoomOwnerUid($roomid);

        $this->jsonResponse([
            'roomId' => $roomid,
            'roomOwnerUid' => $ownerUid,
        ]);
    }

    public function save(): void
    {
        // Validate CSRF token
        \App\Utils\Csrf::verifyRequest();

        // Get and validate POST parameters
        $ligneID = (int)($this->postParam('lineid', 0));
        $text = $this->postParam('text', '');
        $lang = $this->postParam('inputlanguage', 'fr');
        $roomid = $this->postParam('ROOMID', '');
        $targetlanguage = $this->postParam('targetlanguage', '');
        $translatedtext = $this->postParam('translatedtext', '');
        $lastlines = 100000;
        if (isset($_POST['LastLines'])) {
            $lastlines = (int)$_POST['LastLines'];
        }

        // Validate ROOMID format
        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['error' => 'ROOMID invalide'], 400);
        }

        // Get max ID by reading existing file
        $ligneID = 0;
        $TLines = [];
        $filePath = APP_ROOT . '/tmp/Link_' . $roomid;

        if (file_exists($filePath)) {
            $fd = fopen($filePath, 'r');
            if ($fd) {
                while ($ligne = trim(fgets($fd, 4096))) {
                    if ($ligne != '') {
                        $TLines[] = $ligne;
                        $ligneID++;
                    }
                }
                fclose($fd);
                $lastLine = end($TLines);
                if ($lastLine !== false) {
                    $parts = explode(';', $lastLine);
                    $ligneID = (int)$parts[0] + 1;
                }
            }
        }

        // Always use extended format (5 fields): id;source_lang;source_text;target_lang;translated_text
        if (empty($targetlanguage)) {
            $targetlanguage = $lang;
        }
        if (empty($translatedtext)) {
            $translatedtext = $text;
        }

        $TLines[] = $ligneID . ';' . $lang . ';' . $text . ';' . $targetlanguage . ';' . $translatedtext;

        if (count($TLines) > $lastlines) {
            $TLines = array_slice($TLines, count($TLines) - $lastlines);
        }

        // Use file locking to prevent race conditions
        $fd = fopen($filePath, 'c+');
        if ($fd && flock($fd, LOCK_EX)) {
            ftruncate($fd, 0);
            fputs($fd, implode("\n", $TLines) . "\n");
            fflush($fd);
            flock($fd, LOCK_UN);
        }
        if ($fd) {
            fclose($fd);
        }

        $this->jsonResponse(['success' => true, 'lineid' => $ligneID, 'saved' => strlen($text)]);
    }

    /**
     * Save text with queue
     *
     * Validates room token or CSRF and saves text to tmp/LinkQuestions file.
     * Used by savetextQ.php legacy functionality.
     * Includes rate limiting (1 message/sec, 10 messages/min per user).
     *
     * @throws \RuntimeException When CSRF validation fails
     * @return void
     */
    public function saveQ(): void
    {
        // Get parameters
        $roomid = $this->postParam('ROOMID', '');
        $roomToken = $this->postParam('room_token', '');

        // Validate ROOMID format
        if (!empty($roomid) && !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['error' => 'Invalid ROOMID format'], 400);
        }

        // Validate room token if provided
        $roomTokenValid = false;
        if (!empty($roomToken) && !empty($roomid)) {
            $roomTokenValid = \App\Security\RoomTokenManager::validateRoomToken($roomid, $roomToken);
            error_log('[CHAT-SAVE] room_token received: ' . substr($roomToken, 0, 8) . "... for ROOMID=$roomid");
            error_log('[CHAT-SAVE] Token file exists: ' . (file_exists(APP_ROOT . '/tmp/room_token_' . basename($roomid)) ? 'YES' : 'NO'));
        }

        // If room token valid, allow access
        if ($roomTokenValid) {
            // Access granted
        }
        // Otherwise check CSRF
        elseif (!\App\Utils\Csrf::verifyRequest()) {
            $this->jsonResponse(['error' => 'CSRF token validation failed'], 403);
        }

        // Validate required parameters
        if (!isset($_POST['lineid'])) {
            $this->jsonResponse(['error' => 'Missing lineid'], 400);
        }

        if (!isset($_POST['inputlanguage'])) {
            $this->jsonResponse(['error' => 'Missing inputlanguage'], 400);
        }

        if (!isset($_POST['text'])) {
            $this->jsonResponse(['error' => 'Missing text'], 400);
        }

        $ligneID = substr($_POST['lineid'], 0, 256);
        $ligneID = preg_replace('/[^a-zA-Z0-9_\-:\s]/u', '', $ligneID);

        $lang = substr($_POST['inputlanguage'], 0, 16);
        $lang = preg_replace('/[^a-zA-Z0-9_-]/', '', $lang);

        $text = $_POST['text'];
        $senderRole = strtolower(trim((string)($_POST['sender_role'] ?? $_POST['role'] ?? '')));
        if (!in_array($senderRole, ['intervenant', 'auditor'], true)) {
            $senderRole = 'auditor';
        }

        // Security: Limit text length to prevent DoS (10KB max)
        if (strlen($text) > 10240) {
            $text = substr($text, 0, 10240);
        }

        // Security: Remove HTML tags to prevent XSS
        $text = strip_tags($text);

        // Apply rate limiting
        if (!$this->checkRateLimit($ligneID, $roomid)) {
            $this->jsonResponse(['error' => 'Rate limit exceeded: Maximum 1 message per second and 10 messages per minute per user'], 429);
        }

        // Ensure tmp directory exists and is writable
        $tmpDir = APP_ROOT . '/tmp';
        if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
            $this->jsonResponse(['error' => 'Server configuration error'], 500);
        }

        // Save to file with explicit server-side role metadata.
        $fd = fopen($tmpDir . '/LinkQuestions_' . $roomid, 'a');
        if ($fd) {
            fputs($fd, $ligneID . ';' . $lang . ';' . $text . ';' . $senderRole . "\n");
            fclose($fd);
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['error' => 'Failed to save text'], 500);
        }
    }

    /**
     * Check and update rate limits for a user
     *
     * @param string $userId User identifier (lineid)
     * @param string $roomid Room identifier
     * @return bool True if message is allowed, false if rate limited
     */
    protected function checkRateLimit(string $userId, string $roomid): bool
    {
        $rateLimitFile = APP_ROOT . "/tmp/ratelimit_{$roomid}.json";
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
            function ($ts) use ($now) {
                return ($now - $ts) < 60;
            }
        );

        // Check rate limits
        $recentCount = count($rateData[$userId]['timestamps']);

        // Check per-minute limit (10 messages)
        if ($recentCount >= 10) {
            return false;
        }

        // Check per-second limit (1 message)
        $recentSecond = array_filter(
            $rateData[$userId]['timestamps'],
            function ($ts) use ($now) {
                return ($now - $ts) < 1;
            }
        );

        if (count($recentSecond) >= 1) {
            return false;
        }

        // Add current timestamp
        $rateData[$userId]['timestamps'][] = $now;
        $rateData[$userId]['last_check'] = $now;

        // Save updated rate data
        file_put_contents($rateLimitFile, json_encode($rateData, JSON_PRETTY_PRINT), LOCK_EX);

        return true;
    }

    /**
     * Read the VO transcript history
     *
     * Returns the finalized lines of tmp/Link_{ROOMID} with their lineid
     * and source text: the voice-original transcript of the room. Used to
     * replay the VO history to an auditor joining mid-conference.
     *
     * @return void
     */
    public function readVO(): void
    {
        $roomid = $this->postParam('ROOMID', '');
        $roomToken = $this->postParam('room_token', '');
        $order = $this->postParam('ORDER', 0);

        // Vérification du room_token
        if (!\App\Security\RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->jsonResponse(['error' => 'Token invalide.'], 403);
        }

        if (empty($roomid)) {
            $this->jsonResponse(['error' => 'ROOMID manquant.'], 400);
        }

        $filename = APP_ROOT . '/tmp/Link_' . $roomid;

        if (!file_exists($filename) || !is_readable($filename)) {
            $this->textResponse('');
        }

        $content = file_get_contents($filename);

        // Renvoyer le texte source de chaque finale, precede du lineid
        $lines = explode("\n", trim((string) $content));
        $result = '';
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $T = explode(';', $line);
            if (count($T) > 3) {
                // Format Link_ : id;lang;texte;lang_cible;traduction
                $result .= $T[0] . ';' . $T[2] . "\n";
            }
        }

        $this->textResponse(trim($result));
    }

    /**
     * Translate text using various translation services
     *
     * Supports DeepL Pro, DeepL Free, and LibreTranslate APIs.
     * Used by Translate.php legacy functionality.
     *
     * @return void
     */
    public function translate(): void
    {
        error_log('[TextController.translate] START - POST params: ' . json_encode($_POST));
        error_log('[TextController.translate] CSRF valid: ' . var_export(\App\Utils\Csrf::verifyRequest(), true));

        // Validate CSRF token
        if (!\App\Utils\Csrf::verifyRequest()) {
            error_log('[TextController.translate] CSRF FAILED');
            $this->jsonResponse(['error' => true, 'message' => 'CSRF token validation failed'], 403);
            return;
        }

        // Get and sanitize parameters
        $texte = $this->postParam('texte', '');
        $lang = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->postParam('lang', ''));
        $langinput = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->postParam('langinput', ''));
        $roomId = $this->postParam('ROOMID', 'UNKNOWN');
        $lineid = $this->postParam('lineid', 'NONE');
        $forcelibre = (bool)$this->postParam('forcelibre', false);
        $WhichTranslator = $this->postParam('whichtranslator', null);

        // Load API key from TokenConfigService
        $apikey = '';
        $tokenConfig = null;
        try {
            $tokenConfig = TokenConfigService::create();
            error_log('TextController: TokenConfigService created');
            if ($WhichTranslator === 'DEEPLPRO' || $WhichTranslator === 'DEEPL' || empty($WhichTranslator)) {
                $apikey = $tokenConfig->getDeepLProApiToken();
                error_log('TextController: Loaded DeepL Pro API key (length: ' . strlen($apikey) . ', empty: ' . var_export(empty($apikey), true) . ')');
            } elseif ($WhichTranslator === 'DEEPLFREE') {
                $apikey = $tokenConfig->getDeepLFreeApiToken();
                error_log('TextController: Loaded DeepL Free API key (length: ' . strlen($apikey) . ', empty: ' . var_export(empty($apikey), true) . ')');
            }
        } catch (\Exception $e) {
            error_log('TextController: Failed to load API key: ' . $e->getMessage());
        }

        // Set default translator if not provided
        if (empty($WhichTranslator)) {
            if ($tokenConfig !== null) {
                $WhichTranslator = $tokenConfig->getWhichTranslator();
            }
            if (empty($WhichTranslator)) {
                $WhichTranslator = 'DEEPLPRO';
                // Try to load key for default translator
                // phpstan-ignore-next-line
                if (!empty($tokenConfig) && empty($apikey)) {
                    // phpstan-ignore-next-line
                    $apikey = $tokenConfig->getDeepLProApiToken();
                }
            }
        }

        // Limit text length to prevent DoS
        if (strlen($texte) > 10240) {
            $texte = substr($texte, 0, 10240);
        }

        // Sanitize text
        $texte = strip_tags($texte);
        $texte = htmlspecialchars($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
        $texte = html_entity_decode($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Validate ROOMID
        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomId)) {
            $roomId = 'UNKNOWN';
        }

        // Validate lineid
        if (!preg_match('/^[a-zA-Z0-9_-]{0,64}$/', $lineid)) {
            $lineid = 'NONE';
        }

        // Set default translator
        if (empty($WhichTranslator)) {
            // Try to get from TokenConfigService first
            if ($tokenConfig !== null) {
                $WhichTranslator = $tokenConfig->getWhichTranslator();
            }
            if (empty($WhichTranslator)) {
                $WhichTranslator = 'DEEPLPRO';
            }
        }

        if ($forcelibre) {
            $WhichTranslator = 'LIBRETRANSLATE';
        }

        // Prepare response
        $response = [
            'translator' => $WhichTranslator,
            'roomId' => $roomId,
            'key' => '',
            'data' => []
        ];

        $result = '';

        // Try to read from existing translation files
        if ($roomId != 'UNKNOWN') {
            $result = $this->readFromTranslationFiles($roomId, $lineid, $lang, $texte);
        }

        // If not found in files, call translation API
        if (empty($result)) {
            // Verify API key exists before calling translation API
            if (empty($apikey)) {
                error_log('TextController: ERROR - No API key available for translator: ' . $WhichTranslator);
                $this->jsonResponse(['error' => true, 'message' => 'No API key available for translator: ' . $WhichTranslator], 400);
                return;
            }
            $result = $this->callTranslationAPI($texte, $langinput, $lang, $WhichTranslator, $apikey, $lineid, $roomId);
        } else {
            $result = str_replace('"translatedText":"', '"translatedText":"', $result);
        }

        // Add result to response
        if (!empty($result)) {
            $resultData = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Check if this is an error response from the API
                if (isset($resultData['error']) && $resultData['error'] === true) {
                    $response['error'] = true;
                    $response['message'] = $resultData['message'] ?? 'Translation failed';
                    $response['data'] = [];
                } elseif (isset($resultData['message']) && !isset($resultData['translations'])) {
                    // DeepL API error response (e.g., invalid auth key)
                    $response['error'] = true;
                    $response['message'] = $resultData['message'] ?? 'Translation API error';
                    $response['data'] = [];
                } else {
                    $response['data'] = $resultData;
                }
            } else {
                $response['data'] = ['translations' => [['text' => $result]]];
            }
        } else {
            // No result from cache or API - translation failed
            $response['error'] = true;
            $response['message'] = 'No translation available';
            $response['data'] = [];
        }

        $this->jsonResponse($response, 200);
    }

    /**
     * Read from the per-language translation cache files
     *
     * Partial (lineid DIRECT): reads PartialSub_{roomId}_{lang}, keyed by
     * md5 hash of the source text. Final (numeric lineid): reads
     * FinalSub_{roomId}_{lang}, keyed by lineid. Both files live in
     * sys_get_temp_dir()/ottis/ and are shared by all clients of the
     * same language to avoid redundant translation API calls.
     *
     * @param string $roomId Room identifier
     * @param string $lineid Line identifier ('DIRECT' for partial lines)
     * @param string $lang Target language
     * @param string $texte Source text (hash key for the partial cache)
     * @return string JSON translation response or empty string on cache miss
     */
    protected function readFromTranslationFiles(string $roomId, string $lineid, string $lang, string $texte = ''): string
    {
        $result = '';
        $lang = strtolower(substr($lang, 0, 2));
        $ottisDir = sys_get_temp_dir() . '/ottis/';

        if ($lineid == 'DIRECT') {
            // Partiel courant : cache par langue, cle = empreinte du texte source
            $filename = $ottisDir . 'PartialSub_' . $roomId . '_' . $lang;
            if (is_readable($filename)) {
                $content = file_get_contents($filename);
                if ($content !== false) {
                    $sep = strpos($content, ';');
                    if ($sep !== false && substr($content, 0, $sep) === md5(trim($texte))) {
                        $result = substr($content, $sep + 1);
                    }
                }
            }
        } else {
            // Finales : cache par langue, cle = lineid numerique
            $filename = $ottisDir . 'FinalSub_' . $roomId . '_' . $lang;
            if (is_readable($filename) && $lineid != 'NONE' && $lineid != '') {
                $lines = file($filename, FILE_IGNORE_NEW_LINES);
                foreach ($lines as $line) {
                    $sep = strpos($line, ';');
                    $prefix = $sep !== false ? substr($line, 0, $sep) : '';
                    if ($sep !== false && ctype_digit($prefix) && $prefix === (string) $lineid) {
                        $result = substr($line, $sep + 1);
                    }
                }
            }
        }

        if ($result !== '') {
            if ($lang == 'en') {
                $result = $this->removeAccents($result);
            }
            // Meme forme de reponse que l'API, attendue par translate() et les clients
            $result = json_encode(['translations' => [['text' => $result]]], JSON_UNESCAPED_UNICODE);
        }

        return $result;
    }

    /**
     * Call external translation API
     *
     * @param string $text Text to translate
     * @param string $sourceLang Source language
     * @param string $targetLang Target language
     * @param string $translator Service to use (DEEPLPRO, DEEPLFREE, LIBRETRANSLATE)
     * @param string $apikey API key
     * @param string $lineid Line identifier
     * @param string $roomId Room identifier
     * @return string JSON response from API
     */
    protected function callTranslationAPI(string $text, string $sourceLang, string $targetLang, string $translator, string $apikey, string $lineid, string $roomId): string
    {
        $data = [
            'text' => [$text],
            'source_lang' => strtoupper($sourceLang),
            'target_lang' => strtoupper($targetLang)
        ];
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);

        switch ($translator) {
            case 'DEEPLPRO':
            case 'DEEPL':
                $headers = [
                    'Authorization: DeepL-Auth-Key ' . $apikey,
                    'Content-Type: application/json'
                ];
                $result = $this->httpPost('https://api.deepl.com/v2/translate', $payload, $headers);
                break;

            case 'DEEPLFREE':
                $url = 'https://api-free.deepl.com/v2/translate';
                $formData = http_build_query([
                    'auth_key' => $apikey,
                    'text' => $text,
                    'source_lang' => strtoupper($sourceLang),
                    'target_lang' => strtoupper($targetLang)
                ]);
                $headers = ['Content-Type: application/x-www-form-urlencoded'];
                $result = $this->httpPost($url, $formData, $headers);

                if ($result !== false) {
                    $decoded = json_decode($result, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['translations'])) {
                        $result = json_encode(['data' => ['translations' => $decoded['translations']]], JSON_UNESCAPED_UNICODE);
                    }
                }
                break;

            case 'LIBRETRANSLATE':
            default:
                $tokenConfig = TokenConfigService::create();
                $url = rtrim($tokenConfig->getLibreTranslateServer(), '/') . '/translate';
                $formData = http_build_query([
                    'q' => $text,
                    'format' => 'text',
                    'source' => strtolower($sourceLang),
                    'target' => strtolower($targetLang)
                ]);
                $headers = ['Content-Type: application/x-www-form-urlencoded'];
                $result = $this->httpPost($url, $formData, $headers);

                if ($result !== false) {
                    $decoded = json_decode($result, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['translatedText'])) {
                        $result = json_encode(['data' => ['translations' => [['text' => $decoded['translatedText']]]]], JSON_UNESCAPED_UNICODE);
                    }
                }
                break;
        }

        // Cache par langue : finale (lineid numerique) -> FinalSub,
        // partiel (lineid DIRECT) -> PartialSub
        if ($result !== false && $roomId != 'UNKNOWN' && $lineid != 'NONE' && $lineid != '') {
            $translatedText = $this->extractTranslatedText($result);
            if ($translatedText !== '') {
                $ottisDir = sys_get_temp_dir() . '/ottis/';
                if (!is_dir($ottisDir)) {
                    @mkdir($ottisDir, 0777, true);
                }
                $cacheLang = strtolower(substr($targetLang, 0, 2));
                if ($lineid === 'DIRECT') {
                    $filename = $ottisDir . 'PartialSub_' . $roomId . '_' . $cacheLang;
                    @file_put_contents($filename, md5(trim($text)) . ';' . $translatedText);
                } else {
                    $filename = $ottisDir . 'FinalSub_' . $roomId . '_' . $cacheLang;
                    @file_put_contents($filename, $lineid . ';' . $translatedText . "\n", FILE_APPEND);
                }
            }
        }

        return $result ?: json_encode(['error' => true, 'message' => 'Translation failed']);
    }

    /**
     * Extract the translated text from a translation API response
     *
     * Handles DeepL Pro JSON, wrapped DeepL Free / LibreTranslate
     * responses, and raw LibreTranslate responses.
     *
     * @param string $result JSON response from the translation API
     * @return string Translated text or empty string
     */
    protected function extractTranslatedText(string $result): string
    {
        $decoded = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }
        if (isset($decoded['translations'][0]['text'])) {
            return (string) $decoded['translations'][0]['text'];
        }
        if (isset($decoded['data']['translations'][0]['text'])) {
            return (string) $decoded['data']['translations'][0]['text'];
        }
        if (isset($decoded['translatedText'])) {
            return (string) $decoded['translatedText'];
        }
        return '';
    }

    /**
     * Make HTTP POST request
     *
     * @param string $url Target URL
     * @param string $payload Request body
     * @param array $headers Request headers
     * @return string|false Response or false on failure
     */
    protected function httpPost(string $url, string $payload, array $headers): string|false
    {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($result === false || $httpCode < 200 || $httpCode >= 300) {
                return false;
            }
            return $result;
        }

        if (!ini_get('allow_url_fopen')) {
            return false;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $payload,
                'timeout' => 15,
                'ignore_errors' => true,
            ]
        ]);

        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            return false;
        }

        return $result;
    }

    /**
     * Remove accents from text
     *
     * @param string $str Text to process
     * @return string Text without accents
     */
    protected function removeAccents(string $str): string
    {
        $a = ['À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß'];
        $b = ['A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s'];
        return str_replace($a, $b, $str);
    }
}
