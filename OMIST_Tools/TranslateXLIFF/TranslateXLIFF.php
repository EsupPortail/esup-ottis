<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $From = $argv[3];
    $To = $argv[4];
    // Ajout JB (ligne suivante commentée)
    //$filename = $_FILES["fileToUpload"]["tmp_name"];
    // Fin
    $command = 'bash '.dirname(__FILE__).'/TranslateXLIFF.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" >/dev/null 2>/dev/null';

    system($command);
}


?>

