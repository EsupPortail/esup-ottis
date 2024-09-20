<?php 
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
?>
<?php

if (isset($_GET["SYNC"])) {
  $synchronize = true;
} else {
  $synchronize = false;
}

if (isset($_GET["ROOMID"])) {
  $classroomid = $_GET["ROOMID"];
  $synchronize = false;
} else {
  $classroomid = '';
  $synchronize = true;
}


$wooclapkey="";
if (isset($_GET["WOOCLAPKEY"])) {
  $wooclapaddress = "https://www.wooclap.com/".$_GET["WOOCLAPKEY"];
  $wooclapkey=$_GET["WOOCLAPKEY"];
} else {
  $wooclapaddress = "https://app.wooclap.com";
}

$Tcolors=array("Red","Blue","Orange","Purple","Black","White","Green");
$Tobjects=array("Alpaca","Alligator","Dragoon", "Elephant","Lion", "Panther", "Tiger","Zebra");
$randomname=$Tcolors[mt_rand(0,count($Tcolors)-1)]." ".$Tobjects[mt_rand(0,count($Tobjects)-1)];

$T_lang=array();
$fd = fopen("supportedlanguages.csv","r");
while($line=fgets($fd,4096)) {
//  echo $line."\n";
  $ligne=explode(";",$line);
//  print_r($ligne);
  if (count($ligne)>2) $T_lang[]=array("name_fr"=>trim($ligne[2]), "name_vo"=>trim($ligne[0]), "code"=>trim($ligne[1]), "voice"=>"-1");
}
fclose($fd);

$theme="flat";
if (isset($_GET["theme"])) $theme=$_GET["theme"];

?>
<!doctype html>
<html lang="fr">
<head>
	<title>Automatic translation for teaching purposes</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link href="styles/student.css" rel="stylesheet">
    <?php if ($theme != "") {
    echo '<link href="jquery/'.$theme.'/jquery-ui.min.css" rel="stylesheet">';
    echo '<link href="jquery/'.$theme.'/extras.css" rel="stylesheet">';
  } else {
    echo '<link href="jquery/jquery-ui.min.css" rel="stylesheet">';
  }
	?>
  <link rel="icon" type="image/png" href="./images/favicon.svg" >
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script>

<?php
echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";
if ($synchronize) {
  echo "var needSynchronize=true;\n";
} else {
  echo "var needSynchronize=false;\n";
}
?>

</script>

<?php
  echo "<script src=\"js/student-def.js?v=".$classroomid."\"></script>\n";
  echo "<script src=\"js/common-func.js?v=".$classroomid."\"></script>\n";
  echo "<script src=\"js/student-func.js?v=".$classroomid."\"></script>\n";
  ?>
<?php if ($theme != "") {
    echo '<script src="jquery/'.$theme.'/external/jquery/jquery.js"></script>';
    echo '<script src="jquery/'.$theme.'/jquery-ui.js"></script>';
  } else {
    echo '<script src="jquery/jquery.js"></script>';
    echo '<script src="jquery/jquery-ui.min.js"></script>';
  }
  ?>
<link rel="stylesheet" href="libs/quickmenu/lib/font-awesome/css/font-awesome.min.css">
<script src="libs/quickmenu/src/jquery.popmenu.js"></script>

<style>
#bar2 {
	display:block;
}

#bar, #showmenu {
		display:none;
	}


@media screen and (max-width: 40em) {
	/* #menu is the original menu */
	#bar, #showmenu {
		display:none;
	}
	
	#bar2 {
		display:block;
    font-family: helvetica, arial, sans-serif;
	}
}

        .fa{
            font-size: 40px;
            line-height: 70px;
        }
        .fa-bars{
            color: white;
        }

        .fa-menu {
          font-size: 50px;
          line-height: 50px;
        }


  #demo_ul li {
    font-family: helvetica, arial, sans-serif;
  }
  #VoiceOutput, select, input {
    max-width: 30%;
  } 
#img_slides {
  width: 100%;
  height: 100%;
}

/* Spinner */

#stillwaiting {
	position: absolute;
	width: 50%;
	height: 50%;
	top:200px;
	left:25%;
	color: black;
	font-size: 2em;
	z-index:20000;
}

