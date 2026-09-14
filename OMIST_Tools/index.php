<?php
session_start();
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

include("../Security/Authentification.php");

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="anonymous"; //$uid="e18XXX"; // en attendant de rétablir le CAS....
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

if (! isset($external)) $external=false;

$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

if ($external){
	if (isset($_SESSION["username"])) {
		$uid_connexion = $_SESSION["username"];
	}else if (isset($_SERVER['PHP_AUTH_USER'])){
    $uid_connexion = $_SERVER['PHP_AUTH_USER'];
  }else{
		$uid_connexion = $url;
	}	
}else{
	$uid_connexion = $uid;
}


// if (strpos($url,'NOCAS') !== false) {
//   $uid_connexion = $url;
// } else {
//   $uid_connexion = 'internal';
// }

// $uid_connexion = $_SERVER["REMOTE_USER"];

if ($external){
	$is_external = 1;
}else{
	$is_external = 0;
}

if ($external) $uid="external";

$fake=false;

AuthenticateNotStudent($uid);

function BeginWith($ch, $motif) {
  return substr($ch, 0, strlen($motif)) == $motif;
}



$T_lang=array();
$fd = fopen("../supportedlanguages.csv","r");
while($line=fgets($fd,4096)) {
//  echo $line."\n";
  $ligne=explode(";",$line);
//  print_r($ligne);
  if (count($ligne)>2) $T_lang[]=array("name_fr"=>trim($ligne[2]), "name_vo"=>trim($ligne[0]), "code"=>trim($ligne[1]), "voice"=>"-1");
}
fclose($fd);


include("../config.php");
include("configOT.php");

$res=array();
// if ($DEEPLFREEAPITOKEN != "") {
//   exec($proxy." curl -q https://api-free.deepl.com/v2/usage?auth_key=".$DEEPLFREEAPITOKEN,$res);
//   $Stats_DEEPL = json_decode($res[0],true);
// } else {
//   if ($DEEPLAPITOKEN != "") {
//     exec($proxy." curl -q https://api.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN,$res);
//     $Stats_DEEPL = json_decode($res[0],true);
//   } else {
//     $Stats_DEEPL=array();
//   }
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
$myfile = fopen("../locks/DeeplPro_quota_exceeded.txt", "w") or die("Unable to open file!");
if ($percentage_DEEPL > 199){ // On bloque cette fonctionnalité pour le moment revenir à 99% si besoin
  $txt = "false";
} else {
  $txt = "true";
}
fwrite($myfile, $txt);
fclose($myfile);
*/


$theme="flat_black";
if (isset($_GET["theme"])) $theme=$_GET["theme"];

?>
<!doctype html>
<html lang="en">
<head>
	<title>Slide Manipulation Tools</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($theme != "") {
    echo '<link href="../jquery/'.$theme.'/jquery-ui.min.css" rel="stylesheet">';
    echo '<link href="../jquery/'.$theme.'/extras.css" rel="stylesheet">';
  } else {
    echo '<link href="../jquery/jquery-ui.min.css" rel="stylesheet">';
  }
	?>
    <link rel="icon" type="image/png" href="../images/favicon.svg" >
    <?php if ($theme != "") {
    echo '<script src="../jquery/'.$theme.'/external/jquery/jquery.js"></script>';
    echo '<script src="../jquery/'.$theme.'/jquery-ui.js"></script>';
  } else {
    echo '<script src="../jquery/jquery.js"></script>';
    echo '<script src="../jquery/jquery-ui.min.js"></script>';
  }
  ?>
    <script src="../js/teacher-func.js"></script>
    <script src="../js/edit-anim.js"></script>
    <link href="../styles/edit-anim.css" rel="stylesheet">
    <link rel="stylesheet" href="../libs/quickmenu/lib/font-awesome/css/font-awesome.min.css">
<style>

@import url('https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap');

.internalserveronly {
  <?php if (strpos($_SERVER['SERVER_NAME'],"automatictranslator.sciences.univ-nantes.fr") === false) echo "display:none; visibility: hidden;\n";?>
}


.mybutton {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

select, .jqueryselect, input[type="text"], textarea, button, input[type="file"], input[type="button"], input[type="submit"], .mybutton {
  border-radius: 7px;
  margin: 1px;
  padding: 2px;
}

input[type="button"], input[type="submit"], .mybutton {
cursor: pointer;
}

label {
  font-size: 1.1em;
  cursor: pointer;
}

label input[type="file"] {
/*  display: none; */
}



input[type="submit"], .mybutton {
  padding: 10px;
  background-color: rgb(16, 138, 18);
  color: white;
  border-color: rgb(16, 138, 18);
  font-size: 1.1em;
}




input[type="submit"]:hover, .mybutton:hover {
  background-color: rgba(25,190,40,0.7);
  border-color: rgba(25,190,40,0.7);
}

.mybuttonanimation {
  background-size: 200% 100%;
  border-color: rgba(25,190,40,0);
  background-image: linear-gradient(90deg, rgb(0,255,0) 0%, rgb(255, 153, 0) 50%, rgb(0,255,0) 100%);
  -webkit-animation: AnimateBG 2s ease infinite;
  animation: AnimateBG 2s ease infinite;
}

@-webkit-keyframes AnimateBG, @keyframes AnimateBG{
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

@keyframes AnimateBG{
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

input[type=checkbox] {
    position: relative;
	  cursor: pointer;
    width: 16px;
    height: 16px;
    padding: 3px;
    font: 1.5em;
    border-color:rgb(10,10,10);
    color: rgb(100,100,255);
    background-color: rgb(100,100,255);
}

input[type=checkbox]:hover {
  border-color:rgba(10,10,10,0.5);
  color: rgba(100,100,255,0.5);
  background-color: rgba(100,100,255,0.5);
}

h1 {
      font-family: 'Fjalla One', sans-serif;
}
#logo {
  position: absolute;
  top:0px;
  right:0px;
  z-index:1000;
}

.hidesmallscreens {}

.inputsmallscreens {max-width: 100%;}

@media (max-width:500px) {
	.hidesmallscreens {
		display: none;
	}
}

.fa {
    font-size: 18px;
}

#menu ul {
  list-style: none;
}

#dialog-message ul{
  list-style-type: disc;
}

#logout-button {
	text-decoration: none;
	font-weight: normal;
	margin-left: 15px;
	margin-right: 15px;
	position: relative;
	top: 9px;
}

input[name="downloadTemplateSpeech"]{
	padding-left: 6px;
	padding-right: 6px;
	border-width: thin;
	border-radius: 2px;
	border-color: grey;
}

</style>
<script>

<?php

if ($percentage_DEEPL > 70){
  echo "var critical_percentage_reached = true;";
} else {
  echo "var critical_percentage_reached = false;";
}

echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";
?>

function MultipleSelectionLimitation(s,m) {
  // Multiple selection limitation to m languages max
  var optionCount = 0;
  for (var i = 0; i < s.length; i++) {
    if (s[i].selected) {
      optionCount++;
    }
  }
  if (optionCount > m) {
    alert("When the translation quota exceeds 70%, it is possible to select only "+m+" values.");
    optionCount = 0;
    for (var i = 0; i < s.length; i++) {
      if (s[i].selected) {
        optionCount++;
        if (optionCount>2) s[i].selected = false;
      }
    }
  }
}

supportedlanguages.sort(function(a,b) {if (a["code"] == 'fr-FR') return false; else  return a["code"]>b["code"];});

function PopulateLanguages(selectelement) {
  var select = document.getElementById(selectelement);
  for(var i=0; i<supportedlanguages.length; i++) {
      var lang=supportedlanguages[i].code; //.substr(0,5);
        var opt=document.createElement("option");
        opt.value=lang;
        opt.innerHTML=lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
        select.appendChild(opt);
      
    }
}

function Init() {
  var allselects=['inputlanguage1','outputlanguage1','translatelanguage1','inputlanguage2','outputlanguage2','inputlanguage3','outputlanguage3','inputlanguage4','outputlanguage4','inputlanguage5','outputlanguage5','inputlanguage6','outputlanguage6'];
  allselects.map(PopulateLanguages);
  PopulateVoicesMicrosoft(_('inputlanguage13'));
  showList("all");
}

function displayLanguage(){
  var options = document.getElementById("myLanguage").options;
  var input_lang = options[0].value;
  var output_lang = [];
  for (let i = 1; i < options.length; i++) { 
  	output_lang[i] = options[i].value;
  }
  for(var i=0; i<supportedlanguages.length; i++) {
    var lang=supportedlanguages[i].code;
    if (lang===input_lang){
      if (output_lang.includes(input_lang.toLowerCase())){
        options[0].text = "[INPUT LANGUAGE] " + lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
      }else{
        options[0].text = lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
      }
    }
    var index_lang_output = output_lang.indexOf(lang.toLowerCase());
    if (index_lang_output != -1){
      options[index_lang_output].text = lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
    }
  }
}

