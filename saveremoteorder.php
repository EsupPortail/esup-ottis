<?php

/*
 $nbimages = $_POST["nbimages"];
 $url = "data:image/jpeg;base64,";
for($i=0; $i<1*$nbimages;$i++) {
    $url.=$_POST["image".$i];
}
*/
$file = $_FILES['image']['tmp_name'];

$order = $_POST["ORDER"];
$roomid = $_POST["ROOMID"];

if (file_exists("tmp/remote_".$roomid)) {
  $content = file_get_contents("tmp/remote_".$roomid);
}
$towrite = "";
if ($order == "same") $towrite = $content; else $towrite = $order;

$fd=fopen("tmp/remote_".$roomid,"w");
if ($towrite != "") fputs($fd,$towrite);
fclose($fd);

?>
