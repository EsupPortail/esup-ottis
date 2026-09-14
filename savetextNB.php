<?php
include("Security/referer.php");

//$ligneID = $_POST["lineid"];
$text = $_POST["text"];
$lang = $_POST["inputlanguage"];
$roomid = $_POST["ROOMID"];

$text = str_replace("<br","$$$<br",$text);
$text = str_replace("<div","$$$<div",$text);
$text = str_replace("<p","$$$<p",$text);
$text = strip_tags($text);
$Ttext = explode("$$$",$text);


$fd=fopen("tmp/Link_".$roomid,"w");
for($i=0;$i<count($Ttext);$i++) {
  fputs($fd,$i.";".$lang.";".strip_tags($Ttext[$i])."\n");
}


fclose($fd);

// Clean translated
$fd=fopen("tmp/Translated_".$roomid,"w");
fclose($fd);

?>