function _(e) {return document.getElementById(e);}

// MD5 sum
var MD5 = function(d) {
  var r = M(V(Y(X(d), 8 * d.length)));
  return r.toLowerCase()
};

function M(d) {
  for (var _, m = "0123456789ABCDEF", f = "", r = 0; r < d.length; r++) _ = d.charCodeAt(r), f += m.charAt(_ >>> 4 & 15) + m.charAt(15 & _);
  return f
}
function X(d) {
  for (var _ = Array(d.length >> 2), m = 0; m < _.length; m++) _[m] = 0;
  for (m = 0; m < 8 * d.length; m += 8) _[m >> 5] |= (255 & d.charCodeAt(m / 8)) << m % 32;
  return _
}
function V(d) {
  for (var _ = "", m = 0; m < 32 * d.length; m += 8) _ += String.fromCharCode(d[m >> 5] >>> m % 32 & 255);
  return _
}
function Y(d, _) {
  d[_ >> 5] |= 128 << _ % 32, d[14 + (_ + 64 >>> 9 << 4)] = _;
  for (var m = 1732584193, f = -271733879, r = -1732584194, i = 271733878, n = 0; n < d.length; n += 16) {
    var h = m,
     t = f,
     g = r,
     e = i;
    f = md5_ii(f = md5_ii(f = md5_ii(f = md5_ii(f = md5_hh(f = md5_hh(f = md5_hh(f = md5_hh(f = md5_gg(f = md5_gg(f = md5_gg(f = md5_gg(f = md5_ff(f = md5_ff(f = md5_ff(f = md5_ff(f, r = md5_ff(r, i = md5_ff(i, m = md5_ff(m, f, r, i, d[n + 0], 7, -680876936), f, r, d[n + 1], 12, -389564586), m, f, d[n + 2], 17, 606105819), i, m, d[n + 3], 22, -1044525330), r = md5_ff(r, i = md5_ff(i, m = md5_ff(m, f, r, i, d[n + 4], 7, -176418897), f, r, d[n + 5], 12, 1200080426), m, f, d[n + 6], 17, -1473231341), i, m, d[n + 7], 22, -45705983), r = md5_ff(r, i = md5_ff(i, m = md5_ff(m, f, r, i, d[n + 8], 7, 1770035416), f, r, d[n + 9], 12, -1958414417), m, f, d[n + 10], 17, -42063), i, m, d[n + 11], 22, -1990404162), r = md5_ff(r, i = md5_ff(i, m = md5_ff(m, f, r, i, d[n + 12], 7, 1804603682), f, r, d[n + 13], 12, -40341101), m, f, d[n + 14], 17, -1502002290), i, m, d[n + 15], 22, 1236535329), r = md5_gg(r, i = md5_gg(i, m = md5_gg(m, f, r, i, d[n + 1], 5, -165796510), f, r, d[n + 6], 9, -1069501632), m, f, d[n + 11], 14, 643717713), i, m, d[n + 0], 20, -373897302), r = md5_gg(r, i = md5_gg(i, m = md5_gg(m, f, r, i, d[n + 5], 5, -701558691), f, r, d[n + 10], 9, 38016083), m, f, d[n + 15], 14, -660478335), i, m, d[n + 4], 20, -405537848), r = md5_gg(r, i = md5_gg(i, m = md5_gg(m, f, r, i, d[n + 9], 5, 568446438), f, r, d[n + 14], 9, -1019803690), m, f, d[n + 3], 14, -187363961), i, m, d[n + 8], 20, 1163531501), r = md5_gg(r, i = md5_gg(i, m = md5_gg(m, f, r, i, d[n + 13], 5, -1444681467), f, r, d[n + 2], 9, -51403784), m, f, d[n + 7], 14, 1735328473), i, m, d[n + 12], 20, -1926607734), r = md5_hh(r, i = md5_hh(i, m = md5_hh(m, f, r, i, d[n + 5], 4, -378558), f, r, d[n + 8], 11, -2022574463), m, f, d[n + 11], 16, 1839030562), i, m, d[n + 14], 23, -35309556), r = md5_hh(r, i = md5_hh(i, m = md5_hh(m, f, r, i, d[n + 1], 4, -1530992060), f, r, d[n + 4], 11, 1272893353), m, f, d[n + 7], 16, -155497632), i, m, d[n + 10], 23, -1094730640), r = md5_hh(r, i = md5_hh(i, m = md5_hh(m, f, r, i, d[n + 13], 4, 681279174), f, r, d[n + 0], 11, -358537222), m, f, d[n + 3], 16, -722521979), i, m, d[n + 6], 23, 76029189), r = md5_hh(r, i = md5_hh(i, m = md5_hh(m, f, r, i, d[n + 9], 4, -640364487), f, r, d[n + 12], 11, -421815835), m, f, d[n + 15], 16, 530742520), i, m, d[n + 2], 23, -995338651), r = md5_ii(r, i = md5_ii(i, m = md5_ii(m, f, r, i, d[n + 0], 6, -198630844), f, r, d[n + 7], 10, 1126891415), m, f, d[n + 14], 15, -1416354905), i, m, d[n + 5], 21, -57434055), r = md5_ii(r, i = md5_ii(i, m = md5_ii(m, f, r, i, d[n + 12], 6, 1700485571), f, r, d[n + 3], 10, -1894986606), m, f, d[n + 10], 15, -1051523), i, m, d[n + 1], 21, -2054922799), r = md5_ii(r, i = md5_ii(i, m = md5_ii(m, f, r, i, d[n + 8], 6, 1873313359), f, r, d[n + 15], 10, -30611744), m, f, d[n + 6], 15, -1560198380), i, m, d[n + 13], 21, 1309151649), r = md5_ii(r, i = md5_ii(i, m = md5_ii(m, f, r, i, d[n + 4], 6, -145523070), f, r, d[n + 11], 10, -1120210379), m, f, d[n + 2], 15, 718787259), i, m, d[n + 9], 21, -343485551), m = safe_add(m, h), f = safe_add(f, t), r = safe_add(r, g), i = safe_add(i, e)
  }
  return Array(m, f, r, i)
}
function md5_cmn(d, _, m, f, r, i) {
  return safe_add(bit_rol(safe_add(safe_add(_, d), safe_add(f, i)), r), m)
}
function md5_ff(d, _, m, f, r, i, n) {
  return md5_cmn(_ & m | ~_ & f, d, _, r, i, n)
}
function md5_gg(d, _, m, f, r, i, n) {
  return md5_cmn(_ & f | m & ~f, d, _, r, i, n)
}
function md5_hh(d, _, m, f, r, i, n) {
  return md5_cmn(_ ^ m ^ f, d, _, r, i, n)
}
function md5_ii(d, _, m, f, r, i, n) {
  return md5_cmn(m ^ (_ | ~f), d, _, r, i, n)
}
function safe_add(d, _) {
  var m = (65535 & d) + (65535 & _);
  return (d >> 16) + (_ >> 16) + (m >> 16) << 16 | 65535 & m
}
function bit_rol(d, _) {
  return d << _ | d >>> 32 - _
}

function downloadFile(filePath){
    var link=document.createElement('a');
    link.href = filePath;
    link.download = filePath.substr(filePath.lastIndexOf('/') + 1);
    link.click();
}

/**
 * @summary The function that is executed for downloading a template speech.
 * 
 * @param {string} filename a text value contained the file name.
 * @param {string} elText a text value contained the text to download.
 * @param {string} mimeType a text value contained the MIME type.
 */

 function DownloadTemplateSpeech(mimeType) {
    var link = document.createElement('a');
    mimeType = mimeType || 'text/plain';
    elText = "---------- Slide 1 ---------- \nEntrez votre texte ici \n---------- Slide 2 ---------- \nTexte \n---------- Slide 3 ---------- \n"+
    "Texte \n---------- Slide 4 ---------- \nTexte \n---------- Slide 5 ---------- \nTexte \n---------- Slide 6 ---------- \nTexte \n"+
    "---------- Slide 7 ---------- \n---------- Slide 8 ---------- \n---------- Slide 9 ---------- \n---------- Slide 10 ---------- \n---------- Slide 11 ---------- \n\n\+ ajouter le nombre de slide nécessaire";
    link.setAttribute('download', 'template_speech.txt');
    link.setAttribute('href', 'data:' + mimeType  +  ';charset=utf-8,' + encodeURIComponent(elText));
    link.click();
}

