<?php

namespace App\Service;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

/**
 * ChatService - Tchat de la salle : questions/messages du public
 * (tmp/LinkQuestions_{roomId}) et leur flux SSE (remplace le polling).
 * Rate limiting anti-flood par fichier JSON (tmp/ratelimit_{roomId}.json).
 */
class ChatService
{
    /** Duree maximale d'une connexion SSE avant cycle de reconnexion (s) */
    private const CHAT_STREAM_MAX_DURATION = 60;

    /** Intervalle de verification de nouveaux messages (microsecondes) */
    private const CHAT_STREAM_TICK_US = 300000;

    /** Intervalle entre les heartbeats SSE (s) */
    private const CHAT_STREAM_HEARTBEAT = 15;

    /** Padding : mod_proxy_fcgi met en tampon tant que AP_MIN_BYTES_TO_WRITE (8000 o) n'est pas atteint */
    private const CHAT_STREAM_FLUSH_PADDING_BYTES = 8200;

    /**
     * Contenu du fil de questions ; null si le fichier depasse 10 Mo (DoS)
     */
    public function readQuestions(string $roomid): ?string
    {
        $filePath = APP_ROOT . '/tmp/LinkQuestions_' . $roomid;

        if (file_exists($filePath) && is_file($filePath) && is_readable($filePath)) {
            $fileSize = filesize($filePath);
            if ($fileSize > 10 * 1024 * 1024) {
                return null;
            }
            return (string) file_get_contents($filePath);
        }

        return '';
    }

    /**
     * Enregistre un message du tchat (nettoyage, role serveur, rate limit).
     *
     * @param array{lineid: mixed, inputlanguage: mixed, text: mixed, sender_role: mixed} $fields
     * @return array{status: int, body: array<string, mixed>} Statut HTTP et corps de la reponse
     */
    public function postMessage(string $roomid, array $fields): array
    {
        // Presence des parametres requis
        foreach (['lineid', 'inputlanguage', 'text'] as $required) {
            if (!isset($fields[$required]) || !is_string($fields[$required])) {
                return ['status' => 400, 'body' => ['error' => 'Missing ' . $required]];
            }
        }

        $ligneID = mb_substr((string) $fields['lineid'], 0, 256, 'UTF-8');
        $ligneID = (string) preg_replace('/[^\p{L}\p{N}_\-:\s]/u', '', $ligneID);

        $lang = substr((string) $fields['inputlanguage'], 0, 16);
        $lang = (string) preg_replace('/[^a-zA-Z0-9_-]/', '', $lang);

        $text = (string) $fields['text'];

        $senderRole = strtolower(trim((string) ($fields['sender_role'] ?? '')));
        if (!in_array($senderRole, ['intervenant', 'auditor'], true)) {
            $senderRole = 'auditor';
        }

        // Security: Limit text length to prevent DoS (10KB max)
        if (strlen($text) > 10240) {
            $text = substr($text, 0, 10240);
        }

        // Security: Remove HTML tags to prevent XSS
        $text = strip_tags($text);

        if (!$this->isMessageAllowed($ligneID, $roomid)) {
            return ['status' => 429, 'body' => ['error' => 'Rate limit exceeded: Maximum 1 message per second and 10 messages per minute per user']];
        }

        $tmpDir = APP_ROOT . '/tmp';
        if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
            return ['status' => 500, 'body' => ['error' => 'Server configuration error']];
        }

        // Ecriture explicite du role serveur : partie du protocole du fichier
        $fd = fopen($tmpDir . '/LinkQuestions_' . $roomid, 'a');
        if ($fd) {
            fputs($fd, $ligneID . ';' . $lang . ';' . $text . ';' . $senderRole . "\n");
            fclose($fd);
            return ['status' => 200, 'body' => ['success' => true]];
        }

        return ['status' => 500, 'body' => ['error' => 'Failed to save text']];
    }

    /**
     * Rate limiting : 1 message/s et 10 messages/min par utilisateur.
     */
    public function isMessageAllowed(string $userId, string $roomid): bool
    {
        $rateLimitFile = APP_ROOT . "/tmp/ratelimit_{$roomid}.json";
        $now = time();

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

        if (!isset($rateData[$userId])) {
            $rateData[$userId] = [
                'timestamps' => [],
                'last_check' => $now
            ];
        }

        // Nettoyage des timestamps de plus d'une minute
        $rateData[$userId]['timestamps'] = array_filter(
            $rateData[$userId]['timestamps'],
            function ($ts) use ($now) {
                return ($now - $ts) < 60;
            }
        );

        $recentCount = count($rateData[$userId]['timestamps']);
        if ($recentCount >= 10) {
            return false;
        }

        // Limite par seconde (1 message)
        $recentSecond = array_filter(
            $rateData[$userId]['timestamps'],
            function ($ts) use ($now) {
                return ($now - $ts) < 1;
            }
        );
        if (count($recentSecond) >= 1) {
            return false;
        }

        $rateData[$userId]['timestamps'][] = $now;
        $rateData[$userId]['last_check'] = $now;

        file_put_contents($rateLimitFile, json_encode($rateData, JSON_PRETTY_PRINT), LOCK_EX);

        return true;
    }

    /**
     * Flux SSE du tchat : une connexion longue duree par client, fermee
     * apres CHAT_STREAM_MAX_DURATION s (le navigateur reconnecte avec le
     * Last-Event-ID, aucun message n'est perdu). La reprise se fait par
     * identifiant de ligne : $lastId > 0 ignore les lignes deja recues.
     */
    public function stream(string $roomid, int $lastId): void
    {
        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', 'off');
        ignore_user_abort(true);
        set_time_limit(self::CHAT_STREAM_MAX_DURATION + 10);

        // Fermer et vider les buffers pour que les evenements partent immediatement
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        echo "retry: 3000\n\n";
        echo ": stream open\n\n";
        $this->flushStream();
        ob_implicit_flush(true);

        $filePath = APP_ROOT . '/tmp/LinkQuestions_' . $roomid;
        $startTime = time();
        $lastHeartbeat = $startTime;
        $offset = 0;   // offset d'octets deja servi
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
                    $this->sendEvent($served, $line);
                    $served++;
                }
                fclose($fd);
            }

            if ((time() - $lastHeartbeat) >= self::CHAT_STREAM_HEARTBEAT) {
                echo ": hb\n\n";
                $this->flushStream();
                $lastHeartbeat = time();
            }

            usleep(self::CHAT_STREAM_TICK_US);
        }

        // Fin de cycle : le navigateur reconnecte seul avec Last-Event-ID
        echo "event: bye\ndata: cycle\n\n";
        flush();
    }

    /**
     * Une ligne de tchat en evenement SSE
     */
    private function sendEvent(int $index, string $line): void
    {
        $data = str_replace(["\r", "\n"], ' ', rtrim($line));
        if ($data === '') {
            return;
        }
        echo 'id: ' . $index . "\n";
        echo 'data: ' . $data . "\n\n";
        $this->flushStream();
    }

    /**
     * Vider le flux SSE vers le client. Apache (mod_proxy_fcgi) met en
     * tampon les ecritures jusqu'a AP_MIN_BYTES_TO_WRITE (8000 octets)
     * sur les connexions keepalive : padder chaque ecriture au-dessus de
     * ce seuil pour que chaque evenement parvienne immediatement.
     */
    private function flushStream(): void
    {
        echo ': ', str_pad('', self::CHAT_STREAM_FLUSH_PADDING_BYTES, '*'), "\n\n";
        flush();
    }
}
