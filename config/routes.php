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
    '/index.php' => [
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
    '/conference.php' => [
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
    '/auditor.php' => [
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
    
    // Remote control
    '/remote' => [
        'controller' => \App\Controller\RemoteController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],
    '/remote.php' => [
        'controller' => \App\Controller\RemoteController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],
    
    // Subtitles
    '/subtitles' => [
        'controller' => \App\Controller\SubtitlesController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],
    '/subtitles.php' => [
        'controller' => \App\Controller\SubtitlesController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],
    
    // =========================================================================
    // API Endpoints - File Upload and Processing
    // =========================================================================
    
    '/upload_handler.php' => [
        'controller' => \App\Controller\UploadController::class,
        'action' => 'handle',
        'methods' => ['GET', 'POST']
    ],
    '/readupload.php' => [
        'controller' => \App\Controller\UploadController::class,
        'action' => 'read',
        'methods' => ['GET', 'POST']
    ],
    
    // Text processing
    '/readtext.php' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'read',
        'methods' => ['GET', 'POST']
    ],
    '/readtextQ.php' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readQ',
        'methods' => ['GET', 'POST']
    ],
    '/readtextTranscriptSub.php' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'readTranscriptSub',
        'methods' => ['GET', 'POST']
    ],
    
    // Image processing
    '/readimage.php' => [
        'controller' => \App\Controller\ImageController::class,
        'action' => 'read',
        'methods' => ['GET']
    ],
    
    // =========================================================================
    // Save Endpoints - POST Routes
    // =========================================================================
    
    '/savetext.php' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'save',
        'methods' => ['POST']
    ],
    '/savetextQ.php' => [
        'controller' => \App\Controller\TextController::class,
        'action' => 'saveQ',
        'methods' => ['POST']
    ],
    '/saveimage.php' => [
        'controller' => \App\Controller\ImageController::class,
        'action' => 'save',
        'methods' => ['POST']
    ],
    '/saveremoteorder.php' => [
        'controller' => \App\Controller\RemoteController::class,
        'action' => 'saveOrder',
        'methods' => ['POST']
    ],
    '/savespeechforremote.php' => [
        'controller' => \App\Controller\RemoteController::class,
        'action' => 'saveSpeech',
        'methods' => ['POST']
    ],
    
    // Email
    '/sendamail.php' => [
        'controller' => \App\Controller\EmailController::class,
        'action' => 'send',
        'methods' => ['POST']
    ],
    
    // =========================================================================
    // Extraction Tools
    // =========================================================================
    
    '/ExtractSlidesNotes.php' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'slidesNotes',
        'methods' => ['GET', 'POST']
    ],
    '/ExtractSlidesNotesAppHTML.php' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'slidesNotesAppHTML',
        'methods' => ['GET', 'POST']
    ],
    '/ExtractSlidesNotesTxt.php' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'slidesNotesTxt',
        'methods' => ['GET', 'POST']
    ],
    '/ExtractSlidesNotes_PPTX_images.php' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'pptxImages',
        'methods' => ['GET', 'POST']
    ],
    '/PPTXNotesExtractor.php' => [
        'controller' => \App\Controller\ExtractController::class,
        'action' => 'pptxNotes',
        'methods' => ['GET', 'POST']
    ],
    
    // =========================================================================
    // Legacy Compatibility Routes
    // These routes maintain backward compatibility with existing URLs
    // =========================================================================
    
    // Add legacy routes here as needed for backward compatibility
    // Example:
    // '/old-page.php' => [
    //     'controller' => \App\Controller\LegacyController::class,
    //     'action' => 'oldPage',
    //     'methods' => ['GET', 'POST']
    // ],
];
