<?php
include(dirname(__FILE__)."/configOT.php");

if ($DATABASETYPE == "fakeDB"){
    // Ajout JB car version php server = 7.4
    if (! function_exists('str_ends_with')) {
        function str_ends_with(string $haystack, string $needle): bool
        {
            $needle_len = strlen($needle);
            return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, - $needle_len));
        }
    }
    // fin

    // On vide puis vide toute la base
    $content=file_get_contents("fakeDB/db.json");
    $fd=fopen("fakeDB/db.json","w");
    fclose($fd);

    $alljobs=explode("|||",$content);
    $selectedjobs=array();
    for($i=0; $i<count($alljobs);$i++) {
        $job=$alljobs[$i];
        if (trim($job) !="") {
            $Tjob=(array) json_decode(trim($job));
            $selectedjobs[]=$Tjob;
        }
    }


    // On execute TOUS les jobs
    for ($i=0; $i<count($selectedjobs);$i++) {
        $job = $selectedjobs[$i];
        #On récupère toutes les variables de la base
        // Ajout JB **: si pas de job dans la queue, pas de tableau
        if (is_array($job)) {
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
            $licence = $job['licence'];


            // Ajout JB
            $command = "";
            // fin
            # On exécute la commande lue
            if (str_ends_with(trim($command_to_do), 'php')){
                // Create dir
                system("mkdir -p tmp/".$randompath);
                // Store configuration as a json file
                $fd=fopen("tmp/".$randompath."/parameters.json","w");
                fputs($fd,json_encode($job));
                fclose($fd);
                // Ajout JB : /usr/bin devant php
                $command = '/usr/bin/php '.dirname(__FILE__).'/'.$command_to_do. ' '.$randompath. ' '.$path_file. ' ' .$from. ' ' .$to. ' ' .$sub_length. ' ' .$speech. ' ' .$AspectRatio. ' ' .$KeepAspectRatio. ' ' .$Delay. ' ' .$longrun. ' ' . $licence. ' ' .$TranslateLang. ' ' .$SlideNotes. ' ' .$sub_punct ;
                echo $command;
            }

            // Ajout JB
            if ($command != "") system($command." &");
        }
    }
}else{
    include(dirname(__FILE__)."/config_database.php");
    # connect to Mysql
    
    # Connexion à la base de données
    try{
        $pdo = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
    }catch(PDOException $err){
        echo "Database connection problem: " . $err->getMessage();
        // Ajout JB
        exit;
        // fin
    }
    
    // Ajout JB car version php server = 7.4
    if (! function_exists('str_ends_with')) {
        function str_ends_with(string $haystack, string $needle): bool
        {
            $needle_len = strlen($needle);
            return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, - $needle_len));
        }
    }
    // fin
    
    
    $pdo->exec('START TRANSACTION'); # begin transaction
    
    # On récupère la première ligne de la base
    // Ajout JB (Pb de syntaxe pas connue de MariaDB= mysql installée sur le serveur)
    $statement = $pdo->prepare("SELECT * FROM tasks_queue ORDER BY id LIMIT 1"); # get job and lock it
    //$statement = $pdo->prepare("SELECT * FROM tasks_queue ORDER BY id LIMIT 1 FOR UPDATE SKIP LOCKED"); # get job and lock it
    // Fin
    
    $statement->execute();
    $job = $statement->fetch(); # fetch selected data into php variable
    
    #On récupère toutes les variables de la base
    // Ajout JB **: si pas de job dans la queue, pas de tableau
    if (is_array($job)) {
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
    $licence = $job['licence'];
    
    
    // Ajout JB
    $command = "";
    // fin
    # On exécute la commande lue
    if (str_ends_with(trim($command_to_do), 'php')){
        // Create dir
        system("mkdir -p tmp/".$randompath);
        // Store configuration as a json file
        $fd=fopen("tmp/".$randompath."/parameters.json","w");
        fputs($fd,json_encode($job));
        fclose($fd);
        // Ajout JB : /usr/bin devant php
        $command = '/usr/bin/php '.dirname(__FILE__).'/'.$command_to_do. ' '.$randompath. ' '.$path_file. ' ' .$from. ' ' .$to. ' ' .$sub_length. ' ' .$speech. ' ' .$AspectRatio. ' ' .$KeepAspectRatio. ' ' .$Delay. ' ' .$longrun. ' ' . $licence. ' ' .$TranslateLang. ' ' .$SlideNotes. ' ' .$sub_punct ;
        echo $command;
    }
    
    // Ajout JB
    if ($command != "") system($command." &");
    
    // system($command);
    // fin
    
    # remove fetched job from queue
    $pdo->exec('DELETE FROM tasks_queue WHERE id = ' . $job['id']);
    $pdo->exec('COMMIT'); # commit transaction
    
    }
    // fin ** (pour le "}")
} 
?>