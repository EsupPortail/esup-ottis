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
require_once __DIR__ . '/Security/room_tokens.php';
require_once __DIR__ . '/includes/chat-component.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$_SESSION["from"]="teacher";
$language = $_GET['lang'] ?? 'fr'; // Langue par défaut
$translations = json_decode(file_get_contents("lang/{$language}.json"), true);
$pageTitle = "Omist - Conference";
$pageStyles = [
                "styles/auditor.css",
                "styles/conference.css",
                "https://fonts.googleapis.com/icon?family=Material+Icons",
                "styles/chat.css"
];
$pageScripts = ["/js/conference-def.js",
                "/js/common-func.js",
                "/js/chat-common.js",
                "/js/chat-alert.js",
                "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js",
                "/js/pdf-init.js",
                "/js/utils/dom.js",
                "/js/utils/dialog.js",
                "/js/utils/draggable.js",
                "/js/conference-func.js",
                "/js/qrcode.min.js",
                "/js/conference-post.js",
                "/js/conference-events.js"
];


// Tableau de fonctions à exécuter
$onloadFunctions = [
    "Init()",
    "InitVoices()",
    //"setTimeout(InitVoices,1000)",
    //"setTimeout(InitVoices,2000)",
    //"setTimeout(Lancer,3003)",
	"Lancer()",
    //"ready()",
    "setTimeout(applyConfig, 100)",
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
$roomToken = generateRoomToken($classroomid);

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
    $currentconfig = $_GET["myconfig"];
} else {
    $currentconfig = 'AAYAYKCNYCNYNYNNN';
}

//Variables
$jsVars = [
    "supportedlanguages" => $T_lang,
    "currentconfig" => $currentconfig,
    "roomToken" => $roomToken
];

include_once __DIR__ . '/includes/header.php';
?>

<input type="hidden" id="classroomid" value="<?php echo $classroomid; ?>">
<input type="hidden" id="appid" value="<?php echo $appid; ?>">


<div id="conference-main" class="conference-grid">
          
<section id="language-section" class="conference-card" aria-labelledby="language-section-title">
    <h2 id="language-section-title">Configuration linguistique</h2>
    <div>
	<ul class="no-bullets">
		<li>
			<label for="classroomlanguage">Speaker language :</label>
			<select id="classroomlanguage" class="select"> </select>
		</li>
		<li class="hidden">
			<label for="recognitionon">Speech recognition active ? </label>
			<input type="checkbox" id="recognitionon" >
		</li>
		<li>&nbsp;</li>
		<li class="hidden">Translate subtitles ?
			<input type="checkbox" id="subtitleon" >&nbsp;<span class="yesno" id="spansubtitleon">No.</span></li>
		<li><label for="subtitlelanguage">Language for subtitles :</label>
			<select id="subtitlelanguage" class="select"> </select>
		</li>
		<li>
			<label for="usetextband">Use partialtitre instead of textBand:</label>
			<input type="checkbox" id="usetextband">
		</li>
	</ul>
</div>
</section>

<section id="chat-section" class="conference-card" aria-labelledby="chat-section-title">
    <h2 id="chat-section-title">Classroom Chat</h2>
    <div>
        <?php
        renderChatComponent([
            'roomId' => $classroomid,
            'roomToken' => $roomToken,
            'userName' => $uid,
            'isIntervenant' => true,
            'placeholder' => 'Type your message here...',
            'labelText' => 'Classroom Chat'
        ]);
        ?>
    </div>
</section>

<section id="notebook-section" class="conference-card" aria-labelledby="notebook-section-title">
    <h2 id="notebook-section-title">Notebook</h2>
    <div id="notebooks_container">
	<label>NOTEBOOK</label>
	<div id="VO_container">
		<input type="button" id="buttondownloadspeechVO" value="Download the speech VO" class="button-3 tooltip" title="Download the notebook in txt format.">
		<br>
		<div id="notebookVO" contenteditable="true" aria-label="Complete speech here." title="Complete speech here."></div>
	</div>
	<div id="translated_container">
		<input type="button" id="buttondownloadspeech" value="Download the speech translated" class="button-3 tooltip" title="Download the notebook in txt format.">
		<br>
		<div id="notebook" contenteditable="true" aria-label="Complete speech here." title="Complete speech here."> </div>
	</div>
