<?php


if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {

$randompath=md5(gmdate("U"));

$From="fr-FR";
if (isset($_POST["inputlanguage"])) $From=$_POST["inputlanguage"];
$To="fr-FR";
if (isset($_POST["outputlanguage"])) $To=implode(",",$_POST["outputlanguage"]);
$TranslateLang = "";
if (isset($_POST["translatelanguage"])) $TranslateLang=$_POST["translatelanguage"];
$KeepAspectRatio="--KeepAspectRatio=Y";
if (isset($_POST["keepaspectratio"]) && $_POST["keepaspectratio"] != "X") $KeepAspectRatio="--KeepAspectRatio=N";
$AspectRatio="--AspectRatio=16/10";
if (isset($_POST["aspectratio"])) $AspectRatio="--AspectRatio=".$_POST["aspectratio"];
$Delay="--Delay=0";
if (isset($_POST["extradelay"])) $Delay="--Delay=".$_POST["extradelay"];
$longrun=false;
if (isset($_POST["longrun"]) && $_POST["longrun"] == "long") $longrun=true;

if (!$longrun) {
if ($TranslateLang == "") {
    $command = 'bash ./CreateAppComplete.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$From.'" "'.$To.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' >/dev/null 2>/dev/null';//>/dev/null 2>/dev/null
    system($command);
} else {
    $command = 'bash ./CreateAppComplete_translate.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$From.'" "'.$To.'" "'.$TranslateLang.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' >/dev/null 2>/dev/null';//>/dev/null 2>/dev/null
    system($command);
    
}

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="output.html"');
readfile('tmp/output_'.$randompath.'.html');
unlink('tmp/output_'.$randompath.'.html');

} else {
    if ($TranslateLang == "") {
    $command = 'bash ./CreateAppComplete.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$From.'" "'.$To.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' >/dev/null 2>/dev/null';//>/dev/null 2>/dev/null
    system($command." &");
    } else {
    $command = 'bash ./CreateAppComplete_translate.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$From.'" "'.$To.'" "'.$TranslateLang.'" '.$KeepAspectRatio.' '.$AspectRatio.' '.$Delay.' >/dev/null 2>/dev/null';//>/dev/null 2>/dev/null
    system($command." &");
    
    }

    echo "<html><head><title>Result</title></head><body><p>Please be patient for one or two minutes, your file is being processed.</p><p>When it is finished, you will get the result <a href=\"tmp/output_".$randompath.".html\">here</a>.</p></body></html>";

}

}
//print_r($_POST);

?>

