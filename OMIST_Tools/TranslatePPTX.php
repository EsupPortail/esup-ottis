<?php


if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {

$randompath=md5(gmdate("U"));

$From="fr-FR";
if (isset($_POST["inputlanguage"])) $From=$_POST["inputlanguage"];
$To="fr-FR";
if (isset($_POST["outputlanguage"])) $To=implode(",",$_POST["outputlanguage"]);
$SlideNotes="";
if (isset($_POST["slidenotes"]) && $_POST["slidenotes"] == "X") $SlideNotes="--WithNotes";

$command = 'bash ./TranslatePPTX.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$From.'" "'.$To.'" '.$SlideNotes.'  >/dev/null 2>/dev/null';
system($command);


header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="output.zip"');
readfile('tmp/output_'.$randompath.'.zip');
unlink('tmp/output_'.$randompath.'.zip');

}

?>

