<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

?>
<!DOCTYPE html>
<html lang="fr-FR"><head>
    <meta charset="utf-8">
    <title>Automatic translation for teaching purposes: Scenarios</title>
    <link rel="icon" type="image/png" href="favicon.png" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="jquery/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" href="libs/quickmenu/lib/font-awesome/css/font-awesome.min.css">
    <script src="jquery/jquery.js"></script>
    <script src="jquery/jquery-ui.min.js"></script>
    <script src="js/common-func.js"></script>
    <style>
@import url('https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Shadows+Into+Light&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Inria+Sans:wght@300;400&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Permanent+Marker&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Amatic+SC&display=swap');

@gray-darker:               #444444;
@gray-dark:                 #696969;
@gray:                      #999999;
@gray-light:                #cccccc;
@gray-lighter:              #ececec;
@gray-lightest:             lighten(@gray-lighter,4%);

*,
*::before,
*::after { 
  box-sizing: border-box;
}


.button-10 {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 6px 14px;
  font-family: -apple-system, BlinkMacSystemFont, 'Roboto', sans-serif;
  border-radius: 6px;
  border: none;

  color: #fff;
/*  background: linear-gradient(180deg, #4B91F7 0%, #367AF6 100%);*/
  background-color: #4B91F7;
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


body {
  color: @gray;
  font-family: 'Roboto','Helvetica Neue', Helvetica, Arial, sans-serif;
  font-style: normal;
  font-weight: 400;
  letter-spacing: 0;
  padding: 1rem;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  -moz-font-feature-settings: "liga" on;
  background: linear-gradient(45deg, #243949, #2cacd1, #35eb93);
  background-size:3000px 3000px;
  top: 0px !important;
}

img {
  height: auto;
  max-width: 100%;
  vertical-align: middle;
}

.btn {
  background-color: rgb(150,150,255);
  border: 1px solid @gray-light;
  border-radius: 1rem;
  color: @gray-dark;
  padding: 0.5rem;
}

.btn:hover {
  background-color: rgb(200,200,255);
}


.btn--block {
  display: block;
  width: 100%;
}
 
.cards {
  display: flex;
  flex-wrap: wrap;
  list-style: none;
  margin: 0;
  padding: 0;
}

.cards__item {
  display: flex;
  padding: 1rem;
  @media(min-width: 40rem) {
    width: 50%;
  }
  @media(min-width: 56rem) {
    width: 33.3333%;
  }
  border-radius: 20px;
}

.card {
  background-color: white;
  border-radius: 0.25rem;
/*  box-shadow: 0 20px 40px -14px rgba(0,0,0,0.25); */
  box-shadow: 12px 12px 2px 1px rgba(0, 0, 0, .2);

  display: flex;
  flex-direction: column;
  overflow: hidden;
  &:hover {
    .card__image {
      filter: contrast(100%);
    }
  }
  padding: 5px;
}

.card__content {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  padding: 1rem;
}

.card__image {
  background-position: center center;
  background-repeat: no-repeat;
  background-size: cover;
  border-top-left-radius: 0.25rem;
  border-top-right-radius: 0.25rem;
  filter: contrast(70%);
  filter: saturate(180%);
  overflow: hidden;
  position: relative;
  transition: filter 0.5s cubic-bezier(.43,.41,.22,.91);;
  &::before {
    content: "";
	  display: block;
    padding-top: 56.25%; /* 16:9 aspect ratio */
  }
  @media(min-width: 40rem) {
    &::before {
      padding-top: 66.6%; /* 3:2 aspect ratio */
    }
  }
}

.card__title {
  font-family: Fjalla One;
  color: @gray-dark;
  font-size: 1.25rem;
  font-weight: 300;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.card__text {
//  font-family: 'Shadows Into Light', cursive;
//  font-family: 'Permanent Marker', cursive;
  font-family: 'Inria Sans', sans-serif;
//  font-family: 'Amatic SC', sans-serif;
  flex: 1 1 auto;
//  font-size: 0.875rem;
  font-size: 1.0em;
  line-height: 1.5;
  margin-bottom: 1.25rem;
  font-style: italic;
}

.card__btn {
  font-family: Fjalla One;
}


.card__input {
  font-family: Fjalla One;
  background-color: rgb(150,255,150);
  border: 1px solid #505050;
  box-sizing: border-box;
  border-radius: 1rem;
  color: @gray-dark;
  padding: 0.5rem;
  display: block;
  width: 100%;
}

.card__input::placeholder {
  color: #505050;
  opacity: 1;
}

.details {
  font-size: 0.9em;
  height: 200px;
  overflow: auto;
  font-style: normal;
}

.needbrowser {
  display: none;
}

#nav-top {
  background-color: black;
  color: white;
  position: fixed;
  top: 0px;
  left : 0px;
  width : 100%;
  height : 63px;
}

#nav-bottom {
  width : 100%;
  height : 200px;
  background-color: black;
  color: white;
  position : fixed;
  bottom: 0;
  left: 0;
}

#logoNU {
  width : 231px;
  height : auto;
  margin : 20px;
}

