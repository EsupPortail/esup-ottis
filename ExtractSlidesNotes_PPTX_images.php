<?php
ini_set("upload_max_filesize", "50M");
ini_set("post_max_size", "50M");
include("config.php");

require_once __DIR__ . '/Security/csrf.php';

// Vérification du token CSRF
if (!verifyCsrfRequest()) {
    http_response_code(403);
    die(json_encode(["error" => "CSRF token validation failed"]));
}

// Debug: Log all input
error_log("POST: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));

header('Content-Type: application/json');

// Check for input
if (!isset($_FILES['ppt_presentation']) && !isset($_POST["uncloudlink"])) {
    echo json_encode(["error" => "No file or uncloudlink provided"]);
    exit;
}

// Handle file upload or uncloudlink
if (isset($_POST["uncloudlink"]) && trim($_POST["uncloudlink"]) != "") {
    $tmp_file = "/var/www/html/uploads/uncloudfile_" . md5(gmdate("U")) . ".pptx";
    $uncloudlink = $_POST["uncloudlink"];
    if (strpos($uncloudlink, "download") === false) $uncloudlink .= "/download";
    $command = $proxy . 'curl -q ' . escapeshellarg($uncloudlink) . ' -o ' . escapeshellarg($tmp_file);
    $out = exec($command);
    if (!file_exists($tmp_file)) {
        echo json_encode(["error" => "Failed to download file from uncloudlink"]);
        exit;
    }
} else {
    if (!isset($_FILES['ppt_presentation']) || $_FILES['ppt_presentation']['error'] !== UPLOAD_ERR_OK) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Uploaded file not found or upload error: " . ($_FILES['ppt_presentation']['error'] ?? 'No file')]);
        exit;
    }
    $tmp_file = $_FILES['ppt_presentation']['tmp_name'];
    error_log("Temporary file path: " . $tmp_file);
    if (!file_exists($tmp_file)) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Temporary file does not exist: $tmp_file"]);
        exit;
    }
}

// Generate HTML with notes and images
$randompath = md5(gmdate("U"));

// Créer un répertoire temporaire
$workDir = sys_get_temp_dir() . '/' . $randompath;
mkdir($workDir, 0777, true);
$imagesDir = $workDir . '/images';
mkdir($imagesDir, 0777, true);

// Extraire les notes
require_once __DIR__ . '/PPTXNotesExtractor.php';
try {
    $notes = PPTXNotesExtractor::extract($tmp_file);
} catch (Exception $e) {
    error_log("Note extraction failed: " . $e->getMessage());
    echo json_encode(["error" => "Failed to extract notes: " . $e->getMessage()]);
    exit;
}

// Extraire les images (si LibreOffice/ImageMagick disponibles)
$T_images = [];
try {
    $pdfFile = null;
    
    // Convertir PPTX en PDF
    $cmd = "soffice --headless --convert-to pdf:impress_pdf_Export " . escapeshellarg($tmp_file) . " --outdir " . escapeshellarg($workDir) . " 2>/dev/null";
    exec($cmd, $output, $return);
    
    // LibreOffice utilise le nom original du fichier pour le PDF
    $pdfFiles = glob($workDir . '/*.pdf');
    if ($return === 0 && !empty($pdfFiles)) {
        $pdfFile = $pdfFiles[0];
        // Convertir PDF en images
        $cmd = "convert -density 100 " . escapeshellarg($pdfFile) . " -scale 1024x768 " . escapeshellarg($imagesDir . "/slide-%03d.jpg") . " 2>/dev/null";
        exec($cmd, $output, $return);
        
        // Lister les images générées
        if ($return === 0) {
            $imageFiles = glob($imagesDir . "/slide-*.jpg");
            natsort($imageFiles);
            foreach ($imageFiles as $img) {
                $T_images[] = basename($img);
            }
        }
    }
} catch (Exception $e) {
    error_log("Image extraction failed: " . $e->getMessage());
}

// Ajouter les images à chaque slide
foreach ($notes as &$note) {
    $note['Images'] = $T_images;
}
unset($note);

// Retourner le résultat
echo json_encode($notes);

// Nettoyer les fichiers temporaires
// Supprimer d'abord les fichiers dans les sous-répertoires
array_map('unlink', glob($workDir . '/images/*'));
rmdir($workDir . '/images');

// Supprimer les autres fichiers dans workDir
array_map('unlink', glob($workDir . '/*'));
rmdir($workDir);
?>