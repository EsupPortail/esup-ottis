<?php
include(dirname(__FILE__)."/config_database.php");
include("../config.php");

if (isset($_POST['ProgramName']) && $_FILES["fileToUpload"]["tmp_name"] != "") {

    ini_set('display_errors','display_startup_errors');
    
    # Sauvegarde le fichier en dehors du tmp pour le récupérer avec le cron
    $uploads_dir = '/var/www/html/uploads';
    $tmp_name_file = $_FILES["fileToUpload"]["tmp_name"];
    $name_file = basename($_FILES["fileToUpload"]["tmp_name"]);
    move_uploaded_file($tmp_name_file, "$uploads_dir/$name_file");
    
    // Ajout JB (possibilité d'envoyer le RandomPath via POST)
    if (isset($_POST['randompath'])) $randompath=$_POST['randompath']; else $randompath=md5(gmdate("U"));
    // Fin
 
    
    error_reporting(E_ALL);
    
    # connect to Mysql

    #try{
    #    $pdo = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
    #}catch(PDOException $err){
    #    echo "Database connection problem: " . $err->getMessage();
    #}
    
    # Insertion de la commande et des données dans la base
    
    //command_to_do
    $command_to_add = $_POST['ProgramName'];

    //lang_from
    $lfrom="fr_FR";
    if (isset($_POST["inputlanguage"])) {
        $lfrom = $_POST["inputlanguage"];
    }

    //lang_to
    $lto = "fr_FR";
    if (isset($_POST["outputlanguage"])) {
        $lto = implode(",",$_POST["outputlanguage"]);
    }

    //sub_length
    $Wraplength="";
    if (isset($_POST["wraplength"])) {
        $Wraplength="--wraplength=".$_POST["wraplength"];
    }

    //sub_punct
    $NOPUNC="";
    if (isset($_POST["nopunct"])) {
        if ($_POST["nopunct"]=="Yes") {
         $NOPUNC="--nopunct ";
        }
    }

    //path_file2
    $speech = "";
    if (isset($_FILES["fileToUploadTXT"]["tmp_name"])) {
        if (trim($_FILES["fileToUploadTXT"]["tmp_name"]) != "") {
            $tmp_name_file_txt = $_FILES["fileToUploadTXT"]["tmp_name"];
            $name_file_txt = basename($_FILES["fileToUploadTXT"]["tmp_name"]);
            move_uploaded_file($tmp_name_file_txt, "$uploads_dir/$name_file_txt");
            $speech="$uploads_dir/$name_file_txt";
        }
        else {
            $speech=dirname(__FILE__)."/Templates/blank.txt";
        }
    }

    //slide_notes
    $SlideNotes="";
    if (isset($_POST["slidenotes"]) && $_POST["slidenotes"] == "X") {
        $SlideNotes="--WithNotes";
    }

    //translate_lang
    $TranslateLang = "No";
    if (isset($_POST["translatelanguage"])) {
        $TranslateLang=$_POST["translatelanguage"];
    }

    //aspect_ratio
    $AspectRatio="--AspectRatio=4/3";
    if (isset($_POST["aspectratio"])) {
        $AspectRatio="--AspectRatio=".$_POST["aspectratio"];
    }

    //keep_aspect_ratio
    $KeepAspectRatio="Y";
    if (isset($_POST["keepaspectratio"])) {
        if ($_POST["keepaspectratio"] != "X") {
            $KeepAspectRatio="--KeepAspectRatio=N";
        }
        else {
            $KeepAspectRatio="--KeepAspectRatio=Y";
        }
    }

    //delay_slides
    $Delay="--Delay=0";
    if (isset($_POST["extradelay"])) {
        $Delay="--Delay=".$_POST["extradelay"];
    }

    //long_run
    $longrun="false";
    if (isset($_POST["longrun"])) {
        if ($_POST["longrun"] == "long") {
        $longrun="true";
        }
        else {
            $longrun="false";
        }
    }

    //licence
    $licence="--Licence=None";
    if (isset($_POST["Licence"]) && $_POST["Licence"] != "") {
        $licence="--Licence=".$_POST["Licence"];
    }

    $json=json_encode([
        'command_to_do' => $command_to_add,
        'path_file' => "$uploads_dir/$name_file",
        'randompath' => $randompath,
        'lang_from'=> $lfrom,
        'lang_to' => $lto,
        'sub_length'=>$Wraplength,
        'sub_punct'=> $NOPUNC,
        'path_file2'=>$speech,
        'slide_notes'=>$SlideNotes,
        'translate_lang'=>$TranslateLang,
        'aspect_ratio'=>$AspectRatio,
        'keep_aspect_ratio'=>$KeepAspectRatio,
        'delay_slides'=>$Delay,
        'long_run'=> $longrun,
        'licence'=>$licence
    ]);

    // Add to fakeDB
    $fd=fopen("fakeDB/db.json","a");
    fputs($fd,$json."|||\n");
    fclose($fd);
    
    echo 'Your request will be processed soon...';


    if ($SAVESTATS) {
    //Get IP adress
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
    	//ip from share internet
    	$ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
    	//ip pass from proxy
    	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
    	$ip = $_SERVER['REMOTE_ADDR'];
    }

    //Connexion avec la base de données pour les statistiques
    $date_raw = gmdate("Y-m-d\TH:i:s\Z");
    $date_complete = gmdate("Y-m-d");
    $date_day = gmdate("d");
    $date_month = gmdate("F");
    $date_year = gmdate("Y");
    $user_id = $_POST['userid'];
    $room_id = $randompath;
    $tool = $_POST['ProgramName'];
    $origin = dirname(__FILE__);
    $ip_address = $ip;
    $is_external = $_POST['external'];

    $json_stats=json_encode([
        'date_raw' => $date_raw, 
        'date_complete' => $date_complete, 
        'date_day' => $date_day,
        'date_month' => $date_month, 
        'date_year' => $date_year, 
        'user_id' => $user_id, 
        'room_id' => $room_id, 
        'tool' => $tool, 
        'origin' => $origin, 
        'ip_address' => $ip_address,
        'is_external' => $is_external
    ]);

    $fd_stats=fopen("fakeDB/db_stats.json","a");
    fputs($fd_stats,$json_stats."|||\n");
    fclose($fd_stats);
    }
 }

else{
    print_r($_POST);
    print_r($_FILES);
    echo ("An error has occurred. Please try again. Don't forget to upload a file.");
}


?>