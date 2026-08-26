<?php

namespace App\Controller;

use App\Security\RoomTokenManager;

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
     */
    public function index(): void
    {
        // Initialize CSRF protection
        initCsrfProtection();
        
        // Set session marker
        $_SESSION['from'] = 'teacher';
        
        // Get language from request
        $language = $this->getParam('lang', 'fr');
        
        // Set page-specific data
        $this->viewData['pageTitle'] = 'Omist - Conference';
        
        // Define styles for this page
        $this->viewData['pageStyles'] = [
            'styles/auditor.css',
            'styles/conference.css',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            'styles/chat.css'
        ];
        
        // Define scripts for this page
        $this->viewData['pageScripts'] = [
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js',
            '/js/qrcode.min.js'
        ];
        
        // Get or generate classroom ID
        $classroomid = $this->getParam('ROOMID');
        if (empty($classroomid)) {
            $classroomid = md5(gmdate('Y-m-d\TH:i:s\Z'));
        }
        
        // Generate room token
        $roomToken = generateRoomToken($classroomid);
        
        // Generate app ID
        $appid = md5(gmdate('Y-m-d\TH:i:s\Z') . 'XXX');
        
        // Get user ID
        $uid = $_SERVER['PHP_AUTH_USER'] ?? 'anonymous';
        if (empty($uid)) {
            $uid = 'anonymous';
        }
        if (strpos($_SERVER['SERVER_NAME'], 'univ-nantes.fr') === false) {
            $uid = 'anonymous';
        }
        
        // Load supported languages
        $T_lang = $this->loadSupportedLanguages();
        
        // Get DeepL stats if applicable
        $Stats_DEEPL = [];
        $percentage_DEEPL = 0;
        
        // Get configuration parameter
        $currentconfig = $this->getParam('myconfig', 'AAYAYKCNYCNYNYNNN');
        
        // Prepare data for JavaScript
        $jsVars = [
            'supportedlanguages' => $T_lang,
            'currentconfig' => $currentconfig,
            'roomToken' => $roomToken
        ];
        
        // Set variables for the view
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['appid'] = $appid;
        $this->viewData['uid'] = $uid;
        $this->viewData['T_lang'] = $T_lang;
        $this->viewData['Stats_DEEPL'] = $Stats_DEEPL;
        $this->viewData['percentage_DEEPL'] = $percentage_DEEPL;
        $this->viewData['currentconfig'] = $currentconfig;
        $this->viewData['jsVars'] = $jsVars;
        $this->viewData['roomToken'] = $roomToken;
        
        // Render the conference view
        $this->render('conference/index');
    }
    
    /**
     * Display a specific conference room
     * 
     * @param string $roomId The room identifier
     */
    public function show(string $roomId): void
    {
        // For now, redirect to the main conference page with the room ID
        $this->redirect('/conference.php?ROOMID=' . urlencode($roomId));
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
                // Skip empty lines
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
