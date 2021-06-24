<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');


 //   exit;
?>
<!doctype html>
<html lang="fr">
<head>
	<title>OMIST Tools</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
	<h1>OMIST Tools</h1>
<?php

if ($_POST["submit"] && $_FILES["fileToUpload"]["tmp_name"] != "") {

$command = 'bash ./CreateAppOnly.sh "'.$_FILES["fileToUpload"]["tmp_name"].'" >/dev/null 2>/dev/null';
system($command);
echo '<a href="tmp/output.html">Result</a>';
} else {
    echo '    <form action="index.php" method="post" enctype="multipart/form-data">';
    echo 'Select PPTX file :';
    echo '<input type="file" name="fileToUpload" id="fileToUpload">';
    echo '<input type="submit" value="Upload File" name="submit">';
    echo '   </form>';
}
?>

</body>
</html>
