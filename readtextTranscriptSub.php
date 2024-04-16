<?php

include("Security/referer.php");

$roomid = $_POST["ROOMID"];
$order=0;
if (isset($_POST["ORDER"])) $order=$_POST["ORDER"];

if (file_exists("tmp/VerylastSub_".$roomid)) {
  $fd=fopen("tmp/VerylastSub_".$roomid,"r");
  echo fgets($fd);
  fclose($fd);
}

?>
