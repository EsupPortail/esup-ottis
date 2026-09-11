<?php

namespace App\Controller;

/**
 * UploadController - Handles presentation file upload with automatic conversion to PDF
 * All output files are PDF format for consistent display with PDF.js
 */
class UploadController extends BaseController
{
    /**
     * Upload directory path
     */
    private string $uploadDir;

    /**
     * Initialize controller
     */
    protected function initialize(): void
    {
        parent::initialize();
        $this->uploadDir = (defined('APP_PUBLIC') ? APP_PUBLIC : (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 3)) . '/public') . '/uploads/';
        error_log('UploadController: uploadDir = ' . $this->uploadDir);
    }

    /**
     * Handle file upload request
     *
     * Main entry point for file upload operations.
     * Routes to processUpload() for POST or showUploadForm() for GET requests.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->router->getMethod() === 'POST') {
            $this->processUpload();
        } else {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
    }

    /**
     * Process uploaded file
     */
    protected function processUpload(): void
    {
        // Verify CSRF token
        try {
            \App\Utils\Csrf::verifyRequest();
        } catch (\Exception $e) {
            log_error('CSRF token validation failed: ' . $e->getMessage());
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'CSRF token validation failed'
            ], 403);
            return;
        }

        log_info('Nouvelle requête upload');

        // Check if file was uploaded
        if (!isset($_FILES['presentation_file'])) {
            log_error('ERREUR: No file uploaded');
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'No file uploaded'
            ], 400);
            return;
        }

        $file = $_FILES['presentation_file'];

        // Log file info
        log_info('Fichier: ' . $file['name'] . ', Taille: ' . $file['size'] . ', Type: ' . $file['type'] . ', Error: ' . $file['error']);

        // Handle upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = 'Unknown upload error';
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMessage = 'File exceeds maximum size limit (50MB)';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMessage = 'File was only partially uploaded';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMessage = 'No file was uploaded';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMessage = 'Missing temporary folder';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMessage = 'Failed to write file to disk';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMessage = 'File upload stopped by extension';
                    break;
            }
            log_error('ERREUR UPLOAD: ' . $errorMessage . ' (code: ' . $file['error'] . ')');
            $this->jsonResponse([
                'status' => 'error',
                'message' => $errorMessage,
                'error_code' => $file['error']
            ], 400);
            return;
        }

        // Allowed file types and their MIME types
        $allowedExtensions = ['pptx', 'odp', 'pdf', 'txt', 'html', 'htm'];
        $allowedMimeTypes = [
            'application/pdf',
            'text/plain',
            'text/html',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.oasis.opendocument.presentation',
            'application/zip',
            'application/octet-stream'
        ];

        // Validate file extension
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedExtensions)) {
            log_error('ERREUR: Invalid file type: ' . $extension);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)
            ], 400);
            return;
        }

        // Validate actual MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($detectedMime, $allowedMimeTypes)) {
            log_error('ERREUR: Invalid MIME type: ' . $detectedMime);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid MIME type. Detected: ' . $detectedMime
            ], 400);
            return;
        }

        // Validate file size (50MB max)
        $maxFileSize = 50 * 1024 * 1024;
        if ($file['size'] > $maxFileSize) {
            log_error('ERREUR: File too large (' . $file['size'] . ' bytes)');
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'File too large. Maximum size: 50MB'
            ], 400);
            return;
        }

        // Create uploads directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                log_error('ERREUR: Failed to create upload directory');
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Failed to create upload directory'
                ], 500);
                return;
            }
        }

        // Generate unique filename to prevent collisions
        $uniqueId = uniqid('pres_', true) . '.' . $extension;
        $targetPath = $this->uploadDir . $uniqueId;

        // Move uploaded file to target location
        error_log('UploadController: Moving file from ' . $file['tmp_name'] . ' to ' . $targetPath);
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            log_error('ERREUR: Failed to save uploaded file');
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to save uploaded file'
            ], 500);
            return;
        }
        error_log('UploadController: File successfully saved to ' . $targetPath);
        if (file_exists($targetPath)) {
            error_log('UploadController: File exists at ' . $targetPath . ', size: ' . filesize($targetPath));
        } else {
            error_log('UploadController: WARNING - File does NOT exist at ' . $targetPath);
        }

        // For PPTX and ODP: convert to PDF using LibreOffice
        if ($extension === 'pptx' || $extension === 'odp') {
            $pdfFilename = pathinfo($uniqueId, PATHINFO_FILENAME) . '.pdf';
            $pdfPath = $this->uploadDir . $pdfFilename;

            // Escape paths for security
            $escapedUploadDir = escapeshellarg($this->uploadDir);
            $escapedTargetPath = escapeshellarg($targetPath);

            // LibreOffice conversion command
            // Use custom user profile to avoid permission issues
            $tempProfile = '/tmp/libreoffice_profile_' . getmypid();
            if (!file_exists($tempProfile)) {
                mkdir($tempProfile, 0777, true);
            }

            $command = "HOME={$tempProfile} libreoffice --headless --norestore --convert-to pdf --outdir {$escapedUploadDir} {$escapedTargetPath} 2>/dev/null";

            // Execute conversion
            exec($command, $output, $returnCode);

            // Wait for LibreOffice to finish writing the file
            $maxAttempts = 10;
            $attempt = 0;
            $converted = false;

            while ($attempt < $maxAttempts && !$converted) {
                $attempt++;
                if (file_exists($pdfPath)) {
                    $converted = true;
                    break;
                }
                usleep(500000); // Wait 0.5 seconds
            }

            // Check if conversion succeeded
            if ($converted && file_exists($pdfPath)) {
                log_info('SUCCÈS: Conversion réussie (attempts: ' . $attempt . ')');
                // Remove original file (PPTX or ODP)
                unlink($targetPath);
                $targetPath = $pdfPath;
                $extension = 'pdf';
            } else {
                // Conversion failed - clean up and return error
                if (file_exists($targetPath)) {
                    unlink($targetPath);
                }
                log_error('ERREUR CONVERSION: ' . implode(' ', $output) . ' (attempts: ' . $attempt . ')');
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Failed to convert file to PDF',
                    'details' => implode("\n", $output),
                    'attempts' => $attempt
                ], 500);
                return;
            }
        }

        log_info('Réponse: ' . json_encode([
            'status' => 'success',
            'file' => $targetPath,
            'exists' => file_exists($targetPath) ? 'YES' : 'NO'
        ]));

        // Return success response
        $relativeUrl = '/uploads/' . basename($targetPath);

        $this->jsonResponse([
            'status' => 'success',
            'file_path' => $targetPath,
            'file_name' => $file['name'],
            'file_type' => $extension,
            'display_method' => 'pdf',
            'url' => $relativeUrl,
            'original_extension' => $fileInfo['extension']
        ]);
    }

    /**
     * Read uploaded file content
     *
     * Retrieves and returns the content of a previously uploaded file.
     * Requires file identifier parameter.
     *
     * @return void
     */
    public function read(): void
    {
        $fileId = $this->getParam('file');

        if (empty($fileId)) {
            http_response_code(400);
            die('Fichier non spécifié');
        }

        // Valider le nom de fichier (caractères alphanumériques, underscore, hyphen, point)
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fileId)) {
            http_response_code(400);
            die('Nom de fichier invalide');
        }

        // Chemin vers le dossier uploads
        $filepath = $this->uploadDir . $fileId;

        // Vérifier que le fichier existe et est dans le dossier uploads
        if (!file_exists($filepath)) {
            http_response_code(404);
            die('Fichier introuvable');
        }

        // Vérifier que le fichier est bien dans le dossier uploads (pas de ../)
        $realpath = realpath($filepath);
        if (strpos($realpath, realpath($this->uploadDir)) !== 0) {
            http_response_code(403);
            die('Accès interdit');
        }

        // Déterminer le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        // Servir le fichier
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: inline; filename="' . \App\Utils\Sanitizer::e($fileId) . '"');
        header('Cache-Control: private, max-age=3600');

        // Lire et envoyer le fichier
        readfile($filepath);
        exit;
    }
}
