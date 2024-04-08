<?php

if (isset($_POST['randompath'])) {
$randompath=$_POST['randompath'];
if (isset($_POST['Extension'])) $extension=$_POST['Extension']; else $extension="zip";

$content=file_get_contents("fakeDB/db.json");
$alljobs=explode("|||",$content);
$selectedjobs=array();
for($i=0; $i<count($alljobs);$i++) {
    $job=$alljobs[$i];
    if (trim($job) !="") {
        $Tjob=(array) json_decode(trim($job));
        if (isset($Tjob["randompath"]) && $Tjob["randompath"] == $randompath) $selectedjobs[]=$Tjob;
    }
}

$num_of_rows = count($selectedjobs);

if ($num_of_rows == 0) {
    if (! file_exists(dirname(__FILE__)."/tmp/output_".$randompath.".".$extension)) $num_of_rows="-1"; //dirname(__FILE__)."tmp/output_".$randompath.".".$extension;    
}
echo $num_of_rows;
} else {echo "-2";}

?>