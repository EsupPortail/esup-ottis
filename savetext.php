<?php

$ligneID = $_POST["lineid"];
$text = $_POST["text"];
$lang = $_POST["inputlanguage"];
$roomid = $_POST["ROOMID"];

//GetMaxId
$ligneID = 0;
if (file_exists("tmp/Link_".$roomid)) {
  $fd=fopen("tmp/Link_".$roomid,"r");
  while($line=fgets($fd,4096)) $ligneID++;
  fclose($fd);
}


$fd=fopen("tmp/Link_".$roomid,"a");
fputs($fd,$ligneID.";".$lang.";".$text."\n");
fclose($fd);

?>
