<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

    // $fd = fopen("tmp/StatsLogs.txt","r");
    // echo "test";
    // echo fread($fd, filesize("tmp/StatsLogs.txt"));
    print_r(file("tmp/StatsLogs.txt"));
    // fclose($fd);
?>
<html lang="fr">
<head>
	<title>Automatic translation for teaching purposes: data and statistics</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="favicon.png" />

</head>
<body>
  <main>
    <h1>OMIST : Statistics</h1>
  </main>



</body>
</html>
