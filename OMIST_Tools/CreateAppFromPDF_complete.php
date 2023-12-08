<?php


if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {

$randompath=md5(gmdate("U"));

if (isset($_FILES["fileToUploadTXT"]["tmp_name"]) && trim($_FILES["fileToUploadTXT"]["tmp_name"]) != "") $speech=$_FILES["fileToUploadTXT"]["tmp_name"]; else $speech="blank.txt";

$From="fr-FR";
if (isset($_POST["inputlanguage"])) $From=$_POST["inputlanguage"];
$To="fr-FR";
if (isset($_POST["outputlanguage"])) $To=implode(",",$_POST["outputlanguage"]);

$KeepAspectRatio="--KeepAspectRatio=Y";
if (isset($_POST["keepaspectratio"]) && $_POST["keepaspectratio"] != "X") $KeepAspectRatio="--KeepAspectRatio=N";
$KeepAspectRatio="--KeepAspectRatio=Y";
$AspectRatio="--AspectRatio=16/10";
if (isset($_POST["aspectratio"])) $AspectRatio="--AspectRatio=".$_POST["aspectratio"];
$Delay="--Delay=0";
if (isset($_POST["extradelay"])) $Delay="--Delay=".$_POST["extradelay"];


$command = 'bash ./CreateAppFromPDFComplete.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$speech.'" "'.$From.'" "'.$To.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' >/dev/null 2>/dev/null';
system($command);

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="output.html"');
readfile('tmp/output_'.$randompath.'.html');
unlink('tmp/output_'.$randompath.'.html');

}


?>
