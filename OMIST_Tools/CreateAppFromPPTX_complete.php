<?php
if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {



$command = 'bash ./CreateAppComplete.sh "'.$_FILES["fileToUpload"]["tmp_name"].'" >/dev/null 2>/dev/null';
//system($command);

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="output.html"');
//readfile('tmp/output.html');
print_r($_POST);

}
?>

