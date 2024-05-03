<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

?>
<?php

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="anonymous"; //$uid="e18XXX"; // en attendant de rétablir le CAS....
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

if (! isset($external)) $external=false;

if ($external) $uid="external";

//if ($_SERVER['PHP_AUTH_USER'] == "bourdon-j" && isset($_GET["fakeuser"])) {$fake=true; $uid=$_GET["fakeuser"];} else {$fake=false;}
$fake=false;

//AuthenticateNotStudent($uid);


// Mettre en place une liste de privilégiés
// =1 peut inviter et fixer la durée de l'invitation
// =2 peut inviter pour 24h
// sinon ne peut pas inviter


$theme="flat";
if (isset($_GET["theme"])) $theme=$_GET["theme"];

try{
  $pdo = new PDO("mysql:host=localhost;dbname=connexion_data", "root", "root");
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

// $data_file = file_get_contents("data_connexion_tot.txt");
// $lines = explode("\n",$data_file);
// // while(!feof($myfile)) {
// //   echo fgets($myfile) . "<br>";
// // }
// // fclose($data_file);

// $var_test = "test d'affichage PHP";



?>
<!doctype html>
<html lang="fr">
  <head>
    <title>Automatic translation for teaching purposes: data and statistics</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
    <link href="../../styles/dashboard.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  </head>
  <body onload="openPage('tab-dashboard', 'li-dashboard')">
  <!-- <header>
  <div id="nav-top">
    <img id="logoOMIST" alt="Logo OMIST" src="omist-top-logo-nav_ssfd.png">
    <div id="opentranslator" >STATISTICS</div>
  </div>
    </header> -->

    <section id="sidebar">
      <div id="sidebar-nav">
        <img id="logoOMIST" alt="Logo OMIST" src="omist-top-logo-nav_ssfd.png">
        <ul>
          <li id="li-dashboard" class="tablink" onclick="openPage('tab-dashboard', this.id)"><i class="fa fa-dashboard"></i> Dashboard</li>
          <li id="li-users" class="tablink" onclick="openPage('tab-users', this.id)"><i class="fa fa-user"></i>Users</li>
          <li id="li-uses" class="tablink" onclick="openPage('tab-uses', this.id)"><i class="fa fa-desktop"></i>Uses</li>
          <li id="li-translation" class="tablink" onclick="openPage('tab-translation', this.id)"><i class="fa fa-globe"></i>Translation</li>
        </ul>
      </div>
    </section>

    <section id="content">
      <div class="tab-content">
          <!-- <div class="content-header">
            <div id="nav-top">
              <img id="logoOMIST" alt="Logo OMIST" src="omist-top-logo-nav_ssfd.png">
              <div id="opentranslator" >STATISTICS</div>
            </div>
          </div> -->
          <div id="tab-dashboard" class="tabcontent">
            <iframe title="Nombre d'utilisateurs et d'utilisations totaux" style="width:50%; height: 335px; border: none;" src="uses_users_origin.html" align="left"></iframe>
            <iframe title="Outils utilisés" style="width:50%; height: 335px; border: none;" src="tools_used.html" align="right"></iframe>
            <div class="widget-box sample-widget">
                <div class="widget-header">
                    <h2>Widget Header</h2>
                    <i class="fa fa-cog"></i>
                </div>
                <div class="widget-content">
                    <img
                        src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/87118/sample-data-1.png"
                    />
                </div>
            </div>
          </div>
          <div id="tab-users" class="tabcontent">
            <iframe class="graph-tot" title="Nombre d'utilisateurs par mois" src="users_per_months.html"></iframe>
          </div>
          <div id="tab-uses" class="tabcontent">
            <iframe class="graph-tot" title="Nombre d'utilisations par mois" src="uses_per_months.html"></iframe>
          </div>
          <div id="tab-translation" class="tabcontent">
            <iframe class="graph-tot" title="Utilisation de Deepl par jour" src="deepl_usage_per_day.html"></iframe>
            <iframe class="graph-tot" title="Utilisation de Deepl" src="deepl_usage_per_month.html"></iframe>
          </div>
      </div>
    </section>


    <!-- <main>
      <h1>OMIST : Statistics</h1>
    </main>

    <div id="db-update">
    Last update : 
    </div>

      <iframe title="Nombre d'utilisateurs et d'utilisations totaux" style="width:50%; height: 335px; border: none;" src="uses_users_origin.html" align="left"></iframe>
      <iframe title="Outils utilisés" style="width:50%; height: 335px; border: none;" src="tools_used.html" align="right"></iframe>

    <iframe title="Nombre d'utilisateurs par mois" style="width:90%; height: 500px; border: none;" src="users_per_months.html"></iframe>

    <iframe title="Nombre d'utilisations par mois" style="width:90%; height: 500px; border: none;" src="uses_per_months.html"></iframe>

  
  -->

    <script>
      function openPage(pageName, elmnt) {
        var i, tabcontent;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
          tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < tablinks.length; i++) {
          tablinks[i].style.backgroundColor = "";
          tablinks[i].style.color = "";
        }
        document.getElementById(pageName).style.display = "block";
        document.getElementById(elmnt).style.backgroundColor = 'white';
        document.getElementById(elmnt).style.color = "black";
      }
    </script>

  </body>
</html>