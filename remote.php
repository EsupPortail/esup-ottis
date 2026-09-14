<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
?>
<?php
if (isset($_GET["ROOMID"])) {
  $classroomid = $_GET["ROOMID"];
  $synchronize = false;
} else {
  $classroomid = 'UNKNOWN';
  $synchronize = true;
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
	<link href="jquery/jquery-ui.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="./images/favicon.svg" >

<script type="text/javascript">

/**
 * @summary Sends the order to the teacher application.
 * 
 * @param {string} The order to send.
 */

function remote(myorder) {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "saveremoteorder.php");
  var f=new FormData();
  f.append('ROOMID',document.getElementById('classroomid').value);
  f.append('ORDER',myorder);
	request.send(f);
	request.onload = function(event) {
    var order=request.responseText;
  };
}
}

/**
 * @summary Displays the current, previous and next speech on the remote control.
 * 
 */

function remotespeech() {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "savespeechforremote.php");
  var f=new FormData();
  f.append('ROOMID',document.getElementById('classroomid').value);
  f.append('SPEECH','read');
	request.send(f);
	request.onload = function(event) {
    var data=request.responseText;
    var Tspeech = data.split(':|:');
    if (document.getElementById('speech_before').innerHTML != Tspeech[0])
      document.getElementById('speech_before').innerHTML = Tspeech[0];
    if (document.getElementById('speech_current').innerHTML != Tspeech[1])
      document.getElementById('speech_current').innerHTML = Tspeech[1];
    if (document.getElementById('speech_after').innerHTML != Tspeech[2])
      document.getElementById('speech_after').innerHTML = Tspeech[2];
  };
}
}


function Lancer() {
  setInterval(remotespeech,200);
}

/**
 * @summary Synchronizes the remote control with the teacher application.
 * 
 * @param {int} The session pin code.
 */

function Synchronize(pin) {
	var request = new XMLHttpRequest();
	request.open("POST", "synchronize.php");
  var f=new FormData();
  f.append('pincode',pin);
	request.send(f);
	request.onload = function(event) {
    if (request.responseText != "") {
      document.getElementById('classroomid').value=request.responseText;
    } else {
      alert("ERROR : unknown pin code");
    }
  };
}

var AllLanguages=[];
function Init() {
<?php
if ($synchronize) {
  echo "Synchronize(prompt('Enter the session pincode'));\n";
}
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
if ($synchronize) {
  echo "Synchronize(prompt('Enter the session pincode'));\n";
}
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
    document.getElementById('langdiv').innerHTML='<i class="fa fa-language" aria-hidden="true"></i> '+AllLanguages[currentlanguage];
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
<input type="text" style="display: none;" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" />
<input type="text" style="display: none;" name="appid" id="appid" value="<?php echo $appid; ?>" />

<script src="jquery/jquery.js"></script>
<script src="jquery/jquery-ui.min.js"></script>

</body>
</html>