function SubmitJob(num,apptype) {
  // Step 1
  if (apptype == "Create") {
    _("App"+num).innerHTML = "Processing";
    _("App"+num).classList.add("mybuttonanimation");
    _("App"+num).disabled = true;
    _("Message"+num).innerHTML="Your file is currently being processed. This could take a while (up to several minutes depending on your file). Please be patient...";

    _("Random"+num).value = MD5((new Date()).getTime()+"");
    var form = new FormData(_("Form"+num));
    //form.append("randompath", _("Random1").value);
    //form.append("ProgramName", _("ProgName1").value);
    //form.append("fileToUpload", _("fileToUpload1")[0]);
    form.enctype='multipart/form-data';
	  var request = new XMLHttpRequest();
    <?php
    if ($DATABASETYPE == "fakeDB") {
	    echo "request.open(\"POST\", \"SendJob_noDB.php\");\n";
    } else {
	    echo "request.open(\"POST\", \"SendJob.php\");\n";
    }
    ?>
	  request.send(form);
    request.onload = function(event) {
		  var rep = request.responseText;
      console.log('Sent = '+rep);
      setTimeout(function () {SubmitJob(num,"Processing");},10000);
    }
  } else {
  if (apptype == "Processing") {
    var form = new FormData(_("Form"+num));
    //form.append("randompath", _("Random1").value);
    //form.append("ProgramName", _("ProgName1").value);
    form.enctype='multipart/form-data';
	  var request = new XMLHttpRequest();
    <?php
    if ($DATABASETYPE == "fakeDB") {
	    echo "request.open(\"POST\", \"isReady_noDB.php\");\n";
    } else {
	    echo "request.open(\"POST\", \"isReady.php\");\n";
    }
    ?>
	  request.send(form);
    request.onload = function(event) {
		  var rep = request.responseText;
      console.log("Received rep = "+rep);
      if (rep == "0") {
        // Ready to download
        //_("App1").value = "Ready to download";
        _("App"+num).disabled = false;
        _("App"+num).classList.remove("mybuttonanimation");
        _("App"+num).innerHTML = "Create";
        downloadFile("tmp/output_"+_("Random"+num).value+"."+_("Extension"+num).value);
        _("Message"+num).innerHTML="Your file has just been created, you can download it <a href=\""+"tmp/output_"+_("Random"+num).value+"."+_("Extension"+num).value+"\">here</a>."
        //var requestLog = new XMLHttpRequest();
        //echo "requestLog.open(\"POST\", \"TranslateLogs.php\");\n"
      } else {
        if (rep == "-1") {
          _("Message"+num).innerHTML="Your file will arrive soon."
        }
        setTimeout(function () {SubmitJob(num,"Processing");},10000);
      }
    }
  }
  }
}

function PopulateVoicesMicrosoft(s) {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "SRTtoMP3/ListVoices.php");

  xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            console.log(this.responseText);
            var response = this.responseText.split("\n");
            for(i=0; i<response.length; i++) {
              if (response[i].trim() != "") {
              var rep=response[i].split('|')
              var opt = document.createElement("option");

              opt.value = rep[1].split("_")[0];
              opt.innerHTML = rep[1];
              _('inputlanguage13').appendChild(opt);
              }
            }
        }
  };
  xhr.timeout = 9999;
  xhr.ontimeout = function() {
      console.log('ERROR Timeout');
  };
  xhr.send();
}

function showList(ch) {
    if (ch == "all") ch="1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20";
    var ToShow=ch.split(',');
    for(var i=1; i<20; i++) {
        if (_('tool'+i)) {
            if (ToShow.includes(i.toString())) {
                _('tool'+i).style.display='initial';
            } else {
                _('tool'+i).style.display='none';
            }
        }
    }
}

</script>
<!--
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
-->
<script>
var gt;
function googleTranslateElementInit() {
  document.cookie='';
  gt=new google.translate.TranslateElement({pageLanguage: 'auto', layout: google.translate.TranslateElement.InlineLayout.TOP_RIGHT}, 'google_translate_element');
  setTimeout(Init2,1000);
}

function Init2() {
  var a = document.querySelector("#google_translate_element select");
    if(a){
        var find=0;
        for(var i=0; i<a.options.length; i++) {
          if (a.options[i].value==document.body.lang) find=i;
        }
        a.selectedIndex=find;
        a.dispatchEvent(new Event('change'));
    }
  a=document.querySelector("#google_translate_element > div");
  a.childNodes[1].deleteData(0,100);
  for(var i=2; i<a.childNodes.length; i++) a.childNodes[i].innerHTML='';
}

</script>

<style>
/*
body .skiptranslate:first-child {
  display: none !important;
}
*/

body+.skiptranslate {
  display: none !important;
}

.skiptranslate iframe {
    display: none !important;
}

#google_translate_element .skiptranslate {
    display: block;
    /*display: none !important;*/
}

.goog-te-banner-frame.skiptranslate {
    display: none !important;
} 

@media print {
  #google_translate_element {display: none;}
}

#google_translate_element {position: fixed; top: 0px; z-index: 500000;}

#google_translate_element .goog-te-gadget-icon {
  display: none;
}

#google_translate_element>div {
  content: '';
}

#google_translate_element>div select {
  border-radius: 5px;
}

#goog-gt-tt, #goog-gt-vt {
  display: none !important;
}

body {
  top: 0px !important;
}

.goog-te-gadget-simple {
  border-radius: 8px;
}

/* CSS */
.button-10 {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 6px 14px;
  font-family: -apple-system, BlinkMacSystemFont, 'Roboto', sans-serif;
  border-radius: 6px;
  border: none;

  color: #fff;
  background: linear-gradient(180deg, #4B91F7 0%, #367AF6 100%);
   background-origin: border-box;
  box-shadow: 0px 0.5px 1.5px rgba(54, 122, 246, 0.25), inset 0px 0.8px 0px -0.25px rgba(255, 255, 255, 0.2);
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
}

.button-10:focus {
  box-shadow: inset 0px 0.8px 0px -0.25px rgba(255, 255, 255, 0.2), 0px 0.5px 1.5px rgba(54, 122, 246, 0.25), 0px 0px 0px 3.5px rgba(58, 108, 217, 0.5);
  outline: 0;
}

#opentranslator {
  position: fixed;
  top: 0px;
  left: 0px;
}

</style>
</head>
<body onload="Init();" lang="en">
<div id="google_translate_element"></div>
<input type="button" id="opentranslator" aria-label="Translate this page" title="Translate this page" class="button-10" style="z-index: 10000; padding-top: 10px; padding-bottom: 10px; padding-left: 10px; padding-right: 10px;" onclick="this.style.display='none'; var glink=document.createElement('script'); glink.src='https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'; document.body.appendChild(glink);" value="Translate this page">
  <main>
	<h1 class="hidesmallscreens" style="filter: invert(0.5); margin-left: 150px;">
  Slide Manipulation Tools</h1>
  </main>
  <div id="logo" class="hidesmallscreens"><img src="logo-ufr-sciences.jpg" height="80" alt=""></div>
  <datalist id="listaspectratio">
    <option value="16/10">
    <option value="16/9">
    <option value="4/3">
    <option value="1/1">
  </datalist>
  <datalist id="listdelays">
    <option value="0">
    <option value="5">
    <option value="10">
    <option value="300">
    <option value="600">
    <option value="1200">
  </datalist>
  <datalist id="listlength">
    <option value="80">
    <option value="100">
    <option value="120">
    <option value="150">
    <option value="175">
    <option value="200">
  </datalist>
  <div style="position: absolute; top: 0px; right: 250px;" class="hidesmallscreens">utilisation mensuelle de <?php echo "$WhichTranslator = $percentage_DEEPL"; ?>%</div>
