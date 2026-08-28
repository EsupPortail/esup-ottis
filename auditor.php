<?php
require __DIR__ . '/NoCache.php';
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Security/init.php';
require_once __DIR__ . '/includes/chat-component.php';

use App\Service\LanguageLoader;
use App\Service\RandomNameGenerator;

$classroomid = $_GET['ROOMID'] ?? '';

// Generer un token unique pour ce ROOMID
$roomToken = generateRoomToken($classroomid);

// Use services to load data
$T_lang = LanguageLoader::load();
$randomname = RandomNameGenerator::generateName();

$theme = 'flat';
if (isset($_GET['theme'])) {
    $theme = $_GET['theme'];
}

// Centralized JavaScript variables
// Add currentconfig similar to conference.php
if (isset($_GET['myconfig'])) {
    $currentconfig = $_GET['myconfig'];
} else {
    $currentconfig = 'ABYANACNNCNNNYYYYN';
}

$jsVars = [
    'supportedlanguages' => $T_lang,
    'classroomid' => $classroomid,
    //"wooclapkey" => $wooclapkey,
    //"wooclapaddress" => $wooclapaddress,
    'theme' => $theme,
    'randomname' => $randomname,
    'currentconfig' => $currentconfig
];

?>
<!doctype html>
<html lang="fr">
<head>
	<title>Automatic translation for teaching purposes</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="icon" type="image/png" href="./images/favicon.svg" >
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <?php
    // Centralized styles
    $pageStyles = [
        'styles/auditor.css'
    ];

// Render styles
foreach ($pageStyles as $style) {
    echo "<link href=\"{$style}\" rel=\"stylesheet\">\n";
}
?>

    <?php
// Centralized scripts to be loaded before </body>
$pageScripts = [];
$pageScripts[] = '/js/auditor-def.js';
$pageScripts[] = '/js/common-func.js';
$pageScripts[] = '/js/auditor-post.js';

// ES Modules for auditor
$moduleScripts = [];
// Auditor modules (replaces auditor-func.js) - ES Modules
$moduleScripts[] = '/js/lib/conference/dom-helpers.js';
$moduleScripts[] = '/js/lib/auditor/request-counter.js';
$moduleScripts[] = '/js/lib/auditor/text-processing.js';
$moduleScripts[] = '/js/lib/auditor/speech.js';
$moduleScripts[] = '/js/lib/auditor/display.js';
$moduleScripts[] = '/js/lib/auditor/translation.js';
$moduleScripts[] = '/js/lib/auditor/network.js';
$moduleScripts[] = '/js/lib/auditor/config.js';
$moduleScripts[] = '/js/lib/auditor/mail.js';
$moduleScripts[] = '/js/auditor-init.js';
$moduleScripts[] = '/js/lib/auditor/main.js';
$moduleScripts[] = '/js/utils/dom.js';
$moduleScripts[] = '/js/utils/draggable.js';

// Chat modules and conference legacy as ES modules
$chatScripts = [];
// Charge conference-legacy.js en premier pour définir les fonctions nécessaires (EcrireNB, EcrireCR, etc.)
$chatScripts[] = '/js/conference-legacy.js';
$chatScripts[] = '/js/lib/chat/alert.js';
$chatScripts[] = '/js/lib/chat/network.js';
$chatScripts[] = '/js/lib/chat/handlers.js';
$chatScripts[] = '/js/lib/chat/display.js';
$chatScripts[] = '/js/lib/chat/main.js';

// Inject pageData for JavaScript
echo '<script>window.pageData = ' . json_encode($jsVars) . ";</script>\n";
echo "<script>var ROOM_TOKEN = '" . htmlspecialchars($roomToken, ENT_QUOTES) . "';</script>\n";
?>

    <link href="styles/auditor-specific.css" rel="stylesheet">
    <link href="styles/chat.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
