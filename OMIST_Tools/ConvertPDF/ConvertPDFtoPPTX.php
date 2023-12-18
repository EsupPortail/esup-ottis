<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    if (isset($argv[5]))
        $speech = $argv[5];
    else {
        $speech = $argv[3];        
    }
    $command = 'bash '.dirname(__FILE__).'/ConvertPDFtoPPTX.sh '.$randompath.' "'.$filename.'" "'.$speech.'" >/dev/null 2>/dev/null';
    system($command);
}

?>
