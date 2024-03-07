<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

?>
<?php

include("Security/Authentification.php");

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="anonymous"; //$uid="e18XXX"; // en attendant de rétablir le CAS....
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

if (! isset($external)) $external=false;

if ($external) $uid="external";

//if ($_SERVER['PHP_AUTH_USER'] == "bourdon-j" && isset($_GET["fakeuser"])) {$fake=true; $uid=$_GET["fakeuser"];} else {$fake=false;}
$fake=false;

AuthenticateNotStudent($uid);

// Mettre en place une liste de privilégiés
// =1 peut inviter et fixer la durée de l'invitation
// =2 peut inviter pour 24h
// sinon ne peut pas inviter

$userprivileges=getPrivilege($uid,"tables/listAdminID.txt",2);

if (isset($_GET["ROOMID"]))
  $classroomid = $_GET["ROOMID"];
else {
  $classroomid = md5(gmdate("Y-m-d\TH:i:s\Z"));
}

$appid=md5(gmdate("Y-m-d\TH:i:s\Z")."XXX");

if (isset($_GET["WOOCLAPKEY"])) {
  $wooclapaddress = "https://app.wooclap.com/events/".$_GET["WOOCLAPKEY"]."/0";
} else {
  //$wooclapaddress = "https://www.wooclap.com";
  //$wooclapaddress = "https://app.wooclap.com/auth/login?lang=en&redirectTo=/home";
  $wooclapaddress = "https://app.wooclap.com/events/";
}

// Utilisation de Microsoft Cognitive Services si MicrosoftKey et MicrosoftRegion en paramètres
$MicrosoftKey="";
$MicrosoftRegion="";
if ($userprivileges<2) {
  // default values for privileged users (JB account...)
  $MicrosoftKey='bae361b8f5ef4337bebc416ab9d44cf8';
  $MicrosoftRegion='francecentral';
}
if (isset($_GET["MicrosoftKey"])) $MicrosoftKey=$_GET["MicrosoftKey"];
if (isset($_GET["MicrosoftRegion"])) $MicrosoftRegion=$_GET["MicrosoftRegion"];
$Microsofton=(($userprivileges<2)||($MicrosoftKey !=""));

$theme="flat";
if (isset($_GET["theme"])) $theme=$_GET["theme"];

$T_lang=array();
$fd = fopen("supportedlanguages.csv","r");
while($line=fgets($fd,4096)) {
//  echo $line."\n";
  $ligne=explode(";",$line);
//  print_r($ligne);
  if (count($ligne)>2) $T_lang[]=array("name_fr"=>trim($ligne[2]), "name_vo"=>trim($ligne[0]), "code"=>trim($ligne[1]), "voice"=>"-1");
}
fclose($fd);

// Stats and log
$fd = fopen("tmp/StatsLogs.txt","a");
fwrite($fd,gmdate("Y-m-d\TH:i:s\Z")."|".$uid."|".$classroomid."\n");
fclose($fd);

//print_r($T_lang);
?>
<!doctype html>
<html lang="en">
<head>
	<title>Automatic translation for teaching purposes: Teacher</title>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php if ($theme != "") {
    echo '<link href="jquery/'.$theme.'/jquery-ui.min.css" rel="stylesheet"/>';
    echo '<link href="jquery/'.$theme.'/extras.css" rel="stylesheet"/>';
  } else {
    echo '<link href="jquery/jquery-ui.min.css" rel="stylesheet"/>';
  }
	?>

  <link rel="icon" type="image/png" href="favicon.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet"> 
  <link href="styles/teacher.css" rel="stylesheet"> 
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="libs/quickmenu/lib/font-awesome/css/font-awesome.min.css"/>
  <style>
  
  @-webkit-keyframes rotate {
	  from {-webkit-transform: rotate(0deg);}
	  to {-webkit-transform: rotate(360deg);}
  }
  
  @-moz-keyframes rotate {
	  from {-moz-transform: rotate(0deg);}
	  to {-moz-transform: rotate(360deg);}
  }
  
  @keyframes rotate {
	  from {transform: rotate(0deg);}
	  to {transform: rotate(360deg);}
  }
  
.hidesmallscreens {}

@media (max-width:500px) {
	.hidesmallscreens {
		display: none;
	}
}

.fa {
    font-size: 18px;
}

  </style>
  <script type="text/javascript">
  <?php
  echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";

  if (isset($_GET["myconfig"])) {
    echo "var currentconfig='".$_GET["myconfig"]."';\n";
  } else {
    echo "var currentconfig='AAYANKCNYCYNYYYY';\n";
  }
  echo "var MicrosoftKey='".$MicrosoftKey."';\n";
  echo "var MicrosoftRegion='".$MicrosoftRegion."';\n";
  ?>
  </script>
  <script src="js/teacher-def.js"></script>
  <script src="js/common-func.js"></script>
  <script src="js/teacher-func.js"></script>
  <?php if ($theme != "") {
    echo '<script src="jquery/'.$theme.'/external/jquery/jquery.js"></script>';
    echo '<script src="jquery/'.$theme.'/jquery-ui.min.js"></script>';
  } else {
    echo '<script src="jquery/jquery.js"></script>';
    echo '<script src="jquery/jquery-ui.min.js"></script>';
  }
	?>
</head>
<!-- <body onload="Init(); InitVoices(); setTimeout(InitVoices,1000); setTimeout(InitVoices,2000); /*setTimeout(function () {StartSpeechRecognition(Traduction); },3000); */ setTimeout(Lancer,3000); ready(); updateLinks(); Embedcode(); EcrireNBonly(''); applyConfig();"> -->
<body onload="/*setTimeout(function () {StartSpeechRecognition(Traduction); },3000); */ /*applyConfigSub();*/ ChangeOpacity('titreSub', '0'); console.log('ok');">
	<div id="Allbuttons">
    <div id="icondiv">
      <div id="micon" onclick="Micro();" title="This button allows to switch on/off the microphone transcription. Alternatives for this button are the space key, the smartphone remote red button or the presentation pointer button."><i class='material-icons'>voice_over_off</i></div>
		</div>
	</div>
	<!--  // change here for touchable
<div id="titreleft" onclick="Backward();">&#9666;</div>
<div id="titreright"></div>
<div id="titre" onclick="Forward();"><h1>Automatic translation for teaching purposes: Student</h1></div>
<div id="soustitre"></div>
-->
	<!-- <div id="titreSub" onmouseover="_('titreSub').style.transform='translateY(0px)';"> -->
	<div id="titreSub" onmouseover="_('titreSub').style.transform='translateY(0px)';">
		<h1>Automatic translation for teaching purposes: Teacher</h1></div>
	<div id="partialtitreSub"></div>
	<div id="scrollbarSub"></div>
</body>

</html>
