<?php

if (isset($argc)) {
    



    $randompath = $argv[1];
    $filename = $argv[2];
    $From = $argv[3];
    $To = $argv[4];
    $AspectRatio = $argv[5];
    $KeepAspectRatio = $argv[6];
    $Delay = $argv[7];
    $longrun = $argv[8];
    $licence=$argv[9];
    if (isset($argv[10])){
        $TranslateLang = $argv[10];
    } else {
        $TranslateLang="";
    }
    if ($TranslateLang == "") {
        $command = 'bash '.dirname(__FILE__).'/CreateAppComplete.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' '.$licence.' >/dev/null 2>/dev/null';
        
        echo $command;
        system($command);
    } else {
        $command = 'bash '.dirname(__FILE__).'/CreateAppComplete_translate.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" "'.$TranslateLang.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' '.$licence.' >/dev/null 2>/dev/null';
        system($command);
        //echo "ERREUR";
    }
} else {
    print_r($_POST);
    print_r($_FILES);
}


?>
