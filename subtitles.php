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
    <meta name="viewport" content="width=device-width">
    <link href="styles/student.css" rel="stylesheet">
    <?php if ($theme != "") {
    echo '<link href="jquery/'.$theme.'/jquery-ui.min.css" rel="stylesheet">';
    echo '<link href="jquery/'.$theme.'/extras.css" rel="stylesheet">';
  } else {
    echo '<link href="jquery/jquery-ui.min.css" rel="stylesheet">';
  }
	?>
  <link rel="icon" type="image/png" href="favicon.png" >
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
<script type="text/javascript">
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
<body onload="/*Init();*/ /*InitVoices(); setTimeout(InitVoices,1000); setTimeout(Lancer,3000); */ setTimeout(loadVoicesWhenAvailable,100); /*loadVoicesWhenAvailable();*/">
<main>
<h1 style="display:none;">Sous-titres</h1>
</main>
<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'Isite NExT" style="width: 109px; height: 50px;"></div>
<div id="showmenu" onclick="ShowMenu();" ><img src="images/blank.png" alt="Icone du Menu ☰" id="iconeMenu"></div>
<div id="micon" style="background-color: #EE0000;" onclick="gotolive=true; CompleteSpeech(); this.style.backgroundColor='rgba(238,0,0,0.5)'; setTimeout(()=> {document.getElementById('micon').style.backgroundColor='rgba(238,0,0,1)';},300);"><i class='material-icons'>sensors</i><span style="font-size: 10px;color: white;"><br>LIVE</span></div>
<div id="repeat" onclick="Parle(document.getElementById('titre').innerHTML);" title="Repeat the last sentence"><i class='material-icons'>replay</i><span style="font-size: 10px;color: white;"><br>REPEAT</span></div>

<div id="titre"><h1>Automatic translation provided by NExT OMIST Project</h1></div>


</div>
</div>

  </div>



</body>
</html>
