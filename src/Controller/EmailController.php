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

        // Get POST parameters (using 'body' instead of 'message' to match legacy sendamail.php)
        $to = $this->postParam('to');
        $subject = $this->postParam('subject');
        $body = $this->postParam('body', '');
        $from = $this->postParam('from', '');

        // Validate required parameters
        if (empty($to) || empty($subject)) {
            $this->jsonResponse(['error' => 'Missing required parameters'], 400);
        }

        // Sanitize inputs - clean HTML tags from body
        $body = str_replace('<p>', '', $body);
        $body = str_replace('</p>', '', $body);

        // Convert line breaks for HTML email
        $body = str_replace('<br>', "<br>\r\n", str_replace('<br/>', "<br/>\r\n", $body));

        // Email application sender
        $mailapplication = 'Ne pas répondre <bourdon-j@univ-nantes.fr>';

        // Sanitize other inputs
        $to = filter_var($to, FILTER_SANITIZE_EMAIL);
        $subject = $this->escape($subject);

        // Validate email format for 'to' field
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['error' => 'Invalid email address'], 400);
        }

        // Only send if 'to' is not empty (legacy behavior)
        if ($to != '') {
            $headers = 'From: ' . $mailapplication . "\r\n"
                    . 'Reply-To: ' . $from . "\r\n"
                    . 'Content-type: text/html; charset=utf-8' . "\r\n";

            // Send the email
            $sent = mail($to, $subject, $body, $headers);
        } else {
            $sent = false;
        }

        // Return success/failure
        $this->jsonResponse([
            'success' => $sent !== false,
            'message' => $sent ? 'Email sent successfully' : 'Failed to send email'
        ]);
    }
}
