<?php 
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

?>
<?php

include("Security/Authentification.php");
include("config.php");
include("Admin/Stats/config_database_stats.php");

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
$_SESSION["from"]="teacher";
  

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="anonymous"; //$uid="e18XXX"; // en attendant de rétablir le CAS....
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

if (! isset($external)) $external=false;

if ($external){
	if (isset($_SESSION["username"])) {
		$uid_connexion = $_SESSION["username"];
	}else{
		$uid_connexion = "test-external";
	}	
}else{
	$uid_connexion = $uid;
}

if ($external) $uid="external";

$fake=false;

//AuthenticateNotStudent($uid);
$filenameids = "tables/authorizedStudents.txt";
AuthenticateNotStudentorReader($uid,$filenameids);

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
/*
if ($userprivileges<2) {
  // default values for privileged users (JB account...)
  $MicrosoftKey=$MicrosoftKey_default;
  $MicrosoftRegion=$MicrosoftRegion_default;
}
if (isset($_GET["MicrosoftKey"])) $MicrosoftKey=$_GET["MicrosoftKey"];
if (isset($_GET["MicrosoftRegion"])) $MicrosoftRegion=$_GET["MicrosoftRegion"];
$Microsofton=(($userprivileges<2)||($MicrosoftKey !=""));
*/
$Microsofton=false;

$theme="flat_black";
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


//Get IP address
if(!empty($_SERVER['HTTP_CLIENT_IP'])){
	//ip from share internet
	$ip = $_SERVER['HTTP_CLIENT_IP'];
}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
	//ip pass from proxy
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
	$ip = $_SERVER['REMOTE_ADDR'];
}

// get location from ip
/*
echo "<!--\n";
$aContext = array(
    'http' => array(
        'proxy'           => 'http://cache.univ-nantes.fr:3128',
        'request_fulluri' => true,
    ),
);
$cxContext = stream_context_create($aContext);
echo var_export(unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip, False, $cxContext)));
echo "-->\n";
*/

$res=array();
// if ($DEEPLAPITOKEN != "") {
// 	exec($proxy." curl -q https://api.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN,$res);
// 	$Stats_DEEPL = json_decode($res[0],true);
// } else {
// 	$Stats_DEEPL=array();
// }

if ($WhichTranslator == "DEEPLPRO"){
	if ($DEEPLAPITOKEN != "") {
		exec($proxy." curl -q https://api.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN,$res);
		$Stats_DEEPL = json_decode($res[0],true);
	} else {
		$Stats_DEEPL=array();
	}
} else if ($WhichTranslator == "DEEPLFREE"){
	if ($DEEPLFREEAPITOKEN != "") {
		exec($proxy." curl -q https://api-free.deepl.com/v2/usage?auth_key=".$DEEPLFREEAPITOKEN,$res);
		$Stats_DEEPL = json_decode($res[0],true);
	} else {
		$Stats_DEEPL=array();
	}
}

if (isset($Stats_DEEPL["character_count"])) {
  $percentage_DEEPL = ceil(10000*$Stats_DEEPL["character_count"]/($Stats_DEEPL["character_limit"]+0.00000001))/100;
} else $percentage_DEEPL = 0;

/*
$myfile = fopen("locks/DeeplPro_quota_exceeded.txt", "w") or die("Unable to open file!");
if ($percentage_DEEPL > 99){
  $txt = "false";
} else {
  $txt = "true";
}
fwrite($myfile, $txt);
fclose($myfile);
*/

if ($SAVESTATS) {
//Connexion avec la base de données pour les statistiques
try{
    $pdo_stats = new PDO("mysql:host=" . $dbhost_stats . ";dbname=" . $dbname_stats, $dbuser_stats, $dbpass_stats);
}catch(PDOException $err){
    echo "Database connection problem: " . $err->getMessage();
}

$date_raw = gmdate("Y-m-d\TH:i:s\Z");
$date_complete = gmdate("Y-m-d");
$date_day = gmdate("d");
$date_month = gmdate("F");
$date_year = gmdate("Y");
$user_id = $uid_connexion;
$room_id = $classroomid;
$tool = "teacher";
$origin = dirname(__FILE__);
$ip_address = $ip;
if (isset($_GET["myconfig"])) {
    $config_teacher = $_GET["myconfig"];
  } else {
    $config_teacher = "direct link";
  }
if ($external){
	$is_external = 1;
}else{
	$is_external = 0;
}

$requete_SQL_stats = 'INSERT INTO statslog (date_raw, date_complete, date_day, date_month, date_year, user_id, room_id, tool, origin, config_teacher, ip_address, is_external) VALUES (:date_raw, :date_complete, :date_day, :date_month, :date_year, :user_id, :room_id, :tool, :origin, :config_teacher, :ip_address, :is_external)';
$insertCommande_stats = $pdo_stats->prepare($requete_SQL_stats);

$insertCommande_stats->execute([
    'date_raw' => $date_raw, 
    'date_complete' => $date_complete, 
    'date_day' => $date_day,
    'date_month' => $date_month, 
    'date_year' => $date_year, 
    'user_id' => $user_id, 
    'room_id' => $room_id, 
    'tool' => $tool, 
    'origin' => $origin,
    'config_teacher' => $config_teacher,
    'ip_address' => $ip_address,
	'is_external' => $is_external
]);
}
//print_r($T_lang);
?>
<!doctype html>
<html lang="en">
<head>
	<title>Automatic translation for teaching purposes: Teacher</title>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php if ($theme != "") {
    echo '<link href="jquery/'.$theme.'/jquery-ui.min.css" rel="stylesheet">';
    echo '<link href="jquery/'.$theme.'/extras.css" rel="stylesheet">';
  } else {
    echo '<link href="jquery/jquery-ui.min.css" rel="stylesheet">';
  }
	?>

  <link rel="icon" type="image/png" href="./images/favicon.svg" >
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet"> 
  <link href="styles/teacher.css" rel="stylesheet"> 
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="libs/quickmenu/lib/font-awesome/css/font-awesome.min.css">
  <style>

.bw {
	color: white;
	background-color: black;
	content: '<i class="fa fa-low-vision fa-2x" aria-hidden="true"></i>';
}
.tabbed {}

/* Spinner */

#stillwaiting {
	position: absolute;
	width: 50%;
	height: 50%;
	top:200px;
	left:25%;
	color: black;
	font-size: 2em;
	z-index:20000;
}

#stillwaitingspinnerdiv {
	width:100px;
	height:100px;
	border:20px solid #555555;
	border-top:25px solid #aaaaaa;
	border-radius:50%;
	margin: auto;
  }

  #stillwaitingspinner {
	width:150px;
	height:150px;
	margin: auto;
  }

  #stillwaitingspinner, #stillwaitingspinnerdiv {
	-webkit-transition-property: -webkit-transform;
	-webkit-transition-duration: 2s;
	-webkit-animation-name: rotate;
	-webkit-animation-iteration-count: infinite;
	-webkit-animation-timing-function: linear;
	
	-moz-transition-property: -moz-transform;
	-moz-animation-name: rotate; 
	-moz-animation-duration: 2s; 
	-moz-animation-iteration-count: infinite;
	-moz-animation-timing-function: linear;
	
	transition-property: transform;
	animation-name: rotate; 
	animation-duration: 2s; 
	animation-iteration-count: infinite;
	animation-timing-function: linear;
  }
  
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
  <script>
  <?php
  echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";

  if (isset($_GET["myconfig"])) {
    echo "var currentconfig='".$_GET["myconfig"]."';\n";
  } else {
    echo "var currentconfig='AANAYKCNYCNNYYYYYN';\n";
  }
  echo "var MicrosoftKey='".$MicrosoftKey."';\n";
  echo "var MicrosoftRegion='".$MicrosoftRegion."';\n";
  ?>
  </script>
  <?php
  echo "<script src=\"js/teacher-def.js?v=".$classroomid."\"></script>\n";
  echo "<script src=\"js/common-func.js?v=".$classroomid."\"></script>\n";
  echo "<script src=\"js/teacher-func.js?v=".$classroomid."\"></script>\n";
  ?>
  <?php if ($theme != "") {
    echo '<script src="jquery/'.$theme.'/external/jquery/jquery.js"></script>';
    echo '<script src="jquery/'.$theme.'/jquery-ui.js"></script>';
  } else {
    echo '<script src="jquery/jquery.js"></script>';
    echo '<script src="jquery/jquery-ui.min.js"></script>';
  }
	?>
