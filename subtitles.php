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
	<title>OMIST - Subtitles</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
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
<script>
  <?php
  echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";

  if (isset($_GET["myconfig"])) {
    echo "var currentconfig='".$_GET["myconfig"]."';\n";
  } else {
    echo "var currentconfig='AAYANKCNYCYNYYYY';\n";
  }
  echo "var MicrosoftKey='".$MicrosoftKey."';\n";
  echo "var MicrosoftRegion='".$MicrosoftRegion."';\n";
  ?>
</script>
<script src="js/student-def.js"></script>
<script src="js/common-func.js"></script>
<script src="js/student-func.js"></script>
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
<body onload="/*Init();*/ /*InitVoices(); setTimeout(InitVoices,1000); setTimeout(Lancer,3000); */ PopulateVoicesInSub(); PopulateVoicesOutSub(selectname='VoiceOutput'); applyConfigSub(); setInterval(readTextSub, 500); setInterval(readTextSubTranscript, 300);/*setTimeout(loadVoicesWhenAvailable,100);*/ /*loadVoicesWhenAvailable();*/">
<main>
<h1 style="display:none;">Sous-titres</h1>
</main>
<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'Isite NExT" style="width: 109px; height: 50px;"></div>
<!-- <div id="micon" title="This button allows to switch on/off the microphone transcription. Alternatives for this button are the space key, the smartphone remote red button or the presentation pointer button."><i class='material-icons'>voice_over_off</i></div> -->

<div id="titre" style="line-height: 28px;"><h1>Automatic translation provided by NExT OMIST Project</h1></div>
	<div id="VOBanner"></div>
	<div id="scrollbarSub"></div>
  <select id="selectopacity" class="jqueryselect" style="display : none">
    <option value="0">0% opaque</option>
    <option value="0.25">25% opaque</option>
    <option value="0.5" selected>50% opaque</option>
    <option value="0.75">75% opaque</option>
    <option value="1">100% opaque</option>
  </select>
  <select id="ColorTitre" class="jqueryselect" style="display : none">
    <option value="0,0,255|0,0,255" selected>blue</option>
    <option value="0,255,0|0,100,0">green</option>
    <option value="255,0,0|100,0,0">red</option>
    <option value="255,0,255|100,0,255">purple</option>
    <option value="255,255,0|100,100,0">yellow</option>
    <option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
    <option value="0,255,0|0,255,0|0,50,0">green inverted</option>
    <option value="255,0,0|255,0,0|20,0,0">red inverted</option>
    <option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
    <option value="255,255,0|200,200,0|50,50,0">yellow inverted</option>
    <option value="0,0,0|0,0,0">black</option>
    <option value="255,255,255|255,255,255|0,0,0">white</option>
  </select>
  <select id="ColorPartialTitre" class="jqueryselect" style="display : none">
    <option value="0,0,255|0,0,255">blue</option>
    <option value="0,255,0|0,100,0">green</option>
    <option value="255,0,0|100,0,0">red</option>
    <option value="255,0,255|100,0,255">purple</option>
    <option value="255,255,0|100,100,0">yellow</option>
    <option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
    <option value="0,255,0|0,255,0|0,50,0">green inverted</option>
    <option value="255,0,0|255,0,0|20,0,0">red inverted</option>
    <option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
    <option value="255,255,0|200,200,0|50,50,0">yellow inverted</option>
    <option value="0,0,0|0,0,0" selected>black</option>
    <option value="255,255,255|255,255,255|0,0,0">white</option>
  </select>
  <select id="selectfontsize" class="jqueryselect" style="display : none">
    <option value="12px">Small</option>
    <option value="18px" selected>Normal</option>
    <option value="24px">Big</option>
    <option value="32px">Huge</option>
    <option value="48px">Enormous</option>
  </select>
  <select id="classroomlanguage" class="jqueryselect" style="display : none"> </select>
  <select id="subtitlelanguage" class="jqueryselect" style="display : none"> </select>
  <input type="checkbox" id="recognitionon" style="display : none"/>
  <textarea id="verbaltics" rows="3" cols="20" style="display : none"></textarea>
  <!-- <div id="partialtitre" style="display : none"></div> -->
  <li <?php if (true) echo "style=\"display:none;\""; ?> title="Do not change unless you know what you are doing !">
    <label for="classroomid">Classroom identifier :</label>
    <input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" >
  </li>
  <input type="checkbox" id="liveon" style="display : none"/>
  <select id="VoiceOutput" class="jqueryselect" style="display : none"> </select>
  <!-- <span id="lilanguage"><label for="VoiceOutput">My language :</label> -->
  <!-- <select id="VoiceOutput" onchange="setLanguageOutput(this.value);"  onmouseout="if (firsttime && firstclick) {resizeSlides(); document.getElementById('synthesison').checked=true; firsttime=false;}" onclick="if (firsttime) {document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='visible'; document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='hithere 0.5s 5'; firstclick=true; showMessageMenu();}"> -->
  <!-- </select><span id="arrowlanguage">&nbsp;Choose your language first</span>
</span><br> -->
<span id="lisynthese" style="display:none;"><label for="synthesison">Speech synthesis active ? </label>
<!--<div class="checkbox-example">
        <input type="checkbox" value="1" id="synthesison" onclick="resizeSlides();">
        <label for="synthesison"></label>
      </div>-->
<input type="checkbox" id="synthesison"><span id="arrowsynthesis">&nbsp; Then launch the speech synthesis</span>
</span>
<span style="display:none;">Pending requests : <span id="nbrequests">0</span></span>
<div id="notebook" style="display:none;"></div>
  <!-- <button id="bready" style="display : none">Ready to listen ?</button> -->
  <div id="output" style="display:none;"> </div>
  <div id="scrollbar"></div>



</body>
</html>
