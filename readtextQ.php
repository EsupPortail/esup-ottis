<?php 

include("Security/referer.php");

//$ligneID = $_POST["lineid"];
$roomid = $_POST["ROOMID"];
//echo $ligneID."   ".$roomid."\n";
/*
$fd=fopen("tmp/LinkQuestions_".$roomid,"r");
while($ligne=fgets($fd,4096)){
//  $T=explode(";",$ligne);
//  if ($T[0]*1>$ligneID*1) {
  $ligne=trim($ligne);
  if ($ligne != "")
    echo $ligne;
//  }
}
fclose($fd);
*/
if (file_exists("tmp/LinkQuestions_".$roomid)) {
  echo file_get_contents("tmp/LinkQuestions_".$roomid);
} else echo "";

?>
