<?php
require_once __DIR__ . '/Security/room_tokens.php';
require_once __DIR__ . '/Security/validate_room_token.php';

// Vérifier le room_token
requireRoomToken();

$random = $_POST["random"];
//echo $ligneID."   ".$roomid."\n";
if (file_exists("tmp/ImageID_".$roomid)) {
    $randomid = file_get_contents("tmp/ImageID_".$roomid);
    if ($random != $randomid) {
      echo $randomid."|||".file_get_contents("tmp/Image_".$roomid);
    }
}

?>
