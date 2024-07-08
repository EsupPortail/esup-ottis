<?php 
include("Security/referer.php");

include "config.php";

// $texte = html_entity_decode(strip_tags($_GET["texte"]));
$texte = $_GET["texte"];

if (strpos($texte, "<span>") !== false){
  $texte = str_replace("<span>","<span style='font-size: 0.7em;	font-style: italic;'>",$texte);
  // echo json_encode($texte);
}

if (isset($_GET["ROOMID"])) {
  $fd=fopen("tmp/VerylastSub_".$_GET["ROOMID"],"w");
  fwrite($fd,$texte);
  
  echo json_encode($texte);

  fclose($fd);
}else{
  echo json_encode("ERROR");
}

?>
