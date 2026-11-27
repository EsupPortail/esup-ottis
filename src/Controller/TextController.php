<?php

namespace App\Controller;

use App\Security\RoomTokenManager;
use App\Service\ChatService;
use App\Service\NotebookService;
use App\Service\Router;
use App\Service\TranscriptService;
use App\Service\TranslationService;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

/**
 * TextController - Endpoints texte de la salle : transcript,
 * tchat (dont flux SSE), sous-titres et traduction.
 *
 * Controleur mince : validation des parametres, autorisations (room
 * token) et emission des reponses. La logique est portee par les
 * services NotebookService, ChatService, TranscriptService et
 * TranslationService.
 */
class TextController extends BaseController
{
    private NotebookService $notebook;
    private ChatService $chat;
    private TranscriptService $transcript;
    private TranslationService $translation;

    public function __construct(Router $router)
    {
        parent::__construct($router);
        $this->notebook = new NotebookService();
        $this->chat = new ChatService();
        $this->transcript = new TranscriptService();
        $this->translation = new TranslationService();
    }

    /**
     * Lignes du transcript (readtext.php legacy)
     */
    public function read(): void
    {
        $ligneID = (int) ($this->postParam('lineid', 0));
        $roomid = (string) ($this->postParam('ROOMID', ''));
        $order = (int) ($this->postParam('ORDER', 0));

        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->textResponse('');
        }

        $this->textResponse($this->notebook->readLines($roomid, $ligneID, $order));
    }

    /**
     * Fil de questions du tchat (readtextQ.php legacy)
     */
    public function readQ(): void
    {
        $roomid = (string) ($this->postParam('ROOMID', ''));

        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            header('HTTP/1.1 400 Bad Request');
            $this->textResponse('Invalid ROOMID format');
        }

        $content = $this->chat->readQuestions($roomid);

        if ($content === null) {
            header('HTTP/1.1 413 Payload Too Large');
            $this->textResponse('File too large');
        }

        $this->textResponse($content);
    }

    /**
     * Flux SSE du tchat : une connexion longue duree par client. Le token
     * de salle est obligatoire (GET : CSRF impossible) et la session est
     * liberee immediatement pour ne pas bloquer les autres requetes.
     */
    public function chatStream(): void
    {
        $roomid = $this->getParam('ROOMID', '');
        $roomToken = $this->getParam('room_token', '');

        if (!is_string($roomid) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['error' => 'Invalid ROOMID format'], 400);
            return;
        }

        $roomToken = is_string($roomToken) ? $roomToken : '';
        if ($roomToken === '' || !RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->jsonResponse(['error' => 'Room token validation failed'], 403);
            return;
        }

        // CRITICAL: liberer le verrou de session avant le flux SSE, sinon
        // cette connexion bloque toutes les autres requetes de l'utilisateur
        session_write_close();

        // Reprise : Last-Event-ID (reconnexion navigateur) > last_id
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

        $this->chat->stream($roomid, $lastId);
    }

    /**
     * Historique des sous-titres finalises (rejeu aux auditeurs arrivant
     * en cours de conference, sans appel API)
     */
    public function readTranscriptSub(): void
    {
        $roomid = (string) ($this->postParam('ROOMID', ''));
        $roomToken = (string) ($this->postParam('room_token', ''));
        $lang = strtolower(substr((string) $this->postParam('lang', ''), 0, 2));

        if (!RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->jsonResponse(['error' => 'Token invalide.'], 403);
        }

        if ($roomid === '') {
            $this->textResponse('ERREUR: ROOMID manquant.');
        }

        if (!preg_match('/^[a-z]{2}$/', $lang)) {
            $this->textResponse('');
        }

        $this->textResponse($this->transcript->readFinalLines($roomid, $lang));
    }

    /**
     * Derniere ligne partielle publiee (sous-titre en cours)
     */
    public function readLastSub(): void
    {
        $roomid = (string) ($this->postParam('ROOMID', ''));
        $roomToken = (string) ($this->postParam('room_token', ''));

        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->textResponse('');
        }

        if (!RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->jsonResponse(['error' => 'Token invalide.'], 403);
        }

        $this->textResponse($this->transcript->readLastPartial($roomid));
    }

    /**
     * Proprietaire d'une salle
     */
    public function roomMetadata(): void
    {
        $roomid = trim((string) ($this->getParam('ROOMID', '') ?: $this->getParam('roomId', '')));

        if ($roomid === '' || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['error' => 'ROOMID invalide'], 400);
        }

        $ownerUid = RoomTokenManager::getRoomOwnerUid($roomid);

        $this->jsonResponse([
            'roomId' => $roomid,
            'roomOwnerUid' => $ownerUid,
        ]);
    }

    /**
     * Ajout d'une ligne au transcript (savetext.php legacy)
     */
    public function save(): void
    {
        $text = (string) ($this->postParam('text', ''));
        $lang = (string) ($this->postParam('inputlanguage', 'fr'));
        $roomid = (string) ($this->postParam('ROOMID', ''));
        $targetlanguage = (string) ($this->postParam('targetlanguage', ''));
        $translatedtext = (string) ($this->postParam('translatedtext', ''));
        $lastlines = isset($_POST['LastLines']) ? (int) $_POST['LastLines'] : 100000;

        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['error' => 'ROOMID invalide'], 400);
        }

        $result = $this->notebook->saveLine($roomid, $lang, $text, $targetlanguage, $translatedtext, $lastlines);

        $this->jsonResponse(['success' => true, 'lineid' => $result['lineid'], 'saved' => $result['saved']]);
    }

    /**
     * Ajout d'un message au tchat (savetextQ.php legacy)
     */
    public function saveQ(): void
    {
        $roomid = (string) ($this->postParam('ROOMID', ''));

        if ($roomid !== '' && !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['error' => 'Invalid ROOMID format'], 400);
        }

        $result = $this->chat->postMessage($roomid, [
            'lineid' => $_POST['lineid'] ?? null,
            'inputlanguage' => $_POST['inputlanguage'] ?? null,
            'text' => $_POST['text'] ?? null,
            'sender_role' => $_POST['sender_role'] ?? $_POST['role'] ?? null,
        ]);

        $this->jsonResponse($result['body'], $result['status']);
    }

    /**
     * Historique VO de la salle (rejeu aux auditeurs)
     */
    public function readVO(): void
    {
        $roomid = (string) ($this->postParam('ROOMID', ''));
        $roomToken = (string) ($this->postParam('room_token', ''));

        if (!RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->jsonResponse(['error' => 'Token invalide.'], 403);
        }

        if ($roomid === '') {
            $this->jsonResponse(['error' => 'ROOMID manquant.'], 400);
        }

        $this->textResponse($this->notebook->readVoiceOriginal($roomid));
    }

    /**
     * Traduction d'un texte (Translate.php legacy)
     */
    public function translate(): void
    {
        session_write_close();

        $result = $this->translation->translate([
            'texte' => $this->postParam('texte', ''),
            'lang' => $this->postParam('lang', ''),
            'langinput' => $this->postParam('langinput', ''),
            'roomId' => $this->postParam('ROOMID', 'UNKNOWN'),
            'lineid' => $this->postParam('lineid', 'NONE'),
            'forcelibre' => (bool) $this->postParam('forcelibre', false),
            'whichtranslator' => $this->postParam('whichtranslator', null),
        ]);

        $this->jsonResponse($result['body'], $result['status']);
    }
}
