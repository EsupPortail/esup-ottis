<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];

    $command = 'bash '.dirname(__FILE__).'/ExtractSpeechFromPPTX.sh '.$randompath.' "'.$filename.'" >/dev/null 2>/dev/null';
    system($command);
}
?>

