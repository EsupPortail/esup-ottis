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
require_once __DIR__ . '/Security/room_tokens.php';
require_once __DIR__ . '/includes/chat-component.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$_SESSION["from"]="teacher";
$language = $_GET['lang'] ?? 'fr'; // Langue par défaut
$translations = json_decode(file_get_contents("lang/{$language}.json"), true);
$pageTitle = "Omist - Conference";
$pageStyles = ["jquery/jquery-ui.min.css",
                "styles/auditor.css",
                "styles/conference.css",
                "styles/chat.css"
];
$pageScripts = ["/js/conference-def.js",
                "/js/common-func.js",
                "/js/chat-common.js",
                "/js/chat-alert.js",
                "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js",
                "/js/conference-func.js",
                "jquery/jquery.js",
                "jquery/jquery-ui.min.js",
                "/js/qrcode.min.js",
                "/js/conference-post.js"
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
	<ul style="list-style-type: none;">
		<li>
			<label for="classroomlanguage">Speaker language :</label>
			<select id="classroomlanguage" onchange="setLanguageSpeech(this.value);" class="jqueryselect"> </select>
		</li>
		<li style="display: none;">
			<label for="recognitionon">Speech recognition active ? </label>
			<input type="checkbox" id="recognitionon" >
		</li>
		<li>&nbsp;</li>
		<li style="display: none;">Translate subtitles ?
			<input type="checkbox" id="subtitleon" onclick="if (this.checked) _('spansubtitleon').innerHTML='Yes !'; else _('spansubtitleon').innerHTML='No.';" >&nbsp;<span class="yesno" id="spansubtitleon">No.</span></li>
		<li><label for="subtitlelanguage">Language for subtitles :</label>
			<select id="subtitlelanguage" onchange="setLanguageSubtitle(this.value);" class="jqueryselect"> </select>
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




<div id="partialtitre" style="color: white; opacity: 0.5; background-color: black; visibility: visible; font-size: 18px;"></div>


<h3 class="tabbed">For students</h3>
<div>
	<ul style="list-style-type: none;">
		<li><span id="connectedusers">connected users</span></li>
		<li>Students application : <a id="sharedlink" href="auditor.php?ROOMID=<?php echo $classroomid;?>">auditor.php?ROOMID=<?php echo $classroomid;?></a></li>
		<li>QR code :
			<div title="" id="sharedqr" style="margin-left: auto; margin-right: auto;"></div>
		</li>
	</ul>
</div>

<h3 class="tabbed">Remote control</h3>
<div>
	<ul style="list-style-type: none;">
		<li>Remote control : <a id="remotelink" href="remote.php?ROOMID=<?php echo $classroomid;?>&LANG=fr,en&APPID=<?php echo $appid;?>">remote.php?ROOMID=<?php echo $classroomid;?>&LANG=fr,en&APPID=<?php echo $appid;?></a></li>
		<li>QR code :
			<div title="" id="remoteqr" style="margin-left: auto; margin-right: auto;"></div>
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
           title="Upload PPTX, ODP, PDF, TXT, or HTML file"
           onchange="handleFileSelect(event)">

    <!-- Progress indicator -->
    <progress id="presentation-progress" value="0" max="100"
             style="width: 300px; margin-top: 5px;"
             aria-label="Upload and conversion progress"></progress>
    <span id="progress-status" style="margin-left: 10px;" aria-live="polite"></span>

    <!-- PDF.js viewer container (hidden by default) -->
    <div id="pdf-viewer" style="display: none; margin-top: 10px; position: relative;">
        <canvas id="pdf-canvas" style="border: 1px solid #ccc; max-width: 100%;"></canvas>
        <div style="display: flex; justify-content: center; gap: 20px; margin: 10px 0;">
            <button id="pdf-prev" onclick="prevPage()" disabled
                    aria-label="Previous page"
                    title="Go to previous page">← Previous</button>
            <span id="pdf-page-indicator" style="align-self: center;">Page 1 of 1</span>
            <button id="pdf-next" onclick="nextPage()" disabled
                    aria-label="Next page"
                    title="Go to next page">Next →</button>
            <input type="number" id="pdf-page-input" min="1" style="width: 60px;"
                   onchange="goToPage(this.value)" aria-label="Go to page number">
        </div>
        <!-- Fullscreen button (bottom right) -->
        <button id="pdf-fullscreen-btn" onclick="togglePdfFullscreen()"
                style="position: absolute; bottom: 10px; right: 10px;"
                aria-label="Toggle fullscreen mode"
                title="Press ESC to exit fullscreen">
            Fullscreen
        </button>
    </div>

    <!-- Text content viewer (fallback) -->
    <div id="text-content-viewer" style="display: none; border: 1px solid #ccc; padding: 15px; margin-top: 10px; max-height: 500px; overflow: auto;"></div>
</div>
<div id="notebookcompleteformatted" style="border: solid 1px; height: 600px; overflow: auto;"> </div>
</div>
<!-- ADMIN CONFIGURATION -->
<div id="admin-config" style="border: 2px solid #4CAF50; padding: 15px; margin: 20px 0; background-color: #f0fff0;">
    <h2 style="color: #2E7D32; text-align: center;">ADMIN - Configuration Traduction</h2>
    <label for="whichtranslator">Choix traducteur :</label>
    <select id="whichtranslator" class="jqueryselect" style="margin: 5px;">
        <option value="" selected>Default</option>
        <option value="DEEPLPRO">DeepL Pro API</option>
        <option value="DEEPLFREE">DeepL Free API</option>
        <option value="LIBRETRANSLATE">Libre Translate</option>
    </select>
</div>

<!-- Bouton micro flottant en haut à droite -->
<!-- Note: Le style de base est défini dans conference.min.css -->
<!-- Ce style inline améliore la visibilité et la taille -->
<div id="micon" onclick="Micro();"
     title="Activer/Désactiver le microphone. Alternatives : barre d'espace, bouton rouge de la télécommande smartphone ou bouton du pointeur de présentation."
     style="position: fixed; top: 15px; right: 15px; z-index: 1000; width: 56px; height: 56px; font-size: 36px; background-color: #d32f2f; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.4); color: white; cursor: pointer; display: flex !important; align-items: center; justify-content: center;">
    <i class='material-icons'>voice_over_off</i>
</div>

<!-- Amélioration du style pour l'état actif (vert) et animation -->
<style>
    /* Style amélioré pour micon quand actif (via classe .blink ajoutée par Micro()) */
    #micon .blink {
        color: white !important;
        animation: microPulse 1.5s infinite !important;
    }
    #micon:has(.blink) {
        background-color: #4caf50 !important;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.6) !important;
    }
    @keyframes microPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); }
    }
    
    /* Compatibilité pour les anciens navigateurs (sans :has) */
    #micon.blink-active {
        background-color: #4caf50 !important;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.6) !important;
    }
</style>

<script>
// Patch pour compatibilité des anciens navigateurs avec :has()
// Ajoute une classe sur micon quand l'icône est en mode enregistrement
document.addEventListener('DOMContentLoaded', function() {
    var micon = document.getElementById('micon');
    if (micon) {
        // Vérifier périodiquement si l'icône a changé
        setInterval(function() {
            var icon = micon.querySelector('.blink');
            if (icon) {
                micon.classList.add('blink-active');
            } else {
                micon.classList.remove('blink-active');
            }
        }, 500);
    }
});
</script>

<!-- Initialize chat alert after all scripts are loaded -->
<script>
// Initialize chat alert when DOM is ready - using native DOMContentLoaded to avoid jQuery dependency
 document.addEventListener('DOMContentLoaded', function() {
    // Wait to ensure all scripts are loaded
    setTimeout(function() {
        if (typeof initChatAlert === 'function') {
            // In conference, the sender is always "Intervenant" for chat messages
            // Use that as current user to filter out own messages
            var userName = 'Intervenant';
            initChatAlert(userName);
        } else {
            console.error('[Chat Alert] Conference: initChatAlert function not found!');
        }
    }, 500);
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>