#stillwaitingspinnerdiv {
	width:100px;
	height:100px;
	border:20px solid #555555;
	border-top:25px solid #aaaaaa;
	border-radius:50%;
	margin: auto;
  }

  #stillwaitingspinner {
	width:150px;
	height:150px;
	margin: auto;
  }

  #stillwaitingspinner, #stillwaitingspinnerdiv {
	-webkit-transition-property: -webkit-transform;
	-webkit-transition-duration: 2s;
	-webkit-animation-name: rotate;
	-webkit-animation-iteration-count: infinite;
	-webkit-animation-timing-function: linear;
	
	-moz-transition-property: -moz-transform;
	-moz-animation-name: rotate; 
	-moz-animation-duration: 2s; 
	-moz-animation-iteration-count: infinite;
	-moz-animation-timing-function: linear;
	
	transition-property: transform;
	animation-name: rotate; 
	animation-duration: 2s; 
	animation-iteration-count: infinite;
	animation-timing-function: linear;
  }
  
  @-webkit-keyframes rotate {
	  from {-webkit-transform: rotate(0deg);}
	  to {-webkit-transform: rotate(360deg);}
  }
  
  @-moz-keyframes rotate {
	  from {-moz-transform: rotate(0deg);}
	  to {-moz-transform: rotate(360deg);}
  }
  
  @keyframes rotate {
	  from {transform: rotate(0deg);}
	  to {transform: rotate(360deg);}
  }
  
  #tabs-0 {
    margin-top: 50px;
    width: 100%;
  }
  #tabs {
    width: 100%;
  }


  #dialog-choose {
    font-size: 1.2em;
/*    background-color: white;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0px;
    left: 0px;*/
  }

  #dialog-choose select {
    max-width: 100%;
  } 

  .ui-dialog { z-index: 40000 !important ;}
</style>

</head>
<body onload="Init(); /*InitVoices(); setTimeout(InitVoices,1000); setTimeout(Lancer,3000); */ setTimeout(loadVoicesWhenAvailable,100); /*loadVoicesWhenAvailable();*/">
<main>
<h1 style="display:none;">Outils de traduction : application étudiante</h1>
</main>
<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'Isite NExT" style="width: 109px; height: 50px;"></div>
<div id="showmenu" onclick="ShowMenu();" ><img src="images/blank.png" alt="Icone du Menu ☰" id="iconeMenu"></div>
<div id="micon" style="background-color: #EE0000;" onclick="gotolive=true; CompleteSpeech(); this.style.backgroundColor='rgba(238,0,0,0.5)'; setTimeout(()=> {document.getElementById('micon').style.backgroundColor='rgba(238,0,0,1)';},300);"><i class='material-icons'>sensors</i><span style="font-size: 10px;color: white;"><br>LIVE</span></div>
<div id="repeat" onclick="Parle(document.getElementById('titre').innerHTML);" title="Repeat the last sentence"><i class='material-icons'>replay</i><span style="font-size: 10px;color: white;"><br>REPEAT</span></div>
<div id="bar2" style="border:none; position: fixed; top: 1px; left: 1px; z-index: 100000;">
           <div class="pop_ctrl" title="Open the menu dialog"><!--<i class="fa fa-bars fa-border"></i>-->
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
               <li id="lltabs-8" style="opacity: 0.5" onclick="if (this.style.opacity 
               ==1) activetab('tabs-8')" class="demo_li"><div style="line-height: 70px; vertical-align: middle;"><!--<i class="fa fa-television"></i>--><img src="images/wooclap-ico.png" alt="Icone de l'outil WOOCLAP" width="40" style="vertical-align: middle;"></div><div>WOOCLAP</div></li>
               <li id="lltabs-9" style="opacity: 0.5" onclick="if (this.style.opacity 
               ==1) activetab('tabs-9');" class="demo_li"><div><i class="fa fa-desktop"></i></div><div>Tab 1</div></li>
               <li id="lltabs-10" style="opacity: 0.5" onclick="if (this.style.opacity 
               ==1) activetab('tabs-10');" class="demo_li"><div><i class="fa fa-desktop"></i></div><div>Tab 2</div></li>
               <li id="lltabs-11" style="opacity: 0.5" onclick="if (this.style.opacity 
               ==1) activetab('tabs-11')" class="demo_li"><div><i class="fa fa-desktop"></i></div><div>Tab 3</div></li>
               <li id="lltabs-12" style="opacity: 0.5" onclick="if (this.style.opacity 
               ==1) activetab('tabs-12')" class="demo_li"><div>
               <i class="fa fa-desktop"></i></div><div>Tab 4</div></li>

               
           </ul>
       </div>
       <script>
        $(function(){
            $('#bar2').popmenu({
              'controller': true,
              'width': '360px',  // default '300px'  
              'background': '#34495e',
              'focusColor': '#1abc9c',
              'borderRadius': '10px',
              'top': '0', // Default = 50
              'left': '0',
              'iconSize': '90px' // default '100px'           
            });
        })
    </script>
