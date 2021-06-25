<?php


if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {

$From="fr-FR";
if (isset($_POST["inputlanguage"])) $From=$_POST["inputlanguage"];
$To="fr-FR";
if (isset($_POST["outputlanguage"])) $To=implode(",",$_POST["outputlanguage"]);
$TranslateLang = "";
if (isset($_POST["translatelanguage"])) $TranslateLang=$_POST["translatelanguage"];

$KeepAspectRatio="--KeepAspectRatio=Y";
if (isset($_POST["keepaspectratio"]) && $_POST["keepaspectratio"] != "X") $KeepAspectRatio="--KeepAspectRatio=N";
$KeepAspectRatio="--KeepAspectRatio=Y";
$AspectRatio="--AspectRatio=16/10";
if (isset($_POST["aspectratio"])) $AspectRatio="--AspectRatio=".$_POST["aspectratio"];
$Delay="--Delay=0";
if (isset($_POST["extradelay"])) $Delay="--Delay=".$_POST["extradelay"];

if ($TranslateLang == "") {
    $command = 'bash ./CreateAppComplete.sh "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$From.'" "'.$To.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' ';//>/dev/null 2>/dev/null
    system($command);
} else {
    $command = 'bash ./CreateAppComplete_translate.sh "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$From.'" "'.$To.'" "'.$TranslateLang.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' >/dev/null 2>/dev/null';
    system($command);
    
}

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="output.html"');
readfile('tmp/output.html');

}

//print_r($_POST);

?>

