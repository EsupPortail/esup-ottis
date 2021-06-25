<?php


if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {

$From="fr-FR";
if (isset($_POST["inputlanguage"])) $From=$_POST["inputlanguage"];
$To="fr-FR";
if (isset($_POST["outputlanguage"])) $To=implode(",",$_POST["outputlanguage"]);

$command = 'bash ./TranslatePPTX.sh "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$From.'" "'.$To.'"  >/dev/null 2>/dev/null';
system($command);


header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="output.zip"');
readfile('tmp/output.zip');

}

//print_r($_POST);

?>

