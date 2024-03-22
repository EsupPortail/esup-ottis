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

html {
  background: linear-gradient(45deg, #243949, #2cacd1, #35eb93);
  background-size: 200% 300%;
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
  background-size: 200% 300%;
  position: absolute;
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
<button id="opentranslator" aria-hidden="true" aria-label="Translate this page" title="Translate this page" class="button-10" role="button" onclick="this.style.display='none'; var glink=document.createElement('script'); glink.src='https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'; document.body.appendChild(glink);"><i class="fa fa-language" aria-hidden="true"></i> Translate this page</button>
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
          <p class="card__text details">Pour utiliser ce scénario avec une présentation au format .PPTX ou animation .html, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Aller à l'onglet "Complete Lesson" pour y charger la présentation. <br> &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent. <br> &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter. <br> &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents. <br>
            <br> Pour utiliser ce scénario avec WOOCLAP, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Aller à l'onglet "WOOCLAP", se connecter sur son compte WOOCLAP et lancer sa présentation comme habituellement. <br> &nbsp;&nbsp;2. Allumer le micro (icone rouge) qui se mettra à clignoter. <br> &nbsp;&nbsp;3. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents. <br>
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
            <br> Pour utiliser ce scénario avec WOOCLAP, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Aller à l'onglet "WOOCLAP", se connecter sur son compte WOOCLAP et lancer sa présentation comme habituellement. <br> &nbsp;&nbsp;2. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter. <br> &nbsp;&nbsp;3. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents. <br>
          </p>
        </div>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANKCNNCNNNYNY');">Y aller</button>
      </div>
    </li>
    <li class="cards__item">
      <div class="card">
        <div class="card__content">
          <div class="card__title">
            <span class="needbrowser"></span>Sous-titrage anglais avec présentation .PPTX sur uncloud
          </div>
          <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais. Les slides sont fournis via un lien public sur <a href="https://uncloud.univ-nantes.fr">uncloud</a>. </p>
          <p class="card__text details">Pour utiliser ce scénario, il faut suivre les étapes suivantes : <br> &nbsp;&nbsp;1. Copier ci-dessous le lien public UNCLOUD de la présentation (au format .PPTX). <br> &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent. <br> &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter. <br> &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", ainsi que les touches d'un pointeur laser permettent de faire défiler les transparents. <br>
          </p>
        </div>
        <p>
          <input type="text" id="SC31" placeholder="lien public uncloud." class="card__input">
        </p>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANACNNCNNNYYY&tab=4'+'&link='+document.getElementById('SC31').value);">Y aller</button>
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
          <input type="text" id="SC41" placeholder="lien public uncloud." class="card__input">
        </p>
        <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANACNNCNNNYYY&tab=4'+'&htmllink='+document.getElementById('SC41').value);">Y aller</button>
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
          <p class="card__text details">Ici, il est possible de personnaliser le lien de connexion. <br> Tous ces champs peuvent rester vides, dans ce cas, ce sont les valeurs par défaut qui seront utilisées. <br>
            <br>
            <input type="text" title="Définissez ici l'identifiant de connexion au cours. Il peut s'agir de n'importe quel mot, sans espace." class="card__input" id="persroomid" placeholder="Identifiant de séance (libre)" value="">
            <input type="text" title="Nombre de lignes maximal qui seront stockées dans le buffer. La valeur conseillée est 2" class="card__input" id="persmaxlines" placeholder="Nombre maximum de lignes dans le buffer" value="">
            <input type="text" id="persolink" title="Coller ici le lien uncloud public de l'endroit où se trouve l'animation pptx que vous souhaitez utiiser" placeholder="lien public uncloud (PPTX)." class="card__input">
            <input type="text" id="persohtmllink" title="Coller ici le lien uncloud public de l'endroit où se trouve l'animation html que vous souhaitez utiiser" placeholder="lien public uncloud (HTML)." class="card__input">
            <input type="text" id="persoconfig" placeholder="code de configuration (à récupérer sur l'application)" class="card__input">
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
</body>
</html>