<div id="tabs">
  <ul>
    <li style="background-color: #000; color: white"><img onclick="<?php if ($external) echo "javascript:void(window.open('https://automatictranslator.sciences.univ-nantes.fr/AutomaticTranslator/scenarios.php?external', '_self'))"; else echo "javascript:void(window.open('https://automatictranslator.sciences.univ-nantes.fr/AutomaticTranslator/scenarios.php', '_self'))"; ?>" src="../images/omist-top-logo-nav_ssfd.png" height="32" alt="Logo OMIST"></li>
    <li title="Display the complete tool list"><a href="#menu" onclick="showList('all')"><i class="fa fa-cogs"></i><span class="hidesmallscreens">&nbsp;All tools</span></a></li>
    <li title="These tools allow to create and edit HTML capsule"><a href="#menu" onclick="showList('1,2,3,4,12')"><i class="fa fa-picture-o"></i><span class="hidesmallscreens">&nbsp;Capsule only</span></a></li>
    <li title="These tools allow to transcript a video file or an audio file"><a href="#menu" onclick="showList('13,14,16')"><i class="fa fa-file-video-o"></i><span class="hidesmallscreens">&nbsp;Audio/video only</span></a></li>
    <li title="These tools allow to translate diverse types of files including PPTX, subtitles files or SCORM and XLIFF course scenarios"><a href="#menu" onclick="showList('5,6,10,11')"><i class="fa fa-language"></i><span class="hidesmallscreens">&nbsp;Translations only</span></a></li>
    <li title="These tools allow diverse manipulations, mainly for PPTX files"><a href="#menu" onclick="showList('7,8,9')"><i class="fa fa-refresh"></i><span class="hidesmallscreens">&nbsp;Conversions only</span></a></li>
    <li style="display:none;" id="logout" onclick="window.location.href='../Admin/Registration/logout.php';"><span id="logout-button" title="Logout"><i class="fa fa-sign-out" aria-hidden="true"></i><span class="hidesmallscreens">&nbsp;Logout</span></span></li>
  </ul>
<div id="menu">
<div id="tool1">
  <h3 title="Minimal version of the HTML capsule creation tool (from PPTX). With this tool, you can create a self-contained HTML capsule. Taking a PPTX file as input, the tool first converts all the slides to images and extracts the presenter notes. An animation player is added to the file to synchronize the synthesized speech with the images. Note that the generated capsule can be used as input (a complete lesson) for the OMIST live translation tool.">Create web capsule from pptx (minimal App without translations)</h3>
  <div>
  <form id="Form1" action="../blank.html" onsubmit="return false;" method="post" enctype="multipart/form-data">
    <ul>
      <li><label for="fileToUpload1">Select PPTX file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload1" aria-label="Select PPTX file" title="Select a PPTX file" accept=".pptx"  class="inputsmallscreens">
    <input type="hidden" id="ProgName1" name="ProgramName" value="CreateApp/CreateAppFromPPTX.php">
    <input type="hidden" id="Random1" name="randompath" value="">
    <input type="hidden" id="UserID1" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External1" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension1" name="Extension" value="html"></li>
    <!--<input id="App1" type="submit" value="Create" name="submit">-->
    <li><button id="App1" value="Create" onclick="if (_('fileToUpload1').value!='') {this.innerHTML=''; SubmitJob(1,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button></li>
    </ul>
    <p id="Message1"></p>
    </form>
  </div>
</div>
<div id="tool2">
  <h3 title="Minimal version of the HTML capsule creation tool (from PDF and TXT). With this tool, you can effortlessly create a self-contained HTML capsule. Utilizing a PDF file for images and a text file for presenter notes, the tool seamlessly converts all slides into images and extracts corresponding presenter notes. Subsequently, an animation player is incorporated into the file to synchronize the synthesized speech with the images.

It's important to note that the resulting capsule can serve as comprehensive input (a complete lesson) for the OMIST live translation tool. Elevate your content creation with this innovative tool, bridging the gap between dynamic presentations and effective live translation on the OMIST platform.">Create web capsule from pdf+txt (minimal App without translations)</h3>
  <div>
  <form id="Form2" action="../blank.html" onsubmit="return false;" method="post" enctype="multipart/form-data">
    <ul>
    <li>
    <label for="fileToUpload2">Select PDF file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload2"  aria-label="Select PDF file" title="Select a PDF file" accept=".pdf"  class="inputsmallscreens">
    <input type="hidden" id="ProgName2" name="ProgramName" value="CreateApp/CreateAppFromPDF.php"> 
    <input type="hidden" id="Random2" name="randompath" value="">
    <input type="hidden" id="UserID2" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External2" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension2" name="Extension" value="html"></li>
    <li>
      <label for="fileToUploadTXT2">Select SPEECH file (.txt): </label>
    <input type="file" name="fileToUploadTXT" id="fileToUploadTXT2"  aria-label="Select SPEECH file (.txt)" title="Select SPEECH file" accept=".txt"  class="inputsmallscreens"></li>
    <li><label for="downloadTemplateSpeech2">You can download a template speech here : </label>
    <input type="button" value="Download"  name="downloadTemplateSpeech" id="downloadTemplateSpeech2"  aria-label="Download a template speech" title="Download a template speech"  class="inputsmallscreens" onclick="DownloadTemplateSpeech()"></li><br>
    <li><button id="App2" value="Create" onclick="if (_('fileToUpload2').value!='') {this.innerHTML=''; SubmitJob(2,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button></ul>
</ul>
    <p id="Message2"></p>
    </form>
  </div>
</div>
<div id="tool3">
  <h3 title="Complete version of the HTML capsule creation tool (from PPTX). With this tool, you will be able to create a self-contained HTML capsule. Taking a PPTx file as input, the tool first converts all the slides to images and extracts the presenter notes. An animation player is added to the file for synchronizing the synthesized speech (translated in several languages) with the images. Notice that the generated capsule can be used as an input (complete lesson) for the OMIST live translation tool. Several options are available such as the possibility to translate the slides before converting them to images or adding a licence for instance.">Create web capsule from pptx (Complete)</h3>
  <div>
  <form id="Form3" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
    <label for="fileToUpload3">Select PPTX file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload3" aria-label="Select PPTX file" title="Select a PPTX file"  accept=".pptx"  class="inputsmallscreens"><br>
    <input type="hidden" name="ProgramName" value="CreateApp/CreateAppFromPPTX_complete.php">

    <label for="inputlanguage1">Input language : </label>
    <select id="inputlanguage1" name="inputlanguage" class="inputsmallscreens"></select><br>
    <label for="outputlanguage1" style="vertical-align: top;">Output languages : </label>
    <select onchange="if (critical_percentage_reached) MultipleSelectionLimitation(this,2);" multiple id="outputlanguage1"  name="outputlanguage[]" style="height: 125px;" class="inputsmallscreens" style="vertical-align: top;"></select><br>

    <p><b>Advanced options</b></p>
    <label for="translatelanguage1">Translate slides in ? </label>
    <select id="translatelanguage1" name="translatelanguage" class="inputsmallscreens">
      <option value="">No translation</option>
    </select><br>
    <!-- Paramètre devenu inutile. Possible de cacher ces champs -->
    <span style="display:none">
    <label for="aspectratio1">Aspect ratio : </label>
    <input class="jqueryselect inputsmallscreens" id="aspectratio1" name="aspectratio" list="listaspectratio" value="16/10"><br>
    <label for="keepaspectratio1">Keep Aspect ratio ? </label>
    <input type="checkbox" aria-label="Keep Aspect ratio ?" title="Keep Aspect ratio ?" checked onclick="if (this.checked) {document.getElementById('keepaspectratio1').value ='X'; document.getElementById('cbtext1').innerHTML ='Yes !';} else {document.getElementById('keepaspectratio1').value =''; document.getElementById('cbtext1').innerHTML ='No.'; }" style="font-size:2em;">&nbsp;<span id="cbtext1">Yes !</span>
    <input class="jqueryselect" id="keepaspectratio1" name="keepaspectratio"  style="width: 10px; display: none;" value="X" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"><br></span>
    <label title="Extra delay between two slides in tenth of seconds (hence, a value of 10 means 1 second)." for="extradelay1">Extra delay <span class="hidesmallscreens"> between slides (in tenth of seconds): </span></label>
    <input id="extradelay1" name="extradelay" list="listdelays" value="0" onchange="if (this.value.trim()=='') this.value='0'"  class="inputsmallscreens"><br>
  <p><b>Licence options</b></p>
  <label for="licence">Would you like to add a licence ? </label>
  <select id="licence" name="Licence"  class="inputsmallscreens">
  <option value="" selected>No licence</option>
  <option value="CC0">CC0 1.0 Universal</option>
  <option value="CCBY">CC BY 4.0</option>
  <option value="CCBYSA">CC BY-SA 4.0</option>
  <option value="CCBYND">CC BY-ND 4.0</option>
  <option value="CCBYNC">CC BY-NC 4.0</option>
  <option value="CCBYNCSA">CC BY-NC-SA 4.0</option>
  <option value="CCBYNCND">CC BY-NC-ND 4.0</option>
  </select><br>
  
  
    <input type="hidden" id="Random3" name="randompath" value="">
    <input type="hidden" id="UserID3" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External3" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension3"  name="Extension" value="html">
    <!--<input id="App1" type="submit" value="Create" name="submit">-->
    <button id="App3" value="Create" onclick="if (_('fileToUpload3').value!='') {this.innerHTML=''; SubmitJob(3,'Create');} else {alert('You must provide a file and choose a delay first !');}" class="mybutton">Create</button>
    <p id="Message3"></p>
    </form>
  </div>