<!--
<div id="titre" onmouseover="document.getElementById('soustitre').style.transform='translateY(0px)'; document.getElementById('titre').style.transform='translateY(0px)';"><h1>Automatic translation for teaching purposes</h1></div>
<div id="soustitre" onmouseover="this.style.transform='translateY(-64px)'; document.getElementById('titre').style.transform='translateY(-64px)';"></div>-->
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
<!--    <li><a href="#tabs-6" onclick="if (!visioactive) document.getElementById('visio').src='https://meet.jit.si/Translator_'+document.getElementById('classroomid').value; visioactive=true;">Visio</a></li>-->
<!--		<li id="ltabs-4" onclick="activetab('tabs-4')">Scan a QRCode</li>-->
    <li id="ltabs-7" onclick="activetab('tabs-7')">Slides</li>
    <li id="ltabs-8" onclick="activetab('tabs-8')">WOOCLAP</li>
    <li id="ltabs-9" onclick="activetab('tabs-9');">1</li>
		<li id="ltabs-10" onclick="activetab('tabs-10');">2</li>
		<li id="ltabs-11" onclick="activetab('tabs-11');">3</li>
		<li id="ltabs-12" onclick="activetab('tabs-12');">4</li>
		<li id="ltabs-13" onclick="activetab('tabs-13');" class="wordcloud">Word Cloud</li>

	</ul>
	<div id="tabs-0">
    <h3 >Language and basic settings</h3>
    <div>
    <span style="display:none"><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" ></span>
<span style="display:none"><label for="classroomlanguage">Classroom language :</label>
  <select id="classroomlanguage" onchange="setLanguageInput(this.value);">
  </select>
</span>
<span id="lilanguage"><label for="VoiceOutput">My language :</label>
  <select id="VoiceOutput" onchange="setLanguageOutput(this.value);"  onmouseout="if (firsttime && firstclick) {resizeSlides(); document.getElementById('synthesison').checked=true; firsttime=false;}" onclick="if (firsttime) {document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='visible'; document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='hithere 0.5s 5'; firstclick=true; showMessageMenu();}">
  </select><span id="arrowlanguage">&nbsp;Choose your language first</span>
</span><br>
<span><label for="StudentName">My surname :</label>
  <input type="text" id="StudentName" onchange="if (this.value == 'Teacher') this.value='Student';" value="<?php echo $randomname; ?>" autocomplete="given-name">
</span><br>
<span id="lisynthese" style="display:none;"><label for="synthesison">Speech synthesis active ? </label>
<!--<div class="checkbox-example">
        <input type="checkbox" value="1" id="synthesison" onclick="resizeSlides();">
        <label for="synthesison"></label>
      </div>-->
<input type="checkbox" id="synthesison" onclick="resizeSlides(); document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='hidden';  document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='none'; firsttime=false;"><span id="arrowsynthesis">&nbsp; Then launch the speech synthesis</span>
</span>
</div>
<h3>Appearance settings</h3>
<div>
<span style="display:none;">    <input type="button" value="Synchronize" onclick="Synchronize(prompt('Enter the session pincode'));">
</span>
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
<!--  <li title="Sometimes, for security issues, you must be logged to WOOCLAP by an external application. Click here to fix this issue.">External login to WOOCLAP : <button onclick="window.open('https://app.wooclap.com/auth/login?lang=en&redirectTo=/home')">Login to wooclap first</button></li>-->
<span><label for="wooclapkey">WOOCLAP Key? </label> <input type="text" value="<?php echo $wooclapkey; ?>" id="wooclapkey" onchange="if (this.value != '') _('lltabs-8').style.opacity=1; document.getElementById('wooclap').src='https://app.wooclap.com/'+document.getElementById('wooclapkey').value;"></span><br>
  <span style="display:none;">Pending requests : <span id="nbrequests">0</span></span>
  <span>Voice speed (reference value 100): <br><span id="slider_speed"><div id="custom-handle" class="ui-slider-handle"></div></span></span>
