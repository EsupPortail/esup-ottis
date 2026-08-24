<?php
// Security includes
require_once __DIR__ . '/Security/validate.php';
require_once __DIR__ . '/Security/csrf.php';
require_once __DIR__ . '/Security/sanitize.php';

// Disable error display
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Empêche la mise en cache
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');

// Start session for CSRF
ensureSessionStarted();
initCsrfProtection();
?>
<?php
if (isset($_GET["ROOMID"])) {
  $classroomid = $_GET["ROOMID"];
} else {
  $classroomid = 'UNKNOWN';
}

$lang="";
if (isset($_GET["LANG"])) $lang=$_GET["LANG"];
$appid="";
if (isset($_GET["APPID"])) $appid=$_GET["APPID"];

?>
<!doctype html>
<html lang="fr">
<head>
	<title>OMIST - Remote</title>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./styles/remote.css">
  <link rel="icon" type="image/png" href="./images/favicon.svg" >
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
<script src="js/common-func.js"></script>

<script type="text/javascript">

/**
 * @summary Sends the order to the teacher application.
 * 
 * @param {string} The order to send.
 */

function remote(myorder) {
  if (document.getElementById('classroomid').value != "") {
  var f=new FormData();
  f.append('ROOMID',document.getElementById('classroomid').value);
  f.append('ORDER',myorder);
  
  // Ajouter le token CSRF
  var csrfToken = document.querySelector('input[name="csrf_token"]').value;
  if (csrfToken) {
      f.append('csrf_token', csrfToken);
  }
	createXhrRequest("POST", "saveremoteorder.php", f, function(responseText) {
    var order=responseText;
  });
  }
}

/**
 * @summary Displays the current, previous and next speech on the remote control.
 * 
 */

function remotespeech() {
  if (document.getElementById('classroomid').value != "") {
  var f=new FormData();
  f.append('ROOMID',document.getElementById('classroomid').value);
  f.append('SPEECH','read');
	createXhrRequest("POST", "savespeechforremote.php", f, function(responseText) {
    var data=responseText;
    var Tspeech = data.split(':|:');
    if (document.getElementById('speech_before').innerHTML != Tspeech[0])
      document.getElementById('speech_before').innerHTML = DOMPurify.sanitize(Tspeech[0]);
    if (document.getElementById('speech_current').innerHTML != Tspeech[1])
      document.getElementById('speech_current').innerHTML = DOMPurify.sanitize(Tspeech[1]);
    if (document.getElementById('speech_after').innerHTML != Tspeech[2])
      document.getElementById('speech_after').innerHTML = DOMPurify.sanitize(Tspeech[2]);
  });
  }
}


function Lancer() {
  setInterval(remotespeech,200);
}


var AllLanguages=[];
function Init() {
<?php
  echo "// ".$lang."\n\n";

if ($lang!="") {
  echo "    AllLanguages='".$lang."'.split(',');\n";
  echo "    setLanguage();\n";
} else {
  echo "    document.getElementById('langdiv').style.display='none';\n";
  echo "    document.getElementById('skipleft').style.width='40%';\n";
  echo "    document.getElementById('skipright').style.width='40%';\n";
}

?>

}


<?php
?>

var currentlanguage=0;

/**
 * @summary Get the next available language.
 * 
 */

function changeLanguage() {
  if (AllLanguages.length>0) {
    currentlanguage=(currentlanguage+1) % AllLanguages.length;
  }
  setLanguage();
}

/**
 * @summary Changes the speaker language.
 * 
 */

function setLanguage() {
  if (AllLanguages.length>0) {
    document.getElementById('langdiv').innerHTML='<i class="fa fa-language" aria-hidden="true"></i> '+DOMPurify.sanitize(AllLanguages[currentlanguage]);
    remote("language "+AllLanguages[currentlanguage]+" | "+document.getElementById('appid').value);
  }
}

/**
 * @summary Changes the opacity of the object when you click on it.
 * 
 * @param {object} obj the selected object.
 */

function changeOpacity(obj){
  if (obj.title==='Skip subtitle'){
    obj.style.opacity='0.25';
    setTimeout(()=> {obj.style.opacity='0.5';},300);
  } else if ((obj.id==='titreleft')||(obj.id==='titreright')){
    obj.style.opacity='0.5';
    setTimeout(()=> {obj.style.opacity='1';},300);
  } else if (obj.id==='micon'){
    obj.style.opacity='0.5'; 
    setTimeout(()=> {obj.style.opacity='1';},300);
  }
}

</script>


<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body onload=" Init(); setTimeout(Lancer,300);" style="width: 100%; height: 100%;">

<div id="micon" onclick="remote('micro '+document.getElementById('appid').value);changeOpacity(this);"><i id='micro_icon' class='material-icons'>record_voice_over</i></div>
<div id="speech_before" onclick="remote('backward');">Before</div>
<div id="speech_current" onclick="remote('playcurrent');">Current</div>
<div id="speech_after" onclick="remote('forward');">After</div>
<div title="Skip subtitle" id="skipleft" onclick="remote('skipbackward'); changeOpacity(this);"><span style="font-size: 1em;"><i class="fa fa-arrow-left" aria-hidden="true"></i> Skip comment </span></div>
<div title="Change Language" id="langdiv" onclick="changeLanguage();"><i class="fa fa-language" aria-hidden="true"></i> fr</div>
<div title="Skip subtitle" id="skipright" onclick="remote('skipforward');changeOpacity(this);"><span style="font-size: 1em;">Skip comment <i class="fa fa-arrow-right" aria-hidden="true"></i></span></div>
<div id="titreleft" style="font-size: 2em;" onclick="remote('backward'); changeOpacity(this);"><i class="fa fa-arrow-left" aria-hidden="true"></i></div>
<div id="titreright" style="font-size: 2em;" onclick="remote('forward'); changeOpacity(this);"><i class="fa fa-arrow-right" aria-hidden="true"></i></div>
<input type="text" style="display: none;" name="classroomid" id="classroomid" value="<?php echo e($classroomid); ?>" />
<input type="text" style="display: none;" name="appid" id="appid" value="<?php echo e($appid); ?>" />
<?php echo csrfField(); ?>

</body>
</html>
