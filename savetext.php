<?php
require_once __DIR__ . '/Security/validate.php';
require_once __DIR__ . '/Security/validate_room_token.php';
requireRoomToken();

// Input validation and sanitization
$ligneID = validateLineId($_POST["lineid"] ?? 0);
$text = sanitizeText($_POST["text"] ?? '');
$lang = validateLanguage($_POST["inputlanguage"] ?? 'fr');
$roomid = $_POST["ROOMID"] ?? '';
$lastlines = 100000;
if (isset($_POST["LastLines"])) $lastlines = (int)$_POST["LastLines"];

// ===== VALIDATION ROOMID =====
if (!validateRoomId($roomid)) {
    http_response_code(400);
    die("ROOMID invalide");
}

//GetMaxId
$ligneID = 0;
$TLines=[];
if (file_exists("tmp/Link_".$roomid)) {
  $fd=fopen("tmp/Link_".$roomid,"r");
  while($ligne=trim(fgets($fd,4096))) {
    if ($ligne != "") {
      $TLines[]=$ligne;
      $ligneID++;
    }
  }
  fclose($fd);
  $ligneID=explode(";",$TLines[count($TLines)-1])[0]+1;
  $TLines[]=$ligneID.";".$lang.";".$text;
  if (count($TLines)>$lastlines) {
    $TLines=array_slice($TLines,count($TLines)-$lastlines);
  }
} else {
  $TLines[]=$ligneID.";".$lang.";".$text;
}

// Use file_lock to prevent race conditions
$fd = fopen("tmp/Link_".$roomid, "c+");
if (flock($fd, LOCK_EX)) {
    ftruncate($fd, 0);
    fputs($fd, implode("\n", $TLines)."\n");
    fflush($fd);
    flock($fd, LOCK_UN);
}
fclose($fd);

?>
