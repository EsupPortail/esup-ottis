<?php

header("Refresh:10");

session_start();
// include(dirname(__FILE__)."/WriteToDatabase.php");

// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

include("../Security/Authentification.php");

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="e18XXX";
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

if (! isset($external)) $external=false;
if ($external) $uid="external";

$fake=false;

AuthenticateNotStudent($uid);

# Connexion à la base de données
include(dirname(__FILE__)."/config_database.php");

try{
    $pdo = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
}catch(PDOException $err){
    echo "Database connection problem: " . $err->getMessage();
}

$pdo->exec('START TRANSACTION'); # begin transaction

$requete_SQL = 'SELECT * from tasks_queue where randompath="'.$_SESSION['randompath'].'"';
$insertCommande = $pdo->prepare($requete_SQL);
$insertCommande->execute();
$num_of_rows = $insertCommande->rowCount();


include("../config.php");


?>
<!doctype html>
<html lang="fr">
<head>
	<title>Slide Manipulation Tools</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="jquery/jquery-ui.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="favicon.png" />
    <script src="jquery/jquery.js"></script>
    <script src="jquery/jquery-ui.min.js"></script>
<style type="text/css">

@import url('https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap');

h1 {
      font-family: 'Fjalla One', sans-serif;
}
#logo {
  position: fixed;
  top:0px;
  right:0px;
  z-index:1000;
}
</style>

</script>
</head>
<body onload="Init();">
	<h1>Slide Manipulation Tools</h1>
  <div id="logo"><img src="logo-ufr-sciences.jpg" height="80"/></div>

    <div>
        <?php
        $file = 'tmp/output_'.$_SESSION['randompath'].'.zip';
            if ($num_of_rows==0){
                echo "<div>The process is complete. You can download the files by clicking on the following link : </div>";
                echo "<a href=$file>Download ZIP file</a>";
                echo "<div>You have 24 hours to download the files. After this time, it will no longer be possible to retrieve them.</div>";
            }
            else {
                echo "<div>The process is not yet complete, please wait.</div>";
            }
                
        ?>
    </div>
</body>

</html>