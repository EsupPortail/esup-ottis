<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
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

$command = 'python3 ./CreateMultiLingualAnim.py --input=tmp/input.pptx --ImagesDir="tmp/images" --output=tmp/output.html --from="FR" --to="EN-DE"';
system($command);

?>
<a href="tmp/output.html">output</a>
</body>
</html>
