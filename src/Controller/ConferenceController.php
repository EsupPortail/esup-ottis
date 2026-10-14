<?php

namespace App\Controller;

use App\Security\RoomTokenManager;
use App\Service\LanguageLoader;
use App\Service\RoomInfoService;
use App\Service\RoomPresence;
use App\Service\TokenConfigService;
use App\Service\TranslatorStatsService;

/**
 * ConferenceController - Handles conference room creation and management
 *
 * This controller manages the main conference interface where teachers
 * can create and manage translation sessions.
 */
class ConferenceController extends BaseController
{
    /**
     * Display the conference index page
     *
     * Initializes a new conference room or loads an existing one.
     * Handles CSRF protection, language selection, and room configuration.
     * Sets up all necessary data for the conference view including:
     * - Room ID and token generation
     * - User identification
     * - Supported languages loading
     * - DeepL API statistics
     * - Wooclap integration
     *
     * @return void
     */
    public function index(): void
    {
        // Load chat component functions
        require_once __DIR__ . '/../../includes/chat-component.php';

        // Set session marker
        $_SESSION['from'] = 'teacher';

        // Get language from request
        $language = $this->getParam('lang', 'fr');

        // Load config values from TokenConfigService
        $tokenConfig = TokenConfigService::create();
        $WhichTranslator = $tokenConfig->getWhichTranslator();

        // Load translations using parent method
        $this->viewData['language'] = $language;
        $this->loadTranslations();

        // Set page metadata
        $this->viewData['pageTitle'] = 'OTTIS - Conference';

        // Define styles for this page
        $this->viewData['pageStyles'] = [
            'styles/auditor.css',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
        ];

        // Define scripts for this page
        $this->viewData['pageScripts'] = [
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js'
        ];

        // Get or generate classroom ID
        // Identification de l'intervenant d'abord : necessaire pour retrouver sa salle
        $uid = $_SESSION['cas_user'] ?? RoomInfoService::getUserId('anonymous');
        $uid = is_string($uid) && trim($uid) !== '' ? trim($uid) : 'anonymous';
        $classroomid = $this->getParam('ROOMID');
        if (empty($classroomid)) {
            // Retour d'un intervenant (ex. reconnexion CAS) : reutiliser sa salle
            // la plus recente pour ne pas perdre la conference et les auditeurs
            $classroomid = RoomTokenManager::findRecentRoomByOwner($uid)
                ?? md5(gmdate('Y-m-d\TH:i:s\Z'));
        }

        // Use services to load data
        $T_lang = LanguageLoader::load();
        $roomToken = RoomTokenManager::generateRoomToken($classroomid, $uid);
        RoomTokenManager::setRoomOwnerUid($classroomid, $uid);
        RoomPresence::heartbeat($classroomid);
        $appid = RoomInfoService::generateAppId();
        $uid = trim($uid) !== '' ? trim($uid) : 'anonymous';
        $casAttributes = $_SESSION['cas_attributes'] ?? [];
        $givenName = trim((string) ($casAttributes['givenName'] ?? ''));
        $sn = trim((string) ($casAttributes['sn'] ?? ''));
        $currentUserDisplayName = trim($givenName . ' ' . $sn);
        if ($currentUserDisplayName === '') {
            $currentUserDisplayName = $uid;
        }
        $ip = RoomInfoService::getClientIp();

        // Get DeepL statistics
        $statsService = new TranslatorStatsService($tokenConfig->getProxy());
        try {
            $statsResult = $statsService->getTranslatorStats($WhichTranslator, $tokenConfig->getDeepLProApiToken() ?: $tokenConfig->getDeepLFreeApiToken());
            $Stats_DEEPL = $statsResult['stats'] ?? [];
            $percentage_DEEPL = $statsResult['percentage_used'] ?? 0;
        } catch (\Throwable $e) {
            $Stats_DEEPL = [];
            $percentage_DEEPL = 0;
        }

        // Get configuration parameter
        $currentconfig = $this->getParam('myconfig', 'AAYAYKCNYCNYNYNNN');

        // Get Wooclap address
        if (isset($_GET['WOOCLAPKEY'])) {
            $wooclapaddress = 'https://app.wooclap.com/events/' . $_GET['WOOCLAPKEY'] . '/0';
        } else {
            $wooclapaddress = 'https://app.wooclap.com/events/';
        }

        // Prepare JavaScript variables
        $jsVars = [
            'supportedlanguages' => $T_lang,
            'currentconfig' => $currentconfig,
            'roomToken' => $roomToken,
            'intervenantUid' => $uid
        ];

        // Set all variables for the view
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['appid'] = $appid;
        $this->viewData['uid'] = $uid;
        $this->viewData['currentUserDisplayName'] = $currentUserDisplayName;
        $this->viewData['T_lang'] = $T_lang;
        $this->viewData['Stats_DEEPL'] = $Stats_DEEPL;
        $this->viewData['percentage_DEEPL'] = $percentage_DEEPL;
        $this->viewData['currentconfig'] = $currentconfig;
        $this->viewData['wooclapaddress'] = $wooclapaddress;
        $this->viewData['intervenantUid'] = $uid;
        $this->viewData['jsVars'] = $jsVars;
        $this->viewData['roomToken'] = $roomToken;
        $this->viewData['ip'] = $ip;

        // Rediriger les anciennes URLs avec ?tab= vers le format hash-based SPA
        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
            $validTabs = ['outil', 'langues', 'partage', 'transcription'];
            if (in_array($tab, $validTabs)) {
                header('Location: /conference#' . $tab);
                exit;
            }
        }