</head>
<body>
<main>
<h1 style="display:none;">Outils de traduction : application étudiante</h1>
</main>
<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'Isite NExT" style="width: 109px; height: 50px;"></div>
<div id="micon" style="background-color: #EE0000;" onclick="gotolive=true; CompleteSpeech(); this.style.backgroundColor='rgba(238,0,0,0.5)'; setTimeout(()=> {document.getElementById('micon').style.backgroundColor='rgba(238,0,0,1)';},300);"><i class='material-icons'>sensors</i><span style="font-size: 10px;color: white;"><br>LIVE</span></div>
<div id="repeat" onclick="Parle(document.getElementById('titre').innerHTML);" title="Repeat the last sentence"><i class='material-icons'>replay</i><span style="font-size: 10px;color: white;"><br>REPEAT</span></div>

<div id="titre"><h1>Automatic translation provided by NExT OMIST Project</h1></div>

<div id="VOBanner"></div>
<div id="soustitre"></div>

<form id="myform" onsubmit="return false;">
<?php echo csrfField(); ?>

<!-- Sections affichées à la suite -->
	<section id="section-settings">
    <h2>Settings</h2>
	<div id="tabs-0">
    <h3 >Language and basic settings</h3>
    <div>
    <span style="display:none"><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo e($classroomid); ?>" ></span>
<span style="display:none">Pending requests : <span id="nbrequests">0</span></span>
<span style="display:none"><label for="classroomlanguage">Classroom language :</label>
  <select id="classroomlanguage" onchange="setLanguageInput(this.value);">
  </select>
</span>
<span id="lilanguage"><label for="VoiceOutput">My language :</label>
  <select id="VoiceOutput" onchange="setLanguageOutputAndRetranslate(this.value);"  onmouseout="if (firsttime && firstclick) {document.getElementById('synthesison').checked=false; firsttime=false;}" onclick="if (firsttime) {document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='visible'; document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='hithere 0.5s 5'; firstclick=true;}">
  </select><span id="arrowlanguage">&nbsp;Choose your language first</span>
</span><br>
<span><label for="AuditorName">My surname :</label>
  <input type="text" id="AuditorName" onchange="if (this.value == 'Intervenant') this.value='Auditor';" value="<?php echo e($randomname); ?>" autocomplete="given-name">
</span><br>
<span id="lisynthese"><label for="synthesison">Speech synthesis active ? </label>
<input type="checkbox" id="synthesison" onclick="document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='hidden';  document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='none'; firsttime=false;"><span id="arrowsynthesis">&nbsp; Then launch the speech synthesis</span>
</span>

</div>
	</div>
	<section id="section-chat">
    <h2>Classroom Chat</h2>
	<section id="tabs-1" role="region" aria-labelledby="chat-section-title">
    <div>
        <?php
    renderChatComponent([
        'roomId' => $classroomid,
        'roomToken' => $roomToken,
        'userName' => $randomname,
        'isIntervenant' => false,
        'placeholder' => 'Type your message here...',
        'labelText' => 'Classroom Chat'
    ]);
?>
    </div>
</section>

	<section id="section-notebook">
    <h2>Notebook</h2>
	<div id="tabs-2">

    <input style="margin-left: 64px;" type="button" value="Download" class="button-3 tooltip" title="Download the original speech in txt format." onclick="DownloadSpeechLesson('lesson_'+_('classroomid').value+'.txt', document.getElementById('notebook').innerHTML);"><br>
    <div id="notebook" style="border: solid 1px;"></div>

	</div>
	<section id="section-output">
    <h2>Output</h2>
	<div id="tabs-3">
  <input style="margin-left: 64px;" type="button" value="Download" class="button-3 tooltip" title="Download the translated speech in txt format." onclick="DownloadSpeechLesson('translated_lesson_'+_('classroomid').value+'.txt', document.getElementById('output').innerHTML);"><br>
    <div id="output">
    </div>
  </div>



	<!-- <div id="tabs-13" class="wordcloud" style="display:none;">
    <div id="form">
    <div id="vis"></div>
    <div id="configwc" style="display: none">
    <p><textarea id="text">Ceci est un test...</textarea>
    <button id="go" type="submit">Go!</button>
    <div style="float: right; text-align: right">
  <p><label for="max">Number of words:</label> <input type="number" value="250" min="1" id="max">
  <p><label for="per-line"><input type="checkbox" id="per-line"> One word per line</label>
  <p><label>Download:</label>
    <button id="download-svg">SVG</button>
