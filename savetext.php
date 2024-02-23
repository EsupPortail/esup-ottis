<?php
include("Security/referer.php");

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
  while($ligne=trim(fgets($fd,4096))) {
    if ($ligne != "") {
      $TLines[]=$ligne; //replace("\n","",$ligne);
      $ligneID++;
    }
  }
  fclose($fd);
  $ligneID=explode(";",$TLines[count($TLines)-1])[0]+1;
  $TLines[]=$ligneID.";".$lang.";".$text;
  if (count($TLines)>$lastlines) {
    $TLines=array_slice($TLines,count($TLines)-$lastlines);
  }
} else {
  $TLines[]=$ligneID.";".$lang.";".$text;
}


$fd=fopen("tmp/Link_".$roomid,"w");
fputs($fd,implode("\n",$TLines)."\n");
fclose($fd);

/*
$fd=fopen("tmp/Link_".$roomid,"a");
fputs($fd,$ligneID.";".$lang.";".$text."\n");
fclose($fd);
*/

?>
