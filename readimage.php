<?php
include("Security/referer.php");

$random = $_POST["random"];
$roomid = $_POST["ROOMID"];
//echo $ligneID."   ".$roomid."\n";
if (file_exists("tmp/ImageID_".$roomid)) {
    $randomid = file_get_contents("tmp/ImageID_".$roomid);
    if ($random != $randomid) {
      echo $randomid."|||".file_get_contents("tmp/Image_".$roomid);
    }
}

?>
