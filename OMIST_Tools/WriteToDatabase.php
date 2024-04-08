<?php
session_start();
include(dirname(__FILE__)."/config_database.php");
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
if (isset($_POST['ProgramName']) && isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {

    ini_set('display_errors','display_startup_errors');
    
    # Sauvegarde le fichier en dehors du tmp pour le récupérer avec le cron
    $uploads_dir = '/var/www/html/uploads';
    $tmp_name_file = $_FILES["fileToUpload"]["tmp_name"];
    $name_file = basename($_FILES["fileToUpload"]["tmp_name"]);
    move_uploaded_file($tmp_name_file, "$uploads_dir/$name_file");
    
    // Ajout JB (possibilité d'envoyer le RandomPath via POST)
    if (isset($_POST['randompath'])) $randompath=$_POST['randompath']; else $randompath=md5(gmdate("U"));
    // Fin
    $_SESSION['randompath']=$randompath;
    
    error_reporting(E_ALL);
    
    # connect to Mysql

    try{
        $pdo = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
    }catch(PDOException $err){
        echo "Database connection problem: " . $err->getMessage();
    }
    
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
            $speech=dirname(__FILE__)."/blank.txt";
        }
    }

    //slide_notes
    $SlideNotes="";
    if (isset($_POST["slidenotes"]) && $_POST["slidenotes"] == "X") {
        $SlideNotes="--WithNotes";
    }

    //translate_lang
    $TranslateLang = "";
    if (isset($_POST["translatelanguage"])) {
        $TranslateLang=$_POST["translatelanguage"];
    }

    //aspect_ratio
    $AspectRatio="";
    if (isset($_POST["aspectratio"])) {
        $AspectRatio="--AspectRatio=".$_POST["aspectratio"];
    }

    //keep_aspect_ratio
    $KeepAspectRatio="";
    if (isset($_POST["keepaspectratio"])) {
        if ($_POST["keepaspectratio"] != "X") {
            $KeepAspectRatio="--KeepAspectRatio=N";
        }
        else {
            $KeepAspectRatio="--KeepAspectRatio=Y";
        }
    }

    //delay_slides
    $Delay="";
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

    
    $requete_SQL = 'INSERT INTO tasks_queue (command_to_do, path_file, randompath, lang_from, lang_to, sub_length, sub_punct, path_file2, slide_notes, translate_lang, aspect_ratio, keep_aspect_ratio, delay_slides, long_run) VALUES (:command_to_add, :file_uploaded, :randompath, :lfrom, :lto, :sub_length, :sub_punct, :path_file2, :slide_notes, :translate_lang, :aspect_ratio, :keep_aspect_ratio, :delay_slides, :long_run)';
    $insertCommande = $pdo->prepare($requete_SQL);
    
    
    $insertCommande->execute([
        'command_to_add' => $command_to_add,
        'file_uploaded' => "$uploads_dir/$name_file",
        'randompath' => $randompath,
        'lfrom'=> $lfrom,
        'lto' => $lto,
        'sub_length'=>$Wraplength,
        'sub_punct'=> $NOPUNC,
        'path_file2'=>$speech,
        'slide_notes'=>$SlideNotes,
        'translate_lang'=>$TranslateLang,
        'aspect_ratio'=>$AspectRatio,
        'keep_aspect_ratio'=>$KeepAspectRatio,
        'delay_slides'=>$Delay,
        'long_run'=> $longrun
    ]);
    
    // $pdo = null;
    echo 'Your request has been processed. You\'ll find the result on <a href="DownloadZIP.php">this page</a>, which will inform you when the process is complete.';
 }

else{
    echo ("An error has occurred. Please try again. Don't forget to upload a file.");
}


?>

</div>
</body>

</html>