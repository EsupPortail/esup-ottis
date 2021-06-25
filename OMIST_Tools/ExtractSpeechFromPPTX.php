<?php
if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {

$command = 'bash ./ExtractSpeechFromPPTX.sh "'.$_FILES["fileToUpload"]["tmp_name"].'" >/dev/null 2>/dev/null';
system($command);

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="speech.txt"');
readfile('tmp/speech.txt');
}
?>

