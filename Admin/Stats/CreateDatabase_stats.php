<?php
include("./config_database_stats.php");

try{
    $pdo = new PDO("mysql:host=" . $dbhost_stats, $dbuser_stats, $dbpass_stats);
}catch(PDOException $err){
    echo "Database connection problem: " . $err->getMessage();
}

$requete_createDB = 'CREATE DATABASE db_stats';
$insertCommande1 = $pdo->prepare($requete_createDB);
$insertCommande1->execute();

$requete_useDB = 'USE db_stats';
$insertCommande2 = $pdo->prepare($requete_useDB);
$insertCommande2->execute();

$requete_createTable= 'CREATE TABLE statslog
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  date_raw text, #gmdate("Y-m-d\TH:i:s\Z")
  date_complete datetime, #gmdate("Y-m-d")
  date_day int, #DATE_FORMAT(date_complete, "%d");
  date_month text, #filled with an SQL query, e.g April for 2024-04-02
  date_year text, 
  user_id text,
  room_id text,
  tool text,
  origin text,
  config_teacher text,
  ip_address text,
  city text,
  country text,
  latitude float,
  longitude float,
  is_external boolean,
  char_translated int
)';

$insertCommande3 = $pdo->prepare($requete_createTable);
$insertCommande3->execute();

echo ("The database has been created successfully \n");

$command = escapeshellcmd('/usr/bin/python3 ./first_complete_db.py');
$output = shell_exec($command);
echo $output;

$requete_createTableTranslate= 'CREATE TABLE translatelog
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  date_raw text, #gmdate("Y-m-d\TH:i:s\Z")
  date_complete datetime, #gmdate("Y-m-d")
  date_day int, #SELECT DATE_FORMAT(date_complete, "%d");
  date_month text, #SELECT DATE_FORMAT(date_complete, "%M");
  date_year text, #SELECT DATE_FORMAT(date_complete, "%Y");
  origin_translate text, #Deepl Pro, Deepl Free, ...
  percentage_char int, #$percentage_DEEPL."% -> plutôt le calculer au moment du graphique pour si changement de limite de charactères ? 
  characters int, #$Stats_DEEPL["character_count"]
  char_max int #$Stats_DEEPL["character_limit"]
)';
$insertCommande4 = $pdo->prepare($requete_createTableTranslate);
$insertCommande4->execute();

echo ("The database has been created successfully \n");

$requete_createTable_IP= 'CREATE TABLE iplog
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  ip_address text,
  city text,
  country text,
  latitude float,
  longitude float
)';

$insertCommande5 = $pdo->prepare($requete_createTable_IP);
$insertCommande5->execute();

?>
