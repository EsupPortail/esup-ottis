<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

    // $fd = fopen("tmp/StatsLogs.txt","r");
    // echo "test";
    // echo fread($fd, filesize("tmp/StatsLogs.txt"));
    $array_uses_teacher=file("tmp/StatsLogs.txt");
    $nb_uses_teach=sizeof($array_users_teacher);
    // echo $array_uses_teacher;
    // fclose($fd);
?>
<html lang="fr">
<head>
	<title>Automatic translation for teaching purposes: data and statistics</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="favicon.png" />
  <style>
    #nav-top {
      background-color: black;
      color: white;
      position: fixed;
      top: 0px;
      left : 0px;
      width : 100%;
      height : 63px;
    }

    #logoOMIST {
      width : 163px;
      height : auto;
      margin : 10px;
    }
  </style>
</head>
<body>
<div id="nav-top">
  <img id="logoOMIST" alt="Logo OMIST" src="images/omist-top-logo-nav_ssfd.png">
</div>
  <main>
    <h1>OMIST : Statistics</h1>
  </main>

  <div id="nb-users-teacher"> Number of uses on teacher application : <?php echo "$nb_uses_teach"; ?> 



</body>
</html>
