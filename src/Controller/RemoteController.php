<?php

namespace App\Controller;

/**
 * RemoteController - Handles the remote control interface
 * 
 * This controller manages the remote control interface that allows
 * users to control the conference presentation remotely.
 */
class RemoteController extends BaseController
{
    /**
     * Display the remote control interface
     */
    public function index(): void
    {
        // Initialize CSRF protection
        initCsrfProtection();
        
        // Get parameters from request
        $classroomid = $this->getParam('ROOMID', 'UNKNOWN');
        $lang = $this->getParam('LANG', '');
        $appid = $this->getParam('APPID', '');
        
        // Set page data
        $this->viewData['pageTitle'] = 'OMIST - Remote Control';
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['lang'] = $lang;
        $this->viewData['appid'] = $appid;
        
        // Define styles for this page
        $this->viewData['pageStyles'] = [
            'styles/remote.css'
        ];
        
        // Define scripts for this page
        $this->viewData['pageScripts'] = [
            'https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js',
            'js/common-func.js'
        ];
        
        // Render the remote view
        $this->render('remote/index');
    }
    
    /**
     * Save remote order (AJAX endpoint)
     * 
     * This method handles POST requests to save remote control orders
     */
    public function saveOrder(): void
    {
        // Get parameters
        $classroomid = $this->postParam('ROOMID', '');
        $order = $this->postParam('ORDER', '');
        
        // Validate CSRF token
        validateCsrfToken();
        
        // Validate required parameters
        if (empty($classroomid) || empty($order)) {
            $this->jsonResponse(['error' => 'Missing required parameters'], 400);
        }
        
        // Here we would normally save the order to the database or session
        // For now, just return success to maintain compatibility
        
        // TODO: Implement actual order saving logic
        // This should update the conference state with the new order
        
        $this->jsonResponse(['success' => true, 'order' => $order]);
    }
    
    /**
     * Save speech for remote (AJAX endpoint)
     * 
     * This method handles POST requests to save speech for remote display
     */
    public function saveSpeech(): void
    {
        // Get parameters
        $classroomid = $this->postParam('ROOMID', '');
        $speech = $this->postParam('SPEECH', '');
        
        // Validate CSRF token
        validateCsrfToken();
        
        // Validate required parameters
        if (empty($classroomid)) {
            $this->jsonResponse(['error' => 'Missing classroom ID'], 400);
        }
        
        // Here we would normally process the speech
        // For now, just return the speech data to maintain compatibility
        
        // TODO: Implement actual speech processing logic
        
        // Return mock data in the expected format
        $this->jsonResponse('speech_before:speech_current:speech_next');
    }
}
