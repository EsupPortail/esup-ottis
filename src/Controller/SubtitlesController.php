<?php

namespace App\Controller;

use App\Service\LanguageLoader;
use App\Service\RandomNameGenerator;
use App\Service\TokenConfigService;

/**
 * SubtitlesController - Handles subtitle generation and display
 */
class SubtitlesController extends BaseController
{
    /**
     * Display the subtitles page
     *
     * Renders the subtitles interface for real-time transcription display.
     * Generates random names for anonymous users, loads supported languages,
     * and sets up Wooclap integration and Microsoft Azure speech services.
     * Configures page styles and JavaScript modules for subtitle functionality.
     *
     * @return void
     */
    public function index(): void
    {
        // Get Wooclap key
        $wooclapkey = $this->getParam('WOOCLAPKEY', '');
        $wooclapaddress = !empty($wooclapkey)
            ? 'https://www.wooclap.com/' . $wooclapkey
            : 'https://app.wooclap.com';

        // Use services to load data
        $randomname = RandomNameGenerator::generateName();
        $T_lang = LanguageLoader::load();

        // Get theme
        $theme = $this->getParam('theme', 'flat');

        // Get config
        $currentconfig = $this->getParam('myconfig', 'AAYANKCNYCYNYYYY');

        // Get classroom ID
        $classroomid = $this->getParam('ROOMID', '');

        // Get global config variables
        $tokenConfig = TokenConfigService::create();
        $MicrosoftKey = $tokenConfig->getMicrosoftKey();
        $MicrosoftRegion = $tokenConfig->getMicrosoftRegion();

        // Set page data
        $this->viewData['pageTitle'] = 'OMIST - Subtitles';
        $this->viewData['wooclapaddress'] = $wooclapaddress;
        $this->viewData['randomname'] = $randomname;
        $this->viewData['T_lang'] = $T_lang;
        $this->viewData['theme'] = $theme;
        $this->viewData['currentconfig'] = $currentconfig;
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['MicrosoftKey'] = $MicrosoftKey;
        $this->viewData['MicrosoftRegion'] = $MicrosoftRegion;

        // Define styles
        $this->viewData['pageStyles'] = [
            'styles/auditor.css',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            'libs/quickmenu/lib/font-awesome/css/font-awesome.min.css'
        ];

        // Define scripts
        $this->viewData['pageScripts'] = [
            'js/auditor-def.js'
        ];

        // Auditor modules
        $this->viewData['auditorScripts'] = [
            '/js/lib/compat/global.js',
            'js/lib/conference/dom-helpers.js',
            'js/lib/auditor/text-processing.js',
            'js/lib/auditor/voices.js',
            'js/lib/auditor/speech.js',
            'js/lib/auditor/display.js',
            'js/lib/auditor/translation.js',
            'js/lib/auditor/network.js',
            'js/lib/auditor/config.js',
            'js/lib/auditor/mail.js',
            'js/lib/auditor/main.js'
        ];

        // No layout as this page has its own complete HTML structure
        $this->layout = false;
        $this->render('subtitles/index');
    }
}
