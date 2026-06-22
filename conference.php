<?php require("./NoCache.php"); ?>
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Empêche la mise en cache
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
    
//include("Security/Authentification.php");
include("config.php");
include("Admin/Stats/config_database_stats.php");

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$_SESSION["from"]="teacher";
$language = $_GET['lang'] ?? 'fr'; // Langue par défaut
$translations = json_decode(file_get_contents("lang/{$language}.json"), true);
$pageTitle = "Omist - Conference";
$pageStyles = ["jquery/flat_black/jquery-ui.min.css",
                "jquery/flat_black/extras.css",
                //"styles/teacher.css",
                "styles/conference.css"
];
$pageScripts = ["/js/teacher-def.js",
                "/js/common-func.js",
                "/js/conf-func.js",
                "/js/qrcode.min.js",
                "/js/conf-post.js",
                "jquery/flat_black/external/jquery/jquery.js",
                "jquery/flat_black/jquery-ui.js"
];


// Tableau de fonctions à exécuter
$onloadFunctions = [
    "Init()",
    "InitVoices()",
    //"setTimeout(InitVoices,1000)",
    //"setTimeout(InitVoices,2000)",
    //"setTimeout(Lancer,3000)",
	"Lancer()",
    //"ready()",
    "updateLinks()",
    "Embedcode()",
    "applyConfig()",
];
$onloadScripts = implode(";\n    ", $onloadFunctions);

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="anonymous"; //$uid="e18XXX"; // en attendant de rétablir le CAS....
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

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

