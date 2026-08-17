<?php
require_once __DIR__ . '/Security/validate.php';
require_once __DIR__ . '/Security/validate_room_token.php';

requireRoomToken();

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
