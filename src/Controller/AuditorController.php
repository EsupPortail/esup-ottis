<?php

namespace App\Controller;

use App\Security\RoomTokenManager;
use App\Service\LanguageLoader;
use App\Service\RandomNameGenerator;
use App\Service\RoomPresence;

/**
 * AuditorController - Handles the student/attendee view for conference rooms
 *
 * This controller manages the interface for students to join and participate
 * in translation sessions created by teachers.
 */
class AuditorController extends BaseController
{
    /**
     * Display the auditor interface
     *
     * Renders the student/attendee view for joining conference rooms.
     * Handles room token generation, random name assignment for anonymous users,
     * and loads supported languages for the translation interface.
     * Sets up JavaScript variables and module scripts for the auditor functionality.
     *
     * @return void
     */
    public function index(): void
    {
        // Set session marker
        $_SESSION['from'] = 'student';

        // Interface auditeur en anglais par defaut, sauf si une langue
        // est explicitement demandee (?lang=) ou deja definie en session
        $this->language = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';

        // Get room ID from request
        $classroomid = $this->getParam('ROOMID', '');

        // Generate room token and recover the room owner uid from the shared room metadata.
        $roomToken = RoomTokenManager::generateRoomToken($classroomid);
        $intervenantUid = RoomTokenManager::getRoomOwnerUid($classroomid);

        // Use services to load data
        $T_lang = LanguageLoader::load();
        $randomname = RandomNameGenerator::generateName();

        // Get theme parameter
        $theme = $this->getParam('theme', 'flat');

        // Get configuration
        $currentconfig = $this->getParam('myconfig', 'ABYANACNNCNNNYYYYN');

        // Prepare JavaScript variables
        $jsVars = [
            'supportedlanguages' => $T_lang,
            'classroomid' => $classroomid,
            'theme' => $theme,
            'randomname' => $randomname,
            'currentconfig' => $currentconfig,
            'intervenantUid' => $intervenantUid,
            'roomOwnerUid' => $intervenantUid,
        ];

        // Set page data
        $this->viewData['pageTitle'] = 'OTTIS - Auditor';
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['roomToken'] = $roomToken;
        $this->viewData['randomname'] = $randomname;
        $this->viewData['theme'] = $theme;
        $this->viewData['T_lang'] = $T_lang;
        $this->viewData['currentconfig'] = $currentconfig;
        $this->viewData['intervenantUid'] = $intervenantUid;
        $this->viewData['jsVars'] = $jsVars;

        // Define styles for this page
        $this->viewData['pageStyles'] = [
            'styles/conference-new.css',
            'styles/auditor-new.css',
        ];

        // Define scripts for this page
        $pageScripts = [];

        // DOM utilities - must load FIRST (required by many modules)
        $chatScripts = [
            '/js/lib/dom/selector.js',
            '/js/lib/dom/styles.js',
            '/js/lib/dom/visibility.js',
            '/js/lib/dom/layout.js',
            '/js/lib/dom/events.js',
            '/js/lib/dom/dialog.js',
            // Network utilities
            '/js/lib/network/utils.js',
            '/js/lib/network/xhr.js',
            '/js/lib/network/fetch.js',
            '/js/lib/network/counter.js',
            // Language utilities
            '/js/lib/language/state.js',
            '/js/lib/language/management.js',
            // Chat utilities
            '/js/lib/chat/state.js',
            '/js/lib/chat/utils.js',
            // Store must be loaded FIRST
            '/js/lib/state/store.js',
            '/js/lib/state/shared.js',
            '/js/lib/state/auditor.js',
            '/js/lib/utils/draggable.js',
            '/js/lib/conference/network.js',
            '/js/lib/utils/request-counter.js',
            '/js/lib/auditor/text-processing.js',
            '/js/lib/auditor/speech.js',
            '/js/lib/auditor/display.js',
            '/js/lib/auditor/config.js',  // Must load AFTER display.js (depends on showBanner, hideBanner)
            '/js/lib/auditor/translation.js',
            '/js/lib/auditor/mail.js',
            '/js/lib/auditor/main.js',
            '/js/lib/chat/alert.js',
            '/js/lib/chat/network.js',
            '/js/lib/chat/handlers.js',
            '/js/lib/chat/display.js',
            '/js/lib/chat/main.js',
            '/js/lib/auditor/network.js',  // Must load BEFORE auditor-init.js (provides readText)
            '/js/auditor-init.js',
            '/js/lib/auditor/post.js',  // REQUIRED for openLanguageDialog - must load AFTER auditor-init.js
            '/js/lib/auditor/seg.js',   // Segmented control VO / langue (vue Transcription)
        ];

        $this->viewData['pageScripts'] = $pageScripts;
        // Le layout commun ne rend que $pageScripts (comme pour la conference) :
        // on y fusionne la liste des scripts modules de l'auditeur.
        $this->viewData['pageScripts'] = array_merge($pageScripts, $chatScripts);
        $this->viewData['isAuditorPage'] = true;

        // Render the auditor view with the shared layout (header/footer communs)
        $this->render('auditor/index');
    }

    /**
     * Statut de la conference pour la page auditor :
     * repond {"live": true|false} selon la presence d'une page
     * conference ouverte (RoomPresence) pour la salle demandee.
     */
    public function status(): void
    {
        $roomid = (string) $this->postParam('ROOMID', '');

        if ($roomid === '' || $roomid === 'UNKNOWN'
            || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomid)) {
            $this->jsonResponse(['live' => false]);
        }

        $this->jsonResponse(['live' => RoomPresence::isLive($roomid)]);
    }

    /**
     * Display a specific auditor room
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
