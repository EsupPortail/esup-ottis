<?php
include("Security/referer.php");

$order = $_POST["ORDER"];
$roomid = $_POST["ROOMID"];

if (file_exists("tmp/remote_".$roomid)) {
  $content = file_get_contents("tmp/remote_".$roomid);
} else $content = "";
$towrite = "";
if ($order == "same") $towrite = $content; else $towrite = $order;

$fd=fopen("tmp/remote_".$roomid,"w");
if ($towrite != "") fputs($fd,$towrite);
fclose($fd);

echo $content;
?>
