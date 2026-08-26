<?php 
ini_set ( "upload_max_filesize" , "50M" );
ini_set ( "post_max_size" , "50M" );

$tmp_file = $_FILES['ppt_presentation']['tmp_name'];


if ($_FILES["ppt_presentation"]["tmp_name"] != "") {
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
            // LibreOffice utilise le nom original pour le fichier converti
            $pptxFiles = glob($tempDir . '/*.pptx');
            if (!empty($pptxFiles)) {
                $convertedFile = $pptxFiles[0];
                $isTempFile = true;
            }
        }
        
        if (!$isTempFile) {
            echo json_encode(["error" => "Failed to convert ODP to PPTX"]);
            exit;
        }
    }
    
    // Utiliser la nouvelle librairie d'extraction de notes PPTX
    require_once __DIR__ . '/PPTXNotesExtractor.php';
    
    try {
        $notes = PPTXNotesExtractor::extract($convertedFile);
        echo json_encode($notes);
    } catch (Exception $e) {
        error_log("Erreur d'extraction des notes PPTX: " . $e->getMessage());
        echo json_encode(["error" => "Failed to extract notes: " . $e->getMessage()]);
    }
    
    // Nettoyer le fichier temporaire si converti
    if ($isTempFile && file_exists($convertedFile)) {
        unlink($convertedFile);
    }
}

?>
