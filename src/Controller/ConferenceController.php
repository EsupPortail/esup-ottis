<?php

namespace App\Controller;

use App\Security\RoomTokenManager;
use App\Service\LanguageLoader;
use App\Service\RoomInfoService;
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
            'styles/chat.css'
        ];

        // Define scripts for this page
        $this->viewData['pageScripts'] = [
            // Store must be loaded FIRST
            '/js/lib/state/store.js',
            '/js/lib/state/shared.js',
            '/js/lib/state/conference.js',
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js'
        ];

        // Get or generate classroom ID
        $classroomid = $this->getParam('ROOMID');
        if (empty($classroomid)) {
            $classroomid = md5(gmdate('Y-m-d\TH:i:s\Z'));
        }

        // Use services to load data
        $T_lang = LanguageLoader::load();
        $uid = $_SESSION['cas_user'] ?? RoomInfoService::getUserId('anonymous');
        $uid = is_string($uid) && trim($uid) !== '' ? trim($uid) : 'anonymous';
        $roomToken = RoomTokenManager::generateRoomToken($classroomid, $uid);
        RoomTokenManager::setRoomOwnerUid($classroomid, $uid);
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

        // Tous les scripts nécessaires pour la SPA (tous les onglets chargés)
        $afterContentScripts = '';

        // DOM and Network utilities
        $afterContentScripts .= '<script type="module" src="/js/lib/dom/selector.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/dom/styles.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/dom/visibility.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/dom/layout.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/dom/events.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/dom/dialog.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/network/utils.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/network/xhr.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/network/fetch.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/network/counter.js"></script>';

        // Language utilities
        $afterContentScripts .= '<script type="module" src="/js/lib/language/state.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/language/management.js"></script>';

        // Chat utilities
        $afterContentScripts .= '<script type="module" src="/js/lib/chat/state.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/chat/utils.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/utils/cleanup.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/index.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/chat/alert.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/chat/network.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/chat/main.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/chat/handlers.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/chat/display.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/index.js"></script>';

        // Conference scripts (tous les onglets)
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/pdf-viewer.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/pdf-fullscreen/core.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/pdf-fullscreen/ui.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/events.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/notebook.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/config-functions.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/post.js"></script>';
        $afterContentScripts .= '<script type="module" src="/js/lib/conference/speech.js"></script>';

        // Script d'initialisation
        $afterContentScripts .= '<script type="module" src="/js/conference-init.js"></script>';

        // Script de test pour localhost
        if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) {
            $afterContentScripts .= '<script src="/js/tests/no-jquery-validation.js"></script>';
        }

        $this->viewData['afterContentScripts'] = $afterContentScripts;

        $this->render('conference/index');
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