</div>
<div id="tool4">
  <h3 title="Minimal version of the HTML capsule creation tool (from PDF and TXT). With this tool, you will be able to create a self-contained HTML capsule. Taking a PDF file as input for the images and a text file as input for the presenter notes, the tool first converts all the slides to images and extracts the presenter notes. An animation player is added to the file for synchronizing the synthesized speech (translated in several languages) with the images. Notice that the generated capsule can be used as an input (complete lesson) for the OMIST live translation tool. Several options are available such as the possibility to add a licence for instance.">Create web capsule from pdf+txt (Complete)</h3>
  <div>
  <form id="Form4" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
  
    <label for="fileToUpload4">Select PDF file :   </label><input type="file" name="fileToUpload" id="fileToUpload4" aria-label="Select PDF file" title="Select a PDF file"  accept=".pdf"  class="inputsmallscreens"><br>
    <label for="fileToUploadTXT4">Select SPEECH file (.txt): </label><input type="file" name="fileToUploadTXT" id="fileToUploadTXT4" aria-label="Select SPEECH file (.txt):" title="Select SPEECH file (.txt):"  accept=".txt"  class="inputsmallscreens"><br>
    <label for="downloadTemplateSpeech4">You can download a template speech here : </label>
    <input type="button" value="Download"  name="downloadTemplateSpeech" id="downloadTemplateSpeech4"  aria-label="Download a template speech" title="Download a template speech"  class="inputsmallscreens" onclick="DownloadTemplateSpeech()"></li><br>
    <input type="hidden" id="ProgName4" name="ProgramName" value="CreateApp/CreateAppFromPDF_complete.php">
    <input type="hidden" id="Random4" name="randompath" value="">
    <input type="hidden" id="UserID4" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External4" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension4" name="Extension" value="html">


    <label for="inputlanguage2">Input language : </label>
    <select id="inputlanguage2" name="inputlanguage" class="inputsmallscreens"></select><br>
    <label for="outputlanguage2" style="vertical-align: top;">Output languages : </label>
    <script>
      MultipleSelectionLimitation
  </script>

    <select onchange="if (critical_percentage_reached) MultipleSelectionLimitation(this,2);" multiple id="outputlanguage2" name="outputlanguage[]" style="height: 125px;" class="inputsmallscreens" style="vertical-align: top;"></select><br>

    <p><b>Advanced options</b></p>
    <label for="aspectratio2">Aspect ratio : </label>
    <input class="jqueryselect inputsmallscreens" id="aspectratio2" name="aspectratio" list="listaspectratio" value="16/10"><br>
    <label for="keepaspectratio2">Keep Aspect ratio ? </label>
    <input type="checkbox" aria-label="Keep Aspect ratio ?" title="Keep Aspect ratio ?" checked onclick="if (this.checked) {document.getElementById('keepaspectratio2').value ='X'; document.getElementById('cbtext2').innerHTML ='Yes !';} else {document.getElementById('keepaspectratio2').value =''; document.getElementById('cbtext2').innerHTML ='No.'; }" style="font-size:2em;">&nbsp;<span id="cbtext2">Yes !</span>
    <input class="jqueryselect" id="keepaspectratio2" name="keepaspectratio"  style="width: 10px; display: none;" value="X" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"><br>
    <label title="Extra delay between two slides in tenth of seconds (hence, a value of 10 means 1 second)." for="extradelay2">Extra delay <span class="hidesmallscreens"> between slides (in tenth of seconds): </span></label>
    <input id="extradelay2" name="extradelay" list="listdelays" value="0" onchange="if (this.value.trim()=='') this.value='0'"  class="inputsmallscreens">
  <p><b>Licence options</b></p>
  <label for="licence2">Would you like to add a licence ? </label>
  <select id="licence2" name="Licence"  class="inputsmallscreens">
  <option value="" selected>No licence</option>
  <option value="CC0">CC0 1.0 Universal</option>
  <option value="CCBY">CC BY 4.0</option>
  <option value="CCBYSA">CC BY-SA 4.0</option>
  <option value="CCBYNC">CC BY-ND 4.0</option>
  <option value="CCBYND">CC BY-NC 4.0</option>
  <option value="CCBYNCSA">CC BY-NC-SA 4.0</option>
  <option value="CCBYNCND">CC BY-NC-ND 4.0</option>
  </select><br>
  

  <!--
<table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage2" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage2"  name="outputlanguage[]" multiple size="5"></select>
  </td></tr>
  <tr><td colspan="2"><b>Advanced options</b></td></tr>
     <tr>
  <td style="vertical-align: top;">
    Aspect ratio : </td>
  <td>
  <input class="jqueryselect" id="aspectratio2" name="aspectratio" list="listaspectratio" value="16/10">
 </td></tr>
 <tr>
    <td style="vertical-align: top;">
    Keep Aspect ratio ? </td>
  <td>
  
    <input type="checkbox" checked onclick="if (this.checked) {document.getElementById('keepaspectratio2').value ='X'; document.getElementById('cbtext2').innerHTML ='Yes !';} else {document.getElementById('keepaspectratio2').value =''; document.getElementById('cbtext2').innerHTML ='No.'; }" style="font-size:2em;">&nbsp;<span id="cbtext2">Yes !</span>
    <input id="keepaspectratio2" name="keepaspectratio"  style="width: 10px; display: none;" value="X" onclick="if (this.value == 'X') this.value =''; else this.value ='X';">
  </td></tr>
  <tr>
    <td style="vertical-align: top;">
    Extra delay between slides (in tenth of seconds): </td>
  <td>
    <input class="jqueryselect" id="extradelay2" name="extradelay" list="listdelays" value="0">

  </td></tr>
  <tr><td colspan="2"><b>Licence options</b></td></tr>
  <tr>
  <td style="vertical-align: top;">
    Would you like to add a licence ? </td>
  <td>
  <select id="licence" name="Licence">
  <option value="" selected>No licence</option>
  <option value="CC0">CC0 1.0 Universal</option>
  <option value="CCBY">CC BY 4.0</option>
  <option value="CCBYSA">CC BY-SA 4.0</option>
  <option value="CCBYNC">CC BY-ND 4.0</option>
  <option value="CCBYND">CC BY-NC 4.0</option>
  <option value="CCBYNCSA">CC BY-NC-SA 4.0</option>
  <option value="CCBYNCND">CC BY-NC-ND 4.0</option>
 </select>
  </td></tr>
  
</table>-->

    <button id="App4" value="Create" onclick="if (_('fileToUpload4').value!='') {this.innerHTML=''; SubmitJob(4,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message4"></p>
    </form>
  </div>
</div>
<div id="tool5">
    <h3 title="This tool allows to translate the text elements included in a pptx file. The result is a PPTx file. Notice that several translations can be performed in parallel.">Translate pptx</h3>
  <p>Cette fonctionnalité, très consommatrice de données de traduction a dû être fermée temporairement, le quota de traduction disponible étant atteint ou en passe de l'être. Il est possible de traduire ce type de document via d'autres services comme par exemple celui accessible à cette <a href="https://www.onlinedoctranslator.com/en/translationform">adresse</a> qui prend en charge de nombreux formats de documents sans limitation de taille. Nous mettons tout en oeuvre pour rétablir ce service prochainement.</p>
  <div style="display:none;">
  <form id="Form5" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
  <label for="fileToUpload5">Select PPTX file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload5" accept=".pptx"  aria-label="Select PPTX file" title="Select a PPTX file"  class="inputsmallscreens"><br>
    <input type="hidden" id="ProgName5" name="ProgramName" value="TranslatePPTX/TranslatePPTX.php">
    <input type="hidden" id="Random5" name="randompath" value="">
    <input type="hidden" id="UserID5" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External5" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension5" name="Extension" value="zip">
    
    <label for="inputlanguage3">Input language : </label>
    <select id="inputlanguage3" name="inputlanguage" class="inputsmallscreens"></select><br>
    <label for="outputlanguage3" style="vertical-align: top;">Output languages : </label>
    <select multiple id="outputlanguage3"  name="outputlanguage[]" style="height: 125px;" class="inputsmallscreens" style="vertical-align: top;"></select><br>

    <label for="slidenotes3">Translate slide notes too ? </label>
    <input type="checkbox" aria-label="Translate slide notes too ?" title="Translate slide notes too ?" onclick="if (this.checked) {document.getElementById('slidenotes3').value ='X'; document.getElementById('cbtext3').innerHTML ='Yes !';} else {document.getElementById('slidenotes3').value =''; document.getElementById('cbtext3').innerHTML ='No.'; }" style="font-size:2em;">&nbsp;<span id="cbtext3">No.</span>
    <input id="slidenotes3" name="slidenotes"  style="width: 10px; display: none;" value="" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"><br>
  
