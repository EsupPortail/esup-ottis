<?php 
include("Security/referer.php");

$pincode = $_POST["pincode"];
$roomid = $_POST["roomid"];

//echo $ligneID."   ".$roomid."\n";
$valid_pin=array();
$today = gmdate("U");

if (file_exists("tmp/pincodes.txt")) {
  $fd=fopen("tmp/pincodes.txt","r");
  while($ligne=fgets($fd,4096)){
    $ligne=trim($ligne);
    $T=explode(";",$ligne);
    if (1*$today-1*$T[0] < 3600*12 && $T[2] != $roomid) { // remove pins after 12 hours
      $valid_pin[]=$ligne;
    }
  }
  fclose($fd);
}
$valid_pin[]=$today.";".$pincode.";".$roomid;
$fd=fopen("tmp/pincodes.txt","w");
for($i=0; $i<count($valid_pin); $i++) {
  fputs($fd,$valid_pin[$i]."\n");
}
fclose($fd);


?>
