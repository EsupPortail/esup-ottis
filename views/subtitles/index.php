<!doctype html>
<html lang="fr">
<head>
	<title><?php echo $this->escape($pageTitle); ?></title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link href="styles/auditor.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/images/favicon.svg" >
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <?php foreach ($pageStyles as $style): ?>
        <link href="<?php echo $this->escape($style); ?>" rel="stylesheet">
    <?php endforeach; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
<script>
  <?php
  echo "window.supportedlanguages=JSON.parse('" . json_encode($T_lang) . "');";
	echo "window.currentconfig='" . $this->escape($currentconfig) . "';";
	echo "window.MicrosoftKey='" . $this->escape($MicrosoftKey) . "';";
	echo "window.MicrosoftRegion='" . $this->escape($MicrosoftRegion) . "';";
	?>
</script>
<?php if (!empty($pageScripts)): ?>
<script src="<?php echo $this->escape($pageScripts[0]); ?>"></script>
<?php endif; ?>
<!-- Auditor modules - ES Modules -->
<?php foreach ($auditorScripts as $script): ?>
<script type="module" src="<?php echo $this->escape($script); ?>"></script>
<?php endforeach; ?>

<style>
#bar2 {
	display:block;
}

#bar, #showmenu {
	display:none;
	}


@media screen and (max-width: 40em) {
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
	d from {-webkit-transform: rotate(0deg);}
	d to {-webkit-transform: rotate(360deg);}
  }

  @-moz-keyframes rotate {
	d from {-moz-transform: rotate(0deg);}
	d to {-moz-transform: rotate(360deg);}
  }

  @keyframes rotate {
	d from {transform: rotate(0deg);}
	d to {transform: rotate(360deg);}
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
  }

  #dialog-choose select {
    max-width: 100%;
  }

  .ui-dialog { z-index: 40000 !important ;}
</style>

</head>
<body onload="PopulateVoicesInSub(); PopulateVoicesOutSub(selectname='VoiceOutput'); applyConfigSub(); setInterval(readTextSub, 500); setInterval(readTextSubTranscript, 300);">
<?php echo \App\Utils\Csrf::field(); ?>
<main>
<h1 style="display:none;">Sous-titres</h1>
</main>
<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'Isite NExT" style="width: 109px; height: 50px;"></div>

<div id="titre" style="line-height: 28px;"><h1>Automatic translation provided by NExT OMIST Project</h1></div>
	<div id="VOBanner"></div>
	<div id="classroom" style="display: none;" aria-live="polite" aria-atomic="true"></div>
	<div id="scrollbarSub"></div>
  <select id="selectopacity" class="select" style="display : none">
    <option value="0">0% opaque</option>
    <option value="0.25">25% opaque</option>
    <option value="0.5" selected>50% opaque</option>
    <option value="0.75">75% opaque</option>
    <option value="1">100% opaque</option>
  </select>
  <select id="ColorTitre" class="select" style="display : none">
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
  <select id="ColorPartialTitre" class="select" style="display : none">
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
  <select id="selectfontsize" class="select" style="display : none">
    <option value="12px">Small</option>
    <option value="18px" selected>Normal</option>
    <option value="24px">Big</option>
    <option value="32px">Huge</option>
    <option value="48px">Enormous</option>
  </select>
  <select id="classroomlanguage" class="select" style="display : none"> </select>
  <select id="subtitlelanguage" class="select" style="display : none"> </select>
  <input type="checkbox" id="recognitionon" style="display : none"/>
  <textarea id="verbaltics" rows="3" cols="20" style="display : none"></textarea>
  <li <?php if (true) {
      echo 'style="display:none;"';
  } ?> title="Do not change unless you know what you are doing !">
    <label for="classroomid">Classroom identifier :</label>
    <input type="text" name="classroomid" id="classroomid" value="<?php echo $this->escape($classroomid); ?>" >
  </li>
  <input type="checkbox" id="liveon" style="display : none"/>
  <select id="VoiceOutput" class="select" style="display : none"> </select>
<span id="lisynthese" style="display:none;"><label for="synthesison">Speech synthesis active ? </label>
<input type="checkbox" id="synthesison"><span id="arrowsynthesis">&nbsp; Then launch the speech synthesis</span>
</span>
<span style="display:none;">Pending requests : <span id="nbrequests">0</span></span>
<div id="notebook" style="display:none;"></div>
  <div id="output" style="display:none;"> </div>
  <div id="scrollbar"></div>

<?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
    <script src="/js/tests/no-jquery-validation.js"></script>
<?php endif; ?>
</body>
</html>
