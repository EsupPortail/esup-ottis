<?php
include("Security/referer.php");

$ligneID = $_POST["lineid"];
$text = $_POST["text"];
$lang = $_POST["inputlanguage"];
$roomid = $_POST["ROOMID"];

$fd=fopen("tmp/LinkQuestions_".$roomid,"a");
fputs($fd,$ligneID.";".$lang.";".$text."\n");
fclose($fd);

?>
