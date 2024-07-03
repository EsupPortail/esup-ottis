<?php
include("./Admin/Stats/config_database_stats.php");
include("./config.php");
include("./OMIST_Tools/configOT.php");

try{
    $pdo_stats = new PDO("mysql:host=" . $dbhost_stats . ";dbname=" . $dbname_stats, $dbuser_stats, $dbpass_stats);
}catch(PDOException $err){
    echo "Database connection problem: " . $err->getMessage();
}

$res=array();
if ($WhichTranslator == "DEEPLFREE") {
  exec($proxy." curl -q https://api-free.deepl.com/v2/usage?auth_key=".$DEEPLAPIFREETOKEN." 2>/dev/null",$res);
} else {
  exec($proxy." curl -q https://api.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN." 2>/dev/null",$res);
}

print_r($res);

$Stats_DEEPL = json_decode($res[0],true);
if (isset($Stats_DEEPL["character_count"])) {
  $percentage_DEEPL = ceil(10000*$Stats_DEEPL["character_count"]/($Stats_DEEPL["character_limit"]+0.00000001))/100;
} else $percentage_DEEPL = 0;

$date_raw = gmdate("Y-m-d\TH:i:s\Z");
$date_complete = gmdate("Y-m-d");
$date_day = gmdate("d");
$date_month = gmdate("F");
$date_year = gmdate("Y");
$origin = $WhichTranslator;
$percentage_char = $percentage_DEEPL;
$characters = $Stats_DEEPL["character_count"];
$char_max = $Stats_DEEPL["character_limit"];

$requete_SQL_stats = 'INSERT INTO translatelog (date_raw, date_complete, date_day, date_month, date_year, origin, percentage_char, characters, char_max) VALUES (:date_raw, :date_complete, :date_day, :date_month, :date_year, :origin, :percentage_char, :characters, :char_max)';
$insertCommande_stats = $pdo_stats->prepare($requete_SQL_stats);

$insertCommande_stats->execute([
    'date_raw' => $date_raw, 
    'date_complete' => $date_complete, 
    'date_day' => $date_day,
    'date_month' => $date_month, 
    'date_year' => $date_year, 
    'origin' => $origin,
    'percentage_char' => $percentage_char, 
    'characters' => $characters, 
    'char_max' => $char_max
]);

try{
  echo 'test1';
  $sql = 'SELECT * FROM translatelog';
  $insertCommande_sql = $pdo_stats->prepare($sql);
  $insertCommande_sql->execute();
  $recipes = $insertCommande_sql->fetchAll();
  echo 'test2';
  print_r($recipes);
  foreach ($recipes as $recipe) {
    echo 'test3';
    echo $recipe;
  }
} catch(Exception $e){
	print_r($e);
}

$command = 'bash '.dirname(__FILE__).'/Admin/Stats/generateStatsGraph.sh';// >/dev/null 2>/dev/null';
system($command);

?>