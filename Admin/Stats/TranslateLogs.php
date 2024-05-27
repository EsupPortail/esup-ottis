<?php
include(dirname(__FILE__)."/config_database.php");
include("../Admin/Stats/config_database_stats.php")

if (isset($_POST['ProgramName']) && $_FILES["fileToUpload"]["tmp_name"] != "") {

    ini_set('display_errors','display_startup_errors');
    
    # Sauvegarde le fichier en dehors du tmp pour le récupérer avec le cron
    // $uploads_dir = '/var/www/html/uploads';
    // $tmp_name_file = $_FILES["fileToUpload"]["tmp_name"];
    // $name_file = basename($_FILES["fileToUpload"]["tmp_name"]);
    // move_uploaded_file($tmp_name_file, "$uploads_dir/$name_file");
    
    // Ajout JB (possibilité d'envoyer le RandomPath via POST)
    if (isset($_POST['randompath'])) $randompath=$_POST['randompath']; else $randompath=md5(gmdate("U"));
    // Fin
 
    
    error_reporting(E_ALL);

    $res=array();
    if ($WhichTranslator == "DEEPLFREE") {
        exec($proxy." curl -q https://api-free.deepl.com/v2/usage?auth_key=".$DEEPLAPIFREETOKEN,$res);
    } else {
        exec($proxy." curl -q https://api.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN,$res);
    }

    $Stats_DEEPL = json_decode($res[0],true);
    if (isset($Stats_DEEPL["character_count"])) {
    $percentage_DEEPL = ceil(10000*$Stats_DEEPL["character_count"]/($Stats_DEEPL["character_limit"]+0.00000001))/100;
    } else $percentage_DEEPL = 0;

    //Connexion avec la base de données pour les statistiques
    // try{
    //     $pdo_stats = new PDO("mysql:host=" . $dbhost_stats . ";dbname=" . $dbname_stats, $dbuser_stats, $dbpass_stats);
    // }catch(PDOException $err){
    //     echo "Database connection problem: " . $err->getMessage();
    // }

    // $date_raw = gmdate("Y-m-d\TH:i:s\Z");
    // $date_complete = gmdate("Y-m-d");
    // $date_day = gmdate("d");
    // $date_month = gmdate("F");
    // $date_year = gmdate("Y");
    // $origin_translate = "Deepl PRO";
    // $percentage_char = $percentage_DEEPL;
    // $characters = $Stats_DEEPL["character_count"];
    // $char_max = $Stats_DEEPL["character_limit"];

    // $requete_SQL_stats = 'INSERT INTO translatelog (date_raw, date_complete, date_day, date_month, date_year, origin_translate, percentage_char, characters, char_max) VALUES (:date_raw, :date_complete, :date_day, :date_month, :date_year, :origin_translate, :percentage_char, :characters, :char_max)';
    // $insertCommande_stats = $pdo_stats->prepare($requete_SQL_stats);


    // $insertCommande_stats->execute([
    //     'date_raw' => $date_raw, 
    //     'date_complete' => $date_complete, 
    //     'date_day' => $date_day,
    //     'date_month' => $date_month, 
    //     'date_year' => $date_year, 
    //     'origin_translate' = $origin_translate;
    //     'percentage_char' = $percentage_char;
    //     'characters' = $characters;
    //     'char_max' = $char_max;
    // ]);

 }

else{
    print_r($_POST);
    print_r($_FILES);
    echo ("An error has occurred. Please try again. Don't forget to upload a file.");
}


?>