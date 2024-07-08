<?php  
include("Security/referer.php");
$external=true;
$roomid="unknown";
if (isset($_POST["ROOMID"]))
  $roomid = $_POST["ROOMID"];
else {
    echo "Access forbidden !";
    exit;
}
$duration=3600*24;
if (isset($_POST["DURATION"])) $duration=$_POST["DURATION"];

$today=1*gmdate("U");
$fd = fopen('Security/externalTokens.txt','a');
fputs($fd,$roomid.";".$today.";".$duration."\n");
fclose($fd);

echo "ok";
?>
