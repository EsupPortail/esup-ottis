<?php
include("Security/referer.php");

include "config.php";

$texte = html_entity_decode(strip_tags($_GET["texte"]));

// echo json_encode("test");

if (isset($_GET["ROOMID"])) {
  $fd=fopen("tmp/VerylastSub_".$_GET["ROOMID"],"w");
  fwrite($fd,$texte);
  
  // echo json_encode($texte);

  fclose($fd);
  $myfile=fopen("tmp/VerylastSub_".$_GET["ROOMID"],"r");
  echo json_encode(fgets($myfile));
  fclose($myfile);
}else{
  echo json_encode("ERREUR");
}

?>