</div>
</section>




<div id="partialtitre" class="hidden" style="display: none;"></div>

<h3 class="tabbed">For students</h3>
<div>
	<ul class="no-bullets">
		<li><span id="connectedusers">connected users</span></li>
		<li>Students application : <a id="sharedlink" href="auditor.php?ROOMID=<?php echo $classroomid;?>">auditor.php?ROOMID=<?php echo $classroomid;?></a></li>
		<li>QR code :
			<div title="" id="sharedqr"></div>
		</li>
	</ul>
</div>

<h3 class="tabbed">Remote control (SUPPRIMER ?)</h3>
<div>
	<ul class="no-bullets">
		<li>Remote control : <a id="remotelink" href="remote.php?ROOMID=<?php echo $classroomid;?>&LANG=fr,en&APPID=<?php echo $appid;?>">remote.php?ROOMID=<?php echo $classroomid;?>&LANG=fr,en&APPID=<?php echo $appid;?></a></li>
		<li>QR code :
			<div title="" id="remoteqr"></div>
		</li>
	</ul>
</div>

<!-- NEW PRESENTATION UPLOAD INTERFACE -->
<div>
    <!-- File input with auto-upload on change -->
    <label for="presentation_file">Upload Presentation:</label>
    <input type="file" id="presentation_file" name="presentation_file"
           accept=".pptx,.odp,.pdf,.txt,.html,.htm"
           aria-label="Upload presentation file"
           title="Upload PPTX, ODP, PDF, TXT, or HTML file">

    <!-- Progress indicator -->
    <progress id="presentation-progress" value="0" max="100"
             aria-label="Upload and conversion progress"></progress>
    <span id="progress-status" aria-live="polite"></span>

    <!-- PDF.js viewer container -->
    <div id="pdf-viewer">
        <canvas id="pdf-canvas"></canvas>
        <!-- PDF control panel -->
        <div id="pdf-control">
            <div>
                <button id="pdf-prev" disabled
                        aria-label="Previous page"
                        title="Go to previous page">← Previous</button>
                <span id="pdf-page-indicator">Page 1 of 1</span>
                <button id="pdf-next" disabled
                        aria-label="Next page"
                        title="Go to next page">Next →</button>
                <input type="number" id="pdf-page-input" min="1"
                       aria-label="Go to page number">
                <button id="pdf-fullscreen-btn"
                        aria-label="Toggle fullscreen mode"
                        title="Press ESC to exit fullscreen">
                    Fullscreen
                </button>
            </div>
        <!-- textBand -->
        <div id="textBand" class="text-band-style" style="display: block; visibility: visible;"></div>
    </div>

    <!-- Text content viewer (fallback) -->
    <div id="text-content-viewer"></div>


</div>


<div id="textfixe" class="text-band-style" style="display: block;"></div>
<!-- ADMIN CONFIGURATION -->
<div id="admin-config">
    <h2>ADMIN - Configuration Traduction</h2>
    <label for="whichtranslator">Choix traducteur :</label>
    <select id="whichtranslator" class="select">
        <option value="" selected>Default</option>
        <option value="DEEPLPRO">DeepL Pro API</option>
        <option value="DEEPLFREE">DeepL Free API</option>
        <option value="LIBRETRANSLATE">Libre Translate</option>
    </select>
</div>

<!-- Bouton micro flottant en haut à droite -->
<!-- Note: Le style de base est défini dans conference.min.css -->
<!-- Ce style inline améliore la visibilité et la taille -->
<div id="micon" title="Activer/Désactiver le microphone. Alternatives : barre d'espace, bouton rouge de la télécommande smartphone ou bouton du pointeur de présentation.">
    <i class='material-icons'>voice_over_off</i>
</div>

<!-- -->


<?php include_once __DIR__ . '/includes/footer.php'; ?>