</style>

<script>

function _(e) {return document.getElementById(e);}

function CreateLink() {
    res="";
    if (_('persroomid').value != "") res+="&ROOMID="+_('persroomid').value;
    if (_('persmaxlines').value != "") res+="&MaxSavedLines="+_('persmaxlines').value;
    if (_('persolink').value != "") res+="&link="+_('persolink').value;
    if (_('persohtmllink').value != "") res+="&htmllink="+_('persohtmllink').value;
    if (_('persoconfig').value != "") res+="&myconfig="+_('persoconfig').value;
    if (_('persotab').value != "" && _('persotab').value != "0") res+="&tab="+_('persotab').value;
    if (_('persomicrosoftkey').value != "") res+="&MicrosoftKey="+_('persomicrosoftkey').value;
    if (_('persomicrosoftregion').value != "") res+="&MicrosoftRegion="+_('persomicrosoftregion').value;
    if (_('persotheme').value != "" && _('persotheme').value != "0") res+="&theme="+_('persotheme').value;
    return res;
}

function CreateLinkStudent() {
    res="";
    if (_('persroomid').value != "") res+="&ROOMID="+_('persroomid').value;
    return res;
}

function GenereLinkAlert() {
  var linkteacher = root+'/index.php?'+CreateLink(); 
  linkteacher="<a href=\""+linkteacher+"\">"+linkteacher+"</a>"; 
  var linkstudent=CreateLinkStudent(); 
  if (linkstudent != '') {
    linkstudent=root+'/Student.php?'+linkstudent; 
    linkstudent="<a href=\""+linkstudent+"\">"+linkstudent+"</a>"; 
    linkstudent='\n\nEt voici le lien étudiant : \n '+linkstudent;
  } 
  myalert('Voici le lien de connexion, à utiliser n\'importe quand\n'+linkteacher+linkstudent,'Lien de connexion');
}
var root = window.location.href.replace("/scenarios.php","");

