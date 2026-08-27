<?php
require __DIR__ . '/NoCache.php';
require __DIR__ . '/vendor/autoload.php';

use App\Service\LanguageLoader;
use App\Service\RoomInfoService;
use App\Service\TranslatorStatsService;

// Désactiver l'affichage des erreurs en production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
// Activer le logging des erreurs
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/tmp/php_errors.log');
error_reporting(E_ALL);
// Empêche la mise en cache
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
    
include("config.php");
require_once __DIR__ . '/includes/chat-component.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
initCsrfProtection();
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
$pageScripts = ["https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js",
                "/js/qrcode.min.js"
];


// Tableau de fonctions à exécuter
$onloadFunctions = [
    //"setTimeout(InitVoices,1000)",
    //"setTimeout(InitVoices,2000)",
    //"setTimeout(Lancer,3003)",
    // Init(), Lancer(), InitVoices() and applyConfig() are now called from conference-legacy.js
];
$onloadScripts = implode(";\n    ", $onloadFunctions);

// Use services to load data
$uid = RoomInfoService::getUserId('anonymous');

if (isset($_GET["ROOMID"]))
  $classroomid = $_GET["ROOMID"];
else {
  $classroomid = md5(gmdate("Y-m-d\TH:i:s\Z"));
}
$roomToken = generateRoomToken($classroomid);

$appid = RoomInfoService::generateAppId();

if (isset($_GET["WOOCLAPKEY"])) {
  $wooclapaddress = "https://app.wooclap.com/events/".$_GET["WOOCLAPKEY"]."/0";
} else {
  $wooclapaddress = "https://app.wooclap.com/events/";
}

// Use services to load languages
$T_lang = LanguageLoader::load();

// Get IP address using service
$ip = RoomInfoService::getClientIp();

// Get DeepL statistics using service
$statsService = new TranslatorStatsService($proxy ?? '');
$statsResult = $statsService->getTranslatorStats($WhichTranslator ?? '', $DEEPLAPITOKEN ?? $DEEPLFREEAPITOKEN ?? '');
$Stats_DEEPL = $statsResult['stats'];
$percentage_DEEPL = $statsResult['percentage_used'];

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

<input type="hidden" id="classroomid" value="<?php echo e($classroomid); ?>">

<input type="hidden" id="classroomid" value="<?php echo e($classroomid); ?>">
<input type="hidden" id="appid" value="<?php echo e($appid); ?>">
<?php echo csrfField(); ?>


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
		<li>Students application : <a id="sharedlink" href="auditor.php?ROOMID=<?php echo e($classroomid);?>">auditor.php?ROOMID=<?php echo e($classroomid);?></a></li>
		<li>QR code :
			<div title="" id="sharedqr"></div>
		</li>
	</ul>
</div>

<h3 class="tabbed">Remote control (SUPPRIMER ?)</h3>
<div>
	<ul class="no-bullets">
		<li>Remote control : <a id="remotelink" href="remote.php?ROOMID=<?php echo e($classroomid);?>&LANG=fr,en&APPID=<?php echo e($appid);?>">remote.php?ROOMID=<?php echo e($classroomid);?>&LANG=fr,en&APPID=<?php echo e($appid);?></a></li>
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


<!-- Chat modules - common-func.js doit être chargé AVANT conference-legacy.js -->
<script src="/js/common-func.js"></script>
<script type="module" src="/js/conference-legacy.js"></script>
<script type="module" src="/js/lib/chat/alert.js"></script>
<script type="module" src="/js/lib/chat/network.js"></script>
<script type="module" src="/js/lib/chat/handlers.js"></script>
<script type="module" src="/js/lib/chat/display.js"></script>
<script type="module" src="/js/lib/chat/main.js"></script>
<?php include_once __DIR__ . '/includes/footer.php'; ?>