</div>
	</div>
	<div id="tabs-1">
    <div style="height: 50px;"></div>
    <div id="classroom" style="border: solid 1px;"></div>

    <!-- <input type="text" id="questionold" placeholder="Your question here">-->
    <label for="question" style="display:none">Your question here</label>
    <textarea id="question" aria-label="Your question here" title="Your question here" placeholder="Your question here" rows="2" cols="50"></textarea><br>
     <input type="button" value="send" class="button-3 tooltip" id="questionsend" onclick="sendclick();" title="Send your question">
    <input type="button" value="refresh" class="button-3 tooltip" title="Reload all the conversation and translate it if necessary." onclick="recharger();">
  	</div>

	<div id="tabs-2">
    <!--<input type="button" value="Restart" onclick="inputnumber=-1;">-->
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
<?php
$nbcircles=15;
$minsize=1.3;
$maxsize=4;
$r=20;
$TeinteFrom=[45,63,80];
$TeinteTo=[26,189,157];
for($i=0; $i<$nbcircles; $i++) {
	$teinteR=$TeinteFrom[0]*(1-$i/$nbcircles)+$TeinteTo[0]*$i/$nbcircles;
	$teinteV=$TeinteFrom[1]*(1-$i/$nbcircles)+$TeinteTo[1]*$i/$nbcircles;
	$teinteB=$TeinteFrom[2]*(1-$i/$nbcircles)+$TeinteTo[2]*$i/$nbcircles;
	echo "<circle cx=\"".(24+20*cos($i*6.283/$nbcircles))."\" cy=\"".(24+20*sin($i*6.283/$nbcircles))."\" r=\"".($minsize*(1-$i/$nbcircles)+$maxsize*$i/$nbcircles)."\" fill=\"rgb(".$teinteR.",".$teinteV.",".$teinteB.")\"/>\n";
}
?>
</svg></div>
					<p id="spinnertxt">No presentation yet...</p>
</div>
			<img src="images/blank.png"
				 id="img_slides" style="border: none; width: 1280px; height: 800px" alt="Emplacement des slides diffusés" > 

	</div>


	<div id="tabs-13" class="wordcloud" style="display:none;">
    <div id="form">
    <div id="vis"></div>
    <div id="configwc" style="display: none">
    <p><textarea id="text">
Ceci est un test...
    </textarea>
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

  </div>
  
<!--  	<div id="tabs-6">
    <div id="slideshow" style="border: solid 1px; height: 600px; overflow: auto;">
<iframe title="Fenêtre pour la visioconférence" id="visio" src = "" width="100%" height="100%" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture" ></iframe>
    </div>
	</div>-->