</head>
<body onload="Init(); InitVoices(); setTimeout(InitVoices,1000); setTimeout(InitVoices,2000); /*setTimeout(function () {StartSpeechRecognition(Traduction); },3000); */ setTimeout(Lancer,3000); ready(); updateLinks(); Embedcode(); EcrireNBonly(''); applyConfig(); FixTabOrderIssue();">
<main>
<h1 style="display:none;">Outils de traduction : application enseignante</h1>
</main>
	<div id="Allbuttons">
		<div id="pincodediv" title="Pincode that can be communicated to the students. It allows to synchronize the student app with the presented lesson. Alternatives (QRcode, direct link) can be found in the share tab." onclick="/*if (sizepincodediv == 1) sizepincodediv=0.1; else sizepincodediv=1; this.style.transform='scaleX('+sizepincodediv+')';*/">
		<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'I-Site NExT" style="width: 109px; height: 50px;" ></div>
			<!--<span style="font-size: 0.5em;">Pin code</span><br>--><span title="" id="sharedpin"></span></div>
    <div id="icondiv">
      <div id="connexion"><i class='material-icons big'>signal_wifi_off</i></div>
      <div id="micon" onclick="Micro();" title="This button allows to switch on/off the microphone transcription. Alternatives for this button are the space key, the smartphone remote red button or the presentation pointer button."><i class='material-icons'>voice_over_off</i></div>
		  <div id="questionicon" title="This icon indicates (blinks) when new messages arrived on the Tchat tab."><i class='material-icons'>thumb_up</i></div>
		  <!-- <div id="FS" title="Toggle fullscreen view" onclick="GoFS();" title="Toggle fullscreen mode"><img alt="Passage en mode plein écran" id='FSimage' src="data:image/png;base64,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" style="width:40px; height: 40px"></div> -->
		  <div id="FS" title="Toggle fullscreen view" onclick="GoFS();"><i class='material-icons' style="font-size: 41px;" >fullscreen</i></div>
		</div>
	</div>
	<!--  // change here for touchable
