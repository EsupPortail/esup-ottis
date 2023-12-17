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
if ($uid == "") $uid="e18XXX";
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

if (! isset($external)) $external=false;
if ($external) $uid="external";

//if ($_SERVER['PHP_AUTH_USER'] == "bourdon-j" && isset($_GET["fakeuser"])) {$fake=true; $uid=$_GET["fakeuser"];} else {$fake=false;}
$fake=false;

AuthenticateNotStudent($uid);


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

$res=array();
exec($proxy." curl https://api.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN,$res);

$Stats_DEEPL = json_decode($res[0],true);
if (isset($Stats_DEEPL["character_count"])) {
  $percentage_DEEPL = ceil(10000*$Stats_DEEPL["character_count"]/($Stats_DEEPL["character_limit"]+0.00000001))/100;
} else $percentage_DEEPL = 0;
?>
<!doctype html>
<html lang="fr">
<head>
	<title>Slide Manipulation Tools</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="../jquery/jquery-ui.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../favicon.png" />
    <script src="../jquery/jquery.js"></script>
    <script src="../jquery/jquery-ui.min.js"></script>
<style type="text/css">

@import url('https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap');
select, .jqueryselect, input[type="text"], textarea, button, input[type="file"], input[type="button"], input[type="submit"] {
  border-radius: 7px;
  margin: 1px;
  padding: 2px;
}

input[type="button"], input[type="submit"] {
cursor: pointer;
}

label {
  font-size: 1.1em;
  cursor: pointer;
}

label input[type="file"] {
//  display: none;
}


input[type="submit"] {
  padding: 10px;
  background-color: rgb(25,190,40);
  color: white;
  border-color: rgb(25,190,40);
  font-size: 1.1em;
}

input[type="submit"]:hover {
  background-color: rgba(25,190,40,0.7);
  border-color: rgba(25,190,40,0.7);
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
  position: fixed;
  top:0px;
  right:0px;
  z-index:1000;
}
</style>
<script type="text/javascript">

<?php
echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";
?>
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

function SubmitJobCreateAnim1() {
  // Step 1
  if (_("App1").value == "Create") {
    _("App1").value = "Processing";
    _("App1").disabled = true;
    _("Random1").value = MD5((new Date()).getTime());
    console.log("Randompath = "+_("Random1").value);
    var form = new FormData();
    form.append("randompath", _("Random1").value);
    form.append("ProgramName", _("ProgName1").value);
    form.append("fileToUpload", _("fileToUpload1"));
    form.enctype='multipart/form-data';
    console.log(form);
	  var request = new XMLHttpRequest();
	  request.open("POST", "SendJob.php");
	  request.send(form);
    request.onload = function(event) {
		  var rep = request.responseText;
      console.log("Sent = "+rep);
      setTimeout(SubmitJobCreateAnim1,10000);
    }
  }
  if (_("App1").value == "Processing") {
    var form = new FormData();
    form.append("randompath", _("Random1").value);
    form.append("ProgramName", _("ProgName1").value);
    form.enctype='multipart/form-data';
	  var request = new XMLHttpRequest();
	  request.open("POST", "isReady.php");
	  request.send(form);
    request.onload = function(event) {
		  var rep = request.responseText;
      console.log("isReady = "+rep);
      if (rep == "0") {
        // Ready to download
        _("App1").value = "Ready to download";
        _("App1").disabled = false;
      } else {
        setTimeout(SubmitJobCreateAnim1,10000);
      }
    }
  }
  if (_("App1").value == "Ready to download") {
    _("App1").value = "Create";
    window.open("tmp/output_"+_("Random1").value+".html");
  }
}

</script>
</head>
<body onload="Init();">
	<h1>Slide Manipulation Tools</h1>
  <div id="logo"><img src="logo-ufr-sciences.jpg" height="80"/></div>
  <datalist id="listaspectratio">
    <option value="16/10"/>
    <option value="16/9"/>
    <option value="4/3"/>
    <option value="1/1"/>
  </datalist>
  <datalist id="listdelays">
    <option value="0"/>
    <option value="5"/>
    <option value="10"/>
    <option value="300"/>
    <option value="600"/>
    <option value="1200"/>
  </datalist>
  <datalist id="listlength">
    <option value="80"/>
    <option value="100"/>
    <option value="120"/>
    <option value="150"/>
    <option value="175"/>
    <option value="200"/>
  </datalist>
 <div>Important : Ces outils proposés à titre expérimental utilisent des ressources partagées avec l’outil de traduction en direct (même serveur web et abonnement mutualisé pour le service de traduction (nb: utilisation mensuelle de DeepL = <?php echo "$percentage_DEEPL"; ?>%)). A noter, les traductions de fichiers pptx sont limitées afin de permettre un partage de ces ressources. Seuls les 5000 premiers caractères des présentations sont traduits.<!--Afin de ne pas impacter ce service, nous vous demandons de les utiliser prioritairement en dehors des heures d’enseignement. En outre, si vous souhaitez obtenir les traductions dans plus de deux langues, nous vous demandons de ne pas utiliser ces outils mais de nous transmettre votre demande car nous avons la possibilité de la traiter sans impacter les ressources partagées.--></div>
<div id="menu">
  <h3>Create web animation from pptx (minimal App without translations)</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
    <label for="fileToUpload1">Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload1" accept=".pptx" max-size="50000"/></label><br/>
    <input type="hidden" id="ProgName1" name="ProgramName" value="CreateAppFromPPTX.php">
    <input type="hidden" id="Random1" value=""/>
    <!--<input id="App1" type="submit" value="Create" name="submit"/>-->
    <input id="App1" type="button" value="Create" onclick="SubmitJobCreateAnim1();" />
    </form>
  </div>
  <h3>Create web animation from pdf+txt (minimal App without translations)</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
    Select PDF file : 
    <input type="file" name="fileToUpload" id="fileToUpload2" accept=".pdf" max-size="50000"/><br/>
    <input type="hidden" name="ProgramName" value="CreateAppFromPDF.php">
    Select SPEECH file (.txt): 
    <input type="file" name="fileToUploadTXT" id="fileToUploadTXT2" accept=".txt" max-size="50000"/><br/>
    <input type="submit" value="Create" name="submit"/>
    </form>
  </div>
  <h3>Create web animation from pptx (Complete)</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload3" accept=".pptx" max-size="50000"><br/>
    <input type="hidden" name="ProgramName" value="CreateAppFromPPTX_complete.php">
  <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage1" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage1"  name="outputlanguage[]" multiple rows="5"></select>
  </td></tr>
  <tr><td colspan="2"><b>Advanced options</b></td></tr>
  <tr>
  <td style="vertical-align: top;">
    Translate slides in ? </td>
  <td>
  <select id="translatelanguage1" name="translatelanguage" value="">
  <option value="">No translation</option>
  </select>
  </td></tr>
     <tr>
  <td style="vertical-align: top;">
    Aspect ratio : </td>
  <td>
  <input class="jqueryselect" id="aspectratio1" name="aspectratio" list="listaspectratio" value="16/10"/>
 </td></tr>
 <tr>
    <td style="vertical-align: top;">
    Keep Aspect ratio ? </td>
  <td>
    <input type="checkbox" checked onclick="if (this.checked) {document.getElementById('keepaspectratio1').value ='X'; document.getElementById('cbtext1').innerHTML ='Yes !';} else {document.getElementById('keepaspectratio1').value =''; document.getElementById('cbtext1').innerHTML ='No.'; }" style="font-size:2em;"/>&nbsp;<span id="cbtext1">Yes !</span>
    <input class="jqueryselect" id="keepaspectratio1" name="keepaspectratio"  style="width: 10px; display: none;" value="X" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"/>
  </td></tr>
  <tr>
    <td style="vertical-align: top;">
    Extra delay between slides (in tenth of seconds): </td>
  <td>
    <input id="extradelay1" name="extradelay" list="listdelays" value="0"/>

  </td></tr>
  
  <tr>
    <td>
    <input type="checkbox" name="longrun" value="long"/> Offline mode (for long presentations)
    </td>
  </tr>
 </table>
    <input type="submit" value="Create" name="submit">
    </form>
  </div>
  <h3>Create web animation from pdf+txt (Complete)</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
  
    Select PDF file :   <input type="file" name="fileToUpload" id="fileToUpload" accept=".pdf" max-size="50000"><br/>
    Select SPEECH file (.txt): <input type="file" name="fileToUploadTXT" id="fileToUploadTXT" accept=".txt" max-size="50000"><br/>
    <input type="hidden" name="ProgramName" value="CreateAppFromPDF_complete.php">


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
  <td><select id="outputlanguage2"  name="outputlanguage[]" multiple rows="5"></select>
  </td></tr>
  <tr><td colspan="2"><b>Advanced options</b></td></tr>
     <tr>
  <td style="vertical-align: top;">
    Aspect ratio : </td>
  <td>
  <input class="jqueryselect" id="aspectratio2" name="aspectratio" list="listaspectratio" value="16/10"/>
 </td></tr>
 <tr>
    <td style="vertical-align: top;">
    Keep Aspect ratio ? </td>
  <td>
  
    <input type="checkbox" checked onclick="if (this.checked) {document.getElementById('keepaspectratio2').value ='X'; document.getElementById('cbtext2').innerHTML ='Yes !';} else {document.getElementById('keepaspectratio2').value =''; document.getElementById('cbtext2').innerHTML ='No.'; }" style="font-size:2em;"/>&nbsp;<span id="cbtext2">Yes !</span>
    <input id="keepaspectratio2" name="keepaspectratio"  style="width: 10px; display: none;" value="X" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"/>
  </td></tr>
  <tr>
    <td style="vertical-align: top;">
    Extra delay between slides (in tenth of seconds): </td>
  <td>
    <input class="jqueryselect" id="extradelay2" name="extradelay" list="listdelays" value="0"/>

  </td></tr>
 </table>

    <input type="submit" value="Create" name="submit">
    </form>
  </div>
    <h3>Translate pptx</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload" accept=".pptx" max-size="50000"><br/>
    <input type="hidden" name="ProgramName" value="TranslatePPTX/TranslatePPTX.php">
    <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage3" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage3"  name="outputlanguage[]" multiple rows="5"></select>
  </td></tr>
  <tr>
    <td style="vertical-align: top;">
    Translate slide notes too ? </td>
  <td>
  <input type="checkbox" onclick="if (this.checked) {document.getElementById('slidenotes3').value ='X'; document.getElementById('cbtext3').innerHTML ='Yes !';} else {document.getElementById('slidenotes3').value =''; document.getElementById('cbtext3').innerHTML ='No.'; }" style="font-size:2em;"/>&nbsp;<span id="cbtext3">No.</span>
  <input id="slidenotes3" name="slidenotes"  style="width: 10px; display: none;" value="" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"/>
  </td></tr>
 </table>
    <input type="submit" value="Translate" name="submit">
    </form>
  </div>
  <h3>Translate subtitles SRT file</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
    Select SRT file : 
    <input type="file" name="fileToUpload" id="fileToUpload" accept=".srt,.vtt" max-size="5000"><br/>
    <input type="hidden" name="ProgramName" value="TranslateSRT/TranslateSRT.php">
    <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage4" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><!--<select id="outputlanguage4"  name="outputlanguage[]" multiple rows="5"></select>--><select id="outputlanguage4"  name="outputlanguage[]" rows="5"></select>
  </td></tr>
  <tr>
  <td style="vertical-align: top;">
    Maximum subtitle length : </td>
  <td>
  <input type="number" name="wraplength" id="wraplength" min="50" max="200" value="80" step="10"/>
  </td></tr>
  <tr>
  <td style="vertical-align: top;">
  My subtitles do not have any punctuation : </td>
  <td><input type="checkbox" name="nopunct" value="Yes" />
  </td></tr>
 </table>
    <input type="submit" value="Translate" name="submit">
    </form>
  </div>
  <h3>Convert pdf+txt to pptx</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">  
    Select PDF file :   <input type="file" name="fileToUpload" id="fileToUpload" accept=".pdf" max-size="50000"><br/>
    Select SPEECH file (.txt): <input type="file" name="fileToUploadTXT" id="fileToUploadTXT" accept=".txt" max-size="50000"><br/>
    <input type="hidden" name="ProgramName" value="ConvertPDF/ConvertPDFtoPPTX.php">
    <input type="submit" value="Convert" name="submit">
    </form>
  </div>

  <h3>Convert pptx+txt to pptx</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">  
    Select PPTx file :   <input type="file" name="fileToUpload" id="fileToUpload" accept=".pptx" max-size="50000"><br/>
    Select SPEECH file (.txt): <input type="file" name="fileToUploadTXT" id="fileToUploadTXT" accept=".txt" max-size="50000"><br/>
    <input type="hidden" name="ProgramName" value="ConvertPPTX/ConvertPPTX_TXT_toPPTX.php">
    <input type="submit" value="Convert" name="submit">
    </form>
  </div>

  <h3>Extract speech from pptx</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload" accept=".pptx" max-size="50000"><br/>
    <input type="hidden" name="ProgramName" value="ExtractSpeech/ExtractSpeechFromPPTX.php">
    <input type="submit" value="Extract" name="submit">
    </form>
  </div>

  <h3>Translate XLIFF</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
    Select XLF file : 
    <input type="file" name="fileToUpload" id="fileToUpload" accept=".xlf" max-size="50000"><br/>
    <input type="hidden" name="ProgramName" value="TranslateXLIFF/TranslateXLIFF.php">
    <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage5" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage5"  name="outputlanguage[]" rows="5"></select>
  </td></tr>

 </table>
    <input type="submit" value="Translate" name="submit">
    </form>
  </div>

  <h3>Translate SCORM</h3>
  <div>
  <form action="WriteToDatabase.php" method="post" enctype="multipart/form-data">
    Select ZIP file : 
    <input type="file" name="fileToUpload" id="fileToUpload" accept=".zip" max-size="50000"><br/>
    <input type="hidden" name="ProgramName" value="TranslateSCORM/TranslateSCORM.php">
    <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage6" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage6"  name="outputlanguage[]" rows="5"></select>
  </td></tr>

 </table>
    <input type="submit" value="Translate" name="submit">
    </form>
  </div>
  
</div>
</body>
<script>
$("body").tooltip();
$( "#menu" ).accordion();
</script>
</html>
