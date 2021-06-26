<?php
if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {
$randompath=md5(gmdate("U"));

$command = 'bash ./ExtractSpeechFromPPTX.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" >/dev/null 2>/dev/null';
system($command);

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="speech.txt"');
readfile('tmp/speech_'.$randompath.'.txt');
unlink('tmp/speech_'.$randompath.'.txt');

}
?>

