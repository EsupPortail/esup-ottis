<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

?>
<!DOCTYPE html>
<html lang="fr-FR"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Automatic translation for teaching purposes: Scenarios</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
@import url('https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Shadows+Into+Light&display=swap');

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

html {
  background-color: #f0f0f0;
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
  box-shadow: 0 20px 40px -14px rgba(0,0,0,0.25);
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
    padding-top: 56.25%; // 16:9 aspect ratio
  }
  @media(min-width: 40rem) {
    &::before {
      padding-top: 66.6%; // 3:2 aspect ratio
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
  font-family: 'Shadows Into Light', cursive;
  flex: 1 1 auto;
//  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 1.25rem;
}

.card__btn {
  font-family: Fjalla One;
}

.card__input {
  font-family: Fjalla One;
  background-color: rgb(150,255,150);
  border: 1px solid rgb(150,255,150);
  border-radius: 1rem;
  color: @gray-dark;
  padding: 0.5rem;
  display: block;
  width: 100%;
}

.details {
  font-size: 0.9em;
  height: 200px;
  overflow: auto;
}

// Tooltips style

/* 
  You want a simple and fancy tooltip?
  Just copy all [data-tooltip] blocks:
*/
[data-tooltip] {
  --arrow-size: 5px;
  position: relative;
  z-index: 10;
}

/* Positioning and visibility settings of the tooltip */
[data-tooltip]:before,
[data-tooltip]:after {
  position: absolute;
  visibility: hidden;
  opacity: 0;
  left: 50%;
  bottom: calc(100% + var(--arrow-size));
  pointer-events: none;
  transition: 0.2s;
  will-change: transform;
}

/* The actual tooltip with a dynamic width */
[data-tooltip]:before {
  content: attr(data-tooltip);
  padding: 10px 18px;
  min-width: 50px;
  max-width: 300px;
  width: max-content;
  width: -moz-max-content;
  border-radius: 6px;
  font-size: 14px;
  background-color: rgba(59, 72, 80, 0.9);
  background-image: linear-gradient(30deg,
    rgba(59, 72, 80, 0.44),
    rgba(59, 68, 75, 0.44),
    rgba(60, 82, 88, 0.44));
  box-shadow: 0px 0px 24px rgba(0, 0, 0, 0.2);
  color: #fff;
  text-align: center;
  white-space: pre-wrap;
  transform: translate(-50%,  calc(0px - var(--arrow-size))) scale(0.5);
}

/* Tooltip arrow */
[data-tooltip]:after {
  content: '';
  border-style: solid;
  border-width: var(--arrow-size) var(--arrow-size) 0px var(--arrow-size); /* CSS triangle */
  border-color: rgba(55, 64, 70, 0.9) transparent transparent transparent;
  transition-duration: 0s; /* If the mouse leaves the element, 
                              the transition effects for the 
                              tooltip arrow are "turned off" */
  transform-origin: top;   /* Orientation setting for the
                              slide-down effect */
  transform: translateX(-50%) scaleY(0);
}

/* Tooltip becomes visible at hover */
[data-tooltip]:hover:before,
[data-tooltip]:hover:after {
  visibility: visible;
  opacity: 1;
}
/* Scales from 0.5 to 1 -> grow effect */
[data-tooltip]:hover:before {
  transition-delay: 0.3s;
  transform: translate(-50%, calc(0px - var(--arrow-size))) scale(1);
}
/* 
  Arrow slide down effect only on mouseenter (NOT on mouseleave)
*/
[data-tooltip]:hover:after {
  transition-delay: 0.5s; /* Starting after the grow effect */
  transition-duration: 0.2s;
  transform: translateX(-50%) scaleY(1);
}
/*
  That's it for the basic tooltip.

  If you want some adjustability
  here are some orientation settings you can use:
*/

/* LEFT */
/* Tooltip + arrow */
[data-tooltip-location="left"]:before,
[data-tooltip-location="left"]:after {
  left: auto;
  right: calc(100% + var(--arrow-size));
  bottom: 50%;
}

/* Tooltip */
[data-tooltip-location="left"]:before {
  transform: translate(calc(0px - var(--arrow-size)), 50%) scale(0.5);
}
[data-tooltip-location="left"]:hover:before {
  transform: translate(calc(0px - var(--arrow-size)), 50%) scale(1);
}

/* Arrow */
[data-tooltip-location="left"]:after {
  border-width: var(--arrow-size) 0px var(--arrow-size) var(--arrow-size);
  border-color: transparent transparent transparent rgba(55, 64, 70, 0.9);
  transform-origin: left;
  transform: translateY(50%) scaleX(0);
}
[data-tooltip-location="left"]:hover:after {
  transform: translateY(50%) scaleX(1);
}



/* RIGHT */
[data-tooltip-location="right"]:before,
[data-tooltip-location="right"]:after {
  left: calc(100% + var(--arrow-size));
  bottom: 50%;
}

[data-tooltip-location="right"]:before {
  transform: translate(var(--arrow-size), 50%) scale(0.5);
}
[data-tooltip-location="right"]:hover:before {
  transform: translate(var(--arrow-size), 50%) scale(1);
}

[data-tooltip-location="right"]:after {
  border-width: var(--arrow-size) var(--arrow-size) var(--arrow-size) 0px;
  border-color: transparent rgba(55, 64, 70, 0.9) transparent transparent;
  transform-origin: right;
  transform: translateY(50%) scaleX(0);
}
[data-tooltip-location="right"]:hover:after {
  transform: translateY(50%) scaleX(1);
}



/* BOTTOM */
[data-tooltip-location="bottom"]:before,
[data-tooltip-location="bottom"]:after {
  top: calc(100% + var(--arrow-size));
  bottom: auto;
}

[data-tooltip-location="bottom"]:before {
  transform: translate(-50%, var(--arrow-size)) scale(0.5);
}
[data-tooltip-location="bottom"]:hover:before {
  transform: translate(-50%, var(--arrow-size)) scale(1);
}

[data-tooltip-location="bottom"]:after {
  border-width: 0px var(--arrow-size) var(--arrow-size) var(--arrow-size);
  border-color: transparent transparent rgba(55, 64, 70, 0.9) transparent;
  transform-origin: bottom;
}


html {
  background: linear-gradient(45deg, #243949, #2cacd1, #35eb93);
  background-size: 200% 200%;
}

body {
  padding: 0 4%;
  display: flex;
  flex-direction: row;
  margin: auto 0;
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

var root = window.location.href.replace("/scenarios.php","");

</script>
</head>
<body>


<ul class="cards">
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title" data-tooltip="Essai" data-tooltip-position="bottom">
    Transcription instantanée</div>
        <p class="card__text">Scenario d'un cours sous-titré en Français de manière instantanée, par exemple pour un public mal-entendant.</p>
        <p class="card__text details">Pour utiliser ce scénario avec une présentation au format .PPTX ou animation .html, il faut suivre les étapes suivantes :<br/>
            &nbsp;&nbsp;1. Aller à l'onglet "Complete Lesson" pour y charger la présentation.<br/>
            &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent.<br/>
            &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter.<br/>
            &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", aussi que les touches d'un pointeur laser permettent de faire défiler les transparents.<br/>
              <br/>
          Pour utiliser ce scénario avec WOOCLAP, il faut suivre les étapes suivantes :<br/>
          &nbsp;&nbsp;1. Aller à l'onglet "WOOCLAP", se connecter sur son compte WOOCLAP et lancer sa présentation comme habituellement.<br/>
          &nbsp;&nbsp;2. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter.<br/>
          &nbsp;&nbsp;3. Les touches "Flèche gauche" ou "Flèche droite", aussi que les touches d'un pointeur laser permettent de faire défiler les transparents.<br/>
        </p>
      </div>
      <button data-tooltip="Essai2" data-tooltip-position="bottom" class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=AANAYKCYNCNNNYNN');">Y aller</button>
    </div>
  </li>
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title">Sous-titrage anglais (projection)</div>
        <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais.</p>
        <p class="card__text details">Pour utiliser ce scénario avec une présentation au format .PPTX ou animation .html, il faut suivre les étapes suivantes :<br/>
            &nbsp;&nbsp;1. Aller à l'onglet "Complete Lesson" pour y charger la présentation.<br/>
            &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent.<br/>
            &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter.<br/>
            &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", aussi que les touches d'un pointeur laser permettent de faire défiler les transparents.<br/>
              <br/>
          Pour utiliser ce scénario avec WOOCLAP, il faut suivre les étapes suivantes :<br/>
          &nbsp;&nbsp;1. Aller à l'onglet "WOOCLAP", se connecter sur son compte WOOCLAP et lancer sa présentation comme habituellement.<br/>
          &nbsp;&nbsp;2. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter.<br/>
          &nbsp;&nbsp;3. Les touches "Flèche gauche" ou "Flèche droite", aussi que les touches d'un pointeur laser permettent de faire défiler les transparents.<br/>
        </p>
      </div>
      <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANKCNNCNNNYNN');">Y aller</button>
    </div>
  </li>
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title">Sous-titrage anglais avec présentation .PPTX sur uncloud</div>
        <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais. Les slides sont fournis via un lien public sur <a href="https://uncloud.univ-nantes.fr">uncloud</a>.</p>
        <p class="card__text details">Pour utiliser ce scénario, il faut suivre les étapes suivantes :<br/>
            &nbsp;&nbsp;1. Copier ci-dessous le lien public UNCLOUD de la présentation (au format .PPTX).<br/>
            &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent.<br/>
            &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter.<br/>
            &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", aussi que les touches d'un pointeur laser permettent de faire défiler les transparents.<br/>
        </p>
      </div>
      <p><input type="text" id="SC31" placeholder="lien public uncloud." class="card__input" /></p>
      <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANACNNCNNNYYY&tab=4'+'&link='+document.getElementById('SC31').value);">Y aller</button>
    </div>
  </li>
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title">Sous-titrage anglais avec présentation .html sur uncloud</div>
        <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais. Les slides sont fournis via un lien public sur <a href="https://uncloud.univ-nantes.fr">uncloud</a>.</p>
        <p class="card__text details">Pour utiliser ce scénario, il faut suivre les étapes suivantes :<br/>
            &nbsp;&nbsp;1. Copier ci-dessous le lien public UNCLOUD de la présentation (au format .html).<br/>
            &nbsp;&nbsp;2. Aller à l'onglet "Slides" pour diffuser les transparents et cliquer sur le transparent.<br/>
            &nbsp;&nbsp;3. Allumer le micro (icone rouge ou touche "Espace" ou une touche d'un pointeur laser) qui se mettra à clignoter.<br/>
            &nbsp;&nbsp;4. Les touches "Flèche gauche" ou "Flèche droite", aussi que les touches d'un pointeur laser permettent de faire défiler les transparents.<br/>
        </p>
      </div>
      <p><input type="text" id="SC41" placeholder="lien public uncloud." class="card__input" /></p>
      <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?myconfig=ABYANACNNCNNNYYY&tab=4'+'&htmllink='+document.getElementById('SC41').value);">Y aller</button>
    </div>
  </li>
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title">Outils de manipulation de slides</div>
        <p class="card__text">Accès à l'ensemble des outils OMIST de manipulation de slides</a>.</p>
        <p class="card__text details">Parmi les outils proposés, il est possible de :<br/>
            &nbsp;&nbsp;Préparer en amont une présentation/animation au format .html utilisable dans l'outil de traduction en direct. 
            Cette animation peut-être produite à partir de fichiers .PPTX (commentés ou non) ou de .PDF, accompagnés ou non du discours. Ces discours peuvent alors être incorporés à la demande lors de la séance en direct.<br/>
            &nbsp;&nbsp;Générer des animations multilingues pour les temps asynchrones. Comme précédemment, ces animations peuvent-être produite à partir de fichiers .PPTX commentés ou de .PDF, accompagnés du discours. Ce discours sera alors traduit dans diverses langues et lu lors de la diffusion de l'animation.<br/>
            &nbsp;&nbsp;Traduire les sous-titres d'une vidéo.<br/>
            &nbsp;&nbsp;Traduire des cours scénarisés au format XLIFF ou au format SCORM.<br/>
            &nbsp;&nbsp;Faire bien plus, la liste ici n'est pas exhaustive....
        </p>
      </div>
      <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?tab=9');">Y aller</button>
    </div>
  </li>
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title">Préparation de séance</div>
        <p class="card__text">Création d'un lien permettant de préparer à l'avance une séance</a>.</p>
        <p class="card__text details">Ici, il est possible de personnaliser le lien de connexion.<br/>
        Tous ces champs peuvent rester vides, dans ce cas, ce sont les valeurs par défaut qui seront utilisées.<br/>
        <br/>
            <input type="text" data-tooltip="Essai2" data-tooltip-position="bottom" title="N'importe quel mot, sans espace" class="card__input tooltip expand" id="persroomid" placeholder="Identifiant de séance (libre)" value=""  />
            <input type="text" title="valeur conseillée : 2" class="card__input" id="persmaxlines" placeholder="Nombre maximum de lignes dans le buffer" value=""  />
            <input type="text" id="persolink" placeholder="lien public uncloud (PPTX)." class="card__input" />
            <input type="text" id="persohtmllink" placeholder="lien public uncloud (HTML)." class="card__input" />
            <input type="text" id="persoconfig" placeholder="code de configuration (à récupérer sur l'application)" class="card__input" />
            <select id="persotab" class="card__input">
                <option value="0" selected disabled>Quel onglet ouvert par défaut ?</option>
                <option value="0">Onglet "Informations"</option>
                <option value="1">Onglet "Classroom"</option>
                <option value="2">Onglet "Notebook"</option>
                <option value="3">Onglet "Complete lesson"</option>
                <option value="4">Onglet "Slides"</option>
                <option value="7">Onglet "Share"</option>
                <option value="8">Onglet "WOOCLAP"</option>
                <option value="9">Onglet "Slide Tools"</option>
            </select>
            <input type="text" title="Pour utiliser les fonctionnalités de transcription et traduction de Microsoft, il faut disposer d'une clé de connexion, dont la création se fait sur Azure." id="persomicrosoftkey" placeholder="Clé de connexion (pour utiliser la transcription de) Microsoft" class="card__input" />
            <input type="text" title="Valeur conseillée = francecentral" id="persomicrosoftregion" placeholder="Région de connexion Microsoft" class="card__input" />
            <select id="persotheme" class="card__input">
                <option value="" selected disabled>Quel thème ?</option>
                <option value="">Thème clair (par défaut)</option>
                <option value="theme2">Thème sombre</option>
            </select>
        </p>
      </div>
      <button class="btn btn--block card__btn" onclick="var linkteacher = CreateLink(); var linkstudent=CreateLinkStudent(); if (linkstudent != '') linkstudent='\n\nEt voici le lien étudiant : \n '+root+'/Student.php?'+linkstudent; alert('Voici le lien de connexion, à utiliser n\'importe quand\n'+root+'/index.php?'+linkteacher+linkstudent);">Générer le lien</button>
      <button class="btn btn--block card__btn" onclick="window.open(root+'/index.php?'+CreateLink());">Y aller</button>
    </div>
  </li>

</ul>

</body></html>