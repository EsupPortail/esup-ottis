<?php

include(dirname(__FILE__)."/config_database.php");
# connect to Mysql

# Connexion à la base de données
try{
    $pdo = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
}catch(PDOException $err){
    echo "Database connection problem: " . $err->getMessage();
}

$pdo->exec('START TRANSACTION'); # begin transaction

# On récupère la première ligne de la base
$statement = $pdo->prepare("SELECT * FROM tasks_queue ORDER BY id LIMIT 1 FOR UPDATE SKIP LOCKED"); # get job and lock it
$statement->execute();
$job = $statement->fetch(); # fetch selected data into php variable

#On récupère toutes les variables de la base
$command_to_do = $job['command_to_do'];
$path_file = $job['path_file'];
$randompath = $job['randompath'];
$from=$job['lang_from'];
$to=$job['lang_to'];
$sub_length = $job['sub_length'];
$sub_punct = $job['sub_punct'];
$speech = $job['path_file2'];
$SlideNotes = $job['slide_notes'];
$TranslateLang = $job['translate_lang'];
$AspectRatio = $job['aspect_ratio'];
$KeepAspectRatio = $job['keep_aspect_ratio'];
$Delay = $job['delay_slides'];
$longrun = $job['long_run'];

# On exécute la commande lue
if (str_ends_with(trim($command_to_do), 'php')){
    $command = 'php '.dirname(__FILE__).'/'.$command_to_do. ' '.$randompath. ' '.$path_file. ' ' .$from. ' ' .$to. ' ' .$sub_length. ' ' .$speech. ' ' .$AspectRatio. ' ' .$KeepAspectRatio. ' ' .$Delay. ' ' .$longrun. ' ' .$TranslateLang. ' ' .$SlideNotes. ' ' .$sub_punct;
    echo $command;
}

system($command);


# remove fetched job from queue
$pdo->exec('DELETE FROM tasks_queue WHERE id = ' . $job['id']);
$pdo->exec('COMMIT'); # commit transaction

?>