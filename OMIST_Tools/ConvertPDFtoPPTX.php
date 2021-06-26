<?php
if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {
$randompath=md5(gmdate("U"));

if (isset($_FILES["fileToUploadTXT"]["tmp_name"]) && trim($_FILES["fileToUploadTXT"]["tmp_name"]) != "") $speech=$_FILES["fileToUploadTXT"]["tmp_name"]; else $speech="blank.txt";


$command = 'bash ./ConvertPDFtoPPTX.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$speech.'" >/dev/null 2>/dev/null';//>/dev/null 2>/dev/null
system($command);

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="output.zip"');
readfile('tmp/output_'.$randompath.'.zip');
unlink('tmp/output_'.$randompath.'.zip');
}
?>
