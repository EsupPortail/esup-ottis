<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $From = $argv[3];
    $To = $argv[4];
    $Wraplength = $argv[5];

    if (isset($argv[7])){
        $NOPUNC = $argv[7];
    }
    else {
        $NOPUNC="";
    }

$command = 'bash '.dirname(__FILE__).'/TranslateSRT.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" '.$Wraplength.' '.$NOPUNC.' >/dev/null 2>/dev/null';
system($command);
}

?>
