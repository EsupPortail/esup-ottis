<?php

/**
 * Routes Configuration for OMIST Application
 *
 * This file centralizes all route definitions for the MVC architecture.
 * Each route maps a URL path to a controller and action method.
 */

return [
    // =========================================================================
    // Public Pages - GET Routes
    // =========================================================================

    // Home page
    '/' => [
        'controller' => \App\Controller\HomeController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],

    // Conference pages
    '/conference' => [
        'controller' => \App\Controller\ConferenceController::class,
        'action' => 'index',
        'methods' => ['GET', 'POST']
    ],
    '/conference/{roomId}' => [
        'controller' => \App\Controller\ConferenceController::class,
        'action' => 'show',
        'methods' => ['GET'],
        'params' => ['roomId']
    ],

    // Auditor page
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

    // Subtitles
    '/subtitles' => [
        'controller' => \App\Controller\SubtitlesController::class,
        'action' => 'index',
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
    '/readupload' => [
        'controller' => \App\Controller\UploadController::class,
        'action' => 'read',
        'methods' => ['GET', 'POST']
    ],

    // Text processing
    '/readtext' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'read',
        'methods' => ['GET', 'POST']
    ],
    '/readtextQ' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readQ',
        'methods' => ['GET', 'POST']
    ],
    '/readtextTranscriptSub' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readTranscriptSub',
        'methods' => ['GET', 'POST']
    ],
    '/read-vo' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readVO',
        'methods' => ['GET', 'POST']
    ],
    '/translate' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'translate',
        'methods' => ['GET', 'POST']
    ],

    // Text processing with /text/ prefix (for JavaScript compatibility)
    '/text/read' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'read',
        'methods' => ['GET', 'POST']
    ],
    '/text/readQ' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readQ',
        'methods' => ['GET', 'POST']
    ],
    '/text/translate' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'translate',
        'methods' => ['GET', 'POST']
    ],
    '/text/save' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'save',
        'methods' => ['POST']
    ],
    '/text/saveQ' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'saveQ',
        'methods' => ['POST']
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
    '/text/read-q' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readQ',
        'methods' => ['GET', 'POST']
    ],
    '/text/save-q' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'saveQ',
        'methods' => ['POST']
    ],

    // Image processing
    '/readimage' => [
        'controller' => \App\Controller\ImageController::class,
        'action' => 'read',
        'methods' => ['GET', 'POST']
    ],

    // =========================================================================
    // Save Endpoints - POST Routes
    // =========================================================================

    '/savetext' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'save',
        'methods' => ['POST']
    ],
    '/savetextQ' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'saveQ',
        'methods' => ['POST']
    ],
    '/saveimage' => [
        'controller' => \App\Controller\ImageController::class,
        'action' => 'save',
        'methods' => ['POST']
    ],

    // Email
    '/sendamail' => [
        'controller' => \App\Controller\EmailController::class,
        'action' => 'send',
        'methods' => ['POST']
    ],
    '/transcription-sub' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'transcriptionSub',
        'methods' => ['GET', 'POST']
    ],

    // =========================================================================
    // Extraction Tools
    // =========================================================================

    // New MVC-style routes for extraction
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
    '/ExtractSlidesNotes' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'slidesNotes',
        'methods' => ['GET', 'POST']
    ],
];
