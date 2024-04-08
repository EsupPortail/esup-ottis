<?php

if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $Voice = $argv[3];
    $Voice=explode("_",$Voice)[0];
    $Speed = $argv[5];
    $Speed = str_replace("--wraplength=","",$Speed);
    $outputdir = "tmp/".$randompath."/";
    $outputfilename = "tmp/output_".$randompath.".mp3";
    $command = '/usr/bin/python3 '.dirname(__FILE__).'/ReadSRT.py -o '.$outputdir.' -i "'.$filename.'" -V "'.$Voice.'" -f "'.$outputfilename.'" --speed '.$Speed.' ';//>/dev/null 2>/dev/null';
    echo $command;
    system($command);
}

?>