        $this->viewData['isConferencePage'] = true;

        // Tous les scripts sont charges via le graphe d'imports de conference-init.js.
        // Une seule balise d'entree garantit une instance unique par module ES :
        // le cache-busting (?v=) ne doit jamais s'appliquer a une URL importee,
        // sinon la deduplication des modules par URL est cassee (double evaluation,
        // double QR code, ecouteurs dupliques).
        $afterContentScripts = '';
        $afterContentScripts .= '<script type="module" src="/js/conference-init.js"></script>';

        // Script de test pour localhost
        if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) {
            $afterContentScripts .= '<script src="/js/tests/no-jquery-validation.js"></script>';
        }

        $this->viewData['afterContentScripts'] = $afterContentScripts;

        $this->render('conference/index');
    }

    /**
     * Heartbeat periodique envoye par la page conference :
     * signale que la page est ouverte (conference "en direct").
     */
    public function heartbeat(): void
    {
        $roomid = (string) $this->postParam('ROOMID', '');
        $roomToken = (string) $this->postParam('room_token', '');

        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)
            || !RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->textResponse('Invalid room', 403);
        }

        RoomPresence::heartbeat($roomid);
        // Activite recente sur la salle : maintient la fenetre de recuperation
        RoomTokenManager::touchRoomToken($roomid);
        $this->jsonResponse(['ok' => true]);
    }

    /**
     * Signal de fermeture envoye par la page conference (sendBeacon) :
     * marque la conference comme fermee pour les auditeurs.
     */
    public function close(): void
    {
        $roomid = (string) $this->postParam('ROOMID', '');
        $roomToken = (string) $this->postParam('room_token', '');

        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)
            || !RoomTokenManager::validateRoomToken($roomid, $roomToken)) {
            $this->textResponse('Invalid room', 403);
        }

        RoomPresence::close($roomid);
        $this->jsonResponse(['ok' => true]);
    }

    /**
     * Display a specific conference room
     *
     * Sets the ROOMID parameter and reuses the index logic.
     *
     * @param string $roomId The room identifier
     * @return void
     */
    public function show(string $roomId): void
    {
        // Set ROOMID parameter and reuse index logic
        $_GET['ROOMID'] = $roomId;
        $this->index();
    }
}
