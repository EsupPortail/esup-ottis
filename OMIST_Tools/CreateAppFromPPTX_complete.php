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
    if (isset($argv[9])){
        $TranslateLang = $argv[9];
    }
    else {
        $TranslateLang="No";
    }
    $licence=$argv[10];
    $TranslateLang="";
    if (!$longrun) {
    if ($TranslateLang == "") {
        $command = 'bash '.dirname(__FILE__).'/CreateAppComplete.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' '.$licence; //.' >/dev/null 2>/dev/null';
        
        echo $command;
        system($command);
    } else {
        //$command = 'bash '.dirname(__FILE__).'/CreateAppComplete_translate.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" "'.$TranslateLang.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' '.$licence.' >/dev/null 2>/dev/null';
        //system($command);
        
    }

    } else {
        if ($TranslateLang == "") {
        $command = 'bash '.dirname(__FILE__).'/CreateAppComplete_long.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' >/dev/null 2>/dev/null';
        $out=exec($command);
        } else {
        $command = 'bash '.dirname(__FILE__).'/CreateAppComplete_translate.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" "'.$TranslateLang.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' >/dev/null 2>/dev/null';
        $out=shell_exec($command." &");
        
        }

        //echo "<html><head><title>Result</title></head><body><p>Please be patient for one or two minutes, your file is being processed.</p><p>When it is finished, you will get the result <a href=\"tmp/output_".$randompath.".html\">here</a>.</p></body></html>";

}

} else {
    print_r($_POST);
    print_r($_FILES);
}


?>
