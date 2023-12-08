<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $speech = $argv[5];

    $command = 'bash '.dirname(__FILE__).'/CreateAppOnlyPDF.sh '.$randompath.' "'.$filename.'" "'.$speech.'" >/dev/null 2>/dev/null';
    system($command);
}
?>

