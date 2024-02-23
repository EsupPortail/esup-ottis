<?php
include("Security/referer.php");

$random = $_POST["random"];
/*
 $nbimages = $_POST["nbimages"];
 $url = "data:image/jpeg;base64,";
for($i=0; $i<1*$nbimages;$i++) {
    $url.=$_POST["image".$i];
}
*/
$file = $_FILES['image']['tmp_name'];
$url = "data:image/jpeg;base64,".base64_encode(file_get_contents($_FILES['image']['tmp_name']));

$roomid = $_POST["ROOMID"];

$fd=fopen("tmp/ImageID_".$roomid,"w");
fputs($fd,$random);
fclose($fd);

$fd=fopen("tmp/Image_".$roomid,"w");
fputs($fd,$url);
fclose($fd);

?>
