<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $From = $argv[3];
    $To = $argv[4];

    $command = 'bash '.dirname(__FILE__).'/TranslateSCORM.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" >/dev/null 2>/dev/null';
    system($command);
}

?>
