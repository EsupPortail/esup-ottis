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
<body onload="Init(); InitVoices(); setTimeout(InitVoices,1000); setTimeout(InitVoices,2000); /*setTimeout(function () {StartSpeechRecognition(Traduction); },3000); */ setTimeout(Lancer,3000); ready(); updateLinks(); Embedcode(); EcrireNBonly(''); applyConfig();">
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
		<div id="tabs">
			<ul id="bar">
				<li title="Settings tab (language configuration, appearance,...)"><a href="#tabs-0" id="ttab-0" onclick="inSlideTab=false;"><i class="fa fa-cog"></i><span class="hidesmallscreens">&nbsp;Settings</span></a></li>
				<li title="Multilingual Tchat tab"><a href="#tabs-1" id="ttab-1" onclick="timenew_question=0; inSlideTab=false;"><i class="fa fa-comments"></i><span class="hidesmallscreens">&nbsp;Tchat</span></a></li>
				<li title="Speech transcription tab. From here, it is possible to export a transcription of the speech."><a href="#tabs-2" id="ttab-2" onclick="inSlideTab=false;"><i class="fa fa-cc"></i><!--<i class="fa fa-arrow-right"></i>-->
                 <span class="hidesmallscreens">&nbsp;Speech</span></a></a></li>
				<li title="Upload your PPTX or HTML complete lesson from this tab"><a href="#tabs-5" id="ttab-3" onclick="inSlideTab=false;"><!--<i class="fa fa-arrow-right"></i>--><i class="fa fa-upload"></i>
                 <span class="hidesmallscreens">&nbsp;Complete lesson</span></a></li>
				<li title="The slides are shown in this tab" id="titleSlides"><a href="#tabs-7" id="ttab-4" onclick="inSlideTab=true; this.blur(); _('img_slides').focus(); _('img_slides').click(); this.blur();"><i class="fa fa-picture-o"></i><span class="hidesmallscreens">&nbsp;Slides</span></a></li>
				<li title="Jitsi visioconference tab" id="titlevisio"><a href="#tabs-6" id="ttab-5" onclick="if (!visioactive) _('visio').src='https://meet.jit.si/Translator_'+_('classroomid').value; visioactive=true; inSlideTab=false;">Visio</a></li>
				<li title="Live translation tab" id="titlelive"><a href="#tabs-3" id="ttab-6" onclick="inSlideTab=false;">Live translation</a></li>
				<li title="This tab contains links allowing to share the lesson with students or other teachers. A link for using a smartphone remote is also available." id="titleshare"><a href="#tabs-4" id="ttab-7" onclick="inSlideTab=false;"><i class="fa fa-qrcode"></i><span class="hidesmallscreens">&nbsp;Share</span></a></li>
				<li title="A tab allowing to interact with WOOCLAP(c)" id="titlewooclap"><a href="#tabs-8" id="ttab-8" onclick="inSlideTab=false;"><img src="images/wooclap-ico.png" width="18px" style="vertical-align: middle;"/><span class="hidesmallscreens">&nbsp;WOOCLAP</span></a></li>
				<li title="All the slide manipulation tools can be found here" id="titletools"><a href="#tabs-20" id="ttab-9" onclick="inSlideTab=false;"><i class="fa fa-wrench"></i><span class="hidesmallscreens">&nbsp;Slide tools</span></a></li>
				<li title="Extra tab for interacting with external tools" id="titleextratab1"><a href="#tabs-9" onclick="inSlideTab=false;"><i class="fa fa-desktop"></i><span class="hidesmallscreens">&nbsp;Tab 1</span></a></li>
				<li title="Extra tab for interacting with external tools" id="titleextratab2"><a href="#tabs-10" onclick="inSlideTab=false;"><i class="fa fa-desktop"></i><span class="hidesmallscreens">&nbsp;Tab 2</span></a></li>
				<li title="Extra tab for interacting with external tools" id="titleextratab3"><a href="#tabs-11" onclick="inSlideTab=false;"><i class="fa fa-desktop"></i><span class="hidesmallscreens">&nbsp;Tab 3</span></a></li>
				<li title="Extra tab for interacting with external tools" id="titleextratab4"><a href="#tabs-12" onclick="inSlideTab=false;"><i class="fa fa-desktop"></i><span class="hidesmallscreens">&nbsp;Tab 4</span></a></li>
			</ul>
			<div id="tabs-0">
				<h3>Language configuration</h3>
				<div>
					<ul style="list-style-type: none;">
						<li>
							<label for="classroomlanguage">Speaker language :</label>
							<select id="classroomlanguage" onchange="setLanguageInput(this.value); Micro(); Micro();" class="jqueryselect"> </select>
						</li>
						<li style="display: none;">
							<label for="recognitionon">Speech recognition active ? </label>
							<input type="checkbox" id="recognitionon" />
						</li>
						<li <?php if (true) echo "style=\"display:none;\""; ?> Pin code :
							<input type="text" id="pincode" style="width: 50px;" disabled/> &nbsp;
							<input type="button" value="Change pin" onclick="_('pincode').value=prompt('Enter a PIN code'); Synchronize_save();" />
							<input type="button" value="Synchronize" onclick="Synchronize(prompt('Enter a PIN code'));" /> </li>
						<li>&nbsp;</li>
						<li style="display: none;">Translate subtitles ?
							<input type="checkbox" id="subtitleon" onclick="if (this.checked) _('spansubtitleon').innerHTML='Yes !'; else _('spansubtitleon').innerHTML='No.';" />&nbsp;<span class="yesno" id="spansubtitleon">No.</span></li>
						<li>Language for subtitles :
							<select id="subtitlelanguage" onchange="setLanguageSubtitle(this.value);" class="jqueryselect"> </select>
						</li>
					</ul>
				</div>
				<h3>Subtitles configuration and Icons</h3>
				<div>
					<ul style="list-style-type: none;">
						<li><b>Subtitles appearance</b></li>
						<li>Show subtitles bar ?
							<input type="checkbox" checked id="subtitlebaron" onclick="if (this.checked) {_('spansubtitlebaron').innerHTML='Yes !'; _('titre').style.visibility='visible'; _('titreright').style.visibility='visible'; } else {_('spansubtitlebaron').innerHTML='No.'; _('titre').style.visibility='hidden'; _('titreleft').style.visibility='hidden'; _('titrerightbis').style.visibility='hidden'; _('titreright').style.visibility='hidden'; _('arrowson').checked=false;}" />&nbsp;<span class="yesno" id="spansubtitlebaron">Yes !</span>&nbsp;Color :
							<select id="ColorTitre" onchange="ChangeColor('titre',this.value); ChangeColor('titreleft',this.value);ChangeColor('titrerightbis',this.value);" class="jqueryselect">
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
						<li>Show arrows around subtitles ?
							<input type="checkbox" checked id="arrowson" onclick="if (this.checked) {_('spanarrowson').innerHTML='Yes !';_('titreleft').style.visibility='visible'; _('titrerightbis').style.visibility='visible'; _('titreright').style.visibility='visible'; } else {_('spanarrowson').innerHTML='No.'; _('titreleft').style.visibility='hidden'; _('titrerightbis').style.visibility='hidden';}" />&nbsp;<span class="yesno" id="spanarrowson">Yes !</span></li>
						<li>Show instant transcription ?
							<input type="checkbox" id="subtitlepartialon" onclick="if (this.checked) {_('spansubtitlepartialon').innerHTML='Yes !'; _('partialtitre').style.visibility='visible'; } else {_('spansubtitlepartialon').innerHTML='No.'; _('partialtitre').style.visibility='hidden'; }" />&nbsp;<span class="yesno" id="spansubtitlepartialon">No.</span>&nbsp; Color :
							<select id="ColorPartialTitre" onchange="ChangeColor('partialtitre',this.value);" class="jqueryselect">
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
						<li style="display:none;">Show student feedback ?
							<input type="checkbox" id="subtitlestudenton" onclick="if (this.checked) {_('spansubtitlestudenton').innerHTML='Yes !'; _('studentfeedback').style.visibility='visible'; } else {_('spansubtitlestudenton').innerHTML='No.'; _('studentfeedback').style.visibility='hidden'; }" />&nbsp;<span class="yesno" id="spansubtitlestudenton">No.</span>&nbsp; Color :
							<select id="Colorstudentfeedback" onchange="ChangeColor('studentfeedback',this.value);" class="jqueryselect" class="jqueryselect">
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
						<li>Subtitles font size :
							<select id="selectfontsize" onchange="_('titre').style.fontSize = this.value; _('partialtitre').style.fontSize = this.value; _('studentfeedback').style.fontSize = this.value;" class="jqueryselect">
								<option value="12px">Small</option>
								<option value="18px" selected>Normal</option>
								<option value="24px">Big</option>
								<option value="32px">Huge</option>
								<option value="48px">Enormous</option>
							</select>
						</li>
						<li>Subtitles opacity :
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
						<li>Show icons
							<input type="checkbox" checked id="iconon" onclick="showEachIcon(); if (this.checked) {
    {_('spaniconon').innerHTML='Yes !';} else {_('spaniconon').innerHTML='No.';}" /> &nbsp;<span class="yesno" id="spaniconon">Yes !</span></li>
						<li id="eachicon" style="display:none">Pin code ?
							<input title="Hide or show the pincode information. If 'Yes !' or checked, the pincode is visible." type="checkbox" id="pincodeon" onclick="if (this.checked) {_('spanpincodeon').innerHTML='Yes !'; _('pincodediv').style.visibility='visible';} else {_('spanpincodeon').innerHTML='No.';_('pincodediv').style.visibility='hidden';}" />&nbsp;<span class="yesno" id="spanpincodeon">No.</span> &nbsp; Micro ?
							<input title="Hide or show the microphone icon. If 'Yes !' or checked, the microphone icon is visible." type="checkbox" id="microon" onclick="if (this.checked) {_('spanmicroon').innerHTML='Yes !'; show('micon');} else {_('spanmicroon').innerHTML='No.';hide('micon');}" />&nbsp;<span class="yesno" id="spanmicroon">No.</span> &nbsp; Question ?
							<input title="Hide or show the question icon. If 'Yes !' or checked, the question icon is visible." type="checkbox" id="questionon" onclick="if (this.checked) {_('spanquestionon').innerHTML='Yes !'; show('questionicon');} else {_('spanquestionon').innerHTML='No.';hide('questionicon');}" />&nbsp;<span class="yesno" class="yesno" id="spanquestionon">No.</span> &nbsp; Full screen ?
							<input title="Hide or show the fullscreen icon. If 'Yes !' or checked, the fullscreen icon is visible." type="checkbox" id="fson" onclick="if (this.checked) {_('spanfson').innerHTML='Yes !'; show('FS');} else {_('spanfson').innerHTML='No.';hide('FS');}" />&nbsp;<span class="yesno" id="spanfson">No.</span></li>
						<li>&nbsp;</li>
						<li><b>Pin code appearance</b></li>
						<li>Pin code size
							<select id="selecticonsize" onchange="ChangeSizeIcons(this.value);" class="jqueryselect">
								<option value=30>Small</option>
								<option value=50 selected>Normal</option>
								<option value=80>Big</option>
							</select>
						</li>
					</ul>
				</div>
				<h3>Tabs configuration and external tools</h3>
				<div>
					<ul style="list-style-type: none;">
						<li><b>Extra tabs</b></li>
						<li title="Hide or show the WOOCLAP tab. If 'Yes !' or checked, the WOOCLAP tab is visible in the toolbar.">Embed WOOCLAP ?
							<input type="checkbox" id="embedwooclapon" onclick="if (this.checked) {_('spanembedwooclapon').innerHTML='Yes !'; show('titlewooclap');} else {_('spanembedwooclapon').innerHTML='No.';  hide('titlewooclap');}" />&nbsp;<span class="yesno" id="spanembedwooclapon">No.</span>
							<button style="display:none;" onclick="window.open('https://app.wooclap.com/auth/login?lang=en&redirectTo=/home')">Login to wooclap first</button>
							<button style="display:none;" onclick="_('wooclap').src='https://app.wooclap.com';">Update the wooclap tab</button>
						</li>
						<!-- <li>Show Live translation tab ?  <input type="checkbox" id="embedliveon" onclick="if (this.checked) {_('spanembedliveon').innerHTML='Yes !'; show('titlelive');} else {_('spanembedliveon').innerHTML='No.';  hide('titlelive');}"/>&nbsp;<span class="yesno" id="spanembedliveon">No.</span></li> -->
						<!--  <li>Show Visio tab ?  <input type="checkbox" id="embedvisioon" onclick="if (this.checked) {_('spanembedvisioon').innerHTML='Yes !'; show('titlevisio');} else {_('spanembedvisioon').innerHTML='No.';  hide('titlevisio');}"/>&nbsp;<span class="yesno" id="spanembedvisioon">No.</span></li> -->
						<li title="Hide or show the share tab. If 'Yes !' or checked, the share tab is visible in the toolbar.">Show Share tab ?
							<input type="checkbox" checked id="embedshareon" onclick="if (this.checked) {_('spanembedshareon').innerHTML='Yes !'; show('titleshare');} else {_('spanembedshareon').innerHTML='No.';  hide('titleshare');}" />&nbsp;<span class="yesno" id="spanembedshareon">Yes !</span></li>
						<li>Extra tab 1 URL :
							<input type="text" id="extraurl1" value="" placeholder="Copy your application url here" />&nbsp; Show ?
							<input type="checkbox" id="embedET1" onclick="showET(this,1);" />&nbsp;<span class="yesno" id="spanembedET1">No.</span>&nbsp;
							<button onclick="saveTextQ('[extratab1|'+_('extraurl1').value+']');">Send to students</button>
						</li>
						<li>Extra tab 2 URL :
							<input type="text" id="extraurl2" value="" placeholder="Copy your application url here" />&nbsp; Show ?
							<input type="checkbox" id="embedET2" onclick="showET(this,2);" />&nbsp;<span class="yesno" id="spanembedET2">No.</span>&nbsp;
							<button onclick="saveTextQ('[extratab2|'+_('extraurl2').value+']');">Send to students</button>
						</li>
						<li>Extra tab 3 URL :
							<input type="text" id="extraurl3" value="" placeholder="Copy your application url here" />&nbsp; Show ?
							<input type="checkbox" id="embedET3" onclick="showET(this,3);" />&nbsp;<span class="yesno" id="spanembedET3">No.</span>&nbsp;
							<button onclick="saveTextQ('[extratab3|'+_('extraurl3').value+']');">Send to students</button>
						</li>
						<li>Extra tab 4 URL :
							<input type="text" id="extraurl4" value="" placeholder="Copy your application url here" />&nbsp; Show ?
							<input type="checkbox" id="embedET4" onclick="showET(this,4);" />&nbsp;<span class="yesno" id="spanembedET4">No.</span>&nbsp;
							<button onclick="saveTextQ('[extratab4|'+_('extraurl4').value+']');">Send to students</button>
						</li>
						<li></li>
					</ul>
					<p>The following links to open source online tools can be useful for the extra tabs feature.
						<dl>
							<dd>Tableau noir blackboard :
								<a id="link1" class="tooltip" onclick="opentab(this)" src="" title="Click to add to extra tabs" href="javascript:void(0)"></a>
							</dd>
							<dd>WBO whiteboard :
								<a id="link2" class="tooltip" onclick="opentab(this)" src="" title="Click to add to extra tabs" href="javascript:void(0)"></a>
							</dd>
							<dd>Jitsi visioconference :
								<a id="link3" class="tooltip" onclick="opentab(this)" src="" title="Click to add to extra tabs" href="javascript:void(0)"></a>
							</dd>
							<dd>Symbolic computations (Xcas) : <a id="link4" class="tooltip" onclick="opentab(this)" src="https://algoscript.info/xcas" title="Click to add to extra tabs" href="javascript:void(0)">https://algoscript.info/xcas</a></dd>
							<dd>PPTX generic viewer : <a id="link5" class="tooltip" onclick="opentab(this)" title="Click to add to extra tabs" href="javascript:void(0)" src="https://products.groupdocs.app/fr/viewer/pptx">https://products.groupdocs.app/fr/viewer/pptx</a></dd>
							<dd>Universal document viewer : <a id="link5" class="tooltip" onclick="opentab(this)" title="Click to add to extra tabs" href="javascript:void(0)" src="https://products.groupdocs.app/fr/viewer/total">https://products.groupdocs.app/fr/viewer/total</a></dd>
							<dd>3D viewer : <a id="link7" class="tooltip" onclick="opentab(this)" title="Click to add to extra tabs" href="javascript:void(0)" src="https://3dviewer.net/">https://3dviewer.net/</a></dd>
							<dd>Nantes University Zoom session [Experimental]: <a id="link5" class="tooltip" onclick="var lnk=prompt('Copy your Zoom link here'); if (lnk.indexOf('univ-nantes-fr') != false) {this.innerHTML=lnk.replace('https://univ-nantes-fr.zoom.us/j/','https://univ-nantes-fr.zoom.us/wc/join/'); opentab(this);}" title="Click to add your zoom link session" href="javascript:void(0)" src="">Enter your zoom link</a></dd>
							<dd>Youtube video [Experimental]: <a id="link6" class="tooltip" onclick="var lnk=prompt('Copy your Youtube ID here'); {this.innerHTML='https://www.youtube.com/embed/'+lnk+'?origin=https://misc-sciences.univ-nantes.fr'; opentab(this);}" title="Click to add your Youtube video" href="javascript:void(0)" src="">Enter your Youtube video ID</a></dd>
						</dl>
						<br/>
						<br/> </div>
				<h3>Extra functionalities</h3>
				<div>
					<ul style="list-style-type: none;">
						<li>&nbsp;</li>
						<li <?php if (true) echo "style=\"display:none;\""; ?> title="Do not change unless you know what you are doing !">
							<label for="classroomid">Classroom identifier :</label>
							<input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" />
						</li>
						<li style="display:none;" title="Do not change unless you know what you are doing !">
							<label for="appid">Application identifier :</label>
							<input type="text" name="appid" id="appid" value="<?php echo $appid; ?>" />
						</li>
						<li title="Do not change unless you know what you are doing !">External transcription service ?
							<input type="checkbox" id="externaltranscritionon" onclick="if (this.checked) _('spanexternaltranscritionon').innerHTML='Yes !'; else _('spanexternaltranscritionon').innerHTML='No.'; builtin_externaltranscritionon=this.checked" />&nbsp;<span class="yesno" id="spanexternaltranscritionon">No.</span></li>
						<li <?php if (! $Microsofton) echo "style=\"display:none\";" ?> title="Do not change unless you know what you are doing !">Microsoft transcription service ?
							<input type="checkbox" id="microsofttranscritionon" onclick="InitializeMicrosoft(); if (this.checked) _('spanmicrosofttranscritionon').innerHTML='Yes !'; else _('spanmicrosofttranscritionon').innerHTML='No.'; builtin_microsofttranscritionon=this.checked" />&nbsp;<span class="yesno" id="spanmicrosofttranscritionon">No.</span></li>
						<li <?php if (! $Microsofton) echo "style=\"display:none\";" ?> title="Do not change unless you know what you are doing !">Microsoft live translation ?
							<input type="checkbox" id="directMicrosoftTranslation" onclick="if (this.checked) _('spanmicrosoftdirecttranscritionon').innerHTML='Yes !'; else _('spanmicrosoftdirecttranscritionon').innerHTML='No.';" />&nbsp;<span class="yesno" id="spanmicrosoftdirecttranscritionon">No.</span></li>
						<li <?php if (! $Microsofton) echo "style=\"display:none\";" ?> title="Do not change unless you know what you are doing !">Boosted words or expressions (one per line)
							<br/>
							<textarea id="BoostedWords" rows="3" cols="20"></textarea>
						</li>
						<li>Remove verbal tics (one per line in lowercase)
							<br/>
							<textarea id="verbaltics" rows="3" cols="20"></textarea>
							<li><span id="connectedusers"></span></li>
							<li></li>
              </ul>
              <br/>
					<br/>
					<p>The Slide Manipulation Toolkit is accessible <a href="<?php if ($external) echo " javascript:void(window.open( 'OMIST_Tools/indexExternal.php?ROOMID=".$classroomid."')) "; else echo "javascript:void(window.open( 'OMIST_Tools')) ";?>">here</a>.</p>

              </div>
              <h3>Configuration code</h3>
              <div>
              <ul>
              <li>Current configuration code :
							  <input type="text" id="myConfig" value="0" style="width: 250px;" disabled/>
						  </li>
							<li>Copy this link to always access the teachers application with this configuration :
								<a href="" id="LinkConfig"></a>
							</li>
					</ul>
				</div>
			</div>
			
			
	<div id="dialog-mail">
		<form>
			<fieldset>
				<label for="dialog_MailTo">Mail to : </label>
				<input type="text" id="dialog_MailTo" />
				<br/>
				<input type="text" id="dialog_MailFrom" style="display:none;" readonly/>
				<label for="dialog_MailSubject">Subject : </label>
				<input type="text" id="dialog_MailSubject" readonly/>
				<br/>
				<label for="dialog_MailBody">Body : </label>
				<br/>
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
	<script src="libs/microsoft.cognitiveservices.speech.sdk.bundle.js"></script>
	<script src="js/teacher-ms.js"></script>
</body>

</html>
