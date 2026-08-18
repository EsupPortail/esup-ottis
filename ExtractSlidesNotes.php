<?php 
//include("Security/referer.php");

ini_set ( "upload_max_filesize" , "50M" );
ini_set ( "post_max_size" , "50M" );

$tmp_file = $_FILES['ppt_presentation']['tmp_name'];

/*

$flag_justif=false;
$filename = "tmpfiles/test.pptx";

if( is_uploaded_file($tmp_file) ) {
    if (!move_uploaded_file($tmp_file, $filename)) {
        echo "ERROR UPLOAD !!!!"; exit;
    }
} else {
    echo "ERROR PB UPLOAD !!!!"; exit;
}
*/
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

/*
$textNotes=array();
$fd=zip_open($filename);
do {
    $entry = zip_read($fd);
    if (is_resource($entry) && strpos(zip_entry_name($entry),"t/notesSlides/notes") > 0) {
        $entryname = zip_entry_name($entry);
        $number = 1*(str_replace(".xml","",str_replace("ppt/notesSlides/notesSlide","",$entryname) ))-1;
        $slidenumber = $number;
        zip_entry_open($fd, $entry, "r");
        $note = zip_entry_read($entry, zip_entry_filesize($entry));
        $note=str_replace("<a:pPr/>","\n<a:pPr/>",$note);
        $note=str_replace("<a:rPr ","\n<a:rPr ",$note);
        $note=str_replace("<a:endParaRPr>","\n<a:endParaRPr>",$note);
        $textonly = trim(strip_tags($note));

        zip_entry_close($entry);
//        echo "Slide #".$slidenumber."\n----------\n".$textonly."\n";
        $textNotes[] = array("Slide"=>$slidenumber,"Notes"=>explode("\n",$textonly));
    }
} while ($entry);
zip_close($fd);

function cmp($a, $b)
{
    return $a["Slide"]*1 - $b["Slide"]*1;
}

usort($textNotes, "cmp");

echo json_encode($textNotes);



//unlink($filename);
*/

?>
