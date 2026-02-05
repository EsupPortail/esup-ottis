<?php

if (isset($_POST['randompath'])) {
$randompath=$_POST['randompath'];
if (isset($_POST['Extension'])) $extension=$_POST['Extension']; else $extension="zip";
# Connexion à la base de données
include(dirname(__FILE__)."/config_database.php");

try{
    $pdo = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
}catch(PDOException $err){
    echo "Database connection problem: " . $err->getMessage();
}

$pdo->exec('START TRANSACTION'); # begin transaction

$requete_SQL = 'SELECT * from tasks_queue where randompath="'.$randompath.'"';
$insertCommande = $pdo->prepare($requete_SQL);
$insertCommande->execute();
$num_of_rows = $insertCommande->rowCount();

if ($num_of_rows == 0) {
    if (! file_exists(dirname(__FILE__)."/tmp/output_".$randompath.".".$extension)) $num_of_rows="-1"; //dirname(__FILE__)."tmp/output_".$randompath.".".$extension;    
}
echo $num_of_rows;
} else {echo "-2";}

?>