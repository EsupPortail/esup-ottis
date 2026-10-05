<?php

/**
 * Chat Component - Reusable chat interface for OMIST
 *
 * This component provides a standardized chat interface that can be used across
 * conference.php, and auditor.php to eliminate code duplication.
 *
 * @package OMIST
 * @subpackage Chat
 */

/**
 * Render the chat component
 *
 * @param array $options {
 *     associative array with the following keys:
 *     - 'roomId': string - The ROOMID for the classroom
 *     - 'roomToken': string - The room token for multi-tab synchronization
 *     - 'userName': string - The username to display (e.g., "Red Alpaca")
 *     - 'isIntervenant': bool - Whether the user is an intervenant
 *     - 'containerClass': string - Optional CSS class for the container (default: 'chat-container')
 *     - 'placeholder': string - Optional placeholder text for the input (default: 'Type your message here...')
 *     - 'labelText': string - Optional label text (default: 'Classroom Chat')
 * }
 * @return void - Outputs HTML directly
 */
function renderChatComponent($options = [])
{
    // Default options
    $defaults = [
        'roomId' => '',
        'roomToken' => '',
        'userName' => '',
        'isIntervenant' => false,
        'containerClass' => 'chat-container',
        'placeholder' => 'Type your message here...',
        'labelText' => 'Classroom Chat'
    ];

    $options = array_merge($defaults, $options);

    // Extract options
    $roomId = htmlspecialchars($options['roomId'], ENT_QUOTES, 'UTF-8');
    $roomToken = htmlspecialchars($options['roomToken'], ENT_QUOTES, 'UTF-8');
    $userName = htmlspecialchars($options['userName'], ENT_QUOTES, 'UTF-8');
    $isIntervenant = $options['isIntervenant'];
    $containerClass = htmlspecialchars($options['containerClass'], ENT_QUOTES, 'UTF-8');
    $placeholder = htmlspecialchars($options['placeholder'], ENT_QUOTES, 'UTF-8');
    $labelText = htmlspecialchars($options['labelText'], ENT_QUOTES, 'UTF-8');

    // Output the chat HTML structure
    echo "<div id=\"classroom\" class=\"{$containerClass}\" aria-live=\"polite\" aria-atomic=\"true\"></div>\n";
    echo "<textarea id=\"question\" aria-label=\"{$labelText}\" title=\"{$labelText}\" placeholder=\"{$placeholder}\" rows=\"2\" cols=\"50\" style=\"width: 70%;\"></textarea>\n";
    echo "<input type=\"button\" value=\"Send\" class=\"button-3 tooltip\" id=\"questionsend\" title=\"Send your message\">\n";
}

/**
 * Output the necessary CSS includes for the chat component
 *
 * @return void
 */
function outputChatCSS()
{
    echo '<link href="styles/chat.css" rel="stylesheet">' . "\n";
}
