<?php

namespace App\Controller;

/**
 * SubtitlesController - Handles subtitle generation and display
 */
class SubtitlesController extends BaseController
{
    /**
     * Display the subtitles page
     */
    public function index(): void
    {
        // Initialize CSRF protection
        initCsrfProtection();
        
        // Set page data
        $this->viewData['pageTitle'] = 'Omist - Subtitles';
        
        // Define styles for this page
        $this->viewData['pageStyles'] = [
            'styles/conference.css'
        ];
        
        // Define scripts for this page
        $this->viewData['pageScripts'] = [
            'js/common-func.js'
        ];
        
        // Render the subtitles view
        $this->render('subtitles/index');
    }
}
