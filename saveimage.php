<?php

$random = $_POST["random"];
$url = $_POST["image"];
$roomid = $_POST["ROOMID"];

$fd=fopen("tmp/ImageID_".$roomid,"w");
fputs($fd,$random);
fclose($fd);

$fd=fopen("tmp/Image_".$roomid,"w");
fputs($fd,$url);
fclose($fd);

?>
