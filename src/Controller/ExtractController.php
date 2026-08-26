<?php

namespace App\Controller;

/**
 * ExtractController - Handles PowerPoint and document extraction operations
 */
class ExtractController extends BaseController
{
    /**
     * Extract slides and notes from presentation
     */
    public function slidesNotes(): void
    {
        // This endpoint extracts slides and notes from a presentation file
        
        if ($this->router->getMethod() === 'POST') {
            $this->processExtraction();
        } else {
            $this->showExtractionForm();
        }
    }
    
    /**
     * Extract slides and notes as HTML
     */
    public function slidesNotesAppHTML(): void
    {
        // Similar to slidesNotes but returns HTML format
        
        if ($this->router->getMethod() === 'POST') {
            $this->processExtraction('html');
        } else {
            $this->showExtractionForm('html');
        }
    }
    
    /**
     * Extract slides and notes as text
     */
    public function slidesNotesTxt(): void
    {
        // Extract and return as plain text
        
        if ($this->router->getMethod() === 'POST') {
            $this->processExtraction('txt');
        } else {
            $this->showExtractionForm('txt');
        }
    }
    
    /**
     * Extract PPTX images
     */
    public function pptxImages(): void
    {
        // Extract images from PPTX files
        
        if ($this->router->getMethod() === 'POST') {
            $this->processExtraction('images');
        } else {
            $this->jsonResponse(['error' => 'POST required for extraction'], 405);
        }
    }
    
    /**
     * Extract PPTX notes using the PPTXNotesExtractor service
     */
    public function pptxNotes(): void
    {
        // Use the PPTXNotesExtractor service
        
        if ($this->router->getMethod() === 'POST') {
            $this->processExtraction('notes');
        } else {
            $this->jsonResponse(['error' => 'POST required for extraction'], 405);
        }
    }
    
    /**
     * Process extraction request
     * 
     * @param string $format Output format (optional)
     */
    protected function processExtraction(string $format = 'default'): void
    {
        // Validate CSRF token
        validateCsrfToken();
        
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['error' => 'No file uploaded'], 400);
        }
        
        $file = $_FILES['file'];
        $allowedTypes = [
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint',
            'application/pdf',
            'text/plain'
        ];
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            $this->jsonResponse(['error' => 'Invalid file type. Only PPTX, PPT, PDF, and TXT files are allowed'], 400);
        }
        
        // TODO: Implement actual extraction logic based on file type
        // This should:
        // 1. Move uploaded file to temporary location
        // 2. Extract content based on file type
        // 3. Format output based on $format parameter
        // 4. Return extracted content
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Extraction completed',
            'format' => $format,
            'filename' => $file['name']
        ]);
    }
    
    /**
     * Show extraction form
     * 
     * @param string $format Output format
     */
    protected function showExtractionForm(string $format = 'default'): void
    {
        // Initialize CSRF protection
        initCsrfProtection();
        
        $this->viewData['pageTitle'] = 'Extract Presentation Content';
        $this->viewData['format'] = $format;
        
        $this->render('extract/form');
    }
}
