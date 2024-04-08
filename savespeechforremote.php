<?php
include("Security/referer.php");

$speech = $_POST["SPEECH"];
$roomid = $_POST["ROOMID"];

if ($speech == "read") {
    if (file_exists("tmp/remotespeech_".$roomid)) {
        $content = file_get_contents("tmp/remotespeech_".$roomid);
        echo $content;
    } else {
        echo ":|:READY !:|:";
    }
} else {
    $fd=fopen("tmp/remotespeech_".$roomid,"w");
    fputs($fd,$speech);
    fclose($fd);
    echo "OK !";
}

?>
