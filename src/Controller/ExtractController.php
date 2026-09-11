<?php

namespace App\Controller;

use App\Utils\PPTXNotesExtractor;

/**
 * ExtractController - Handles PowerPoint and document extraction operations
 */
class ExtractController extends BaseController
{
    /**
     * Base tmp directory path
     */
    private string $tmpDir;

    /**
     * Proxy command prefix
     */
    private string $proxy;

    protected function initialize(): void
    {
        parent::initialize();
        $this->tmpDir = sys_get_temp_dir() . '/omist/';
        $this->proxy = getenv('HTTP_PROXY') ?: '';
    }

    /**
     * Extract slides and notes from presentation
     *
     * Handles both POST (extraction) and GET (form display) requests.
     *
     * @return void
     */
    public function slidesNotes(): void
    {
        if ($this->router->getMethod() === 'POST') {
            $this->pptxNotes();
        } else {
            $this->jsonResponse(['error' => 'POST required'], 405);
        }
    }

    /**
     * Extract slides and notes as HTML
     *
     * Similar to slidesNotes but returns HTML format.
     *
     * @return void
     */
    public function slidesNotesAppHTML(): void
    {
        if ($this->router->getMethod() === 'POST') {
            // Verify CSRF token
            try {
                \App\Utils\Csrf::verifyRequest();
            } catch (\Exception $e) {
                $this->jsonResponse(['error' => 'CSRF token validation failed'], 403);
                return;
            }

            ini_set('upload_max_filesize', '50M');
            ini_set('post_max_size', '50M');

            // Handle file upload or uncloudlink
            $tmp_file = $this->getPresentationFile();
            if ($tmp_file === false) {
                return;
            }

            $flag_justif = false;
            $filename = $this->tmpDir . 'tmpfiles/test.html';

            $contenttxt = file_get_contents($tmp_file);
            $content = explode("\n", $contenttxt);
            $txtslidenotes = '';
            $T_images = [];

            foreach ($content as $txt) {
                if (strpos('_' . $txt, 'var slidesnotes = ') > 0) {
                    $txtslidenotes = str_replace('var slidesnotes = ', '', $txt);
                    $txtslidenotes = substr($txtslidenotes, 0, strlen($txtslidenotes) - 1);
                }
                if (strpos('_' . $txt, 'Timages.push(') > 0) {
                    $txtimage = str_replace("Timages.push('", '', $txt);
                    $txtimage = str_replace("' );", '', $txtimage);
                    $T_images[] = $txtimage;
                }
            }

            $Tnotes = (array) json_decode($txtslidenotes);
            $notes = [];

            // Récupération de la première langue des notes uniquement.
            foreach ($Tnotes as $lang => $noteslang) {
                $notes = $noteslang;
                break;
            }

            $n = count($notes);
            $T = [];
            $T['Images'] = $T_images;
            $T['Slide'] = 0;
            $T['Notes'] = [];
            $notes[$n] = $T;

            echo json_encode($notes);
            exit;
        } else {
            $this->jsonResponse(['error' => 'POST required'], 405);
        }
    }

    /**
     * Extract slides and notes as text
     *
     * Extracts and returns presentation content as plain text.
     *
     * @return void
     */
    public function slidesNotesTxt(): void
    {
        if ($this->router->getMethod() === 'POST') {
            $tmp_file = $_FILES['ppt_presentation']['tmp_name'] ?? '';

            $flag_justif = false;
            $filename = $this->tmpDir . 'tmpfiles/test.pptx';

            if (is_uploaded_file($tmp_file)) {
                if (!move_uploaded_file($tmp_file, $filename)) {
                    echo 'ERROR UPLOAD !!!!';
                    exit;
                }
            }

            $contenttxt = file_get_contents($filename);
            $contenttxt = $contenttxt . "\n--------------END----------";
            $content = explode("\n", $contenttxt);
            $notes = [];
            $num = 0;
            $notesslide = [];

            foreach ($content as $txt) {
                if ((strpos('_' . $txt, '----------') > 0) || (strpos('_' . $txt, '[forward]') > 0)) {
                    if ($num > 0) {
                        $slidenotes = [];
                        $slidenotes['Slide'] = $num;
                        $slidenotes['Notes'] = str_replace("\r", '', $notesslide);
                        $notes[] = $slidenotes;
                        $notesslide = [];
                    }
                    $num++;
                } else {
                    $notesslide[] = $txt;
                }
            }

            echo json_encode($notes);
            if (file_exists($filename)) {
                unlink($filename);
            }
            exit;
        } else {
            $this->jsonResponse(['error' => 'POST required'], 405);
        }
    }

