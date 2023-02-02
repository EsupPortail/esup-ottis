<?php

$ligneID = $_POST["lineid"];
$text = $_POST["text"];
$lang = $_POST["inputlanguage"];
$roomid = $_POST["ROOMID"];
$lastlines = 100000;
if (isset($_POST["LastLines"])) $lastlines =1*$_POST["LastLines"];

//GetMaxId
$ligneID = 0;
$TLines=[];
if (file_exists("tmp/Link_".$roomid)) {
  $fd=fopen("tmp/Link_".$roomid,"r");
  while($TLines[]=trim(fgets($fd,4096))) $ligneID++;
  fclose($fd);
}

$TLines[]=$ligneID.";".$lang.";".$text;
$TLines=array_slice($TLines,-$lastlines);
$fd=fopen("tmp/Link_".$roomid,"a");
fputs($fd,implode("\n",$TLines)."\n");
fclose($fd);

//$fd=fopen("tmp/Link_".$roomid,"a");
//fputs($fd,$ligneID.";".$lang.";".$text."\n");
//fclose($fd);

?>
