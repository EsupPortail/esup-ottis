<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $Voice = $argv[3];
    $Voice=explode("_",$Voice)[0];
    $Speed = $argv[4];
    $Speed = "1.0";
    $outputdir = "../tmp/".$randompath."/";
    $outputfilename = "../tmp/output_".$randompath.".mp3";
    $command = '/usr/bin/python3 '.dirname(__FILE__).'/ReadSRT.py -o '.$outputdir.' -i "'.$filename.'" -V "'.$Voice.'" -s '.$Speed.' -f "'.$outputfilename.'" ';//>/dev/null 2>/dev/null';
    system($command);
}

?>