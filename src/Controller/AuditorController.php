<?php

namespace App\Controller;

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
     */
    public function index(): void
    {
        // Get room ID from request
        $classroomid = $this->getParam('ROOMID', '');
        
        // Generate room token
        $roomToken = generateRoomToken($classroomid);
        
        // Initialize CSRF protection
        initCsrfProtection();
        
        // Generate random name for anonymous users
        $Tcolors = ['Red', 'Blue', 'Orange', 'Purple', 'Black', 'White', 'Green'];
        $Tobjects = ['Alpaca', 'Alligator', 'Dragoon', 'Elephant', 'Lion', 'Panther', 'Tiger', 'Zebra'];
        $randomname = $Tcolors[mt_rand(0, count($Tcolors) - 1)] . ' ' . $Tobjects[mt_rand(0, count($Tobjects) - 1)];
        
        // Load supported languages
        $T_lang = $this->loadSupportedLanguages();
        
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
        $this->viewData['pageTitle'] = 'Omist - Student View';
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['roomToken'] = $roomToken;
        $this->viewData['randomname'] = $randomname;
        $this->viewData['theme'] = $theme;
        $this->viewData['T_lang'] = $T_lang;
        $this->viewData['currentconfig'] = $currentconfig;
        $this->viewData['jsVars'] = $jsVars;
        
        // Define styles for this page
        $this->viewData['pageStyles'] = [
            'styles/auditor.css'
        ];
        
        // Define scripts for this page
        $this->viewData['pageScripts'] = [
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
            '/js/conference-legacy.js',
        ];
        
        // Render the auditor view
        $this->render('auditor/index');
    }
    
    /**
     * Display a specific auditor room
     * 
     * @param string $roomId The room identifier
     */
    public function show(string $roomId): void
    {
        // Redirect to the main auditor page with the room ID
        $this->redirect('/auditor.php?ROOMID=' . urlencode($roomId));
    }
    
    /**
     * Load supported languages from CSV file
     * 
     * @return array Array of supported languages
     */
    protected function loadSupportedLanguages(): array
    {
        $T_lang = [];
        $fd = fopen(APP_ROOT . '/supportedlanguages.csv', 'r');
        
        if ($fd) {
            while ($line = fgets($fd, 4096)) {
                if (trim($line) === '') {
                    continue;
                }
                
                $ligne = explode(';', $line);
                if (count($ligne) >= 3) {
                    $code = trim($ligne[1]);
                    $T_lang[$code] = [
                        'name_fr' => trim($ligne[2]),
                        'name_vo' => trim($ligne[0]),
                        'code' => trim($ligne[1]),
                        'voice' => '-1'
                    ];
                }
            }
            fclose($fd);
        }
        
        return $T_lang;
    }
}
