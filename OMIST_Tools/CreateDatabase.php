<?php
include(dirname(__FILE__)."/config_database.php");

try{
    $pdo = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
}catch(PDOException $err){
    echo "Database connection problem: " . $err->getMessage();
}

$requete_createDB = 'CREATE DATABASE db_tasks';
$insertCommande1 = $pdo->prepare($requete_createDB);
$insertCommande1->execute();

$requete_useDB = 'USE db_tasks';
$insertCommande2 = $pdo->prepare($requete_useDB);
$insertCommande2->execute();

$requete_createTable= 'CREATE TABLE tasks_queue
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  command_to_do TEXT,
  path_file TEXT,
  randompath TEXT,
  lang_from TEXT,
  lang_to TEXT,
  sub_length TEXT,
  sub_punct TEXT,
  path_file2 TEXT,
  slide_notes TEXT,
  translate_lang TEXT,
  aspect_ratio TEXT,
  keep_aspect_ratio TEXT,
  delay_slides TEXT,
  long_run TEXT,
  licence TEXT
)';
$insertCommande3 = $pdo->prepare($requete_createTable);
$insertCommande3->execute();

echo ("The database has been created successfully \n")

?>