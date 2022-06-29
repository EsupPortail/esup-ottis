<?php
if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {
$randompath=md5(gmdate("U"));

if (isset($_FILES["fileToUploadTXT"]["tmp_name"]) && trim($_FILES["fileToUploadTXT"]["tmp_name"]) != "") $speech=$_FILES["fileToUploadTXT"]["tmp_name"]; else $speech="blank.txt";


$command = 'bash ./ConvertPPTX_TXTtoPPTX.sh '.$randompath.' "'.$_FILES["fileToUpload"]["tmp_name"].'" "'.$speech.'" >/dev/null 2>/dev/null';//>/dev/null 2>/dev/null
system($command);

header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
header('Content-Disposition: attachment; filename="output.pptx"');
readfile('tmp/output_'.$randompath.'.pptx');
unlink('tmp/output_'.$randompath.'.pptx');
}
?>
