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

$command = 'bash ./CreateAppOnly.sh test.pptx';
system($command);

?>
<a href="tmp/output.html">output</a>
</body>
</html>
