<?php
exit();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
        
        $external=true;
        // Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
        if(!isset($_SESSION["username"])){
            $mysite = "automatictranslator.sciences.univ-nantes.fr";
            if (! isset($_SERVER['HTTP_REFERER'])) {
                header("Location: https://automatictranslator.sciences.univ-nantes.fr");
                exit();
            } 
            $referer = $_SERVER['HTTP_REFERER'];

            if ($referer == "") {
                $domain = $mysite;
            } else {
                $domain = parse_url($referer);
            }
                
            if($domain['host'] != $mysite) {
                header("Location: ../Admin/Registration/login.php?ref=OMT");
                exit(); 
                //header("Location: https://automatictranslator.sciences.univ-nantes.fr");
                //exit();
            } 
        }
    
    if (! isset($_SESSION["from"]) || $_SESSION["from"] == "logout") {
        header("Location: ../Admin/Registration/login.php?ref=OMT");
        exit(); 
    }

    if ($_SESSION["from"] != "scenarios")  {

        include("index.php");
       if ($_SESSION["from"] == "login") echo "\n<script>document.getElementById('logout').style.display='inline';</script>\n";
    } else {
        //echo "<pre>";
        //print_r($_SESSION);
        //echo "</pre>";
        header("Location: ../Admin/Registration/login.php?ref=OMT");
        exit(); 
    }
    ?>
    <!--
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
    <script>
        // document.getElementById('bar').innerHTML+='<a id="logout-button" href="Admin/Registration/logout.php" title="Logout"><i class="fa fa-sign-out" aria-hidden="true"></i><span class="hidesmallscreens">&nbsp;Logout</span></a>';
    </script>
    

