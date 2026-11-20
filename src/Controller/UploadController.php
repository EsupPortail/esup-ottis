<?php

namespace App\Controller;

use App\Utils\Logger;

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

        // Utiliser tmp/ dans APP_ROOT, avec fallback sur sys temp dir si permissions insuffisantes
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', dirname(__DIR__, 2));
        }

        // Essayer d'abord APP_ROOT/tmp/
        $this->uploadDir = APP_ROOT . '/tmp/';

        // Vérifier que le répertoire existe et est accessible en écriture
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        // Si pas accessible en écriture, utiliser sys temp dir
        if (!is_writable($this->uploadDir)) {
            $this->uploadDir = sys_get_temp_dir() . '/ottis_uploads/';
            if (!file_exists($this->uploadDir)) {
                mkdir($this->uploadDir, 0775, true);
            }
        }

        Logger::debug('UploadController: uploadDir = ' . $this->uploadDir);
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
        // === DEBUG: Début du traitement
        Logger::debug('[UPLOAD-DEBUG] 1. Début de processUpload');

        Logger::debug('[UPLOAD-DEBUG] 3. Vérification $_FILES...');
        if (!isset($_FILES['presentation_file'])) {
            Logger::debug('[UPLOAD-DEBUG] 3a. AUCUN FICHIER dans $_FILES');
            Logger::debug('[UPLOAD-DEBUG] 3b. Contenu de $_FILES: ' . print_r($_FILES, true));
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'No file uploaded'
            ], 400);
            return;
        }

        $file = $_FILES['presentation_file'];
        Logger::debug('[UPLOAD-DEBUG] 4. Fichier détecté: ' . $file['name']);
        Logger::debug('[UPLOAD-DEBUG] 4a. Error code: ' . $file['error']);
        Logger::debug('[UPLOAD-DEBUG] 4b. Taille: ' . $file['size']);
        Logger::debug('[UPLOAD-DEBUG] 4c. Type: ' . $file['type']);
        Logger::debug('[UPLOAD-DEBUG] 4d. tmp_name: ' . $file['tmp_name']);

        // Log file info
        Logger::info('Fichier: ' . $file['name'] . ', Taille: ' . $file['size'] . ', Type: ' . $file['type'] . ', Error: ' . $file['error']);

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
            Logger::debug('[UPLOAD-DEBUG] 5. Erreur upload: ' . $errorMessage . ' (code: ' . $file['error'] . ')');
            Logger::error('ERREUR UPLOAD: ' . $errorMessage . ' (code: ' . $file['error'] . ')');
            $this->jsonResponse([
                'status' => 'error',
                'message' => $errorMessage,
                'error_code' => $file['error']
            ], 400);
            return;
        }

        Logger::debug('[UPLOAD-DEBUG] 6. Validation extension/MIME...');

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
        Logger::debug('[UPLOAD-DEBUG] 7. Extension: ' . $extension);

        if (!in_array($extension, $allowedExtensions)) {
            Logger::debug('[UPLOAD-DEBUG] 7a. Extension non autorisée: ' . $extension);
            Logger::error('ERREUR: Invalid file type: ' . $extension);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)
            ], 400);
            return;
        }
        Logger::debug('[UPLOAD-DEBUG] 7b. Extension autorisée');

        // Validate actual MIME type using finfo
        Logger::debug('[UPLOAD-DEBUG] 8. Vérification MIME type...');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            Logger::warning('[UPLOAD-DEBUG] 8a. finfo_open a échoué');
        }
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        Logger::debug('[UPLOAD-DEBUG] 8b. MIME détecté: ' . $detectedMime);

        if (!in_array($detectedMime, $allowedMimeTypes)) {
            Logger::debug('[UPLOAD-DEBUG] 8c. MIME non autorisé: ' . $detectedMime);
            Logger::error('ERREUR: Invalid MIME type: ' . $detectedMime);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid MIME type. Detected: ' . $detectedMime
            ], 400);
            return;
        }
        Logger::debug('[UPLOAD-DEBUG] 8d. MIME autorisé');

        // Validate file size (50MB max)
        $maxFileSize = 50 * 1024 * 1024;
        Logger::debug('[UPLOAD-DEBUG] 9. Vérification taille: ' . $file['size'] . ' octets (max: ' . $maxFileSize . ')');
        if ($file['size'] > $maxFileSize) {
            Logger::debug('[UPLOAD-DEBUG] 9a. Fichier trop grand');
            Logger::error('ERREUR: File too large (' . $file['size'] . ' bytes)');
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'File too large. Maximum size: 50MB'
            ], 400);
            return;
        }
        Logger::debug('[UPLOAD-DEBUG] 9b. Taille OK');

        // Create uploads directory if it doesn't exist
        Logger::debug('[UPLOAD-DEBUG] 10. Vérification répertoire upload: ' . $this->uploadDir);
        if (!file_exists($this->uploadDir)) {
            Logger::debug('[UPLOAD-DEBUG] 10a. Répertoire inexistant, création...');
            if (!mkdir($this->uploadDir, 0755, true)) {
                Logger::debug('[UPLOAD-DEBUG] 10b. ÉCHEC création répertoire');
                Logger::error('ERREUR: Failed to create upload directory');
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Failed to create upload directory'
                ], 500);
                return;
            }
            Logger::debug('[UPLOAD-DEBUG] 10c. Répertoire créé');
        } else {
            Logger::debug('[UPLOAD-DEBUG] 10d. Répertoire existe déjà');
        }

        // Generate unique filename to prevent collisions
        $uniqueId = uniqid('pres_', true) . '.' . $extension;
        $targetPath = $this->uploadDir . $uniqueId;
        Logger::debug('[UPLOAD-DEBUG] 11. Chemin cible: ' . $targetPath);

        // Move uploaded file to target location
        Logger::debug('[UPLOAD-DEBUG] 12. Déplacement du fichier vers: ' . $targetPath);
        Logger::debug('UploadController: Moving file from ' . $file['tmp_name'] . ' to ' . $targetPath);
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            Logger::debug('[UPLOAD-DEBUG] 12a. ÉCHEC déplacement du fichier');
            Logger::error('ERREUR: Failed to save uploaded file');
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to save uploaded file'
            ], 500);
            return;
        }
        Logger::debug('[UPLOAD-DEBUG] 12b. Fichier déplacé avec succès');
        Logger::debug('UploadController: File successfully saved to ' . $targetPath);
        if (file_exists($targetPath)) {
            Logger::debug('[UPLOAD-DEBUG] 12c. Fichier existe: ' . filesize($targetPath) . ' octets');
            Logger::debug('UploadController: File exists at ' . $targetPath . ', size: ' . filesize($targetPath));
        } else {
            Logger::warning('[UPLOAD-DEBUG] 12d. ATTENTION: Fichier NON trouvé à: ' . $targetPath);
            Logger::warning('UploadController: WARNING - File does NOT exist at ' . $targetPath);
        }

        // For PPTX and ODP: convert to PDF using LibreOffice
        Logger::debug('[UPLOAD-DEBUG] 13. Extension: ' . $extension . ' - Conversion nécessaire: ' . ($extension === 'pptx' || $extension === 'odp' ? 'OUI' : 'NON'));
        if ($extension === 'pptx' || $extension === 'odp') {
            $pdfFilename = pathinfo($uniqueId, PATHINFO_FILENAME) . '.pdf';
            $pdfPath = $this->uploadDir . $pdfFilename;

            // Escape paths for security
            $escapedUploadDir = escapeshellarg($this->uploadDir);
            $escapedTargetPath = escapeshellarg($targetPath);

            // LibreOffice conversion command
            Logger::debug('[UPLOAD-DEBUG] 14. Préparation conversion LibreOffice...');
            // Use custom user profile to avoid permission issues
            $tempProfile = '/tmp/libreoffice_profile_' . getmypid();
            if (!file_exists($tempProfile)) {
                Logger::debug('[UPLOAD-DEBUG] 14a. Création profil LibreOffice: ' . $tempProfile);
                mkdir($tempProfile, 0777, true);
            }

            $command = "HOME={$tempProfile} libreoffice --headless --norestore --convert-to pdf --outdir {$escapedUploadDir} {$escapedTargetPath} 2>/dev/null";
            Logger::debug('[UPLOAD-DEBUG] 14b. Commande: ' . $command);

            // Execute conversion
            Logger::debug('[UPLOAD-DEBUG] 15. Exécution commande de conversion...');
            exec($command, $output, $returnCode);
            Logger::debug('[UPLOAD-DEBUG] 15a. Return code: ' . $returnCode);
            Logger::debug('[UPLOAD-DEBUG] 15b. Output: ' . print_r($output, true));

            // Wait for LibreOffice to finish writing the file
            $maxAttempts = 10;
            $attempt = 0;
            $converted = false;
            Logger::debug('[UPLOAD-DEBUG] 16. Attente conversion (max ' . $maxAttempts . ' tentatives)...');

            while ($attempt < $maxAttempts) {
                $attempt++;
                Logger::debug('[UPLOAD-DEBUG] 16a. Tentative ' . $attempt . '...');
                if (file_exists($pdfPath)) {
                    Logger::debug('[UPLOAD-DEBUG] 16b. Fichier PDF trouvé !');
                    $converted = true;
                    break;
                }
                usleep(500000); // Wait 0.5 seconds
            }
            Logger::debug('[UPLOAD-DEBUG] 16c. Tentatives totales: ' . $attempt);

            // Check if conversion succeeded
            if ($converted && file_exists($pdfPath)) {
                Logger::debug('[UPLOAD-DEBUG] 17. Conversion réussie !');
                Logger::info('SUCCÈS: Conversion réussie (attempts: ' . $attempt . ')');
                // Remove original file (PPTX or ODP)
                unlink($targetPath);
                $targetPath = $pdfPath;
                $extension = 'pdf';
            } else {
                // Conversion failed - clean up and return error
                Logger::debug('[UPLOAD-DEBUG] 17a. Conversion ÉCHOUÉE');
                Logger::debug('[UPLOAD-DEBUG] 17b. Output: ' . print_r($output, true));
                Logger::debug('[UPLOAD-DEBUG] 17c. Attempts: ' . $attempt);
                if (file_exists($targetPath)) {
                    unlink($targetPath);
                }
                Logger::error('ERREUR CONVERSION: ' . implode(' ', $output) . ' (attempts: ' . $attempt . ')');
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Failed to convert file to PDF',
                    'details' => implode("\n", $output),
                    'attempts' => $attempt
                ], 500);
                return;
            }
            Logger::debug('[UPLOAD-DEBUG] 18. Utilisation du PDF converti: ' . $pdfPath);
        }

        Logger::debug('[UPLOAD-DEBUG] 19. Préparation réponse finale...');
        Logger::info('Réponse: ' . json_encode([
            'status' => 'success',
            'file' => $targetPath,
            'exists' => file_exists($targetPath) ? 'YES' : 'NO'
        ]));

        // Return success response
        Logger::debug('[UPLOAD-DEBUG] 20. Préparation URL relative: ' . '/tmp/' . basename($targetPath));
        Logger::debug('[UPLOAD-DEBUG] 21. Vérification existence finale: ' . (file_exists($targetPath) ? 'OUI' : 'NON'));

        $relativeUrl = '/tmp/' . basename($targetPath);

        Logger::debug('[UPLOAD-DEBUG] 22. Réponse JSON prête à être envoyée');
        $this->jsonResponse([
            'status' => 'success',
            'file_path' => $targetPath,
            'file_name' => $file['name'],
            'file_type' => $extension,
            'display_method' => 'pdf',
            'url' => $relativeUrl,
            'original_extension' => $fileInfo['extension']
        ]);
        Logger::debug('[UPLOAD-DEBUG] 23. Réponse envoyée avec succès');
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