</div>
<div style="float: left">
  <p><label>Spiral:</label>
    <fieldset><legend>spiral</legend>
    <label for="archimedean"><input type="radio" name="spiral" id="archimedean" value="archimedean" checked="checked"> Archimedean</label>
    <label for="rectangular"><input type="radio" name="spiral" id="rectangular" value="rectangular"> Rectangular</label>
</fieldset>
  <p>Scale:
  <fieldset><legend>spiral</legend>
    <label for="scale-log"><input type="radio" name="scale" id="scale-log" value="log" checked="checked"> log n</label>
    <label for="scale-sqrt"><input type="radio" name="scale" id="scale-sqrt" value="sqrt"> √n</label>
    <label for="scale-linear"><input type="radio" name="scale" id="scale-linear" value="linear"> n</label>
    </fieldset>
  <p><label for="font">Font:</label> <input type="text" id="font" value="Impact">
</div>
<div id="angles">
  <p><input type="number" id="angle-count" value="5" min="1"> <label for="angle-count">orientations</label>
    <label for="angle-from">from</label> <input type="number" id="angle-from" value="-60" min="-90" max="90"> °
    <label for="angle-to">to</label> <input type="number" id="angle-to" value="60" min="-90" max="90"> °
</div>
</div>
</div>
  </div> -->




  <!-- <div id="tabs-8"><iframe title="Fenêtre qui contient WOOCLAP" id="wooclap" src="<?php echo $wooclapaddress; ?>" style="width: 95%; height: 90%; bottom:0px; left:0px; z-index:1000;"   allowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write"></iframe>
  </div> -->

  <!-- <div id="tabs-9"><iframe title="Première fenêtre pour inclure un outil externe" id="extratab1" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" ></iframe>
  </div> -->
  <!-- <div id="tabs-10"><iframe title="Deuxième fenêtre pour inclure un outil externe" id="extratab2" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" ></iframe>
  </div> -->
  <!-- <div id="tabs-11"><iframe title="Troisième fenêtre pour inclure un outil externe" id="extratab3" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" ></iframe>
  </div> -->
  <!-- <div id="tabs-12"><iframe title="Quatrième fenêtre pour inclure un outil externe" id="extratab4" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" ></iframe>
  </div> -->

</form>
<div id="FS" onclick="GoFS();"  title="Toggle fullscreen mode"><i class='material-icons' style="font-size: 41px;" >fullscreen</i></div>

<dialog id="dialog-mail" style="width: 800px; height: 500px;">
  <form method="dialog">
    <fieldset>
      <legend style="display: none;">Send a mail</legend>
	<?php echo csrfField(); ?>
	<label for="dialog_MailTo">Mail to : </label><input type="text" id="dialog_MailTo" ><br>
	<label style="display:none;" for="dialog_MailFrom">Mail From : </label><input type="text" id="dialog_MailFrom" style="display:none;" readonly>
	<label for="dialog_MailSubject">Subject : </label><input type="text" id="dialog_MailSubject" readonly><br>
	Body : <br>
	<div id="dialog_MailBody">
	</div>
    <menu>
      <button type="submit" onclick="EnvoiMail(); return false;">Send</button>
      <button type="reset" onclick="document.getElementById('dialog-mail').close();">Cancel</button>
    </menu>
    </fieldset>
  </form>
</dialog>

<dialog id="dialog-choose" style="min-width: 300px;">
  <form method="dialog">
  <?php echo csrfField(); ?>
  <span><label for="VoiceOutputchoose">You first have to choose your language</label><br>
  <select id="VoiceOutputchoose">
  </select>
  </span>
  <menu>
    <button type="submit" id="dialog-choose-go">Go</button>
  </menu>
  </form>
</dialog>
<?php foreach ($pageScripts as $script) {
    echo "<script src=\"{$script}\"></script>\n";
}
// ES Modules for auditor
foreach ($moduleScripts as $script) {
    echo "<script type=\"module\" src=\"{$script}\"></script>\n";
}
// Chat modules as ES modules
foreach ($chatScripts as $script) {
    echo "<script type=\"module\" src=\"{$script}\"></script>\n";
}
?>
<?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
    <script src="/js/tests/no-jquery-validation.js"></script>
<?php endif; ?>
</body>
</html>
