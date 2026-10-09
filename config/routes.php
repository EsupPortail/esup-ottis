<?php

/**
 * Routes Configuration for OMIST Application
 *
 * Single source of truth for all routes: this file is loaded by
 * public/index.php, which registers each route for the HTTP methods
 * listed in 'methods'.
 *
 * 'params' documents the dynamic segments (e.g. '/conference/{roomId}').
 */

return [
    // =========================================================================
    // Public Pages - GET Routes
    // =========================================================================

    '/' => [
        'controller' => \App\Controller\HomeController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],
    '/keepalive' => [
        'controller' => \App\Controller\HomeController::class,
        'action' => 'keepalive',
        'methods' => ['GET']
    ],
    '/conference' => [
        'controller' => \App\Controller\ConferenceController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],
    '/conference/{roomId}' => [
        'controller' => \App\Controller\ConferenceController::class,
        'action' => 'show',
        'methods' => ['GET'],
        'params' => ['roomId']
    ],
    '/conference/heartbeat' => [
        'controller' => \App\Controller\ConferenceController::class,
        'action' => 'heartbeat',
        'methods' => ['POST']
    ],
    '/conference/close' => [
        'controller' => \App\Controller\ConferenceController::class,
        'action' => 'close',
        'methods' => ['POST']
    ],
    '/auditor' => [
        'controller' => \App\Controller\AuditorController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],
    '/auditor/{roomId}' => [
        'controller' => \App\Controller\AuditorController::class,
        'action' => 'show',
        'methods' => ['GET'],
        'params' => ['roomId']
    ],
    '/auditor/status' => [
        'controller' => \App\Controller\AuditorController::class,
        'action' => 'status',
        'methods' => ['POST']
    ],
    '/subtitles' => [
        'controller' => \App\Controller\SubtitlesController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],

    // =========================================================================
    // Static Pages (footer links)
    // =========================================================================

    '/mentions-legales' => [
        'controller' => \App\Controller\PageController::class,
        'action' => 'mentionsLegales',
        'methods' => ['GET']
    ],
    '/donnees-personnelles' => [
        'controller' => \App\Controller\PageController::class,
        'action' => 'donneesPersonnelles',
        'methods' => ['GET']
    ],
    '/accessibilite' => [
        'controller' => \App\Controller\PageController::class,
        'action' => 'accessibilite',
        'methods' => ['GET']
    ],
    '/plan-du-site' => [
        'controller' => \App\Controller\PageController::class,
        'action' => 'planDuSite',
        'methods' => ['GET']
    ],

    // =========================================================================
    // API Endpoints - File Upload and Processing
    // =========================================================================

    '/upload' => [
        'controller' => \App\Controller\UploadController::class,
        'action' => 'handle',
        'methods' => ['GET', 'POST']
    ],
    '/upload/handle' => [
        'controller' => \App\Controller\UploadController::class,
        'action' => 'handle',
        'methods' => ['GET', 'POST']
    ],

    // =========================================================================
    // API Endpoints - Text Processing
    // =========================================================================

    '/text/read' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'read',
        'methods' => ['GET', 'POST']
    ],
    '/text/read-q' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readQ',
        'methods' => ['GET', 'POST']
    ],
    '/text/read-transcript-sub' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readTranscriptSub',
        'methods' => ['GET', 'POST']
    ],
    '/text/read-vo' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readVO',
        'methods' => ['GET', 'POST']
    ],
    '/text/save' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'save',
        'methods' => ['GET', 'POST']
    ],
    '/text/save-q' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'saveQ',
        'methods' => ['GET', 'POST']
    ],
    '/text/translate' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'translate',
        'methods' => ['GET', 'POST']
    ],
    '/text/chat-stream' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'chatStream',
        'methods' => ['GET']
    ],
    '/room/metadata' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'roomMetadata',
        'methods' => ['GET']
    ],

    // =========================================================================
    // API Endpoints - Image Processing
    // =========================================================================

    '/image/read' => [
        'controller' => \App\Controller\ImageController::class,
        'action' => 'read',
        'methods' => ['GET', 'POST']
    ],
    '/image/save' => [
        'controller' => \App\Controller\ImageController::class,
        'action' => 'save',
        'methods' => ['GET', 'POST']
    ],

    // =========================================================================
    // API Endpoints - Email
    // =========================================================================

    '/email/send' => [
        'controller' => \App\Controller\EmailController::class,
        'action' => 'send',
        'methods' => ['GET', 'POST']
    ],

    // =========================================================================
    // API Endpoints - Extraction Tools
    // =========================================================================

    '/extract/transcription-sub' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'transcriptionSub',
        'methods' => ['GET', 'POST']
    ],
    '/extract/slides-notes' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'slidesNotes',
        'methods' => ['GET', 'POST']
    ],
    '/extract/slides-notes-txt' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'slidesNotesTxt',
        'methods' => ['GET', 'POST']
    ],
    '/extract/slides-notes-apphtml' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'slidesNotesAppHTML',
        'methods' => ['GET', 'POST']
    ],
    '/extract/pptx-images' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'pptxImages',
        'methods' => ['GET', 'POST']
    ],
    '/extract/pptx-notes' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'pptxNotes',
        'methods' => ['GET', 'POST']
    ],
];