/**
 * @summary A prettier alert function
 * 
 * @param {string} ch The text shown in the alert.
 * @param {string} title The title of the alert box (default to "Message").
 */

 function myalert(ch,title="Message") {
    ch = ch.replaceAll("\n", "<br>");
    $("#dialog-invite").dialog( "option", "title", title );
    _('dialog-invite-p').innerHTML = ch;
    // $("#dialog-invite").dialog( "option", "title", title ).dialog("open");
    $("#dialog-invite").dialog("open");
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

.goog-te-gadget-simple {
  border-radius: 8px;
}

</style>

</head>
<body onload="document.cookie=''; " lang="fr">
<div id="nav-top"></div>
<input type="button" id="opentranslator" aria-label="Translate this page" title="Translate this page" class="button-10" onclick="this.style.display='none'; var glink=document.createElement('script'); glink.src='https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'; document.body.appendChild(glink);" value="Translate this page">
 <main>
    <h1 style="display:none;">Divers scenarios d'utilisation pour les Outils de traduction</h1>
</main>
<div id="google_translate_element"></div>
  <ul class="cards">
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">
            <span class="needbrowser"></span>Transcription instantanée
          </div>
          <p class="card__text">Scenario d'un cours sous-titré en Français de manière instantanée, par exemple pour un public mal-entendant.</p>
          <p class="card__text details">
            Pour utiliser ce scénario avec une présentation au format .PPTX ou animation .html, il faut suivre les étapes suivantes : 
            <br> &nbsp;&nbsp;1. Aller à l'onglet "Complete Lesson" pour y charger la présentation. 
            <br> &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent. 
            <br> &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter. 
            <br> &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents.<br>
            Pour utiliser ce scénario avec WOOCLAP, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Aller à l'onglet "WOOCLAP", se connecter sur son compte WOOCLAP et lancer sa présentation comme habituellement. <br> &nbsp;&nbsp;2. Allumer le micro (icone rouge) qui se mettra à clignoter. <br> &nbsp;&nbsp;3. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents.
          </p>
        </div>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=AANAYKCYNCNNNYNY');">Y aller</button>
      </div>
    </li>
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">
            <span class="needbrowser"></span>Sous-titrage anglais (projection)
          </div>
          <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais.</p>
          <p class="card__text details">Pour utiliser ce scénario avec une présentation au format .PPTX ou animation .html, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Aller à l'onglet "Complete Lesson" pour y charger la présentation. <br> &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent. <br> &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter. <br> &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents. <br>
          Pour utiliser ce scénario avec WOOCLAP, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Aller à l'onglet "WOOCLAP", se connecter sur son compte WOOCLAP et lancer sa présentation comme habituellement. <br> &nbsp;&nbsp;2. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter. <br> &nbsp;&nbsp;3. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents.
          </p>
        </div>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANKCNYCNNNYNY');">Y aller</button>
      </div>
    </li>
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">
            <span class="needbrowser"></span>Sous-titrage anglais avec présentation .PPTX sur uncloud
          </div>
          <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais. Les slides sont fournis via un lien public sur <a href="https://uncloud.univ-nantes.fr">uncloud</a>. </p>
          <p class="card__text details">Pour utiliser ce scénario, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Copier ci-dessous le lien public UNCLOUD de la présentation (au format .PPTX). <br> &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent. <br> &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter. <br> &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents. 
          </p>
        </div>
        <p>
          <input type="text" id="SC31" aria-label="Lien public uncloud" title="Lien public uncloud" placeholder="lien public uncloud." class="card__input">
        </p>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANACNYCNNNYYY&tab=4'+'&link='+document.getElementById('SC31').value);">Y aller</button>
      </div>
    </li>
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">
            <span class="needbrowser"></span>Sous-titrage anglais avec présentation .html sur uncloud
          </div>
          <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais. Les slides sont fournis via un lien public sur <a href="https://uncloud.univ-nantes.fr">uncloud</a>. </p>
          <p class="card__text details">Pour utiliser ce scénario, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Copier ci-dessous le lien public UNCLOUD de la présentation (au format .html). <br> &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent. <br> &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter. <br> &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents. <br>
          </p>
        </div>
        <p>
          <input type="text" id="SC41" aria-label="Lien public uncloud" title="Lien public uncloud" placeholder="lien public uncloud." class="card__input">
        </p>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANACNYCNNNYYY&tab=4'+'&htmllink='+document.getElementById('SC41').value);">Y aller</button>
      </div>
    </li>
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">Outils de manipulation de slides</div>
          <p class="card__text">Accès à l'ensemble des outils OMIST de manipulation de slides et de vidéos.</p>
          <p class="card__text details">Parmi les outils proposés, il est possible de : <br> &nbsp;&nbsp;- Préparer en amont une présentation/animation au format .html utilisable dans l'outil de traduction en direct. Cette animation peut-être produite à partir de fichiers .PPTX (commentés ou non) ou de .PDF, accompagnés ou non du discours. Ces discours peuvent alors être incorporés à la demande lors de la séance en direct. <br> &nbsp;&nbsp;- Générer des animations multilingues pour les temps asynchrones. Comme précédemment, ces animations peuvent-être produite à partir de fichiers .PPTX commentés ou de .PDF, accompagnés du discours. Ce discours sera alors traduit dans diverses langues et lu lors de la diffusion de l'animation. <br> &nbsp;&nbsp;- Extraire les sous-titres d'une vidéo <span title="L'outil utilisé est différent et plus performant lorsqu'on est connecté par VPN sur le réseau de Nantes Université. Cliquez pour obtenir de l'aide." onclick="window.open('https://wiki.univ-nantes.fr/doku.php?id=nomade:client_lourd');">(*)</span>. <br> &nbsp;&nbsp;- Transcrire un entretien audio <span title="L'outil utilisé est différent et plus performant lorsqu'on est connecté par VPN sur le réseau de Nantes Université. Cliquez pour obtenir de l'aide." onclick="window.open('https://wiki.univ-nantes.fr/doku.php?id=nomade:client_lourd');">(*)</span>. <br> &nbsp;&nbsp;- Traduire les sous-titres d'une vidéo. <br> &nbsp;&nbsp;- Traduire des cours scénarisés au format XLIFF ou au format SCORM. <br> &nbsp;&nbsp;Faire bien plus, la liste ici n'est pas exhaustive.... </p>
        </div>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/OMIST_Tools/index.php');">Y aller</button>
      </div>
    </li>
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">Préparation de séance</div>
          <p class="card__text">Création d'un lien permettant de préparer à l'avance une séance.</p>
          <p class="card__text details">Ici, il est possible de personnaliser le lien de connexion. <br> Tous ces champs peuvent rester vides, dans ce cas, ce sont les valeurs par défaut qui seront utilisées. 
            <br>
            <input type="text" title="Définissez ici l'identifiant de connexion au cours. Il peut s'agir de n'importe quel mot, sans espace." class="card__input" id="persroomid" placeholder="Identifiant de séance (libre)" value="">
            <input type="text" title="Nombre de lignes maximal qui seront stockées dans le buffer. La valeur conseillée est 2" class="card__input" id="persmaxlines" placeholder="Nombre maximum de lignes dans le buffer" value="">
            <input type="text" id="persolink" title="Coller ici le lien uncloud public de l'endroit où se trouve l'animation pptx que vous souhaitez utiiser" placeholder="lien public uncloud (PPTX)." class="card__input">
            <input type="text" id="persohtmllink" title="Coller ici le lien uncloud public de l'endroit où se trouve l'animation html que vous souhaitez utiiser" placeholder="lien public uncloud (HTML)." class="card__input">
            <input type="text" id="persoconfig" aria-label="Code de configuration" title="Code de configuration" placeholder="code de configuration (à récupérer sur l'application)" class="card__input">
            <select id="persotab" class="card__input" title="Choix de la fenêtre qui sera ouverte par défaut.">
              <option value="0" selected disabled>Quel onglet ouvert par défaut ?</option>
              <option value="0">Onglet "Settings"</option>
              <option value="1">Onglet "Tchat"</option>
              <option value="2">Onglet "Notebook"</option>
              <option value="3">Onglet "Complete lesson"</option>
              <option value="4">Onglet "Slides"</option>
              <option value="7">Onglet "Share"</option>
              <option value="8">Onglet "WOOCLAP"</option>
              <option value="9">Onglet "Slide Tools"</option>
            </select>
            <input type="text" title="Pour utiliser les fonctionnalités de transcription et traduction de Microsoft, il faut disposer d'une clé de connexion, dont la création se fait sur Azure." id="persomicrosoftkey" placeholder="Clé de connexion (pour utiliser la transcription de) Microsoft" class="card__input">
            <input type="text" title="Valeur conseillée = francecentral" id="persomicrosoftregion" placeholder="Région de connexion Microsoft" class="card__input">
            <select id="persotheme" class="card__input" title="Choix du thème de l'application. Pour le moment, il n'y a que trois thèmes disponibles">
              <option value="" selected disabled>Quel thème ?</option>
              <option value="">Thème clair</option>
              <option value="theme2">Thème sombre</option>
              <option value="flat">Thème applati/flat (thème par défaut)</option>
            </select>
          </p>
        </div>
        <button class="btn btn--block card__btn" onclick="GenereLinkAlert();">Générer le lien</button>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?'+CreateLink());">Y aller</button>
      </div>
    </li>
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">
            <span class="needbrowser"></span>Conversation à deux
          </div>
          <p class="card__text">Scénario pour une conversation multi-lingue en duo.</p>
          <p class="card__text details">Dans ce scénario, il est possible de configurer les deux langues qui seront utilisées par défaut. Il faudra alors conserver le lien créé. <br>
            <select id="defaultIn" class="card__input" title="Choix de la langue/voix d'entrée de l'application.">
              <option value="" selected disabled>Quelle langue d'entrée ?</option> <?php
        $allLanguages="";
        $fd=fopen("supportedlanguages.csv","r");
        while($ligne=fgetcsv($fd,4096,";")) {
          if (count($ligne)>2) {
            $allLanguages.="
																																																									<option value=\"".$ligne[1]."\">".$ligne[0]."</option>\n";
          }
        }
        fclose($fd);
        echo $allLanguages;
        ?>
            </select>
            <br>
            <select id="defaultOut" class="card__input" title="Choix de la langue/voix de sortie de l'application.">
              <option value="" selected disabled>Quelle langue de sortie ?</option> <?php
        echo $allLanguages;
        ?>
            </select>
            <br>
          </p>
        </div>
        <button class="btn btn--block card__btn" onclick="var dIn=document.getElementById('defaultIn').value; var dOut=document.getElementById('defaultOut').value; var params=''; if (dIn !='') params+='&defaultIn='+dIn; if (dOut !='') params+='&defaultOut='+dOut; window.open(root+'/Translator2.html?'+params);">Y aller</button>
      </div>
    </li>
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">
            <span class="needbrowser"></span>Documentation et démonstrations de l'outil
          </div>
          <p class="card__text">Accès à la documentation et aux démonstrations disponibles sur Madoc.</p>
          <p class="card__text details">Vous trouverez des tutoriels pour : <br> &nbsp;&nbsp;- l'utilisation de l'outil de traduction en direct. <br> &nbsp;&nbsp;- créer des animations. <br> &nbsp;&nbsp;- traduire les sous-titres d'une vidéo. <br> &nbsp;&nbsp;Mais également des guides d'utilisations des applications enseignantes et étudiantes, ainsi que les liens vers ces dernières. </p>
        </div>
        <button class="btn btn--block card__btn" onclick="window.open('https\:\/\/madoc.univ-nantes.fr/course/view.php?id=58282');">Y aller</button>
      </div>
    </li>
  </ul>
  <div id="dialog-invite" title="Alert">
    <p id="dialog-invite-p"></p>
  </div>
  <script>
    $(function() {
      $(document).tooltip();
      $("#dialog-invite").dialog({
        autoOpen: false,
        modal: true,
        height: "auto",
        width: "auto",
        buttons: {
          Ok: function() {
            $(this).dialog("close");
          }
        }
      });
      var elements = document.getElementsByClassName("needbrowser");
      for (var i = 0; i < elements.length; i++) {
        elements[i].title = "We detect that your browser (" + whichBrowser() + ") has not a built-in Speech-to-Text functionality. Use Google Chrome of Microsoft Edge instead or create a Microsoft Azure key and use the external transcription capabilities.";
        elements[i].innerHTML = ' <i class = "fa fa-exclamation-triangle" style = "color: red;" aria - hidden = "true" > </i>';
      }
      if (!["Chrome", "Edge"].includes(whichBrowser())) $(".needbrowser").css("display", "inline");
    });
  </script>
  <footer id="nav-bottom">
    <img id="logoNU" alt="Logo Nantes Université" src="data:image/png;base64,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">
  </footer>
</body>
</html>