$T_lang = array();
$fd = fopen("supportedlanguages.csv", "r");
while ($line = fgets($fd, 4096)) {
    // Ignorer les lignes vides
    if (trim($line) === '') {
        continue;
    }
    $ligne = explode(";", $line);
    if (count($ligne) >= 3) {
        $code = trim($ligne[1]); 
        $T_lang[$code] = [
            "name_fr" => trim($ligne[2]),
            "name_vo" => trim($ligne[0]),
            "code" => trim($ligne[1]),
            "voice" => "-1"
        ];
    }
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

$res=array();

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

if (isset($_GET["myconfig"])) {
echo $currentconfig=$_GET["myconfig"];
} else {
echo $currentconfig='AANAYKCNYCNNYYYYYN';
}

//Variables
$jsVars = [
    "supportedlanguages" => $T_lang,
    "currentconfig" => $currentconfig
];

include_once __DIR__ . '/includes/header.php';
?>

<div id="conference-main" class="conference-grid">

<section id="teacher-session" class="conference-card" aria-labelledby="teacher-session-title">
    <h2 id="teacher-session-title">Session et code PIN</h2>
    <div id="PinCode">
    <label>PINCODE</label>
                <input type="text" id="pincode" style="width: 50px;" disabled> &nbsp;
                <div id="pincodediv" title="Pincode that can be communicated to the students. It allows to synchronize the student app with the presented lesson. Alternatives (QRcode, direct link) can be found in the share tab." onclick="/*if (sizepincodediv == 1) sizepincodediv=0.1; else sizepincodediv=1; this.style.transform='scaleX('+sizepincodediv+')';*/">
		<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'I-Site NExT" style="width: 109px; height: 50px;" ></div>
			<span title="" id="sharedpin"></span>
                <li>Pin code : <span title="" id="sharedpin2" style="font-size: 5em;"></span></li>
                <input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" >
    </div>
</section>

<section id="session-configuration" class="conference-card" aria-labelledby="session-configuration-title">
    <h2 id="session-configuration-title">Configuration de la session</h2>
    <div id="param">
    <label>PARAM</label>
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
    <select id="selectopacity" onchange="ChangeOpacity('titre', this.value); ChangeOpacity('partialtitre', this.value); ChangeOpacity('studentfeedback', this.value);ChangeOpacity('titreleft', this.value); ChangeOpacity('titrerightbis', this.value);" class="jqueryselect">
        <option value="0">0% opaque</option>
        <option value="0.25">25% opaque</option>
        <option value="0.5" selected>50% opaque</option>
        <option value="0.75">75% opaque</option>
        <option value="1">100% opaque</option>
    </select>
</div>
<label>VOICE</label>
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
                        
<h3 class="tabbed">Chat only</h3>
<div>
	<ul style="list-style-type: none;">
		<li>Chat only link : <a id="chatlink" href="chat.php?ROOMID=<?php echo $classroomid; ?>">chat.php?ROOMID=<?php echo $classroomid; ?></a></li>
		<li><label for="embedcontent">Chat only embed code :</label>
			<input type="text" id="embedcontent" value="" > </li>
	</ul>
</div>
</section>

<section id="notebook-section" class="conference-card" aria-labelledby="notebook-section-title">
    <h2 id="notebook-section-title">Notebook</h2>
    <div id="notebooks_container" style="display: flex;">
	<label>NOTEBOOK</label>
	<div id="VO_container" style="width: 50%;">
		<input type="button" id="buttondownloadspeechVO" value="Download the speech VO" class="button-3 tooltip" title="Download the notebook in txt format." onclick="DownloadSpeechLesson('lessonVO_'+_('classroomid').value+'.txt', _('notebookVO').innerHTML);;" >
		<br>
		<div id="notebookVO" style="border: solid 1px;" contenteditable="true" aria-label="Complete speech here." title="Complete speech here."></div>
	</div>
	<div id="translated_container" style="width: 50%;">
		<input type="button" id="buttondownloadspeech" value="Download the speech translated" class="button-3 tooltip" title="Download the notebook in txt format." onclick="DownloadSpeechLesson('lesson_'+_('classroomid').value+'.txt', _('notebook').innerHTML);;" >
		<br>
		<div id="notebook" style="border: solid 1px;" contenteditable="true" aria-label="Complete speech here." title="Complete speech here."> </div>
	</div>
</div>
</section>

<section id="language-section" class="conference-card" aria-labelledby="language-section-title">
    <h2 id="language-section-title">Configuration linguistique</h2>
    <div>
	<ul style="list-style-type: none;">
		<li>
			<label for="classroomlanguage">Speaker language :</label>
			<select id="classroomlanguage" onchange="setLanguageSpeech(this.value);" class="jqueryselect"> </select>
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
</section>

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

<div id="titreleft" onmouseover="_('soustitre').style.transform='translateY(0px)'; _('titreleft').style.transform='translateY(0px)';_('titrerightbis').style.transform='translateY(0px)'; _('titreright').style.transform='translateY(0px)'; _('titre').style.transform='translateY(0px)';" onclick="Backward();">&#9666;</div>
<div id="titrerightbis" onmouseover="_('soustitre').style.transform='translateY(0px)'; _('titreleft').style.transform='translateY(0px)'; _('titrerightbis').style.transform='translateY(0px)';_('titreright').style.transform='translateY(0px)'; _('titre').style.transform='translateY(0px)';" onclick="Forward();">&#9656;</div>
<div id="titreright"></div>
<div id="titre" onmouseover="_('soustitre').style.transform='translateY(0px)'; _('titreleft').style.transform='translateY(0px)'; _('titrerightbis').style.transform='translateY(0px)'; _('titreright').style.transform='translateY(0px)'; _('titre').style.transform='translateY(0px)';">
	<h1>Automatic translation for teaching purposes: Teacher</h1></div>
<div id="soustitre" onmouseover="this.style.transform='translateY(-64px)'; _('titreleft').style.transform='translateY(-64px)'; _('titrerightbis').style.transform='translateY(-64px)'; _('titreright').style.transform='translateY(-64px)'; _('titre').style.transform='translateY(-64px)';"></div>
<div id="partialtitre"></div>
<div id="studentfeedback"></div>
<div id="scrollbar"></div>

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
	<p>The following links to open source online tools can be useful for the extra tabs feature.</p>
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
		<li><b>Icons</b></li>
		<li><label for="iconon">Show icons</label>
			<input type="checkbox" checked id="iconon" onclick="showEachIcon(); if (this.checked) {{_('spaniconon').innerHTML='Yes !';} else {_('spaniconon').innerHTML='No.';}" > &nbsp;<span class="yesno" id="spaniconon">Yes !</span></li>
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
		<div id="icondiv">
		<div id="connexion"><i class='material-icons big'>signal_wifi_off</i></div>
		<div id="micon" onclick="Micro();" title="This button allows to switch on/off the microphone transcription. Alternatives for this button are the space key, the smartphone remote red button or the presentation pointer button."><i class='material-icons'>voice_over_off</i></div>
		<div id="questionicon" title="This icon indicates (blinks) when new messages arrived on the Tchat tab."><i class='material-icons'>thumb_up</i></div>
		<!-- <div id="FS" title="Toggle fullscreen view" onclick="GoFS();" title="Toggle fullscreen mode"><img alt="Passage en mode plein écran" id='FSimage' src="data:image/png;base64,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" style="width:40px; height: 40px"></div> -->
		<div id="FS" title="Toggle fullscreen view" onclick="GoFS();"><i class='material-icons' style="font-size: 41px;" >fullscreen</i></div>
</div>
	
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
		<li title="Do not change unless you know what you are doing ! This functionnality imply a huge increase of the translation cost !"><label for="directMicrosoftTranslation">Enable the direct translation functionnality ?</label>
			<input type="checkbox" id="directMicrosoftTranslation" onclick="if (this.checked) _('spanmicrosoftdirecttranscritionon').innerHTML='Yes !'; else _('spanmicrosoftdirecttranscritionon').innerHTML='No.';" >&nbsp;<span class="yesno" id="spanmicrosoftdirecttranscritionon">No.</span></li>
		<li title="Do not change unless you know what you are doing !"><label for="directMicrosoftTranslationBoth">Show both the direct transcription and its translation ?</label>
			<input type="checkbox" id="directMicrosoftTranslationBoth" onclick="if (this.checked) _('spanmicrosoftdirecttranscritionBothon').innerHTML='Yes !'; else _('spanmicrosoftdirecttranscritionBothon').innerHTML='No.';" >&nbsp;<span class="yesno" id="spanmicrosoftdirecttranscritionBothon">No.</span></li>
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

<h3 class="tabbed">For students</h3>
<div>
	<ul style="list-style-type: none;">
		<li>Students application : <a id="sharedlink" href="auditeur.php?ROOMID=<?php echo $classroomid;?>">auditeur.php?ROOMID=<?php echo $classroomid;?></a></li>
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
			<?php /*
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
			*/?>
		</li>
	</ul>
</div>

<h3 class="tabbed">Subtitles only</h3>
<div>
	<ul style="list-style-type: none;">
		<li>Subtitles only link : <a id="sublink" target="popup" href="" onclick="SubWindow()" aria-label="Open a window with subtitles only"></a></li>
	</ul>
</div>
<div>
	<img src="images/blank.png" id="img_slides" style="cursor: url(images/pointer.png), zoom-in;" alt="Emplacement des slides diffusés"> 
</div>

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
	</select>
	<span style="display:none">
		<label for="tobesaid" title="Portion of text inserted between each slides (eq. Next slide...)">Slide transition : </label>
		<input type="text" id="tobesaid" value="">
	</span>
	<br>
	<progress title="Progress bar" id="progressBar" value="0" max="100" style="width:300px;"></progress>
</div>
<div id="notebookcompleteformatted" style="border: solid 1px; height: 600px; overflow: auto;"> </div>
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
</svg>
</div>
					<p id="spinnertxt">No presentation yet...<br>Please go to the complete lesson tab to import it.</p>
</div>

<div class="settings-panel">
    <!-- Panneau 1 -->
    <details class="panel">
        <summary class="panel-title">
            Paramètres généraux
        </summary>
        <div class="panel-content">
            <div class="form-group">
                <label for="theme">Thème :</label>
                <select id="theme" name="theme" class="form-control">
                    <option value="light">Clair</option>
                    <option value="dark">Sombre</option>
                </select>
            </div>
            <div class="form-group">
                <input type="checkbox" id="notifications" name="notifications" checked>
                <label for="notifications">Activer les notifications</label>
            </div>
            
        </div>
    </details>

    <!-- Panneau 2 -->
    <details class="panel">
        <summary class="panel-title">
            Options avancées
        </summary>
        <div class="panel-content">
            <div class="form-group">
                <label for="language">Langue :</label>
                <select id="language" name="language" class="form-control">
                    <option value="fr">Français</option>
                    <option value="en">Anglais</option>
                </select>
            </div>
            <div class="form-group">
                <input type="checkbox" id="backup" name="backup">
                <label for="backup">Sauvegardes automatiques</label>
            </div>
        </div>
    </details>

    <!-- Ajoute d'autres panneaux au besoin -->
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>