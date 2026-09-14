<?php  
exit();

$external=true;
$roomid="unknown";
if (isset($_GET["ROOMID"]))
  $roomid = $_GET["ROOMID"];
else {
    echo "Access forbidden !";
    exit;
}

$today=1*gmdate("U");
$fd = fopen('Security/externalTokens.txt','r');
$outids="";
$find=false;
while($ligne=fgetcsv($fd,4096,";")) {
    $id=trim($ligne[0]);
    $dateid=1*trim($ligne[1]);
    if (isset($ligne[2])) $duration=1*$ligne[2]; else $duration=3600*24;
    // echo $id." --- ".($today-$dateid);
    if ($id !="") {
        if (abs($today-$dateid) < $duration) {
            $outids.=$id.";".$dateid.";".$duration."\n";
            if ($roomid == $id) $find=true;
        }
    }
}
fclose($fd);
$fd = fopen('Security/externalTokens.txt','w');
fputs($fd,$outids);
fclose($fd);

if (! $find) {
    echo "This token has expired !";
    exit;
}

include("index.php");
?>
