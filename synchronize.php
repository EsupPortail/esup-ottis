<?php 
include("Security/referer.php");

$pincode = $_POST["pincode"];
$roomid="";
//echo $ligneID."   ".$roomid."\n";
if (file_exists("tmp/pincodes.txt")) {
  $fd=fopen("tmp/pincodes.txt","r");
  while($ligne=fgets($fd,4096)){
    $T=explode(";",$ligne);
    if ($T[1] == $pincode) {
      $roomid=$T[2];
    }
  }
  fclose($fd);
}
echo $roomid;

?>
