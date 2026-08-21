<?php
/**
 * upload_handler.php
 * Handles presentation file upload with automatic conversion to PDF
 * All output files are PDF format for consistent display with PDF.js
 */

header('Content-Type: application/json');

// === LOGGING ===
$logFile = __DIR__ . '/uploads/upload_log.txt';
file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Nouvelle requête\n", FILE_APPEND);

// Log des infos du fichier
if (isset($_FILES['presentation_file'])) {
    $file = $_FILES['presentation_file'];
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Fichier: " . $file['name'] .
        ", Taille: " . $file['size'] .
        ", Type: " . $file['type'] .
        ", Error: " . $file['error'] . "\n", FILE_APPEND);
}

// Check if file was uploaded
if (!isset($_FILES['presentation_file'])) {
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERREUR: No file uploaded\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'No file uploaded'
    ]);
    exit;
}

// Handle upload errors
if ($_FILES['presentation_file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'Unknown upload error';
    switch ($_FILES['presentation_file']['error']) {
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
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERREUR UPLOAD: " . $errorMessage . " (code: " . $_FILES['presentation_file']['error'] . ")\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $errorMessage,
        'error_code' => $_FILES['presentation_file']['error']
    ]);
    exit;
}

$file = $_FILES['presentation_file'];
$uploadDir = __DIR__ . '/uploads/';

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
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERREUR: Invalid file type: " . $extension . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)
    ]);
    exit;
}

// Validate file size (50MB max)
$maxFileSize = 50 * 1024 * 1024;
if ($file['size'] > $maxFileSize) {
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERREUR: File too large (" . $file['size'] . " bytes)\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'File too large. Maximum size: 50MB'
    ]);
    exit;
}

// Create uploads directory if it doesn't exist
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERREUR: Failed to create upload directory\n", FILE_APPEND);
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create upload directory'
        ]);
        exit;
    }
}

// Generate unique filename to prevent collisions
$uniqueId = uniqid('pres_', true) . '.' . $extension;
$targetPath = $uploadDir . $uniqueId;

// Move uploaded file to target location
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERREUR: Failed to save uploaded file\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save uploaded file'
    ]);
    exit;
}

// For PPTX and ODP: convert to PDF using LibreOffice
if ($extension === 'pptx' || $extension === 'odp') {
    $pdfFilename = pathinfo($uniqueId, PATHINFO_FILENAME) . '.pdf';
    $pdfPath = $uploadDir . $pdfFilename;

    // Escape paths for security
    $escapedUploadDir = escapeshellarg($uploadDir);
    $escapedTargetPath = escapeshellarg($targetPath);

    // LibreOffice conversion command
    // Use custom user profile to avoid permission issues with /var/www/.cache
    // Create temp profile directory if needed
    $tempProfile = '/tmp/libreoffice_profile_' . getmypid();
    if (!file_exists($tempProfile)) {
        mkdir($tempProfile, 0777, true);
    }
    
    $command = "HOME={$tempProfile} libreoffice --headless --norestore --convert-to pdf --outdir {$escapedUploadDir} {$escapedTargetPath} 2>/dev/null";

    // Execute conversion
    exec($command, $output, $returnCode);

    // Wait a moment for LibreOffice to finish writing the file
    // LibreOffice may take some time to complete the conversion
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

    // Check if conversion succeeded (focus on file existence, not return code)
    // LibreOffice may return non-zero even on success due to warnings
    if ($converted && file_exists($pdfPath)) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "SUCCÈS: Conversion réussie (attempts: " . $attempt . ")\n", FILE_APPEND);
        // Remove original file (PPTX or ODP)
        unlink($targetPath);
        $targetPath = $pdfPath;
        $extension = 'pdf';
    } else {
        // Conversion failed - clean up and return error
        if (file_exists($targetPath)) {
            unlink($targetPath);
        }
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERREUR CONVERSION: " . implode(" ", $output) . " (attempts: " . $attempt . ")\n", FILE_APPEND);
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to convert file to PDF',
            'details' => implode("\n", $output),
            'command' => $command,
            'attempts' => $attempt,
            'pdf_exists' => file_exists($pdfPath) ? 'yes' : 'no'
        ]);
        exit;
    }
}

// Return success response
// The URL is relative to the website root
$relativeUrl = '/uploads/' . basename($targetPath);

// Log de la réponse
file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Réponse: " .
    json_encode([
        'status' => 'success',
        'file' => $targetPath,
        'exists' => file_exists($targetPath) ? 'YES' : 'NO'
    ]) . "\n", FILE_APPEND);

echo json_encode([
    'status' => 'success',
    'file_path' => $targetPath,
    'file_name' => $file['name'],
    'file_type' => $extension,
    'display_method' => 'pdf',
    'url' => $relativeUrl,
    'original_extension' => $fileInfo['extension']
]);
?>