<div id="titreleft" onclick="Backward();">&#9666;</div>
<div id="titreright"></div>
<div id="titre" onclick="Forward();"><h1>Automatic translation for teaching purposes: Student</h1></div>
<div id="soustitre"></div>
-->
	<div id="titreleft" onmouseover="_('soustitre').style.transform='translateY(0px)'; _('titreleft').style.transform='translateY(0px)';_('titrerightbis').style.transform='translateY(0px)'; _('titreright').style.transform='translateY(0px)'; _('titre').style.transform='translateY(0px)';" onclick="Backward();">&#9666;</div>
	<div id="titrerightbis" onmouseover="_('soustitre').style.transform='translateY(0px)'; _('titreleft').style.transform='translateY(0px)'; _('titrerightbis').style.transform='translateY(0px)';_('titreright').style.transform='translateY(0px)'; _('titre').style.transform='translateY(0px)';" onclick="Forward();">&#9656;</div>
	<div id="titreright"></div>
	<div id="titre" onmouseover="_('soustitre').style.transform='translateY(0px)'; _('titreleft').style.transform='translateY(0px)'; _('titrerightbis').style.transform='translateY(0px)'; _('titreright').style.transform='translateY(0px)'; _('titre').style.transform='translateY(0px)';">
		<h1>Automatic translation for teaching purposes: Teacher</h1></div>
	<div id="soustitre" onmouseover="this.style.transform='translateY(-64px)'; _('titreleft').style.transform='translateY(-64px)'; _('titrerightbis').style.transform='translateY(-64px)'; _('titreright').style.transform='translateY(-64px)'; _('titre').style.transform='translateY(-64px)';"></div>
	<div id="partialtitre"></div>
	<div id="studentfeedback"></div>
	<div id="scrollbar"></div>
	<form id="myform" onchange="updateConfig();">
  		<input type="submit" style="display: none;" value="Envoyer">
		<div id="tabs" style="border: 0px;">
			<ul id="bar" onmouseover="this.style.transform='scaleY(1)'" onmouseout="reduceToolbar()">
				<li id="logonextbar" style="background-color: #000; color: white"><img onclick="<?php if ($external) echo "javascript:void(window.open('scenarios.php?external', '_self'))"; else echo "javascript:void(window.open('scenarios.php', '_self'))"; ?>" src="images/omist-top-logo-nav_ssfd.png" height="32" alt="Logo OMIST"></li>
				<li class="tabbed" title="Settings tab (language configuration, appearance,...)"><a class="tabbed" href="#tabs-0" id="ttab-0" onclick="inSlideTab=false;"><i class="fa fa-cog"></i><span class="hidesmallscreens">&nbsp;Settings</span></a></li>
				<li class="tabbed" title="Multilingual Tchat tab"><a class="tabbed" href="#tabs-1" id="ttab-1" onclick="timenew_question=0; inSlideTab=false;"><i class="fa fa-comments"></i><span class="hidesmallscreens">&nbsp;Tchat</span></a></li>
				<li class="tabbed" title="Speech transcription tab. From here, it is possible to export a transcription of the speech."><a class="tabbed" href="#tabs-2" id="ttab-2" onclick="inSlideTab=false;"><i class="fa fa-cc"></i><!--<i class="fa fa-arrow-right"></i>-->
                 <span class="hidesmallscreens">&nbsp;Speech</span></a></li>
				<li class="tabbed" title="Upload your PPTX or HTML complete lesson from this tab"><a class="tabbed" href="#tabs-5" id="ttab-3" onclick="inSlideTab=false;"><!--<i class="fa fa-arrow-right"></i>--><i class="fa fa-upload"></i>
                 <span class="hidesmallscreens">&nbsp;Complete lesson</span></a></li>
				<li class="tabbed" title="The slides are shown in this tab" id="titleSlides"><a class="tabbed" href="#tabs-7" id="ttab-4" onclick="inSlideTab=true; this.blur(); _('img_slides').focus(); _('img_slides').click(); this.blur();"><i class="fa fa-picture-o"></i><span class="hidesmallscreens">&nbsp;Slides</span></a></li>
				<li class="tabbed" title="Jitsi visioconference tab" id="titlevisio"><a class="tabbed" href="#tabs-6" id="ttab-5" onclick="if (!visioactive) _('visio').src='https://meet.jit.si/Translator_'+_('classroomid').value; visioactive=true; inSlideTab=false;">Visio</a></li>
				<li class="tabbed" title="Live translation tab" id="titlelive"><a class="tabbed" href="#tabs-3" id="ttab-6" onclick="inSlideTab=false;">Live translation</a></li>
				<li class="tabbed" title="This tab contains links allowing to share the lesson with students or other teachers. A link for using a smartphone remote is also available." id="titleshare"><a class="tabbed" href="#tabs-4" id="ttab-7" onclick="inSlideTab=false;"><i class="fa fa-qrcode"></i><span class="hidesmallscreens">&nbsp;Share</span></a></li>
				<li class="tabbed" title="A tab allowing to interact with WOOCLAP(c)" id="titlewooclap"><a class="tabbed" href="#tabs-8" id="ttab-8" onclick="inSlideTab=false;"><img src="images/wooclap-ico.png" alt="Icone de l'outil WOOCLAP" width="18" style="vertical-align: middle;"><span class="hidesmallscreens">&nbsp;WOOCLAP</span></a></li>
				<!-- <li class="tabbed" title="All the slide manipulation tools can be found here" id="titletools"><a class="tabbed" href="#tabs-20" id="ttab-9" onclick="inSlideTab=false;"><i class="fa fa-wrench"></i><span class="hidesmallscreens">&nbsp;Slide tools</span></a></li> -->
				<li class="tabbed" title="Extra tab for interacting with external tools" id="titleextratab1"><a class="tabbed" href="#tabs-9" onclick="inSlideTab=false;"><i class="fa fa-desktop"></i><span class="hidesmallscreens">&nbsp;Tab 1</span></a></li>
				<li class="tabbed" title="Extra tab for interacting with external tools" id="titleextratab2"><a class="tabbed" href="#tabs-10" onclick="inSlideTab=false;"><i class="fa fa-desktop"></i><span class="hidesmallscreens">&nbsp;Tab 2</span></a></li>
				<li class="tabbed" title="Extra tab for interacting with external tools" id="titleextratab3"><a class="tabbed" href="#tabs-11" onclick="inSlideTab=false;"><i class="fa fa-desktop"></i><span class="hidesmallscreens">&nbsp;Tab 3</span></a></li>
				<li class="tabbed" title="Extra tab for interacting with external tools" id="titleextratab4"><a class="tabbed" href="#tabs-12" onclick="inSlideTab=false;"><i class="fa fa-desktop"></i><span class="hidesmallscreens">&nbsp;Tab 4</span></a></li>
				<li style="background-color: #000; color: white" id="confortplus" title="Accessibilité"></li>
				<li style="display:none;" id="logout" onclick="window.location.href='Admin/Registration/logout.php';"><span id="logout-button" title="Logout"><i class="fa fa-sign-out" aria-hidden="true"></i><span class="hidesmallscreens">&nbsp;Logout</span></span></li>
			</ul>
			<div id="tabs-0">
				<h3 class="tabbed">Language configuration</h3>
				<div>
					<ul style="list-style-type: none;">
						<li>
							<label for="classroomlanguage">Speaker language :</label>
							<select id="classroomlanguage" onchange="setLanguageInput(this.value); Micro(); Micro();" class="jqueryselect"> </select>
						</li>
						<li style="display: none;">
							<label for="recognitionon">Speech recognition active ? </label>
							<input type="checkbox" id="recognitionon" >
						</li>
						<li <?php if (true) echo "style=\"display:none;\""; ?> > <label for="pincode">Pin code :</label>
							<input type="text" id="pincode" style="width: 50px;" disabled> &nbsp;
							<input type="button" value="Change pin" onclick="_('pincode').value=prompt('Enter a PIN code'); Synchronize_save();" >
							<input type="button" value="Synchronize" onclick="Synchronize(prompt('Enter a PIN code'));" > </li>
						<li>&nbsp;</li>
						<li style="display: none;">Translate subtitles ?
							<input type="checkbox" id="subtitleon" onclick="if (this.checked) _('spansubtitleon').innerHTML='Yes !'; else _('spansubtitleon').innerHTML='No.';" >&nbsp;<span class="yesno" id="spansubtitleon">No.</span></li>
						<li><label for="subtitlelanguage">Language for subtitles :</label>
							<select id="subtitlelanguage" onchange="setLanguageSubtitle(this.value);" class="jqueryselect"> </select>
						</li>
					</ul>
				</div>
				<h3 class="tabbed">Subtitles configuration and Icons</h3>
				<div>
					<ul style="list-style-type: none;">
						<li><b>Subtitles appearance</b></li>
						<li><label for="subtitlebaron">Show subtitles bar ?</label>
							<input type="checkbox" checked id="subtitlebaron" onclick="if (this.checked) {_('spansubtitlebaron').innerHTML='Yes !'; _('titre').style.visibility='visible'; _('titreright').style.visibility='visible'; } else {_('spansubtitlebaron').innerHTML='No.'; _('titre').style.visibility='hidden'; _('titreleft').style.visibility='hidden'; _('titrerightbis').style.visibility='hidden'; _('titreright').style.visibility='hidden'; _('arrowson').checked=false;}" >&nbsp;<span class="yesno" id="spansubtitlebaron">Yes !</span>&nbsp;
							<label for="ColorTitre">Color :</label>
							<select id="ColorTitre" name="ColorTitre" onchange="ChangeColor('titre',this.value); ChangeColor('titreleft',this.value);ChangeColor('titrerightbis',this.value);" class="jqueryselect">
								<option value="0,0,255|0,0,255" selected>blue</option>
								<option value="0,255,0|0,100,0">green</option>
								<option value="255,0,0|100,0,0">red</option>
								<option value="255,0,255|100,0,255">purple</option>
								<option value="255,255,0|100,100,0">yellow</option>
								<option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
								<option value="0,255,0|0,255,0|0,50,0">green inverted</option>
								<option value="255,0,0|255,0,0|20,0,0">red inverted</option>
								<option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
								<option value="255,255,0|200,200,0|50,50,0">yellow inverted</option>
								<option value="0,0,0|0,0,0">black</option>
								<option value="255,255,255|255,255,255|0,0,0">white</option>
							</select>
						</li>
						<li><label for="arrowson">Show arrows around subtitles ?</label>
							<input type="checkbox" id="arrowson" onclick="if (this.checked) {_('spanarrowson').innerHTML='Yes !';_('titreleft').style.visibility='visible'; _('titrerightbis').style.visibility='visible'; _('titreright').style.visibility='visible'; } else {_('spanarrowson').innerHTML='No.'; _('titreleft').style.visibility='hidden'; _('titrerightbis').style.visibility='hidden';}" >&nbsp;<span class="yesno" id="spanarrowson">Yes !</span></li>
						<li><label for="subtitlepartialon">Show instant transcription ?</label>
							<input type="checkbox" id="subtitlepartialon" onclick="if (this.checked) {_('spansubtitlepartialon').innerHTML='Yes !'; _('partialtitre').style.visibility='visible'; } else {_('spansubtitlepartialon').innerHTML='No.'; _('partialtitre').style.visibility='hidden'; }" >&nbsp;<span class="yesno" id="spansubtitlepartialon">No.</span>&nbsp; 
							<label for="ColorPartialTitre">Color :</label>
							<select id="ColorPartialTitre" name="ColorPartialTitre" onchange="ChangeColor('partialtitre',this.value);" class="jqueryselect">
								<option value="0,0,255|0,0,255">blue</option>
								<option value="0,255,0|0,100,0">green</option>
								<option value="255,0,0|100,0,0">red</option>
								<option value="255,0,255|100,0,255">purple</option>
								<option value="255,255,0|100,100,0">yellow</option>
								<option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
								<option value="0,255,0|0,255,0|0,50,0">green inverted</option>
								<option value="255,0,0|255,0,0|20,0,0">red inverted</option>
								<option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
								<option value="255,255,0|200,200,0|50,50,0">yellow inverted</option>
								<option value="0,0,0|0,0,0" selected>black</option>
								<option value="255,255,255|255,255,255|0,0,0">white</option>
							</select>
						</li>
						<li style="display:none;"><label for="subtitlestudenton">Show student feedback ?</label>
							<input type="checkbox" id="subtitlestudenton" onclick="if (this.checked) {_('spansubtitlestudenton').innerHTML='Yes !'; _('studentfeedback').style.visibility='visible'; } else {_('spansubtitlestudenton').innerHTML='No.'; _('studentfeedback').style.visibility='hidden'; }" >&nbsp;<span class="yesno" id="spansubtitlestudenton">No.</span>&nbsp; Color :
							<label style="display:none;" for="Colorstudentfeedback">Colorstudentfeedback</label>
							<select id="Colorstudentfeedback" onchange="ChangeColor('studentfeedback',this.value);" class="jqueryselect">
								<option value="0,0,255|0,0,255">blue</option>
								<option value="0,255,0|0,100,0">green</option>
								<option value="255,0,0|100,0,0">red</option>
								<option value="255,0,255|100,0,255">purple</option>
								<option value="255,255,0|100,100,0">yellow</option>
								<option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
								<option value="0,255,0|0,255,0|0,50,0">green inverted</option>
								<option value="255,0,0|255,0,0|20,0,0">red inverted</option>
								<option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
								<option value="255,255,0|200,200,0|50,50,0">yellow inverted</option>
								<option value="0,0,0|0,0,0" selected>black</option>
								<option value="255,255,255|255,255,255|0,0,0">white</option>
							</select>
						</li>
						<li><label for="selectfontsize">Subtitles font size :</label>
							<select id="selectfontsize" onchange="_('titre').style.fontSize = this.value; _('partialtitre').style.fontSize = this.value; _('studentfeedback').style.fontSize = this.value;" class="jqueryselect">
								<option value="12px">Small</option>
								<option value="18px" selected>Normal</option>
								<option value="24px">Big</option>
								<option value="32px">Huge</option>
								<option value="48px">Enormous</option>
							</select>
						</li>
						<li><label for="selectopacity">Subtitles opacity :</label>
							<select id="selectopacity" onchange="ChangeOpacity('titre', this.value); ChangeOpacity('partialtitre', this.value); ChangeOpacity('studentfeedback', this.value);ChangeOpacity('titreleft', this.value); ChangeOpacity('titrerightbis', this.value);" class="jqueryselect">
								<option value="0">0% opaque</option>
								<option value="0.25">25% opaque</option>
								<option value="0.5" selected>50% opaque</option>
								<option value="0.75">75% opaque</option>
								<option value="1">100% opaque</option>
							</select>
						</li>
						<li>&nbsp;</li>
						<li><b>Icons</b></li>
						<li><label for="iconon">Show icons</label>
							<input type="checkbox" checked id="iconon" onclick="showEachIcon(); if (this.checked) {
    {_('spaniconon').innerHTML='Yes !';} else {_('spaniconon').innerHTML='No.';}" > &nbsp;<span class="yesno" id="spaniconon">Yes !</span></li>
						<li id="eachicon" style="display:none">
							<label for="pincodeon">Pin code ?</label>
							<input title="Hide or show the pincode information. If 'Yes !' or checked, the pincode is visible." type="checkbox" id="pincodeon" onclick="if (this.checked) {_('spanpincodeon').innerHTML='Yes !'; _('pincodediv').style.visibility='visible';} else {_('spanpincodeon').innerHTML='No.';_('pincodediv').style.visibility='hidden';}" >&nbsp;<span class="yesno" id="spanpincodeon">No.</span> &nbsp; 
							<label for="microon">Micro ?</label>
							<input title="Hide or show the microphone icon. If 'Yes !' or checked, the microphone icon is visible." type="checkbox" id="microon" onclick="if (this.checked) {_('spanmicroon').innerHTML='Yes !'; show('micon');} else {_('spanmicroon').innerHTML='No.';hide('micon');}" >&nbsp;<span class="yesno" id="spanmicroon">No.</span> &nbsp; 
							<label for="questionon">Question ?</label>
							<input title="Hide or show the question icon. If 'Yes !' or checked, the question icon is visible." type="checkbox" id="questionon" onclick="if (this.checked) {_('spanquestionon').innerHTML='Yes !'; show('questionicon');} else {_('spanquestionon').innerHTML='No.';hide('questionicon');}" >&nbsp;<span class="yesno" id="spanquestionon">No.</span> &nbsp; 
							<label for="fson">Full screen ?</label>
							<input title="Hide or show the fullscreen icon. If 'Yes !' or checked, the fullscreen icon is visible." type="checkbox" id="fson" onclick="if (this.checked) {_('spanfson').innerHTML='Yes !'; show('FS');} else {_('spanfson').innerHTML='No.';hide('FS');}" >&nbsp;<span class="yesno" id="spanfson">No.</span></li>
						<li>&nbsp;</li>
						<li><b>Pin code appearance</b></li>
						<li><label for="selecticonsize">Pin code size</label>
							<select id="selecticonsize" onchange="ChangeSizeIcons(this.value);" class="jqueryselect">
								<option value=30>Small</option>
								<option value=50 selected>Normal</option>
								<option value=80>Big</option>
							</select>
						</li>
					</ul>
				</div>
				<h3 class="tabbed">Tabs configuration and external tools</h3>
				<div>
					<ul style="list-style-type: none;">
						<li><b>Extra tabs</b></li>
						<li title="Hide or show the WOOCLAP tab. If 'Yes !' or checked, the WOOCLAP tab is visible in the toolbar."><label for="embedwooclapon">Embed WOOCLAP ?</label>
							<input type="checkbox" id="embedwooclapon" onclick="if (this.checked) {_('spanembedwooclapon').innerHTML='Yes !'; show('titlewooclap');} else {_('spanembedwooclapon').innerHTML='No.';  hide('titlewooclap');}" >&nbsp;<span class="yesno" id="spanembedwooclapon">No.</span>
							<button style="display:none;" onclick="window.open('https://app.wooclap.com/auth/login?lang=en&redirectTo=/home')">Login to wooclap first</button>
							<button style="display:none;" onclick="_('wooclap').src='https://app.wooclap.com';">Update the wooclap tab</button>
						</li>
						<!-- <li>Show Live translation tab ?  <input type="checkbox" id="embedliveon" onclick="if (this.checked) {_('spanembedliveon').innerHTML='Yes !'; show('titlelive');} else {_('spanembedliveon').innerHTML='No.';  hide('titlelive');}">&nbsp;<span class="yesno" id="spanembedliveon">No.</span></li> -->
						<!--  <li>Show Visio tab ?  <input type="checkbox" id="embedvisioon" onclick="if (this.checked) {_('spanembedvisioon').innerHTML='Yes !'; show('titlevisio');} else {_('spanembedvisioon').innerHTML='No.';  hide('titlevisio');}">&nbsp;<span class="yesno" id="spanembedvisioon">No.</span></li> -->
						<li title="Hide or show the share tab. If 'Yes !' or checked, the share tab is visible in the toolbar.">
							<label for="embedshareon">Show Share tab ?</label>
							<input type="checkbox" checked id="embedshareon" onclick="if (this.checked) {_('spanembedshareon').innerHTML='Yes !'; show('titleshare');} else {_('spanembedshareon').innerHTML='No.';  hide('titleshare');}" >&nbsp;<span class="yesno" id="spanembedshareon">Yes !</span></li>
						<li><label for="extraurl1">Extra tab 1 URL : </label>
							<input type="text" id="extraurl1" value="" placeholder="Copy your application url here" >&nbsp;
							<label for="embedET1"> Show ?</label>
							<input type="checkbox" id="embedET1" onclick="showET(this,1);" >&nbsp;<span class="yesno" id="spanembedET1">No.</span>&nbsp;
							<button onclick="saveTextQ('[extratab1|'+_('extraurl1').value+']');">Send to students</button>
						</li>
						<li><label for="extraurl2">Extra tab 2 URL : </label>
							<input type="text" id="extraurl2" value="" placeholder="Copy your application url here" >&nbsp;
							<label for="embedET2"> Show ?</label>
							<input type="checkbox" id="embedET2" onclick="showET(this,2);" >&nbsp;<span class="yesno" id="spanembedET2">No.</span>&nbsp;
							<button onclick="saveTextQ('[extratab2|'+_('extraurl2').value+']');">Send to students</button>
						</li>
						<li><label for="extraurl3">Extra tab 3 URL : </label>
							<input type="text" id="extraurl3" value="" placeholder="Copy your application url here" >&nbsp;
							<label for="embedET3"> Show ?</label>
							<input type="checkbox" id="embedET3" onclick="showET(this,3);" >&nbsp;<span class="yesno" id="spanembedET3">No.</span>&nbsp;
							<button onclick="saveTextQ('[extratab3|'+_('extraurl3').value+']');">Send to students</button>
						</li>
						<li><label for="extraurl4">Extra tab 4 URL : </label>
							<input type="text" id="extraurl4" value="" placeholder="Copy your application url here" >&nbsp;
							<label for="embedET4"> Show ?</label>
							<input type="checkbox" id="embedET4" onclick="showET(this,4);" >&nbsp;<span class="yesno" id="spanembedET4">No.</span>&nbsp;
							<button onclick="saveTextQ('[extratab4|'+_('extraurl4').value+']');">Send to students</button>
						</li>
						<li></li>
					</ul>
					<p>The following links to open source online tools can be useful for the extra tabs feature.
						<ul style="list-style-type: none; margin: 10px; padding:0;">
							<li>Tableau noir blackboard :
								<a id="link1" class="tooltip" onload="this.src='';" onclick="opentab(this)" title="Click to add to extra tabs" href="javascript:void(0)"></a>
							</li>
							<li>WBO whiteboard :
								<a id="link2" class="tooltip" onclick="opentab(this)" onload="this.src='';" title="Click to add to extra tabs" href="javascript:void(0)"></a>
							</li>
							<li>Jitsi visioconference :
								<a id="link3" class="tooltip" onclick="opentab(this)" onload="this.src='';" title="Click to add to extra tabs" href="javascript:void(0)"></a>
							</li>
							<li>Symbolic computations (Xcas) : <a id="link4" class="tooltip" onclick="opentab(this)" onload="this.src='https://algoscript.info/xcas';"  title="Click to add to extra tabs for https://algoscript.info/xcas" href="javascript:void(0)">https://algoscript.info/xcas</a></li>
							<li>PPTX generic viewer : <a id="link5" class="tooltip" onclick="opentab(this)" title="Click to add to extra tabs for https://products.groupdocs.app/fr/viewer/pptx" href="javascript:void(0)" onload="this.src='https://products.groupdocs.app/fr/viewer/pptx';">https://products.groupdocs.app/fr/viewer/pptx</a></li>
							<li>Universal document viewer : <a id="link6" class="tooltip" onclick="opentab(this)" title="Click to add to extra tabs for https://products.groupdocs.app/fr/viewer/total" href="javascript:void(0)" onload="this.src='https://products.groupdocs.app/fr/viewer/total';">https://products.groupdocs.app/fr/viewer/total</a></li>
							<li>3D viewer : <a id="link7" class="tooltip" onclick="opentab(this)" title="Click to add to extra tabs for https://3dviewer.net/" href="javascript:void(0)" onload="this.src='https://3dviewer.net/';">https://3dviewer.net/</a></li>
							<li>Nantes University Zoom session [Experimental]: <a id="link8" class="tooltip" onclick="var lnk=prompt('Copy your Zoom link here'); if (lnk.indexOf('univ-nantes-fr') != false) {this.innerHTML=lnk.replace('https://univ-nantes-fr.zoom.us/j/','https://univ-nantes-fr.zoom.us/wc/join/'); opentab(this);}" title="Enter your zoom link" href="javascript:void(0)" onload="this.src='';">Enter your zoom link</a></li>
							<li>Youtube video [Experimental]: <a id="link9" class="tooltip" onclick="var lnk=prompt('Copy your Youtube ID here'); {this.innerHTML='https://www.youtube.com/embed/'+lnk+'?origin=https://misc-sciences.univ-nantes.fr'; opentab(this);}" title="Enter your Youtube video ID" href="javascript:void(0)" onload="this.src='';">Enter your Youtube video ID</a></li>
						</ul>
						<br> </div>
				<h3 class="tabbed">Extra functionalities</h3>
				<div>
					<ul style="list-style-type: none;">
						<li>&nbsp;</li>
						<li <?php if (true) echo "style=\"display:none;\""; ?> title="Do not change unless you know what you are doing !">
							<label for="classroomid">Classroom identifier :</label>
							<input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" >
						</li>
						<li style="display:none;" title="Do not change unless you know what you are doing !">
							<label for="appid">Application identifier :</label>
							<input type="text" name="appid" id="appid" value="<?php echo $appid; ?>" >
						</li>
						<li title="Do not change unless you know what you are doing !">
							<label for="externaltranscritionon">External transcription service ?</label>
							<input type="checkbox" id="externaltranscritionon" onclick="if (this.checked) _('spanexternaltranscritionon').innerHTML='Yes !'; else _('spanexternaltranscritionon').innerHTML='No.'; builtin_externaltranscritionon=this.checked" >&nbsp;<span class="yesno" id="spanexternaltranscritionon">No.</span></li>
						<li <?php if (! $Microsofton) echo "style=\"display:none;\""; ?> title="Do not change unless you know what you are doing !"><label for="microsofttranscritionon">Microsoft transcription service ?</label>
							<input type="checkbox" id="microsofttranscritionon" onclick="InitializeMicrosoft(); if (this.checked) _('spanmicrosofttranscritionon').innerHTML='Yes !'; else _('spanmicrosofttranscritionon').innerHTML='No.'; builtin_microsofttranscritionon=this.checked" >&nbsp;<span class="yesno" id="spanmicrosofttranscritionon">No.</span></li>
						<li title="Do not change unless you know what you are doing ! This functionnality imply a huge increase of the translation cost !"><label for="directMicrosoftTranslation">Enable the direct translation functionnality ?</label>
							<input type="checkbox" id="directMicrosoftTranslation" onclick="if (this.checked) _('spanmicrosoftdirecttranscritionon').innerHTML='Yes !'; else _('spanmicrosoftdirecttranscritionon').innerHTML='No.';" >&nbsp;<span class="yesno" id="spanmicrosoftdirecttranscritionon">No.</span></li>
						<li title="Do not change unless you know what you are doing !"><label for="directMicrosoftTranslationBoth">Show both the direct transcription and its translation ?</label>
							<input type="checkbox" id="directMicrosoftTranslationBoth" onclick="if (this.checked) _('spanmicrosoftdirecttranscritionBothon').innerHTML='Yes !'; else _('spanmicrosoftdirecttranscritionBothon').innerHTML='No.';" >&nbsp;<span class="yesno" id="spanmicrosoftdirecttranscritionBothon">No.</span></li>
						<li <?php if (! $Microsofton) echo "style=\"display:none;\""; ?> title="Do not change unless you know what you are doing !"><label for="BoostedWords">Boosted words or expressions (one per line)</label>
							<br>
							<textarea id="BoostedWords" rows="3" cols="20"></textarea>
						</li>
						<li><label for="verbaltics">Remove verbal tics (one per line in lowercase)</label>
							<br>
							<textarea id="verbaltics" rows="3" cols="20"></textarea>
							<li><span id="connectedusers"></span></li>
						<li title="Do not change unless you know what you are doing !">
							<label for="whichtranslator">Translator choice</label>
							<select id="whichtranslator">
								<option value="" selected>Default</option>
								<option value="DEEPLPRO">DeepL Pro API</option>
								<option value="DEEPLFREE">DeepL Free API</option>
								<option value="LIBRETRANSLATE">Libre Translate</option>
								<option value="GOOGLE">Google Translate API</option>
							</select>
						</li>
						<li title="Do not change unless you know what you are doing !">
						<label for="apikey">API key (for DeepL or Google)</label>
						<input type="text" id="apikey" value="">
						</li>
             </ul>
					<br>
					<p>The Slide Manipulation Toolkit is accessible <a href="<?php if ($external) echo " javascript:void(window.open( 'OMIST_Tools/indexExternal.php?ROOMID=".$classroomid."')) "; else echo "javascript:void(window.open( 'OMIST_Tools')) ";?>">here</a>.</p>

              </div>
              <h3 class="tabbed">Configuration code</h3>
              <div>
              <ul>
              <li><label for="myConfig">Current configuration code :</label>
							  <input type="text" id="myConfig" value="0" style="width: 250px;" disabled>
						  </li>
							<li>Copy this link to always access the teachers application with this configuration :
								<a href="" id="LinkConfig"></a>
							</li>
							<li><span id="debugspan"></span></li>
					</ul>
				</div>
			</div>
			<div id="tabs-1">
				<div id="classroom" style="border: solid 1px;"></div>
				<textarea id="question" aria-label="Your question here" title="Please use this field to send your question." placeholder="Your question here" rows="2" cols="50"></textarea>
				<br>
				<input type="button" value="send" class="button-3 tooltip" id="questionsend" onclick="sendclick();" title="Send your question" >
				<input type="button" value="refresh" class="button-3 tooltip" title="Reload all the conversation and translate it if necessary." onclick="recharger();" > </div>
			<div id="tabs-2">
				<div id="notebooks_container" style="display: flex;">
					<div id="VO_container" style="width: 50%;">
						<input type="button" id="buttondownloadspeechVO" value="Download the speech VO" class="button-3 tooltip" title="Download the notebook in txt format." onclick="DownloadSpeechLesson('lessonVO_'+_('classroomid').value+'.txt', _('notebookVO').innerHTML);;" >
						<br>
						<div id="notebookVO" style="border: solid 1px;" contenteditable="true" aria-label="Complete speech here." title="Complete speech here."><br></div>
					</div>
					<div id="translated_container" style="width: 50%;">
						<input type="button" id="buttondownloadspeech" value="Download the speech translated" class="button-3 tooltip" title="Download the notebook in txt format." onclick="DownloadSpeechLesson('lesson_'+_('classroomid').value+'.txt', _('notebook').innerHTML);;" >
						<br>
						<div id="notebook" style="border: solid 1px;" contenteditable="true" aria-label="Complete speech here." title="Complete speech here."> </div>
					</div>
				</div>
				<br>
				<!-- <input type="button" id="savenb" value="Save notebook" onclick="saveTextNB(_('notebook').innerHTML);"> -->
			</div>
			<div id="tabs-5">
				<div>
					<label for="link-tools">If your PPTX is too big, you can convert it into HTML using the OMIST tools : </label>
					<input type="button" id="link-tools" onclick="<?php if ($external) echo "javascript:void(window.open('OMIST_Tools/indexNOCAS.php'))"; else echo "javascript:void(window.open('OMIST_Tools'))"; ?>" value="Slide Tools">
					<br>
					<label for="ppt_presentation">Powerpoint/HTML presentation : </label>
					<input type="file" id="ppt_presentation" name="ppt_presentation" aria-label="Powerpoint/HTML presentation" title="Select your presentation file. Light weight PPTX are allowed. For bigger files, it is recommended (and sometimes mandatory) to convert first the PPTX file to an HTML animation by using the Slide Tools." onchange="SlideNotes('')" accept=".pptx,.odp,.txt,.html" >
					<br>
					<label for="speechlanguage" title="If the speech prepared in the slides is of a different language than the speaker language one, it can be adjusted by using this field.">Speech language : </label>
					<select id="speechlanguage" onchange="setLanguageSpeech(this.value);" class="jqueryselect">
						<option value="same" selected>Same as speaker</option>
					</select> <span style="display:none">
   <label for="tobesaid" title="Portion of text inserted between each slides (eq. Next slide...)">Slide transition : </label>
    <input type="text" id="tobesaid" value="">
    </span>
					<br>
					<progress title="Progress bar" id="progressBar" value="0" max="100" style="width:300px;"></progress>
				</div>
				<div id="notebookcompleteformatted" style="border: solid 1px; height: 600px; overflow: auto;"> </div>
			</div>
			<div id="tabs-6">	
				<div id="slideshow" style="border: solid 1px; height: 600px; overflow: auto;">
					<iframe id="visio" src="blank.html" allowfullscreen  allow="camera;microphone;display-capture" style="position: relative; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
				</div>
			</div>
			
			<div id="tabs-3">
				<ul style="list-style-type: none;">
					<li>
						<label for="liveon">Live translation active ? </label>
						<input type="checkbox" id="liveon" >
					</li>
					<li>
						<button id="bready">Ready to listen ?</button>
					</li>
					<li>
						<label for="liveon">Output language </label>
						<select id="VoiceOutput" onchange="setLanguageOutput(this.value);" class="jqueryselect"> </select>
					</li>
					<li>
						<button id="bswitch" onclick="var tmp=_('VoiceOutput').value; _('VoiceOutput').value=_('classroomlanguage').value; setLanguageOutput(_('VoiceOutput').value); setLanguageInput(_('classroomlanguage').value); Micro(); Micro();">input ↔ output</button>
					</li>
				</ul>
				<div id="output"> </div>
			</div>
			<div id="tabs-4">
				<h3 class="tabbed">For students</h3>
				<div>
					<ul style="list-style-type: none;">
						<li>Students application : <a id="sharedlink" href="Student.php?ROOMID=<?php echo $classroomid; if ($theme != "") echo "&theme=".$theme; ?>">Student.php?ROOMID=<?php echo $classroomid; if ($theme != "") echo "&theme=".$theme; ?></a></li>
						<li>Pin code : <span title="" id="sharedpin2" style="font-size: 5em;"></span></li>
						<li>QR code :
							<div title="" id="sharedqr" style="margin-left: auto; margin-right: auto;"></div>
						</li>
					</ul>
				</div>
				<h3 class="tabbed">For teachers</h3>
				<div>
					<ul style="list-style-type: none;">
						<li>Teacher's remote : <a id="remotelink" href="remote.php?ROOMID=<?php echo $classroomid; ?>&LANG=fr,en&APPID=<?php echo $appid; ?>">remote.php?ROOMID=<?php echo $classroomid; ?></a>&nbsp;
							<input type="button" value="Language switcher" title="Add the language switcher capability to the remote (useful for multilingual speakers)." onclick="var difflang=prompt('Enter the list of languages (2 letter codes separated by commas).\n Examples : \'fr,en\' or \'fr,en,es,it\'',''); _('remotelink').href=_('remotelink').innerHTML+'&LANG='+difflang+'&APPID='+_('appid').value; _('remoteqr').innerHTML=''; new QRCode(_('remoteqr'), {	text: _('remotelink').href,	width: 128,	height: 128,	colorDark : '#0000ff',	colorLight : '#ffffff',	correctLevel : QRCode.CorrectLevel.L});" >
						</li>
						<li>QR code :
							<div title="" id="remoteqr" style="margin-left: auto; margin-right: auto;"></div>
						</li>
						<li>Teacher's application : <a id="teacherlink" href="index.php?ROOMID=<?php echo $classroomid; ?>">index.php?ROOMID=<?php echo $classroomid; ?></a>
							<?php 
        if (! $external  && $userprivileges < 3) echo "&nbsp;<button class=\"tooltip\" title=\"Create a valid link allowing a non Nantes University user to access the teachers application. The created link expires 24 hours after its creation.\" onclick=\"InviteExternal();\">Invite an external guest</button>";
        if (! $external && $userprivileges < 2) {
          echo '<label for="duration">&nbsp;for&nbsp;</label><select id="duration" class="jqueryselect">';
          echo "<option value=\"600\">10 minutes</option>";
          echo "<option value=\"3600\">1 hour</option>";
          echo "<option value=\"86400\" selected>24 hours</option>";
          echo "<option value=\"604800\" >1 week</option>";
          echo "<option value=\"2678400\" >1 month</option>";
          echo "</select>";
        } else {
          echo '<label for="duration" style="display:none;">&nbsp;for&nbsp;</label><input type="text" value="604800" id="duration" style="display:none;">';
        }
        ?>
						</li>
					</ul>
				</div>
				<h3 class="tabbed">Chat only</h3>
				<div>
					<ul style="list-style-type: none;">
						<li>Chat only link : <a id="chatlink" href="chat.php?ROOMID=<?php echo $classroomid; ?>">chat.php?ROOMID=<?php echo $classroomid; ?></a></li>
						<li><label for="embedcontent">Chat only embed code :</label>
							<input type="text" id="embedcontent" value="" > </li>
					</ul>
				</div>
				<h3 class="tabbed">Subtitles only</h3>
				<div>
					<ul style="list-style-type: none;">
						<li>Subtitles only link : <a id="sublink" target="popup" href="" onclick="SubWindow()" aria-label="Open a window with subtitles only"></a></li>
					</ul>
				</div>
			</div>
			<div id="tabs-7"><a style="display:none;" href="javascript:void(0)" id="linkslides"></a>
			<div id="stillwaiting">
