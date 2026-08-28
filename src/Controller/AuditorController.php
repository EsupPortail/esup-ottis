<?php

namespace App\Controller;

use App\Service\LanguageLoader;
use App\Service\RandomNameGenerator;

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
        // Get room ID from request
        $classroomid = $this->getParam('ROOMID', '');

        // Generate room token
        $roomToken = generateRoomToken($classroomid);

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
            'currentconfig' => $currentconfig
        ];

        // Set page data
        $this->viewData['pageTitle'] = 'Automatic translation for teaching purposes';
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['roomToken'] = $roomToken;
        $this->viewData['randomname'] = $randomname;
        $this->viewData['theme'] = $theme;
        $this->viewData['T_lang'] = $T_lang;
        $this->viewData['currentconfig'] = $currentconfig;
        $this->viewData['jsVars'] = $jsVars;

        // Define styles for this page
        $this->viewData['pageStyles'] = [
            'styles/auditor.css',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            'styles/auditor-specific.css',
            'styles/chat.css'
        ];

        // Define scripts for this page
        $pageScripts = [
            '/js/auditor-def.js',
            '/js/common-func.js',
            '/js/lib/auditor/text-processing.js',
            '/js/lib/auditor/speech.js',
            '/js/lib/auditor/display.js',
            '/js/lib/auditor/translation.js',
            '/js/lib/auditor/network.js',
            '/js/lib/auditor/config.js',
            '/js/lib/auditor/mail.js',
            '/js/lib/auditor/main.js',
            '/js/utils/dom.js',
            '/js/utils/draggable.js',
            '/js/auditor-post.js',
        ];

        // Chat modules
        $chatScripts = [
            '/js/conference-legacy.js',
            '/js/lib/chat/alert.js',
            '/js/lib/chat/network.js',
            '/js/lib/chat/handlers.js',
            '/js/lib/chat/display.js',
            '/js/lib/chat/main.js',
            '/js/auditor-init.js',
        ];

        $this->viewData['pageScripts'] = $pageScripts;
        $this->viewData['chatScripts'] = $chatScripts;

        // Render the auditor view (without layout as it has its own HTML structure)
        $this->layout = false;
        $this->render('auditor/index');
    }

    /**
     * Display a specific auditor room
     *
     * Redirects to the auditor page with the specified room ID.
     *
     * @param string $roomId The room identifier
     * @return void
     */
    public function show(string $roomId): void
    {
        $this->redirect('/auditor.php?ROOMID=' . urlencode($roomId));
    }
}
