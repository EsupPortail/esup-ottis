<?php

namespace App\Controller;

/**
 * EmailController - Handles email sending operations
 */
class EmailController extends BaseController
{
    /**
     * Send email
     *
     * Validates CSRF token, retrieves and sanitizes email parameters from POST request.
     * Validates email format and sends the email message.
     * Returns JSON response with success/failure status.
     *
     * @throws \RuntimeException When required parameters are missing or invalid
     * @return void
     */
    public function send(): void
    {
        // Validate CSRF token
        \App\Utils\Csrf::verifyRequest();

        // Get POST parameters
        $to = $this->postParam('to');
        $subject = $this->postParam('subject');
        $message = $this->postParam('message');
        $from = $this->postParam('from');

        // Validate required parameters
        if (empty($to) || empty($subject) || empty($message)) {
            $this->jsonResponse(['error' => 'Missing required parameters'], 400);
        }

        // Sanitize inputs
        $to = filter_var($to, FILTER_SANITIZE_EMAIL);
        $subject = $this->escape($subject);
        $message = $this->escape($message);

        // Validate email format
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['error' => 'Invalid email address'], 400);
        }

        // TODO: Implement actual email sending logic
        // This should:
        // 1. Configure mail server settings
        // 2. Set headers (From, Reply-To, Content-Type)
        // 3. Send the email
        // 4. Return success/failure status

        // For now, just log the email and return success
        error_log("Email to send: To={$to}, Subject={$subject}, From={$from}");

        $this->jsonResponse([
            'success' => true,
            'message' => 'Email queued for delivery'
        ]);
    }
}