<!--    <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage3" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage3"  name="outputlanguage[]" multiple size="5"></select>
  </td></tr>
  <tr>
    <td style="vertical-align: top;">
    Translate slide notes too ? </td>
  <td>
  <input type="checkbox" onclick="if (this.checked) {document.getElementById('slidenotes3').value ='X'; document.getElementById('cbtext3').innerHTML ='Yes !';} else {document.getElementById('slidenotes3').value =''; document.getElementById('cbtext3').innerHTML ='No.'; }" style="font-size:2em;">&nbsp;<span id="cbtext3">No.</span>
  <input id="slidenotes3" name="slidenotes"  style="width: 10px; display: none;" value="" onclick="if (this.value == 'X') this.value =''; else this.value ='X';">
  </td></tr>
 </table>
    -->

    <button id="App5" value="Create" onclick="if (_('fileToUpload5').value!='') {this.innerHTML=''; SubmitJob(5,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message5"></p>
    </form>
  </div>
</div>
<div id="tool6">
  <h3 title="This tool allows to translate subtitle files. It can handle both SRT and VTT files.">Translate subtitles SRT or VTT file</h3>
  <div>
  <form id="Form6" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
  <label for="fileToUpload6">Select SRT or VTT file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload6"  aria-label="Select SRT file" title="Select a SRT or VTT file" accept=".srt,.vtt" class="inputsmallscreens"><br>
    <input type="hidden" id="ProgName6" name="ProgramName" value="TranslateSRT/TranslateSRT.php">
    <input type="hidden" id="Random6" name="randompath" value="">
    <input type="hidden" id="UserID6" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External6" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension6" name="Extension" value="zip">

    <label for="inputlanguage4">Input language : </label>
    <select id="inputlanguage4" name="inputlanguage" class="inputsmallscreens"></select><br>
    <label for="outputlanguage4" style="vertical-align: top;">Output language : </label>
    <select id="outputlanguage4"  name="outputlanguage[]" class="inputsmallscreens" style="vertical-align: top;"></select><br>
    <label for="wraplength">Maximum subtitle length : </label>
    <input type="number" name="wraplength" id="wraplength" min="50" max="200" value="80" step="10" class="inputsmallscreens"><br>
    <label for="nopunct">My subtitles do not have any punctuation : </label>
    <input type="checkbox" id="nopunct" name="nopunct" value="Yes" ><br>
    <!--
    <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage4" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output language : </td>
  <td><select id="outputlanguage4"  name="outputlanguage[]" size="5"></select>
  </td></tr>
  <tr>
  <td style="vertical-align: top;">
    Maximum subtitle length : </td>
  <td>
  <input type="number" name="wraplength" id="wraplength" min="50" max="200" value="80" step="10">
  </td></tr>
  <tr>
  <td style="vertical-align: top;">
  My subtitles do not have any punctuation : </td>
  <td><input type="checkbox" name="nopunct" value="Yes" >
  </td></tr>
 </table>
    -->
    <button id="App6" value="Create" onclick="if (_('fileToUpload6').value!='') {this.innerHTML=''; SubmitJob(6,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message6"></p>
    </form>
  </div>
</div>
<div id="tool8">
  <h3 title="Imagine that you have a PPTX file without comments and your notes in a separate text file. This simple tool allows to combine both files. The result is a complete PPTX file.">Convert pptx+txt to pptx</h3>
  <div>
  <form id="Form8" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
    <label for="fileToUpload8">Select PPTx file :   </label><input type="file" name="fileToUpload" id="fileToUpload8"  aria-label="Select PPTX file" title="Select a PPTX file" accept=".pptx"  class="inputsmallscreens"><br>
    <label for="fileToUploadTXT8">Select SPEECH file (.txt): </label><input type="file" name="fileToUploadTXT" id="fileToUploadTXT8" aria-label="Select SPEECH file (.txt)" title="Select SPEECH file (.txt)" accept=".txt"  class="inputsmallscreens"><br>
    <label for="downloadTemplateSpeech8">You can download a template speech here : </label>
    <input type="button" value="Download"  name="downloadTemplateSpeech" id="downloadTemplateSpeech8"  aria-label="Download a template speech" title="Download a template speech"  class="inputsmallscreens" onclick="DownloadTemplateSpeech()"></li><br>
    <input type="hidden" id="ProgName8" name="ProgramName" value="ConvertPPTX/ConvertPPTX_TXT_toPPTX.php">
    <input type="hidden" id="Random8" name="randompath" value="">
    <input type="hidden" id="UserID8" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External8" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension8" name="Extension" value="zip">
    <button id="App8" value="Create" onclick="if (_('fileToUpload8').value!='') {this.innerHTML=''; SubmitJob(8,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message8"></p>
    </form>
  </div>
</div>
<div id="tool7">
  <h3>Convert pdf+txt to pptx</h3>
  <div>
  <form id="Form7" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
    <label for="fileToUpload7">Select PDF file :   </label><input type="file" name="fileToUpload" id="fileToUpload7"  aria-label="Select PDF file" title="Select a PDF file" accept=".pdf"  class="inputsmallscreens"><br>
    <label for="fileToUploadTXT7">Select SPEECH file (.txt): </label><input type="file" name="fileToUploadTXT" id="fileToUploadTXT7" aria-label="Select SPEECH file (.txt)" title="Select SPEECH file (.txt)" accept=".txt"  class="inputsmallscreens"><br>
    <label for="downloadTemplateSpeech7">You can download a template speech here : </label>
    <input type="button" value="Download"  name="downloadTemplateSpeech" id="downloadTemplateSpeech7"  aria-label="Download a template speech" title="Download a template speech"  class="inputsmallscreens" onclick="DownloadTemplateSpeech()"></li><br>
    <input type="hidden" id="ProgName7" name="ProgramName" value="ConvertPDF/ConvertPDFtoPPTX.php">
    <input type="hidden" id="Random7" name="randompath" value="">
    <input type="hidden" id="UserID7" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External7" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension7" name="Extension" value="zip">
    <button id="App7" value="Create" onclick="if (_('fileToUpload7').value!='') {this.innerHTML=''; SubmitJob(7,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message7"></p>
    </form>
  </div>
  </div>
<div id="tool9">
  <h3 title="This simple tool allows to extract the comments of a PPTX file. The result is a text file.">Extract speech from pptx</h3>
  <div>
  <form id="Form9" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
    <label for="fileToUpload9">Select PPTX file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload9" accept=".pptx"  aria-label="Select PPTX file" title="Select a PPTX file" class="inputsmallscreens"><br>
    <input type="hidden" id="ProgName9" name="ProgramName" value="ExtractSpeech/ExtractSpeechFromPPTX.php">
    <input type="hidden" id="Random9" name="randompath" value="">
    <input type="hidden" id="UserID9" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External9" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension9" name="Extension" value="txt">
    <button id="App9" value="Create" onclick="if (_('fileToUpload9').value!='') {this.innerHTML=''; SubmitJob(9,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message9"></p>
    </form>
  </div>
</div>
<div id="tool10">
  <h3 title="This tool allows to translate XLIFF files. XLIFF (XML Localisation Interchange File Format) files are used by softwares, such as Rise Articulate, dedicated to the creation of e-learning contents. Consequently, our tool permits to translate a complete and scenarized e-leaning course.">Translate XLIFF</h3>
  <div>
  <form id="Form10" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
    <label for="fileToUpload10">Select XLF file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload10" aria-label="Select XLF file" title="Select a XLF file" accept=".xlf"  class="inputsmallscreens"><br>
    <input type="hidden" id="ProgName10" name="ProgramName" value="TranslateXLIFF/TranslateXLIFF.php">
    <input type="hidden" id="Random10" name="randompath" value="">
    <input type="hidden" id="UserID10" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External10" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension10" name="Extension" value="zip">

    <label for="inputlanguage5">Input language : </label>
    <select id="inputlanguage5" name="inputlanguage" class="inputsmallscreens"></select><br>
    <label for="outputlanguage5" style="vertical-align: top;">Output language : </label>
    <select id="outputlanguage5"  name="outputlanguage[]" class="inputsmallscreens" style="vertical-align: top;"></select><br>
    <button id="App10" value="Create" onclick="if (_('fileToUpload10').value!='') {this.innerHTML=''; SubmitJob(10,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message10"></p>
    </form>
  </div>
