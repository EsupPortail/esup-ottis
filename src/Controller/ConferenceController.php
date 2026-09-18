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
            'styles/conference.css',
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
        $roomToken = RoomTokenManager::generateRoomToken($classroomid);
        $appid = RoomInfoService::generateAppId();
        $uid = RoomInfoService::getUserId('anonymous');
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
            'roomToken' => $roomToken
        ];

        // Set all variables for the view
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['appid'] = $appid;
        $this->viewData['uid'] = $uid;
        $this->viewData['T_lang'] = $T_lang;
        $this->viewData['Stats_DEEPL'] = $Stats_DEEPL;
        $this->viewData['percentage_DEEPL'] = $percentage_DEEPL;
        $this->viewData['currentconfig'] = $currentconfig;
        $this->viewData['wooclapaddress'] = $wooclapaddress;
        $this->viewData['jsVars'] = $jsVars;
        $this->viewData['roomToken'] = $roomToken;
        $this->viewData['ip'] = $ip;

        // No layout as this page has its own complete HTML structure
        $this->layout = false;
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
