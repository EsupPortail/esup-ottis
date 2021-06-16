<?php

$ligneID = $_POST["lineid"];
$roomid = $_POST["ROOMID"];
//echo $ligneID."   ".$roomid."\n";
if (file_exists("tmp/Link_".$roomid)) {
  $fd=fopen("tmp/Link_".$roomid,"r");
  while($ligne=fgets($fd,4096)){
    $T=explode(";",$ligne);
    if ($T[0]*1>$ligneID*1) {
      echo $ligne;
    }
  }
  fclose($fd);
}

?>