    /**
     * Extract PPTX images
     *
     * Extracts images from PPTX files. Requires POST method.
     *
     * @return void
     */
    public function pptxImages(): void
    {
        if ($this->router->getMethod() === 'POST') {
            // Verify CSRF token
            try {
                \App\Utils\Csrf::verifyRequest();
            } catch (\Exception $e) {
                $this->jsonResponse(['error' => 'CSRF token validation failed'], 403);
                return;
            }

            ini_set('upload_max_filesize', '50M');
            ini_set('post_max_size', '50M');

            // Check for input
            if (!isset($_FILES['ppt_presentation']) && !isset($_POST['uncloudlink'])) {
                echo json_encode(['error' => 'No file or uncloudlink provided']);
                exit;
            }

            // Handle file upload or uncloudlink
            $tmp_file = $this->getPresentationFile();
            if ($tmp_file === false) {
                return;
            }

            // Generate HTML with notes and images
            $randompath = md5(gmdate('U'));
            $workDir = sys_get_temp_dir() . '/' . $randompath;

            // Créer un répertoire temporaire
            if (!file_exists($workDir)) {
                mkdir($workDir, 0777, true);
            }
            $imagesDir = $workDir . '/images';
            if (!file_exists($imagesDir)) {
                mkdir($imagesDir, 0777, true);
            }

            // Extraire les notes
            try {
                $notes = PPTXNotesExtractor::extract($tmp_file);
            } catch (\Exception $e) {
                error_log('Note extraction failed: ' . $e->getMessage());
                echo json_encode(['error' => 'Failed to extract notes: ' . $e->getMessage()]);
                exit;
            }

            // Extraire les images (si LibreOffice/ImageMagick disponibles)
            $T_images = [];
            try {
                $pdfFile = null;

                // Convertir PPTX en PDF
                $cmd = 'soffice --headless --convert-to pdf:impress_pdf_Export ' . escapeshellarg($tmp_file) . ' --outdir ' . escapeshellarg($workDir) . ' 2>/dev/null';
                exec($cmd, $output, $return);

                // LibreOffice utilise le nom original du fichier pour le PDF
                $pdfFiles = glob($workDir . '/*.pdf');
                if ($return === 0 && !empty($pdfFiles)) {
                    $pdfFile = $pdfFiles[0];
                    // Convertir PDF en images
                    $cmd = 'convert -density 100 ' . escapeshellarg($pdfFile) . ' -scale 1024x768 ' . escapeshellarg($imagesDir . '/slide-%03d.jpg') . ' 2>/dev/null';
                    exec($cmd, $output, $return);

                    // Lister les images générées
                    if ($return === 0) {
                        $imageFiles = glob($imagesDir . '/slide-*.jpg');
                        natsort($imageFiles);
                        foreach ($imageFiles as $img) {
                            $T_images[] = basename($img);
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log('Image extraction failed: ' . $e->getMessage());
            }

            // Ajouter les images à chaque slide
            foreach ($notes as &$note) {
                $note['Images'] = $T_images;
            }
            unset($note);

            // Retourner le résultat
            echo json_encode($notes);

            // Nettoyer les fichiers temporaires
            $this->cleanupTempDir($workDir);
            exit;
        } else {
            $this->jsonResponse(['error' => 'POST required'], 405);
        }
    }

    /**
     * Extract PPTX notes using the PPTXNotesExtractor service
     *
     * Uses the PPTXNotesExtractor service for note extraction. Requires POST method.
     *
     * @return void
     */
    public function pptxNotes(): void
    {
        if ($this->router->getMethod() === 'POST') {
            ini_set('upload_max_filesize', '50M');
            ini_set('post_max_size', '50M');

            $tmp_file = $_FILES['ppt_presentation']['tmp_name'] ?? '';

            if ($tmp_file != '') {
                // Convertir ODP en PPTX si nécessaire
                $convertedFile = $tmp_file;
                $isTempFile = false;

                // Détecter le format par l'extension
                $fileName = $_FILES['ppt_presentation']['name'];
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if ($extension === 'odp') {
                    // Convertir ODP en PPTX via LibreOffice
                    $tempDir = sys_get_temp_dir();

                    $cmd = 'soffice --headless --convert-to pptx ' . escapeshellarg($tmp_file) . ' --outdir ' . escapeshellarg($tempDir) . ' 2>/dev/null';
                    exec($cmd, $output, $return);

                    if ($return === 0) {
                        $pptxFiles = glob($tempDir . '/*.pptx');
                        if (!empty($pptxFiles)) {
                            $convertedFile = $pptxFiles[0];
                            $isTempFile = true;
                        }
                    }

                    if (!$isTempFile) {
                        echo json_encode(['error' => 'Failed to convert ODP to PPTX']);
                        exit;
                    }
                }

                // Utiliser PPTXNotesExtractor
                try {
                    $notes = PPTXNotesExtractor::extract($convertedFile);
                    echo json_encode($notes);
                } catch (\Exception $e) {
                    error_log("Erreur d'extraction des notes PPTX: " . $e->getMessage());
                    echo json_encode(['error' => 'Failed to extract notes: ' . $e->getMessage()]);
                }

                // Nettoyer le fichier temporaire si converti
                if ($isTempFile && file_exists($convertedFile)) {
                    unlink($convertedFile);
                }
            }
            exit;
        } else {
            $this->jsonResponse(['error' => 'POST required'], 405);
        }
    }

    /**
     * Save transcription subtitles text
     *
     * Receives text via GET and saves it to a temporary file for the room.
     * Used by TranscriptionSub.php legacy functionality.
     *
     * @return void
     */
    public function transcriptionSub(): void
    {
        // Read raw POST data
        $rawPostData = file_get_contents('php://input');
        error_log('transcriptionSub: RAW POST = ' . $rawPostData);

        // Parse as URL-encoded form data
        $postData = [];
        if (!empty($rawPostData)) {
            parse_str($rawPostData, $postData);
            error_log('transcriptionSub: Parsed data = ' . print_r($postData, true));
        }

        // Get parameters from multiple sources
        $texte = $postData['texte'] ?? $_POST['texte'] ?? $this->requestParam('texte', '');
        $roomId = $postData['ROOMID'] ?? $_POST['ROOMID'] ?? $this->requestParam('ROOMID', '');

        // Debug logging
        if (empty($texte) || empty($roomId)) {
            error_log('transcriptionSub: FINAL texte=' . var_export($texte, true) . ', roomId=' . var_export($roomId, true));
            error_log('transcriptionSub: _POST=' . print_r($_POST, true));
            error_log('transcriptionSub: _REQUEST=' . print_r($_REQUEST, true));
        }

        if (empty($texte) || empty($roomId)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Paramètres manquants : texte ou ROOMID.'
            ], 400);
        }

        // Nettoyer le texte
        $texte = html_entity_decode(strip_tags($texte));

        // Remplacer les balises <span>
        if (strpos($texte, '<span>') !== false) {
            $texte = str_replace('<span>', "<span style='font-size: 0.7em; font-style: italic;'>", $texte);
        }

        // Assurer que le répertoire tmp existe et est accessible en écriture
        if (!is_dir($this->tmpDir)) {
            if (!mkdir($this->tmpDir, 0777, true)) {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Impossible de créer le répertoire temporaire'
                ], 500);
                return;
            }
        }

