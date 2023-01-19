<?php 

$external=true;
$roomid="unknown";
if (isset($_POST["ROOMID"]))
  $roomid = $_POST["ROOMID"];
else {
    echo "Access forbidden !";
    exit;
}

$today=1*gmdate("U");
$fd = fopen('Security/externalTokens.txt','a');
fputs($fd,$roomid.";".$today."\n");
fclose($fd);

echo "ok";
?>