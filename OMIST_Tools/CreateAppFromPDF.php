<?php
if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {
$randompath=md5(gmdate("U"));

if (isset($_FILES["fileToUploadTXT"]["tmp_name"]) && trim($_FILES["fileToUploadTXT"]["tmp_name"]) != "") $speech=$_FILES["fileToUploadTXT"]["tmp_name"]; else $speech="blank.txt";


$command = 'bash ./CreateAppOnlyPDF.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$speech.'" >/dev/null 2>/dev/null';
system($command);

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="output.html"');
readfile('tmp/output_'.$randompath.'.html');
unlink('tmp/output_'.$randompath.'.html');
}
?>