<!--
  <div id="tabs-4">
    <input type="button" value="Start Scanning" onclick="StartScan();">
    <input type="button" value="Stop Scanning" onclick="if (scanner) scanner.stop();">
    <select id="AllCameras" style="display:none" onchange="if (scanner) scanner.stop(); StartScan();"></select>
    <script src="html5-qrcode.min.js"></script>
    <div id="reader"></div>
    <script>
      const scanner = new Html5Qrcode("reader");
      function StartScan() {
        document.getElementById('AllCameras').style.display='block';

        Html5Qrcode.getCameras().then(devices => {
          if (devices && devices.length) {
            var select = document.getElementById('AllCameras');
            if (select.length == 0) {
              for(var i=0; i<devices.length; i++) {
                var opt=document.createElement("option");
                opt.value=devices[i].id;
                opt.innerHTML=devices[i].label;
                if (i==0) opt.selected=true;
                select.appendChild(opt);
              }
            }

            var cameraId = select.value;
            scanner.start(
              cameraId,
              {
                fps: 10,
                qrbox: 250
              },
              qrCodeMessage => {
                var content=qrCodeMessage;
                var pattern='ROOMID=';
                var offset=content.length-content.indexOf(pattern)-pattern.length;
                document.getElementById('classroomid').value=content.substr(-offset);
                alert('Find Classroom id : '+content.substr(-offset));
                scanner.stop();
            },
          errorMessage => {
            console.log(`QR Code no longer in front of camera.`);
          })
          .catch(err => {
              console.log(`Unable to start scanning, error: ${err}`);
          });
          }
        }).catch(err => {
          alert('No camera found !')
        });
      }
    /*
    function StartScan() {
      let scanner = new Instascan.Scanner({ video: document.getElementById('preview'), mirror: false });
      scanner.addListener('scan', function (content) {
        document.getElementById('classroomid').value=content.substr(-32);
        alert('Find Classroom id : '+content.substr(-32));
        scanner.stop();
      });
      Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
          scanner.start(cameras[0]);
        } else {
          console.error('No cameras found.');
        }
      }).catch(function (e) {
        console.error(e);
      });
    }
    */
    </script>
  </div>
-->
    
  <div id="tabs-8"><iframe title="Fenêtre qui contient WOOCLAP" id="wooclap" src="<?php echo $wooclapaddress; ?>" style="width: 95%; height: 90%; bottom:0px; left:0px; z-index:1000;"   allowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write"></iframe>
  </div>

  <div id="tabs-9"><iframe title="Première fenêtre pour inclure un outil externe" id="extratab1" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" ></iframe>
  </div>
  <div id="tabs-10"><iframe title="Deuxième fenêtre pour inclure un outil externe" id="extratab2" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" ></iframe>
  </div>
  <div id="tabs-11"><iframe title="Troisième fenêtre pour inclure un outil externe" id="extratab3" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" ></iframe>
  </div>
  <div id="tabs-12"><iframe title="Quatrième fenêtre pour inclure un outil externe" id="extratab4" allowfullscreen  allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="blank.html" ></iframe>
  </div>

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
<!--
<script src="https://www.jasondavies.com/d3.min.js"></script>
<script src="https://www.jasondavies.com/wordcloud/cloud.min.js"></script>
<script>
  function parseText(t) {_("text").value=t;}

  </script>
  -->

<script src="js/student-post.js"></script>
<script>
  $( function() {
    $("#tabs-0").accordion({
      heightStyle: "panel"
    });
    $( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
    $("body").tooltip();
  } );

  /**
 * @summary Display the selected tab 
 * 
 */

function activetab(tab) {
    var TTabs = ['tabs-0', 'tabs-1', 'tabs-2', 'tabs-3', 'tabs-7', 'tabs-8', 'tabs-9', 'tabs-10', 'tabs-11', 'tabs-12', 'tabs-13'];
    for (var i = 0; i < TTabs.length; i++) {
        _(TTabs[i]).style.display = "none";
        //_("l" + TTabs[i]).style.color = "black";
        //_("l" + TTabs[i]).style.backgroundColor = "rgb(247,247,247)";
    }
    _(tab).style.display = 'block';
    //_("l" + tab).style.color = "white";
    //_("l" + tab).style.backgroundColor = "rgb(57,199,204)";
    //ShowMenu();
    $( "#demo_ul" ).hide();


    
}

/**
 * @summary Switch a given extratab visible or invisible
 * 
 * @param {HTMLInput checkbox} obj a checkbox.
 * @param {number} num the tab number.
 * @returns
 */

 function showET(url, num) {
    //alert('url='+url+'    num='+num+'     tabs='+'ltabs-'+(8+1*num))
    if (url != "") {
//      show('ltabs-' + (8 + 1 * num));
      _('lltabs-' + (8 + 1 * num)).style.opacity=1;
        if (_('extratab' + num).src != url) _('extratab' + num).src = url;
    } else {
//        hide('ltabs-' + (8 + 1 * num));
        _('lltabs-' + (8 + 1 * num)).style.opacity=0.5;
    }
}

  </script>
</body>
</html>