</div>
<div id="tool11">
  <h3 title="This tool allows to translate SCORM files. SCORM (Sharable Content Object Reference Model) files are used by softwares, such as Rise Articulate, dedicated to the creation of e-learning contents. SCORM files can be directly integrated to Moodle. Consequently, our tool permits to translate a complete and scenarized e-leaning course.">Translate SCORM</h3>
  <div>
  <form id="Form11" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
    <label for="fileToUpload11">Select ZIP file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload11" aria-label="Select ZIP file" title="Select a ZIP file" accept=".zip"><br>
    <input type="hidden" id="ProgName11" name="ProgramName" value="TranslateSCORM/TranslateSCORM.php">
    <input type="hidden" id="Random11" name="randompath" value="">
    <input type="hidden" id="UserID11" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External11" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension11" name="Extension" value="zip">
    
    <label for="inputlanguage6">Input language : </label>
    <select id="inputlanguage6" name="inputlanguage" class="inputsmallscreens"></select><br>
    <label for="outputlanguage6" style="vertical-align: top;">Output language : </label>
    <select onchange="if (critical_percentage_reached) MultipleSelectionLimitation(this,2);" id="outputlanguage6"  name="outputlanguage[]" class="inputsmallscreens" style="vertical-align: top;"></select><br>
    
    <button id="App11" value="Create" onclick="if (_('fileToUpload11').value!='') {this.innerHTML=''; SubmitJob(11,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message11"></p>
    </form>
    </div>
</div>
<div id="tool14">
    <h3 title="Link to the Ceméa (Centres d’Entraînement aux Méthodes d’Education Active) Scribe transcripton tool. Here, your file is sent and handled by the CEMEA servers. The transcription is based on the VOSK open model.">Audio/video file transcription with Scribe-CEMEA free tool</h3>
        <div>
            <iframe title="Cadre pour Ceméa Transcription" style="width:80%; height: 500px; border: none;" src="https://scribe.cemea.org/"></iframe>
        </div>
</div>
<!--
<div id="tool15">
  <h3>External vidéo subtitle editor (Nikse.dk)</h3>
  <div>
  <iframe width="100%" height="800px" style="border: none;" src="https://www.nikse.dk/subtitleedit/online"></iframe>
  </div>
  </div>
  -->
<div id="tool16">
  <h3 class="internalserveronly" title="This tool allows to obtain a MP3 file that corresponds to synchronized speech synthesis of a given SRT file. The produced file can be re-combined with the original video to obtain a soundtrack translation of the video.">Subtitle reader</h3>
  <div class="internalserveronly">
  <form id="Form13" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
    <label for="fileToUpload13">Select SRT file : </label>
    <input type="file" name="fileToUpload" id="fileToUpload13" aria-label="Select SRT file" title="Select a SRT file" accept=".srt"><br>
    <input type="hidden" id="ProgName13" name="ProgramName" value="SRTtoMP3/CreateMP3fromSRT.php">
    <input type="hidden" id="Random13" name="randompath" value="">
    <input type="hidden" id="UserID13" name="userid" value="<?php echo $uid_connexion; ?>" >
    <input type="hidden" id="External13" name="external" value="<?php echo $is_external; ?>" >
    <input type="hidden" id="Extension13" name="Extension" value="mp3">
    <label for="inputlanguage13" >Select your voice</label>
    <select id="inputlanguage13" name="inputlanguage">
    </select><br>
    <input type="text" id="speed13" name="wraplength" value="-1" title="Real number. -1 means that the speed is adapted to the video."><br>
    <button id="App13" value="Create" onclick="if (_('fileToUpload13').value!='') {this.innerHTML=''; SubmitJob(13,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>    
    <p id="Message13"></p>
    </form>
  </div>
</div>

