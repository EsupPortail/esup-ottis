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
if ($WhichTranslator == "DEEPLFREE") {
  exec($proxy." curl -q https://api-free.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN,$res);
} else {
  exec($proxy." curl -q https://api.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN,$res);
}

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
    <script src="../js/teacher-func.js"></script>
<style type="text/css">

@import url('https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap');

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
//  display: none;
}



input[type="submit"], .mybutton {
  padding: 10px;
  background-color: rgb(25,190,40);
  color: white;
  border-color: rgb(25,190,40);
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

function downloadFile(filePath){
    var link=document.createElement('a');
    link.href = filePath;
    link.download = filePath.substr(filePath.lastIndexOf('/') + 1);
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
  <div style="position: absolute; top: 0px; right: 250px;">utilisation mensuelle de DeepL = <?php echo "$percentage_DEEPL"; ?>%</div>
<div id="menu">
  <h3>Create web animation from pptx (minimal App without translations)</h3>
  <div>
  <form id="Form1" action="" onsubmit="return false;" method="post" enctype="multipart/form-data">
    <label for="fileToUpload1">Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload1" accept=".pptx" max-size="50000"/></label><br/>
    <input type="hidden" id="ProgName1" name="ProgramName" value="CreateApp/CreateAppFromPPTX.php">
    <input type="hidden" id="Random1" name="randompath" value=""/>
    <input type="hidden" id="Extension1" name="Extension" value="html"/>
    <!--<input id="App1" type="submit" value="Create" name="submit"/>-->
    <button id="App1" value="Create" onclick="if (_('fileToUpload1').value!='') {this.innerHTML=''; SubmitJob(1,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message1"></p>
    </form>
  </div>
  <h3>Create web animation from pdf+txt (minimal App without translations)</h3>
  <div>
  <form id="Form2" action="" onsubmit="return false;" method="post" enctype="multipart/form-data">
    Select PDF file : 
    <input type="file" name="fileToUpload" id="fileToUpload2" accept=".pdf" max-size="50000"/><br/>
    <input type="hidden" id="ProgName2" name="ProgramName" value="CreateApp/CreateAppFromPDF.php"> 
    <input type="hidden" id="Random2" name="randompath" value=""/>
    <input type="hidden" id="Extension2" name="Extension" value="html"/>
    Select SPEECH file (.txt): 
    <input type="file" name="fileToUploadTXT" id="fileToUploadTXT2" accept=".txt" max-size="50000"/><br/>
    <button id="App2" value="Create" onclick="if (_('fileToUpload2').value!='') {this.innerHTML=''; SubmitJob(2,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message2"></p>
    </form>
  </div>
  <h3>Create web animation from pptx (Complete)</h3>
  <div>
  <form id="Form3" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload3" accept=".pptx" max-size="50000"/><br/>
    <input type="hidden" name="ProgramName" value="CreateApp/CreateAppFromPPTX_complete.php">
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
    <input id="extradelay1" name="extradelay" list="listdelays" value="0" onchange="if (this.value.trim()=='') this.value='0'" />

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
  
 </table>
    <input type="hidden" id="Random3" name="randompath" value=""/>
    <input type="hidden" id="Extension3"  name="Extension" value="html"/>
    <!--<input id="App1" type="submit" value="Create" name="submit"/>-->
    <button id="App3" value="Create" onclick="if (_('fileToUpload3').value!='') {this.innerHTML=''; SubmitJob(3,'Create');} else {alert('You must provide a file and choose a delay first !');}" class="mybutton">Create</button>
    <p id="Message3"></p>
    </form>
  </div>
  <h3>Create web animation from pdf+txt (Complete)</h3>
  <div>
  <form id="Form4" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
  
    Select PDF file :   <input type="file" name="fileToUpload" id="fileToUpload4" accept=".pdf" max-size="50000"><br/>
    Select SPEECH file (.txt): <input type="file" name="fileToUploadTXT" id="fileToUploadTXT4" accept=".txt" max-size="50000"><br/>
    <input type="hidden" id="ProgName4" name="ProgramName" value="CreateApp/CreateAppFromPDF_complete.php">
    <input type="hidden" id="Random4" name="randompath" value=""/>
    <input type="hidden" id="Extension4" name="Extension" value="html"/>


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
  
</table>

    <button id="App4" value="Create" onclick="if (_('fileToUpload4').value!='') {this.innerHTML=''; SubmitJob(4,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message4"></p>
    </form>
  </div>
    <h3>Translate pptx</h3>
  <div>
  <form id="Form5" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload5" accept=".pptx" max-size="50000"><br/>
    <input type="hidden" id="ProgName5" name="ProgramName" value="TranslatePPTX/TranslatePPTX.php">
    <input type="hidden" id="Random5" name="randompath" value=""/>
    <input type="hidden" id="Extension5" name="Extension" value="zip"/>
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
    <button id="App5" value="Create" onclick="if (_('fileToUpload5').value!='') {this.innerHTML=''; SubmitJob(5,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message5"></p>
    </form>
  </div>
  <h3>Translate subtitles SRT file</h3>
  <div>
  <form id="Form6" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select SRT file : 
    <input type="file" name="fileToUpload" id="fileToUpload6" accept=".srt,.vtt" max-size="5000"><br/>
    <input type="hidden" id="ProgName6" name="ProgramName" value="TranslateSRT/TranslateSRT.php">
    <input type="hidden" id="Random6" name="randompath" value=""/>
    <input type="hidden" id="Extension6" name="Extension" value="zip"/>
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
    <button id="App6" value="Create" onclick="if (_('fileToUpload6').value!='') {this.innerHTML=''; SubmitJob(6,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message6"></p>
    </form>
  </div>
  <h3>Convert pdf+txt to pptx</h3>
  <div>
  <form id="Form7" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select PDF file :   <input type="file" name="fileToUpload" id="fileToUpload7" accept=".pdf" max-size="50000"><br/>
    Select SPEECH file (.txt): <input type="file" name="fileToUploadTXT" id="fileToUploadTXT7" accept=".txt" max-size="50000"><br/>
    <input type="hidden" id="ProgName7" name="ProgramName" value="ConvertPDF/ConvertPDFtoPPTX.php">
    <input type="hidden" id="Random7" name="randompath" value=""/>
    <input type="hidden" id="Extension7" name="Extension" value="zip"/>
    <button id="App7" value="Create" onclick="if (_('fileToUpload7').value!='') {this.innerHTML=''; SubmitJob(7,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message7"></p>
    </form>
  </div>

  <h3>Convert pptx+txt to pptx</h3>
  <div>
  <form id="Form8" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select PPTx file :   <input type="file" name="fileToUpload" id="fileToUpload8" accept=".pptx" max-size="50000"><br/>
    Select SPEECH file (.txt): <input type="file" name="fileToUploadTXT" id="fileToUploadTXT8" accept=".txt" max-size="50000"><br/>
    <input type="hidden" id="ProgName8" name="ProgramName" value="ConvertPPTX/ConvertPPTX_TXT_toPPTX.php">
    <input type="hidden" id="Random8" name="randompath" value=""/>
    <input type="hidden" id="Extension8" name="Extension" value="zip"/>
    <button id="App8" value="Create" onclick="if (_('fileToUpload8').value!='') {this.innerHTML=''; SubmitJob(8,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message8"></p>
    </form>
  </div>

  <h3>Extract speech from pptx</h3>
  <div>
  <form id="Form9" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload9" accept=".pptx" max-size="50000"><br/>
    <input type="hidden" id="ProgName9" name="ProgramName" value="ExtractSpeech/ExtractSpeechFromPPTX.php">
    <input type="hidden" id="Random9" name="randompath" value=""/>
    <input type="hidden" id="Extension9" name="Extension" value="txt"/>
    <button id="App9" value="Create" onclick="if (_('fileToUpload9').value!='') {this.innerHTML=''; SubmitJob(9,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message9"></p>
    </form>
  </div>

  <h3>Translate XLIFF</h3>
  <div>
  <form id="Form10" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select XLF file : 
    <input type="file" name="fileToUpload" id="fileToUpload10" accept=".xlf" max-size="50000"><br/>
    <input type="hidden" id="ProgName10" name="ProgramName" value="TranslateXLIFF/TranslateXLIFF.php">
    <input type="hidden" id="Random10" name="randompath" value=""/>
    <input type="hidden" id="Extension10" name="Extension" value="zip"/>
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
    <button id="App10" value="Create" onclick="if (_('fileToUpload10').value!='') {this.innerHTML=''; SubmitJob(10,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message10"></p>
    </form>
  </div>

  <h3>Translate SCORM</h3>
  <div>
  <form id="Form11" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select ZIP file : 
    <input type="file" name="fileToUpload" id="fileToUpload11" accept=".zip" max-size="50000000"><br/>
    <input type="hidden" id="ProgName11" name="ProgramName" value="TranslateSCORM/TranslateSCORM.php">
    <input type="hidden" id="Random11" name="randompath" value=""/>
    <input type="hidden" id="Extension11" name="Extension" value="zip"/>
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
    <button id="App11" value="Create" onclick="if (_('fileToUpload11').value!='') {this.innerHTML=''; SubmitJob(11,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message11"></p>
    </form>
    </div>

  <h3>Edit Animation</h3>
  <div>
  <!-- <form action="" method="post" enctype="multipart/form-data">
    <label for="ppt_presentation2">Powerpoint presentation : </label>
    <input type="file" name="ppt_presentation2" id="ppt_presentation2" onchange="SlideNotesTools('')" accept=".html" max-size="50000" />
    <br/>
    <progress id="progressBar" value="0" max="100" style="width:300px;"></progress>
    <tr>
    <td style="vertical-align: top;">
    <input type="submit" value="Download" name="submit"/>
    </td></tr>
  </form> -->

  <label for="ppt_presentation">Presentation in HTML: </label>
	<input type="file" name="ppt_presentation" id="ppt_presentation" onchange="SlideNotesTools(true)" accept=".html" max-size="50000" />
  <span style="display:none">
  <label for="tobesaid" title="Portion of text inserted between each slides (eq. Next slide...)">Slide transition : </label>
  <input type="text" id="tobesaid" value="">
  </span>
	<br/>
	<progress id="progressBar" value="0" max="100" style="width:300px;"></progress>
	<div id="notebookcompleteformatted" style="border: solid 1px; height: 600px; overflow: auto;"> </div>
    
    <!-- <form action="EditAnim.php" method="post" enctype="multipart/form-data">
    <label for="fileToUpload12">Select HTML file : 
    <input type="file" name="fileToUpload" id="fileToUpload12" accept=".html" max-size="50000"/></label><br/>
    <input type="submit" value="Create" name="submit"/>
    </form> -->

    <form id="Form12" onsubmit="return false;" action="" method="post" enctype="multipart/form-data">
    Select HTML file : 
    <input type="file" name="fileToUpload" id="fileToUpload12" accept=".html" max-size="50000000"><br/>
    <input type="hidden" id="ProgName12" name="ProgramName" value="EditAnim/EditAnim.php">
    <input type="hidden" id="Random12" name="randompath" value=""/>
    <input type="hidden" id="Extension12" name="Extension" value="txt"/>
    <button id="App12" value="Create" onclick="if (_('fileToUpload12').value!='') {this.innerHTML=''; SubmitJob(12,'Create');} else {alert('You must provide a file first !');}" class="mybutton">Create</button>
    <p id="Message12"></p>
    </form>

  </div>



  <?php

    $ftcontent="  <h3 title=\"You must be connected to the Nantes University network to use this tool.\">Audio/video file transcription</h3>\n  <div>\n    <iframe width=\"80%\" height=\"500px\" style=\"border: none;\" src=\"https://172.26.98.138/\"></iframe>\n  </div>\n  ";
    $ftcontentalt="  <h3>Audio/video file transcription with Scribe-CEMEA free tool</h3>\n  <div>\n    <iframe width=\"80%\" height=\"500px\" style=\"border: none;\" src=\"https://scribe.cemea.org/\"></iframe>\n  </div>\n  ";

    if (BeginWith($_SERVER['REMOTE_ADDR'],"172.26")) echo $ftcontent; else echo $ftcontentalt;
    //echo $ftcontent;
    //  strpos($_SERVER['SERVER_NAME'],"172.26.98.138") !== false) echo $ftcontent;
  ?>
  <h3>External vidéo subtitle editor (Nikse.dk)</h3>
  <div>
  <iframe width="100%" height="800px" style="border: none;" src="https://www.nikse.dk/subtitleedit/online"></iframe>
  </div>
</div>
</body>
<script>
$("body").tooltip();
$( "#menu" ).accordion({heightStyle: 'panel'});
</script>
</html>
