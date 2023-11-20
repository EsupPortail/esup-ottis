<?php
//echo "<pre>";

if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {

$randompath=md5(gmdate("U"));

$From="fr-FR";
if (isset($_POST["inputlanguage"])) $From=$_POST["inputlanguage"];
$To="fr-FR";
if (isset($_POST["outputlanguage"])) $To=implode(",",$_POST["outputlanguage"]);
$NOPUNC="";
if (isset($_POST["nopunct"]) && $_POST["nopunct"]=="Yes") $NOPUNC="--nopunct ";
$Wraplength="--wraplength=80";
if (isset($_POST["wraplength"])) $Wraplength="--wraplength=".$_POST["wraplength"];




//$randompath="12345";
$filename = $_FILES["fileToUpload"]["tmp_name"];
//$filename = "tmp/test.srt";

$command = 'bash ./TranslateSRT.sh '.$randompath.' "'.$filename.'" "'.$From.'" "'.$To.'" '.$Wraplength.' '.$NOPUNC.' >/dev/null 2>/dev/null'; //>/dev/null 2>/dev/null
echo($command);
system($command);


header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="output.zip"');
readfile('tmp/output_'.$randompath.'.zip');
unlink('tmp/output_'.$randompath.'.zip');

}
//echo "</pre>";

?>
