<?php
include("Security/referer.php");

include "config.php";

$texte = html_entity_decode(strip_tags($_GET["texte"]));

echo "test"

if (isset($_GET["ROOMID"])) {
  $fd=fopen("tmp/VerylastSub_".$_GET["ROOMID"],"w");
  fwrite($fd,$texte);
  
  echo $texte;

  fclose($fd);
}else{
  echo "ERREUR";
}

?>
