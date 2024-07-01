<?php 
include("Security/referer.php");

include "config.php";

$texte = html_entity_decode(strip_tags($_GET["texte"]));

if (isset($_GET["ROOMID"])) {
  $fd=fopen("tmp/VerylastSub_".$_GET["ROOMID"],"w");
  fwrite($fd,$texte);
  
  echo json_encode($texte);

  fclose($fd);
}else{
  echo json_encode("ERROR");
}

?>
