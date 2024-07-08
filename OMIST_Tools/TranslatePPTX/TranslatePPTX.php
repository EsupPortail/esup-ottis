<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $From = $argv[3];
    $To = $argv[4];
    if (isset($argv[6])){
        $SlideNotes = $argv[6];
    }
    else {
        $SlideNotes = "";
    }

    $command = 'bash '.dirname(__FILE__).'/TranslatePPTX.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" '.$SlideNotes.'';//'  >/dev/null 2>/dev/null';
    system($command);
}

?>

