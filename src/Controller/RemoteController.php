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
     *
     * Renders the remote control interface for managing conference presentations.
     * Initializes CSRF protection and retrieves room parameters from the request.
     * Sets up page data including room ID, language, and application ID.
     *
     * @return void
     */
    public function index(): void
    {
        // Get parameters from request
        $classroomid = $this->getParam('ROOMID', 'UNKNOWN');
        $lang = $this->getParam('LANG', '');
        $appid = $this->getParam('APPID', '');

        // Set page data
        $this->viewData['pageTitle'] = 'OMIST - Remote Control';
        $this->viewData['classroomid'] = $classroomid;
        $this->viewData['lang'] = $lang;
        $this->viewData['appid'] = $appid;

        // Render the remote view (without layout as it has its own HTML structure)
        $this->layout = false;
        $this->render('remote/index');
    }

    /**
     * Save remote order (AJAX endpoint)
     *
     * This method handles POST requests to save remote control orders.
     *
     * @throws \RuntimeException When required parameters are missing
     * @return void
     */
    public function saveOrder(): void
    {
        // Get parameters
        $classroomid = $this->postParam('ROOMID', '');
        $order = $this->postParam('ORDER', '');

        // Validate CSRF token
        \App\Utils\Csrf::verifyRequest();

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
     * This method handles POST requests to save speech for remote display.
     *
     * @throws \RuntimeException When classroom ID is missing
     * @return void
     */
    public function saveSpeech(): void
    {
        // Get parameters
        $classroomid = $this->postParam('ROOMID', '');
        $speech = $this->postParam('SPEECH', '');

        // Validate CSRF token
        \App\Utils\Csrf::verifyRequest();

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
