<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $From = $argv[3];
    $To = $argv[4];
    $speech = $argv[5];
    $AspectRatio = $argv[6];
    $KeepAspectRatio = $argv[7];
    $Delay = $argv[8];
    $licence=$argv[10];

    $command = 'bash '.dirname(__FILE__).'/CreateAppFromPDFComplete.sh '.$randompath.' "'.$filename.'" "'.$speech.'" "'.$From.'" "'.$To.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' '.$licence.' >/dev/null 2>/dev/null';
    system($command);

}


?>