<!--					<div id="stillwaitingspinner"></div>-->
					<div style="width:500px; text-align: center;"><svg id="stillwaitingspinner" role="img" aria-label="Spinner image" xmlns="http://www.w3.org/2000/svg" width="350" height="148" viewBox="0 0 48 48">
<?php
$nbcircles=15;
$minsize=1.3;
$maxsize=4;
$r=20;
$TeinteFrom=[45,63,80];
$TeinteTo=[26,189,157];
for($i=0; $i<$nbcircles; $i++) {
	$teinteR=$TeinteFrom[0]*(1-$i/$nbcircles)+$TeinteTo[0]*$i/$nbcircles;
	$teinteV=$TeinteFrom[1]*(1-$i/$nbcircles)+$TeinteTo[1]*$i/$nbcircles;
	$teinteB=$TeinteFrom[2]*(1-$i/$nbcircles)+$TeinteTo[2]*$i/$nbcircles;
	echo "<circle cx=\"".(24+20*cos($i*6.283/$nbcircles))."\" cy=\"".(24+20*sin($i*6.283/$nbcircles))."\" r=\"".($minsize*(1-$i/$nbcircles)+$maxsize*$i/$nbcircles)."\" fill=\"rgb(".$teinteR.",".$teinteV.",".$teinteB.")\"/>\n";
}
?>
</svg></div>
					<p id="spinnertxt">No presentation yet...<br>Please go to the complete lesson tab to import it.</p>