        if (!is_writable($this->tmpDir)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Répertoire temporaire non accessible en écriture'
            ], 500);
            return;
        }

        // Sauvegarder dans tmp/VerylastSub_{roomId}
        $filename = $this->tmpDir . 'VerylastSub_' . $roomId;
        $result = file_put_contents($filename, $texte . "\n", FILE_APPEND);

        if ($result === false) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Impossible de sauvegarder le texte temporaire'
            ], 500);
            return;
        }

        $this->jsonResponse(['status' => 'success', 'message' => 'Texte sauvegardé avec succès', 'texte' => $texte, 'roomId' => $roomId, 'filename' => $filename]);
    }

    /**
     * Get presentation file from either upload or uncloudlink
     *
     * @return string|false Path to the presentation file or false on error
     */
    private function getPresentationFile(): string|false
    {
        // Handle uncloudlink
        if (isset($_POST['uncloudlink']) && trim($_POST['uncloudlink']) != '') {
            $uncloudlink = $_POST['uncloudlink'];
            if (strpos($uncloudlink, 'download') === false) {
                $uncloudlink .= '/download';
            }

            // For HTML: use .html extension
            if (strpos($uncloudlink, '.html') !== false) {
                $tmp_file = '/var/www/html/uploads/uncloudfile_' . md5(gmdate('U')) . '.html';
                $command = $this->proxy . 'curl -q ' . escapeshellarg($uncloudlink) . ' -o ' . escapeshellarg($tmp_file);
            } else {
                // For PPTX: use .pptx extension
                $tmp_file = '/var/www/html/uploads/uncloudfile_' . md5(gmdate('U')) . '.pptx';
                $command = $this->proxy . 'curl -q ' . escapeshellarg($uncloudlink) . ' -o ' . escapeshellarg($tmp_file);
            }

            $out = exec($command);
            if (!file_exists($tmp_file)) {
                echo json_encode(['error' => 'Failed to download file from uncloudlink']);
                exit;
            }
            return $tmp_file;
        }

        // Handle file upload
        if (!isset($_FILES['ppt_presentation']) || $_FILES['ppt_presentation']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'Uploaded file not found or upload error: ' . ($_FILES['ppt_presentation']['error'] ?? 'No file')]);
            exit;
        }

        $tmp_file = $_FILES['ppt_presentation']['tmp_name'];
        if (!file_exists($tmp_file)) {
            echo json_encode(['error' => "Temporary file does not exist: $tmp_file"]);
            exit;
        }

        return $tmp_file;
    }

    /**
     * Clean up temporary directory
     *
     * @param string $dir Directory to clean up
     */
    private function cleanupTempDir(string $dir): void
    {
        if (file_exists($dir)) {
            // Supprimer d'abord les fichiers dans les sous-répertoires
            if (is_dir($dir . '/images')) {
                array_map('unlink', glob($dir . '/images/*'));
                rmdir($dir . '/images');
            }
            // Supprimer les autres fichiers dans dir
            array_map('unlink', glob($dir . '/*'));
            rmdir($dir);
        }
    }
}
