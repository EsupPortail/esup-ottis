<?php require("./NoCache.php"); ?>
<?php

// Simplified logic for synchronize and classroomid
$synchronize = isset($_GET["SYNC"]) && !isset($_GET["ROOMID"]);
$classroomid = $_GET["ROOMID"] ?? '';

// Generer un token unique pour ce ROOMID
require_once __DIR__ . '/Security/room_tokens.php';
$roomToken = generateRoomToken($classroomid);

/*
$wooclapkey = "";
$wooclapaddress = "https://app.wooclap.com";
if (isset($_GET["WOOCLAPKEY"])) {
  $wooclapaddress = "https://www.wooclap.com/" . $_GET["WOOCLAPKEY"];
  $wooclapkey = $_GET["WOOCLAPKEY"];
}
*/

$Tcolors = array("Red", "Blue", "Orange", "Purple", "Black", "White", "Green");
$Tobjects = array("Alpaca", "Alligator", "Dragoon", "Elephant", "Lion", "Panther", "Tiger", "Zebra");
$randomname = $Tcolors[mt_rand(0, count($Tcolors)-1)] . " " . $Tobjects[mt_rand(0, count($Tobjects)-1)];

// Optimized language loading - using associative array by code like conference.php
$T_lang = array();
$fd = fopen("supportedlanguages.csv", "r");
while ($line = fgets($fd, 4096)) {
    if (trim($line) === '') continue;  // Skip empty lines
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

$theme = "flat";
if (isset($_GET["theme"])) $theme = $_GET["theme"];

// Centralized JavaScript variables
$jsVars = [
    "supportedlanguages" => $T_lang,
    "needSynchronize" => $synchronize,
    "classroomid" => $classroomid,
    //"wooclapkey" => $wooclapkey,
    //"wooclapaddress" => $wooclapaddress,
    "theme" => $theme,
    "randomname" => $randomname
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
        "styles/auditeur.css"
    ];
    
    // Add theme-specific styles
    if ($theme != "" && $theme != "flat") {
        $pageStyles[] = "jquery/{$theme}/jquery-ui.min.css";
        $pageStyles[] = "jquery/{$theme}/extras.css";
    } else {
        $pageStyles[] = "jquery/jquery-ui.min.css";
    }
    
    // Add external libraries
    $pageStyles[] = "libs/quickmenu/lib/font-awesome/css/font-awesome.min.css";
    
    // Render styles
    foreach ($pageStyles as $style) {
        echo "<link href=\"{$style}\" rel=\"stylesheet\">\n";
    }
    ?>
    
    <?php
    // Centralized scripts to be loaded before </body>
    $pageScripts = [];
    
    // Add theme-specific jQuery first so plugins and custom scripts can use it.
    if ($theme != "" && $theme != "flat") {
        $pageScripts[] = "jquery/{$theme}/external/jquery/jquery.js";
        $pageScripts[] = "jquery/{$theme}/jquery-ui.js";
    } else {
        $pageScripts[] = "jquery/jquery.js";
        $pageScripts[] = "jquery/jquery-ui.min.js";
    }
    
    $pageScripts[] = "libs/quickmenu/src/jquery.popmenu.js";
    $pageScripts[] = "/js/auditeur-def.js";
    $pageScripts[] = "/js/common-func.js";
    $pageScripts[] = "/js/chat-common.js";
    $pageScripts[] = "/js/auditeur-func.js";
    $pageScripts[] = "/js/auditeur-post.js";
    
    // Inject pageData for JavaScript
    echo "<script>window.pageData = " . json_encode($jsVars) . ";</script>\n";
    echo "<script>var ROOM_TOKEN = '" . htmlspecialchars($roomToken, ENT_QUOTES) . "';</script>\n";
    ?>
    
    <link href="styles/auditeur-specific.css" rel="stylesheet">
    <link href="styles/chat.css" rel="stylesheet">
</head>
<body onload="Init(); setTimeout(loadVoicesWhenAvailable,100);">
<main>
<h1 style="display:none;">Outils de traduction : application étudiante</h1>
</main>
<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'Isite NExT" style="width: 109px; height: 50px;"></div>
<div id="showmenu" onclick="ShowMenu();" ><img src="images/blank.png" alt="Icone du Menu ☰" id="iconeMenu"></div>
<div id="micon" style="background-color: #EE0000;" onclick="gotolive=true; CompleteSpeech(); this.style.backgroundColor='rgba(238,0,0,0.5)'; setTimeout(()=> {document.getElementById('micon').style.backgroundColor='rgba(238,0,0,1)';},300);"><i class='material-icons'>sensors</i><span style="font-size: 10px;color: white;"><br>LIVE</span></div>
<div id="repeat" onclick="Parle(document.getElementById('titre').innerHTML);" title="Repeat the last sentence"><i class='material-icons'>replay</i><span style="font-size: 10px;color: white;"><br>REPEAT</span></div>
<div id="bar2" style="border:none; position: fixed; top: 1px; left: 1px; z-index: 100000;">
           <div class="pop_ctrl" title="Open the menu dialog">-->
               <span class="fa-stack fa-lg">
                   <i class="fa fa-square fa-stack-2x fa-menu" style="color: rgb(26,189,157);"></i>
                   <i class="fa fa-bars fa-stack-1x fa-inverse fa-menu" style="font-size: 1.1em;"></i>
                 </span>
           </div>
           <ul id="demo_ul">
               <li id="lltabs-0" onclick="activetab('tabs-0')" class="demo_li"><div><i class="fa fa-cog"></i></div><div>Settings</div></li>
               <li id="lltabs-1" onclick="activetab('tabs-1')" class="demo_li"><div><i class="fa fa-comments"></i></div><div>Tchat</div></li>
               <li id="lltabs-7" onclick="activetab('tabs-7')" class="demo_li"><div><i class="fa fa-picture-o"></i></div><div>Slides</div></li>
               <li id="lltabs-2" onclick="activetab('tabs-2')" class="demo_li"><div><i class="fa fa-download"></i></div><div>Export VO</div></li>
               <li id="lltabs-3" onclick="activetab('tabs-3')" class="demo_li"><div><i class="fa fa-language" aria-hidden="true"></i></div><div>Export</div></li>
               <!-- <li id="lltabs-8" style="opacity: 0.5" onclick="if (this.style.opacity ==1) activetab('tabs-8')" class="demo_li"><div style="line-height: 70px; vertical-align: middle;"><img src="images/wooclap-ico.png" alt="Icone de l'outil WOOCLAP" width="40" style="vertical-align: middle;"></div><div>WOOCLAP</div></li> -->
               <!-- <li id="lltabs-9" style="opacity: 0.5" onclick="if (this.style.opacity ==1) activetab('tabs-9');" class="demo_li"><div><i class="fa fa-desktop"></i></div><div>Tab 1</div></li> -->
               <!-- <li id="lltabs-10" style="opacity: 0.5" onclick="if (this.style.opacity ==1) activetab('tabs-10');" class="demo_li"><div><i class="fa fa-desktop"></i></div><div>Tab 2</div></li> -->
               <!-- <li id="lltabs-11" style="opacity: 0.5" onclick="if (this.style.opacity ==1) activetab('tabs-11')" class="demo_li"><div><i class="fa fa-desktop"></i></div><div>Tab 3</div></li> -->
               <!-- <li id="lltabs-12" style="opacity: 0.5" onclick="if (this.style.opacity ==1) activetab('tabs-12')" class="demo_li"><div><i class="fa fa-desktop"></i></div><div>Tab 4</div></li> -->

               
           </ul>
       </div>
<div id="titre"><h1>Automatic translation provided by NExT OMIST Project</h1></div>

<div id="VOBanner"></div>
<div id="soustitre"></div>

<form id="myform" onsubmit="return false;">

<div id="tabs" style="border:none;">
	<ul id="bar">
		<li id="ltabs-0" onclick="activetab('tabs-0')">Informations</li>
		<li id="ltabs-1" onclick="activetab('tabs-1')">Classroom</li>
		<li id="ltabs-2" onclick="activetab('tabs-2')">Notebook</li>
		<li id="ltabs-3" onclick="activetab('tabs-3')">Output</li>
    <li id="ltabs-7" onclick="activetab('tabs-7')">Slides</li>
    <!-- <li id="ltabs-8" onclick="activetab('tabs-8')">WOOCLAP</li> -->
    <!-- <li id="ltabs-9" onclick="activetab('tabs-9');">1</li> -->
		<!-- <li id="ltabs-10" onclick="activetab('tabs-10');">2</li> -->
		<!-- <li id="ltabs-11" onclick="activetab('tabs-11');">3</li> -->
		<!-- <li id="ltabs-12" onclick="activetab('tabs-12');">4</li> -->
		<!-- <li id="ltabs-13" onclick="activetab('tabs-13');" class="wordcloud">Word Cloud</li> -->

	</ul>
	<div id="tabs-0">
    <h3 >Language and basic settings</h3>
    <div>
    <span style="display:none"><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" ></span>
<span style="display:none">Pending requests : <span id="nbrequests">0</span></span>
<span style="display:none"><label for="classroomlanguage">Classroom language :</label>
  <select id="classroomlanguage" onchange="setLanguageInput(this.value);">
  </select>
</span>
<span id="lilanguage"><label for="VoiceOutput">My language :</label>
  <select id="VoiceOutput" onchange="setLanguageOutputAndRetranslate(this.value);"  onmouseout="if (firsttime && firstclick) {resizeSlides(); document.getElementById('synthesison').checked=false; firsttime=false;}" onclick="if (firsttime) {document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='visible'; document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='hithere 0.5s 5'; firstclick=true; showMessageMenu();}">
  </select><span id="arrowlanguage">&nbsp;Choose your language first</span>
</span><br>
<span><label for="StudentName">My surname :</label>
  <input type="text" id="StudentName" onchange="if (this.value == 'Intervenant') this.value='Student';" value="<?php echo $randomname; ?>" autocomplete="given-name">
</span><br>
<span id="lisynthese"><label for="synthesison">Speech synthesis active ? </label>
<input type="checkbox" id="synthesison" onclick="resizeSlides(); document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='hidden';  document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='none'; firsttime=false;"><span id="arrowsynthesis">&nbsp; Then launch the speech synthesis</span>
</span>
</div>
<h3>Appearance settings</h3>
<div>
<span><label for="subtitlesfontsize">Subtitles font size : </label><select id="subtitlesfontsize" onchange="document.getElementById('titre').style.fontSize = this.value;document.getElementById('VOBanner').style.fontSize = this.value;">
<option value="12px">Small</option>
<option value="18px" selected>Normal</option>
<option value="24px" >Big</option>
<option value="32px" >Huge</option>
<option value="48px" >Enormous</option>
  </select>
</span><br>
  <span><label for="selectopacity">Subtitles opacity : </label><select id="selectopacity" onchange="ChangeOpacity('titre', this.value); ChangeOpacity('VOBanner', this.value);">
<option value="0">0% opaque</option>
<option value="0.25" >25% opaque</option>
<option value="0.5" selected>50% opaque</option>
<option value="0.75" >75% opaque</option>
<option value="1" >100% opaque</option>
  </select></span><br>
<span>
<label for="checktranlatedbanner">Show the translated version banner ?</label>
<input type="checkbox" id="checktranlatedbanner" checked="checked" onclick="if (this.checked) showBanner('titre'); else hideBanner('titre');"></span><br>
<span>
<label for="ColorTitre">Translated version banner color : </label><select id="ColorTitre" onchange="ChangeColor('titre',this.value);">
<option value="0,0,255|0,0,255" selected>blue</option>
<option value="0,255,0|0,100,0">green</option>
<option value="255,0,0|100,0,0" >red</option>
<option value="255,0,255|100,0,255" >purple</option>
<option value="255,255,0|100,100,0" >yellow</option>
<option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
<option value="0,255,0|0,255,0|0,50,0">green inverted</option>
<option value="255,0,0|255,0,0|20,0,0">red inverted</option>
<option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
<option value="255,255,0|200,200,0|50,50,0" >yellow inverted</option>
<option value="0,0,0|0,0,0">black</option>
<option value="255,255,255|255,255,255|0,0,0">white</option>
  </select></span><br>
<span><label for="checkoriginalbanner">
Show the original version banner</label>
<input type="checkbox"  id="checkoriginalbanner" onclick="if (this.checked) showBanner('VOBanner'); else hideBanner('VOBanner');">
  </span><br>
<span><label for="ColorTitreVO">original version banner color : </label><select id="ColorTitreVO" onchange="ChangeColor('VOBanner',this.value);">
<option value="0,0,255|0,0,255" selected>blue</option>
<option value="0,255,0|0,100,0">green</option>
<option value="255,0,0|100,0,0" >red</option>
<option value="255,0,255|100,0,255" >purple</option>
<option value="255,255,0|100,100,0" >yellow</option>
<option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
<option value="0,255,0|0,255,0|0,50,0">green inverted</option>
<option value="255,0,0|255,0,0|20,0,0">red inverted</option>
<option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
<option value="255,255,0|200,200,0|50,50,0" >yellow inverted</option>
<option value="0,0,0|0,0,0">black</option>
<option value="255,255,255|255,255,255|0,0,0">white</option>
  </select></span>
</div>
<h3>Advanced options</h3>
<div>
<!-- <span><label for="wooclapkey">WOOCLAP Key? </label> <input type="text" value="<?php echo $wooclapkey; ?>" id="wooclapkey" onchange="if (this.value != '') _('lltabs-8').style.opacity=1; document.getElementById('wooclap').src='https://app.wooclap.com/'+document.getElementById('wooclapkey').value;"></span><br> -->
  <span>Voice speed (reference value 100): <br><span id="slider_speed"><div id="custom-handle" class="ui-slider-handle"></div></span></span>
</div>
	</div>
	<section id="tabs-1" role="region" aria-labelledby="chat-section-title">
    <h2 id="chat-section-title" style="display: none;">Classroom Chat</h2>
    <div>
        <label for="question">Classroom Chat</label>
        <div id="classroom" class="chat-container" aria-live="polite" aria-atomic="true"></div>
        <textarea id="question" aria-label="Type your message here" title="Type your message here" placeholder="Type your message here..." rows="2" cols="50" style="width: 70%;"></textarea>
        <input type="button" value="Send" class="button-3 tooltip" id="questionsend" onclick="sendclick();" title="Send your message">
    </div>
</section>

	<div id="tabs-2">
    
    <input style="margin-left: 64px;" type="button" value="Download" class="button-3 tooltip" title="Download the original speech in txt format." onclick="DownloadSpeechLesson('lesson_'+_('classroomid').value+'.txt', document.getElementById('notebook').innerHTML);"><br>
    <div id="notebook" style="border: solid 1px;"></div>

	</div>
	<div id="tabs-3">
  <input style="margin-left: 64px;" type="button" value="Download" class="button-3 tooltip" title="Download the translated speech in txt format." onclick="DownloadSpeechLesson('translated_lesson_'+_('classroomid').value+'.txt', document.getElementById('output').innerHTML);"><br>
    <div id="output">
    </div>
  </div>


  <div id="tabs-7" style="text-align: center;">
    <div id="stillwaiting">
			<div style="width:500px; text-align: center;"><svg id="stillwaitingspinner" role="img" alt="" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="350" height="148" viewBox="0 0 48 48">
<?php include __DIR__ . '/includes/spinner.php'; ?>
</svg></div>
					<p id="spinnertxt">No presentation yet...</p>
</div>
			<img src="images/blank.png"
				 id="img_slides" style="border: none; width: 1280px; height: 800px" alt="Emplacement des slides diffusés" > 

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

</div>

<div id="tmpmessage">Change the visible tab in this menu</div>

</form>
<div id="FS" onclick="GoFS();"  title="Toggle fullscreen mode"><i class='material-icons' style="font-size: 41px;" >fullscreen</i></div>

<div id="dialog-mail" title="Mail"> 
  <form>
    <fieldset>
      <legend style="display: none;">Send a mail</legend>
	<label for="dialog_MailTo">Mail to : </label><input type="text" id="dialog_MailTo" ><br>
	<label style="display:none;" for="dialog_MailFrom">Mail From : </label><input type="text" id="dialog_MailFrom" style="display:none;" readonly>
	<label for="dialog_MailSubject">Subject : </label><input type="text" id="dialog_MailSubject" readonly><br>
	Body : <br>
	<div id="dialog_MailBody">
	</div>
    </fieldset>
  </form>
</div>

<div id="dialog-choose" title="Choose your language"> 
  <form>
  <span><label for="VoiceOutputchoose">You first have to choose your language</label><br>
  <select id="VoiceOutputchoose">
  </select>
  </span>
  </form>
</div>
<?php foreach ($pageScripts as $script) {
    echo "<script src=\"{$script}\"></script>\n";
} ?>
<script>
  $( function() {
    $("#tabs-0").accordion({
      heightStyle: "panel"
    });
    $( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
  } );
  $(function(){
      $('#bar2').popmenu({
        'controller': true,
        'width': '360px',
        'background': '#34495e',
        'focusColor': '#1abc9c',
        'borderRadius': '10px',
        'top': '0',
        'left': '0',
        'iconSize': '90px'
      });
  });

  /**
 * @summary Display the selected tab 
 *  Pourquoi du multi tab ????
 */

function activetab(tab) {
    var TTabs = ['tabs-0', 'tabs-1', 'tabs-2', 'tabs-3', 'tabs-7'];
    for (var i = 0; i < TTabs.length; i++) {
        var elt = _(TTabs[i]);
        if (elt) elt.style.display = "none";
    }
    var target = _(tab);
    if (target) target.style.display = 'block';
    $( "#demo_ul" ).hide();
}

/**
 * @summary Switch a given extratab visible or invisible (DISABLED - tabs commented out)
 * 
 * @param {string} url - The URL to set
 * @param {number} num - The tab number.
 */

 /*
 function showET(url, num) {
    if (url != "") {
      _('lltabs-' + (8 + 1 * num)).style.opacity = 1;
      if (_('extratab' + num).src != url) _('extratab' + num).src = url;
    } else {
      _('lltabs-' + (8 + 1 * num)).style.opacity = 0.5;
    }
}
*/

  </script>
</body>
</html>
