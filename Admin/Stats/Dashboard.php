<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

?>
<?php
include("../../Security/Authentification.php");

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="anonymous"; //$uid="e18XXX"; // en attendant de rétablir le CAS....
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

if (! isset($external)) $external=false;

if ($external) $uid="external";

//if ($_SERVER['PHP_AUTH_USER'] == "bourdon-j" && isset($_GET["fakeuser"])) {$fake=true; $uid=$_GET["fakeuser"];} else {$fake=false;}
$fake=false;

$userprivileges=getPrivilege($uid,"../../tables/listAdminID.txt",2);

if ($userprivileges>1) {
  echo "<p>".$uid.", vous n'êtes pas autorisé à consulter cette page</p>";
  exit;
}


$theme="flat";
if (isset($_GET["theme"])) $theme=$_GET["theme"];

try{
  $pdo = new PDO("mysql:host=localhost;dbname=db_stats", "root", "root");
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
?>

<!doctype html>
<html lang="fr">
  <head>
    <title>OMIST - Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../images/favicon.svg" >
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
    <link href="../../styles/dashboard.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  </head>
  <body onload="openPage('tab-dashboard', 'li-dashboard')">
    <main>
      <section id="sidebar">
        <div id="sidebar-nav">
          <img id="logoOMIST" alt="Logo OMIST" src="../../images/omist-top-logo-nav_ssfd.png">
          <ul>
            <li id="li-dashboard" class="tablink" onclick="openPage('tab-dashboard', this.id)"><i class="fa fa-dashboard"></i> Dashboard</li>
            <li id="li-users" class="tablink" onclick="openPage('tab-users', this.id)"><i class="fa fa-user"></i>Users</li>
            <li id="li-uses" class="tablink" onclick="openPage('tab-uses', this.id)"><i class="fa fa-desktop"></i>Uses</li>
            <li id="li-translation" class="tablink" onclick="openPage('tab-translation', this.id)"><i class="fa fa-language"></i>Translation</li>
          </ul>
        </div>
      </section>

      <section id="content">
        <div class="tab-content">
            <div id="tab-dashboard" class="tabcontent">
            <a href='./table_tot.csv' download='table_tot.csv'><button class="button-3 tooltip">Download all connection data in CSV format</button></a>
              <img src="./indicators/indicator_month.png?v=<?php mt_rand();?>"/>
              <img src="./indicators/indicator_tot.png?v=<?php mt_rand();?>"/>
            </div>
            <div id="tab-users" class="tabcontent">
              <a href='./users/new_users_per_months_tab.csv' download='new_users_per_months_tab.csv'><button class="button-3 tooltip">Download data on the number of new users monthly in CSV format</button></a>
              <embed class="graph-tot" title="Nombre d'utilisateurs par mois" src="./users/new_users_per_months_graph.html?v=<?php mt_rand();?>" />
              <a href='./users/users_per_months_tab.csv' download='users_per_months_tab.csv'><button class="button-3 tooltip">Download data on number of users monthly in CSV format</button></a>
              <embed class="graph-tot" title="Nombre d'utilisateurs par mois" src="./users/users_per_months_graph.html?v=<?php mt_rand();?>" />
              <a href='./users/map_ipaddress_tab.csv' download='map_ipaddress_tab.csv'><button class="button-3 tooltip">Download user location data in CSV format</button></a>
              <embed class="graph-tot" title="Utilisateurs dans le monde" src="./users/map_ipaddress.html?v=<?php mt_rand();?>" />
            </div>
            <div id="tab-uses" class="tabcontent">
              <a href='./uses/uses_per_months_tab.csv' download='uses_per_months_tab.csv'><button class="button-3 tooltip">Download monthly usage data in CSV format</button></a>
              <embed class="graph-tot" title="Nombre d'utilisations par mois" src="./uses/uses_per_months_graph.html?v=<?php mt_rand();?>" />
                  <a href='./uses/tools_used_tab.csv' download='tools_used_tab.csv'><button class="button-3 tooltip">Download data on the number of uses per tool in CSV format</button></a>
                  <embed class="graph-tot" title="Outils utilisés" src="./uses/tools_used_tab.html?v=<?php mt_rand();?>" />
                  <a href='./uses/config_used_tab.csv' download='config_used_tab.csv' ><button class="button-3 tooltip">Download data on the number of uses per configuration in CSV format</button></a>
                  <embed class="graph-tot" title="Configuration utilisées" src="./uses/config_used_tab.html?v=<?php mt_rand();?>" />
              <a href='./users/map_ipaddress_tab.csv' download='map_ipaddress_tab.csv'><button class="button-3 tooltip">Download data on the location of uses</button></a>
              <embed class="graph-tot" title="Utilisations dans le monde" src="./uses/map_ipaddress.html?v=<?php mt_rand();?>" />
            </div>
            <div id="tab-translation" class="tabcontent">
              <a href='./translation/deepl_usage_per_day_tab.csv' download='deepl_usage_per_day_tab.csv'><button class="button-3 tooltip">Download daily Deepl Pro use data in CSV format</button></a>
              <embed class="graph-tot" title="Utilisation de Deepl par jour" src="./translation/deepl_usage_per_day_graph.html?v=<?php mt_rand();?>" />
              <a href='./translation/deepl_usage_per_month_tab.csv' download='deepl_usage_per_month_tab.csv'><button class="button-3 tooltip">Download Deepl Pro monthly usage data in CSV format</button></a>
              <embed class="graph-tot" title="Utilisation de Deepl" src="./translation/deepl_usage_per_month_graph.html?v=<?php mt_rand();?>" />
            </div>
        </div>
      </section>
    </main>

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
        document.getElementById(elmnt).style.backgroundColor = 'var(--blue)';
        document.getElementById(elmnt).style.color = "var(--white)";
      }

      function DownloadTable(filename, elText, mimeType) {
        var link = document.createElement('a');
        mimeType = mimeType || 'text/plain';
        link.setAttribute('download', filename);
        link.setAttribute('href', 'data:' + mimeType  +  ';charset=utf-8,' + encodeURIComponent(elText));
        link.click();
      }
    </script>
  </body>
</html>