<div id="tool12">
  <h3 id="tabledesc" title="This tool allows to modify a HTML capsule created by our tools.">Edit capsule</h3>
  <div id="block12" class="styleEditAnim" translate="no">
    <form id="Form12" class="styleEditAnim" onsubmit="return false;" action="../blank.html" method="post" enctype="multipart/form-data">
    <ul class="ui-menu ui-widget highlight">
      <li class="dropdown">
      <input type="file" name="fileToUpload" id="fileToUpload12" aria-label="Select HTML file" title="Select HTML file" onchange="SlideNotesTools(true); initializeEditAnim();" accept=".html">   
      </li>
      <li class="dropdown">
        <a title="Add a pause in your capsule." class="dropbtn" onclick='if (_("fileToUpload12").value!="") {document.getElementById("dropPause").classList.toggle("show"); closeDropdown("dropPause");} else {warningEmpty("file");}'><i class="fa fa-pause"></i> Pause</a>
        <div class="dropdown-content" id="dropPause">
          <a href="javascript:if (_('imageArea').innerHTML!='') {addAllLanguages('[pause]');} else {warningEmpty('slide');}">Pause capsule</a>
          <a href="javascript:if (_('imageArea').innerHTML!='') {addParameters('[wait toReplace]');} else {warningEmpty('slide');}">Pause for n milliseconds</a>
        </div>
      </li>
      <li class="dropdown">
        <a title="Add a mouse element in your capsule." class="dropbtn" onclick='if (_("fileToUpload12").value!="") {document.getElementById("dropMouse").classList.toggle("show"); closeDropdown("dropMouse");} else {warningEmpty("file");}'><i class="fa fa-mouse-pointer"></i> Mouse</a>
        <div class="dropdown-content" id="dropMouse">
          <a href="javascript:if (_('imageArea').innerHTML!='') {addAllLanguages('[mouse show]');} else {warningEmpty('slide');}">Show the mouse</a>
          <a href="javascript:if (_('imageArea').innerHTML!='') {addAllLanguages('[mouse hide]');} else {warningEmpty('slide');}">Hide the mouse</a>
          <a href="javascript:if (_('imageArea').innerHTML!='') {getMousePosition();} else {warningEmpty('slide');}">Move the mouse</a>
          <a href="javascript:if (_('imageArea').innerHTML!='') {addParameters('[mouse size toReplace]');} else {warningEmpty('slide');}">Change the mouse size</a>
          <a href="javascript:if (_('imageArea').innerHTML!='') {addParameters('[mouse shape toReplace]');} else {warningEmpty('slide');}">Change the mouse shape</a>
        </div>
      </li>
      <li class="dropdown">
        <a title="Add a notification in your capsule (URL or text)." class="dropbtn" onclick='if (_("fileToUpload12").value!="") {document.getElementById("dropNotification").classList.toggle("show"); closeDropdown("dropNotification");} else {warningEmpty("file");}'><i class="fa fa-comment"></i> Notification</a>
        <div class="dropdown-content" id="dropNotification">
          <a href="javascript:if (_('imageArea').innerHTML!='') {addParameters('[resource toReplace]');} else {warningEmpty('slide');}">Open a window with the url</a>
          <a href="javascript:if (_('imageArea').innerHTML!='') {addParameters('[notification toReplace]');} else {warningEmpty('slide');}">Open a window with the text</a>
        </div>
      </li>
      <li class="dropdown">
        <a title="Global options for capsule (aspect ratio, delay between each slides, licence)." class="dropbtn" onclick='if (_("fileToUpload12").value!="") {document.getElementById("dropOptions").classList.toggle("show"); closeDropdown("dropOptions");} else {warningEmpty("file");}'><i class="fa fa-cogs"></i> Options</a>
        <div class="dropdown-content" id="dropOptions">
          <a href="javascript:addOptions('[ratio]')">Aspect Ratio</a>
          <a href="javascript:addOptions('[delay]')">Delay</a>
          <a href="javascript:addOptions('[licence]')">Licence</a>
        </div>
      </li>
      <li class="dropdown">
        <a title="Download your new capsule or just the speech." class="dropbtn" onclick='if (_("fileToUpload12").value!="") {document.getElementById("dropDownload").classList.toggle("show"); closeDropdown("dropDownload");} else {warningEmpty("file");}'><i class="fa fa-download"></i> Download</a>
        <div class="dropdown-content" id="dropDownload">
          <a href="javascript:CreateNewPresentation()">Download complete capsule</a>
          <a href="javascript:DownloadNewSpeech()">Download speech</a>
          <!-- <a href="javascript:CreateNewPPTX()">Download PPTX presentation</a> -->
        </div>
      </li>
      <li class="dropdown">
        <a title="Help with the capsule editor." class="dropbtn" onclick="toolInformation()" ><i class="fa fa-info"></i> Help</a>
      </li>
    </ul>
    <div id="FS-editanim" onclick="GoFSEditAnim();" title="Toggle fullscreen mode"><i class="fa fa-expand" style="float: right;"></i></div>

    <!-- <br> A modifier pour validation RGAA : pas deux "br" à la suite -->
    <br>
    <!-- <div style="display: none;">
    <label for="fullscreenon">Fullscreen ? </label>
    <input type="checkbox" id="fullscreenon" >
    </div> -->
    <!-- <table id="tableEdit" aria-describedby="tabledesc" align="center" style="width: 100%; height:100%; border-collapse: collapse;" border="1"> -->
    <table id="tableEdit" aria-describedby="tabledesc">
    <tr>
      <th scope="col">Slide thumbnails</th>
      <th scope="col">Slide</th>
    </tr>
      <tr>
        <td rowspan="2" style="width: 15%; height:100%">
          <!-- <th scope="col">Slides</th> -->
          <div id="notebookcompleteformatted" style="border: 1px; height: 100%; overflow: auto;"></div>
        </td>
        <td style="width: 85%; height:400px">
          <div id="mouse" hidden>+</div>
          <div id="imageArea" class="container-img" style="text-align: center"></div>
          <!-- <div id="imageArea"></div> -->
        </td>
      </tr>
      <tr>
      <td style="width: 85%; height:200px; text-align:center">
        <div style="text-align:center" id="speechLanguage"></div>
        <div id="container" class="container">
          <!-- <div id="line-numbers" class="container__lines"></div> -->
          <textarea aria-label="Slide notes" title="Slide notes" onkeyup="getEvent(event)" onchange="updateNotes();" id="speechArea" class="container__textarea" style="overflow-y: scroll; resize: none;"></textarea>
        </div>
      </td>
      </tr>
    </table>
    <div id="DesiredDivID" style="height: auto; width: auto"></div>
    <div id="dialog-message" class="dialog-window" title="Information">
      <p>
        It's a tool for editing your capsule. 
        For each slide and language, you can edit and add notes. 
        You can also add actions (pause, move mouse, add a notification, etc.) to slides, and you can change capsule options (delay between slides, image ratio, add license). 
        All you have to do is choose the action or the option you want. 
      </p>
      <p>
        Once you've finished modifying your presentation, you can :
        <ul>
          <li> Download the complete capsule, giving you a ready-to-use HTML file. </li>
          <li> Download the speech: you'll get a TXT file with notes in the original language only (so only modifications made to this language will be taken into account). You can then re-inject this TXT into a PPTX or PDF using our conversion tools. </li>
        </ul>
      </p>
      
      <div id="menuHelp"> <h4>More help : </h4>
        <ul>
          <li> Note that if you modify the parameters of a keyword by hand, this will be reflected in all languages, except for the [notification] keyword.</li>
          <li> If you add a keyword by hand, it will not be reflected in the other languages. We recommend that you always use the menu to add them.</li>
          <li> Pay attention to the position of the cursor when adding a keyword, because if you add it in the wrong place then you will have to make the change for each language. The keyword is placed on the cursor line.</li>
        </ul>
        <ul>
          <li> It is not possible to cut or paste text.</li>
          <li> It is not possible to delete a line, only the text and leave a blank line. If you delete text, remember to do so for each language.</li>
          <li> When you add text, it is not added in the other languages. It's up to you to do this manually.</li>
          <li> When you add text to an empty line, make sure that it is also empty for all languages. If this is not the case, the lines may be displaced and future keywords may not be inserted where you want them.</li>
        </ul>
        <div id=helpPause>
          <h4>Pause</h4>
          <div>
            <b>Pause capsule :</b> pause the capsule as if you were pressing a pause button.
            <br><b>Pause for n milliseconds :</b> incorporate a pause of a certain duration.
          </div>
        </div>
        <div id=helpMouse>
          <h4>Mouse</h4>
          <div>
            <p>
              There's no need to set the size and shape of the mouse on each slide if you don't want to change these settings compared with the previous slides.
            </p>
            <b>Show the mouse :</b> display mouse pointer.
            <br><b>Hide the mouse :</b> hide mouse pointer.
            <br><b>Move the mouse :</b> move the mouse pointer to position (X, Y). X and X are expressed as percentages of the image. (0,0) is top left and (100,100) is bottom right of the slide image. There may be a gap between the cursor and the click in the capsule editor. This is due to the size of the different screens. The cursor is only there as an indication, it is the position of the click that is taken into account.
            <br><b>Change the mouse size :</b> change mouse pointer size (default 1).
            <br><b>Change the mouse shape :</b> modify the shape of the mouse pointer with predefined shapes.
          </div>
        </div>
        <div id=helpNotification>
          <h4>Notification</h4>
          <div>
            <b>Open a window with the url :</b> open a notification window (on the left) with a link to the resource.
            <br><b>Open a window with the text :</b> open a notification window (on the right) displaying the message. The text in this pop-up is added and translated into all languages.
          </div>
        </div>
        <div id=helpNotification>
          <h4>Notification</h4>
          <div>
            <b>Open a window with the url :</b> open a notification window (red on the left) with a link to the resource.
            <br><b>Open a window with the text :</b> open a notification window (orange on the right) displaying the message.
          </div>
        </div>
        <div id=helpOptions>
          <h4>Options</h4>
          <div>
            <b>Aspect Ratio :</b> change slide image format (default 16/10).
            <br><b>Delay :</b> add a delay between slides in tenth of seconds (hence, a value of 10 means 1 second).
            <br><b>Licence :</b> add a licence.
          </div>
        </div>
      </div>
    </div>
    <div id="dialog-warning" class="dialog-window" title="Warning"></div>
    <div id="dialog-all-lang" class="dialog-window" title="Add to all languages ?"></div>
    <div id="dialog-download" class="dialog-window" title="Download"></div>
    <div id="mouse-position" title="Select the position">
    </div>
    <div id="commandKeyword" style="display: none;"></div>
    <div id="cursor-position" style="display: none;"></div>
    <div id="mouse-shape" style="display: none;">+</div>
    <div id="keywordParameter" title="Parameter :" class="dialog-window"></div>
    <div id="ratio-select" style="display: none;">16/10</div>
    <div id="keepratio-select" style="display: none;">X</div>
    <div id="delay-select" style="display: none;">0</div>
    <div id="licence-select" style="display: none;"></div>
    <div id="dialog-ratio" class="dialog-window">
      <label for="aspectratioEditAnim">Aspect ratio : </label>
      <input class="jqueryselect inputsmallscreens" id="aspectratioEditAnim" name="aspectratio" list="listaspectratio" value="16/10" onchange="if (this.value.trim()=='') this.value='16/10';"><br>
      <label for="keepaspectratioEditAnim">Keep Aspect ratio ? </label>
      <input type="checkbox" aria-label="Keep Aspect ratio ?" title="Keep Aspect ratio ?"  id="check-keep-ratio" checked onclick="if (this.checked) {document.getElementById('keepaspectratioEditAnim').value ='X'; document.getElementById('cbtextEditAnim').innerHTML ='Yes !';} else {document.getElementById('keepaspectratioEditAnim').value =''; document.getElementById('cbtextEditAnim').innerHTML ='No.'; }" style="font-size:2em;">&nbsp;<span id="cbtextEditAnim">Yes !</span>
      <input class="jqueryselect" id="keepaspectratioEditAnim" name="keepaspectratio"  style="width: 10px; display: none;" value="X" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"><br>
    </div>
    <div id="dialog-delay" class="dialog-window">
      <label title="Extra delay between two slides in tenth of seconds (hence, a value of 10 means 1 second)." for="extradelayEditAnim">Extra delay <span class="hidesmallscreens"> between slides (in tenth of seconds): </span></label>
      <input id="extradelayEditAnim" name="extradelay" list="listdelays" value="0" onchange="if (this.value.trim()=='') this.value='0';"  class="inputsmallscreens"><br>
    </div>
    <div id="dialog-licence" class="dialog-window">
      <label for="licence">Would you like to add a licence ? </label>
      <select id="licenceEditAnim" name="Licence"  class="inputsmallscreens">
      <option value="">No licence</option>
      <option value="CC0">CC0 1.0 Universal</option>
      <option value="CCBY">CC BY 4.0</option>
      <option value="CCBYSA">CC BY-SA 4.0</option>
      <option value="CCBYNC">CC BY-ND 4.0</option>
      <option value="CCBYND">CC BY-NC 4.0</option>
      <option value="CCBYNCSA">CC BY-NC-SA 4.0</option>
      <option value="CCBYNCND">CC BY-NC-ND 4.0</option>
      </select>
    </div>

    </form>
  </div>
</div>

</div>
</body>
<script>
$( function() {
    $( "#tabs" ).tabs({
      
    });
    $("body").tooltip();
    $( "#menu" ).accordion({heightStyle: 'panel', header: "> div > h3"});
    $( "#menuHelp" ).accordion({heightStyle: 'panel', header: "> div > h4", collapsible: true, active: false});
} );

</script>
</html>