</div>
			<img src="images/blank.png"
				 id="img_slides" style="cursor: url(images/pointer.png), zoom-in;" alt="Emplacement des slides diffusés"> 
<!--
				data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAkACQAAD/4QCeRXhpZgAATU0AKgAAAAgABQESAAMAAAABAAEAAAEaAAUAAAABAAAASgEbAAUAAAABAAAAUgEoAAMAAAABAAIAAIdpAAQAAAABAAAAWgAAAAAAAACQAAAAAQAAAJAAAAABAAOShgAHAAAAEgAAAISgAgAEAAAAAQAAAvugAwAEAAAAAQAAAJYAAAAAQVNDSUkAAABTY3JlZW5zaG90/+EJIWh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8APD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIi8+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPD94cGFja2V0IGVuZD0idyI/PgD/7QA4UGhvdG9zaG9wIDMuMAA4QklNBAQAAAAAAAA4QklNBCUAAAAAABDUHYzZjwCyBOmACZjs+EJ+/+IP8ElDQ19QUk9GSUxFAAEBAAAP4GFwcGwCEAAAbW50clJHQiBYWVogB+UABgALAAYAIgACYWNzcEFQUEwAAAAAQVBQTAAAAAAAAAAAAAAAAAAAAAAAAPbWAAEAAAAA0y1hcHBsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASZGVzYwAAAVwAAABiZHNjbQAAAcAAAASCY3BydAAABkQAAAAjd3RwdAAABmgAAAAUclhZWgAABnwAAAAUZ1hZWgAABpAAAAAUYlhZWgAABqQAAAAUclRSQwAABrgAAAgMYWFyZwAADsQAAAAgdmNndAAADuQAAAAwbmRpbgAADxQAAAA+Y2hhZAAAD1QAAAAsbW1vZAAAD4AAAAAodmNncAAAD6gAAAA4YlRSQwAABrgAAAgMZ1RSQwAABrgAAAgMYWFiZwAADsQAAAAgYWFnZwAADsQAAAAgZGVzYwAAAAAAAAAIRGlzcGxheQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG1sdWMAAAAAAAAAJgAAAAxockhSAAAAFAAAAdhrb0tSAAAADAAAAexuYk5PAAAAEgAAAfhpZAAAAAAAEgAAAgpodUhVAAAAFAAAAhxjc0NaAAAAFgAAAjBkYURLAAAAHAAAAkZubE5MAAAAFgAAAmJmaUZJAAAAEAAAAnhpdElUAAAAFAAAAohlc0VTAAAAEgAAApxyb1JPAAAAEgAAApxmckNBAAAAFgAAAq5hcgAAAAAAFAAAAsR1a1VBAAAAHAAAAthoZUlMAAAAFgAAAvR6aFRXAAAACgAAAwp2aVZOAAAADgAAAxRza1NLAAAAFgAAAyJ6aENOAAAACgAAAwpydVJVAAAAJAAAAzhlbkdCAAAAFAAAA1xmckZSAAAAFgAAA3BtcwAAAAAAEgAAA4ZoaUlOAAAAEgAAA5h0aFRIAAAADAAAA6pjYUVTAAAAGAAAA7ZlbkFVAAAAFAAAA1xlc1hMAAAAEgAAApxkZURFAAAAEAAAA85lblVTAAAAEgAAA95wdEJSAAAAGAAAA/BwbFBMAAAAEgAABAhlbEdSAAAAIgAABBpzdlNFAAAAEAAABDx0clRSAAAAFAAABExwdFBUAAAAFgAABGBqYUpQAAAADAAABHYATABDAEQAIAB1ACAAYgBvAGoAac7st+wAIABMAEMARABGAGEAcgBnAGUALQBMAEMARABMAEMARAAgAFcAYQByAG4AYQBTAHoA7QBuAGUAcwAgAEwAQwBEAEIAYQByAGUAdgBuAP0AIABMAEMARABMAEMARAAtAGYAYQByAHYAZQBzAGsA5gByAG0ASwBsAGUAdQByAGUAbgAtAEwAQwBEAFYA5AByAGkALQBMAEMARABMAEMARAAgAGMAbwBsAG8AcgBpAEwAQwBEACAAYwBvAGwAbwByAEEAQwBMACAAYwBvAHUAbABlAHUAciAPAEwAQwBEACAGRQZEBkgGRgYpBBoEPgQ7BEwEPgRABD4EMgQ4BDkAIABMAEMARCAPAEwAQwBEACAF5gXRBeIF1QXgBdlfaYJyAEwAQwBEAEwAQwBEACAATQDgAHUARgBhAHIAZQBiAG4A/QAgAEwAQwBEBCYEMgQ1BEIEPQQ+BDkAIAQWBBoALQQ0BDgEQQQ/BDsENQQ5AEMAbwBsAG8AdQByACAATABDAEQATABDAEQAIABjAG8AdQBsAGUAdQByAFcAYQByAG4AYQAgAEwAQwBECTAJAgkXCUAJKAAgAEwAQwBEAEwAQwBEACAOKg41AEwAQwBEACAAZQBuACAAYwBvAGwAbwByAEYAYQByAGIALQBMAEMARABDAG8AbABvAHIAIABMAEMARABMAEMARAAgAEMAbwBsAG8AcgBpAGQAbwBLAG8AbABvAHIAIABMAEMARAOIA7MDxwPBA8kDvAO3ACADvwO4A8wDvQO3ACAATABDAEQARgDkAHIAZwAtAEwAQwBEAFIAZQBuAGsAbABpACAATABDAEQATABDAEQAIABhACAAQwBvAHIAZQBzMKsw6TD8AEwAQwBEAAB0ZXh0AAAAAENvcHlyaWdodCBBcHBsZSBJbmMuLCAyMDIxAABYWVogAAAAAAAA8xYAAQAAAAEWylhZWiAAAAAAAACC3QAAPVn///+8WFlaIAAAAAAAAExOAAC06AAACuxYWVogAAAAAAAAJ6sAAA2+AADIhWN1cnYAAAAAAAAEAAAAAAUACgAPABQAGQAeACMAKAAtADIANgA7AEAARQBKAE8AVABZAF4AYwBoAG0AcgB3AHwAgQCGAIsAkACVAJoAnwCjAKgArQCyALcAvADBAMYAywDQANUA2wDgAOUA6wDwAPYA+wEBAQcBDQETARkBHwElASsBMgE4AT4BRQFMAVIBWQFgAWcBbgF1AXwBgwGLAZIBmgGhAakBsQG5AcEByQHRAdkB4QHpAfIB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DQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0N/90ABAAw/9oADAMBAAIRAxEAPwD9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//Q/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooqCW5ghUtI4UDqSaAJ6K8x1/wCMXw38MuY9a8QWFtIP4GnUv/3yCTXES/tOfCaNto1SR/dLWd1/MIRXsYbh7NMRHnoYacl3UJNfgjycRn2WUJunXxMItdHOKf3XPoWivCrL9o74RXjrG2v29sznCi6DW+f+/gWvV9K8TaFrkIn0m+gu425DQyLIP0Nc+MyrG4TXFUZQ/wAUWvzR0YTNMFim1ha0Z2/lkn+TN6ikVlYZU5pa887gooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//R/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACopZkhUs5wBSTzJBGZHOAK+B/jx8ery+u7jwb4MuDDDETFfX8R+YsODDCR0x0dx0+6vOSPe4d4dxec4tYTCLzbe0V3f6LqeHxBxBhcnwrxWKfkkt5Psv1eyR618Uf2k9B8IyzaL4cRdY1aPKuqPtt7dsf8tZBnn/ZUFvbFfEni34o+O/G0rtrurTeQx4tLVjbW6jPA2odzeh3MQfQV54Ska8kIue5wMsfUnkkn6k+9Pxjg1/TnDnAuV5RB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aMy8ZlkU/Qxt/hX3fGNCNfIsXCW3JJ/+ArmX4pHxHCVaVHOsLOO/PFf+BPlf4Nn65wtviVvUVJVLTm3WcR/2RV2v45P62CiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9T9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr5g/aZs3uPBF86gkxIJQP+uZDf0r6fry74q6MuseGrq3ZdweJlI9iMV3ZXi/qmNo4r+SUZfc0zizLCfWsJVw388ZR+9NH5Ingmkqe5tpbK4ls5gRJA7RNnrlDjP49fxqCv7chOM4qcHdPY/jacJRk4zVmuh7t+zT/wAlm0T/AK4X3/pO9fqUK/LX9mr/AJLNon/XC+/9J3r9Su1fzb4xf8jqn/17X/pUz+h/Cb/kT1P+vj/9JgFFFZ+p6rpujWUuo6tdQ2drCC0k07iONQPVmIFflMISnJRgrtn6bKSinKTskaFUNS1TTtHs5dR1W5itLWEFpJp3EcagerNgV8keP/2s9FsPM0/4e2n9rzjK/b7ndFZL7ouPMl9RwFPrXxf4u8b+LPHd59u8XanLqLA7o4W+S2iPX93AvyLg9CdzD1r9Q4e8K8yx1quO/c0/Ne8/+3en/b1vRn5rn/idl2CvSwX76flpBf8Ab3X/ALdT82j7S8fftZ6Hp4ksPh7a/wBsT8r/AGhcborJfdB/rJvUYAQ/3hXxh4v8deLfHl59u8WalNqDK26OJvktoT1/dQL8i47Mdz/7VcmSSck5PrSV+55BwfleTxTwlP3/AOZ6y+/p8kkfi2ecV5nmzaxdT3P5VpH7uv8A282+wpJJya9Q+Ddo118Q9NCjIiEspPpgBR/6FXl1fTn7NXh6S98QXOrsvyR7YEPuPmb9SB+Fc3HuOjhMhxM5fajyr1l7v5Nv5HTwPgpYrPMPBLRS5n6R9780l8z9F9PXbZxD/ZFXajiXZGq+gqSv5DP6sCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9X9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigArP1S0W9s5IGGdykVoUHnigD8q/jd4Om8OeKJL+NCLe8b5iBwJB0P/Ah+oFeJ1+pnxf8Ah9beK9GnQx5YqcEdQexHvX5ma7ol94f1GXTb9Crxk7WIwHX1H9a/pTwv4shjcGsrxEv3tNWX96K2+cdn5Wfe388eJXC88Hi3mVCP7qo9f7snv8pbrzuux67+zV/yWfRP+uF9/wCk7V+nOqatpeiWEup6xdw2VpApeWe4kWKNFHOSzEAV+PngjxhqfgLxJb+KdGigmvbWKeOJbjcYgZ0MZZguC20HIGRk8ZHWo/FfjTxV45vhqPi3U59SlRt0SSHbbwn/AKZQLiNMdmwXx1Y1rxdwBXz3N4YmVRQpRgk+sm05PRbbNat/JmPC3HdHJMqlhoU3Oq5trpFK0Vq9+j0S9Wj7Q+IH7Wmj2Pmaf8O7P+1JuV/tC7DRWannmOPiWb1B+RD2Y18YeLPGvivx1e/b/Fmpzag6nMcbnbbxf9c4Vwi49cFvUmuXOe9JX1uQcIZXk8V9Tp+//M9Zff09Ekj5bPOKszzZ/wC11Pd/lWkfu6+rbYZooor6Y+dCiil96AJIIZbiaO3gXfJKwRF9WboP8fav0z+BPglfDfh63Dr+8K7mbHJZuSfxNfL3wN+GFzrGpRa5qMREa/6lWHQd2Puf0FfozpljHYWqQRjAUAV/NnilxZDMMTHLsJK9Om9X0cttPKKur9W30sz+h/DXheeAw7zDFK1SotF1Ud9fOTs7dkutzQooor8mP1EKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigD//1v38ooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigCKaJJkKOMg18yfFv4OWniO3e5to8SjLKyjBB9RX1BTJI0lUq4yDW+GxNXD1Y16EnGUXdNaNMxxGHpV6cqNaKlF6NPZn4z+IvCur+Gbp7fUIWCKSBIB8pHv6fyrm6/W3xd8NNH8RwuJIV3MD2r5E8X/s53VtI82k7kHJ2r0/Kv3ThzxepuKo5zCz/nitH6x6f9u39EfivEHhTNSdbKJ3X8knqvSXX529WfJlFeh6l8MPFmnMVa28wDuAR/jXPN4U8RIcNZSZ+lfpWG40yKvHmhi4fOSi/ulZn53iOD87oy5Z4WfyXN+Mbo52iustvBHie6YLHZOM+vH9K9D0D4HeJtVdTdAxqTyFHP5muXHcf5BhYtyxMZPtH3n+F197R04LgXPMTK0cO4rvL3fzs/uTPFI4pJpFiiUu7dFUZJ/Cvob4X/AAX1HXbuK/1eIrErBljI4+p9TX0P4D+AGmaRsnuowz8EkjJP419L6XotlpcKxW6BQoxwK/HOLPFHE5hCWFy6Lp03u/tNdtNIrva787aH63wv4a4bATWJx7VSotl9lP5/E/Wy8r6mX4Y8M2egWUdvAgXaAOBXWUUV+Tn6gFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/X/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAqKSGKUYdQalooAwrnw7pdz/rIFP4Vkv4G0Fzk26flXZ0UAcrB4O0WA5S3T8q3INNs7biKNR+FXqKAEAA4HFLRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/Q/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAP/2Q==" 
	-->
			</div>
			<div id="tabs-8">
				<iframe id="wooclap" aria-label="Wooclap tab" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="<?php echo $wooclapaddress; ?>" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
			</div>
			<div id="tabs-9">
				<iframe id="extratab1" title="Extra tab 1" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
			</div>
			<div id="tabs-10">
				<iframe id="extratab2" title="Extra tab 2" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
			</div>
			<div id="tabs-11">
				<iframe id="extratab3" title="Extra tab 3" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
			</div>
			<div id="tabs-12">
				<iframe id="extratab4" title="Extra tab 4" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
			</div>
		</div>
	</form>

	<div id="dialog-mail">
		<form>
			<fieldset>
				<legend style="display:none;">Send a mail form</legend>
				<label for="dialog_MailTo">Mail to : </label>
				<input type="text" id="dialog_MailTo" >
				<br>
				<label for="dialog_MailFrom" style="display:none;">Mail from : </label><input type="text" id="dialog_MailFrom" style="display:none;" readonly>
				<label for="dialog_MailSubject">Subject : </label>
				<input type="text" id="dialog_MailSubject" readonly>
				<br>
				Body : 
				<br>
				<div id="dialog_MailBody"> </div>
			</fieldset>
		</form>
	</div>
	<div id="dialog-invite" title="External guest token creation">
		<p id="dialog-invite-p"> </p>
	</div>
	<script src="qrcode.min.js"></script>
	<script src="js/teacher-post.js"></script>
	<!-- Microsoft Azure Cognitive Services -->
	<!--    <script src="https://aka.ms/csspeech/jsbrowserpackageraw"></script> -->
	<!-- <script src="libs/microsoft.cognitiveservices.speech.sdk.bundle.js"></script>-->
	<!-- <script src="js/teacher-ms.js"></script> -->
	<!--
	<script>
  var hebergementDomaine = 'https://automatictranslator.sciences.univ-nantes.fr'; 
  var hebergementFullPath = hebergementDomaine + '/AutomaticTranslator-test/libs/confortplus/serveur/';
</script>
<script src="libs/confortplus/serveur/js/toolbar.min.js"></script>
<script>
	accessibilitytoolbar_custom = {
		idLinkModeContainer : "confortplus",
		cssLinkModeClassName : "bw"
	};
</script>
-->
</body>

</html>
