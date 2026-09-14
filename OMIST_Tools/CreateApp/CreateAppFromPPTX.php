<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];

    $command = 'bash '.dirname(__FILE__).'/CreateAppOnly.sh "'.$filename.'" '.$randompath; //.' >/dev/null 2>/dev/null';
    system($command);
}

?>
