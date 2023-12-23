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

.details {
  font-size: 0.9em;
  height: 200px;
  overflow: auto;
}
    </style>
</head>
<body>


<ul class="cards">
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title">Transcription instantanée</div>
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
      <button class="btn btn--block card__btn" onclick="window.open('https://misc-sciences.univ-nantes.fr/AutomaticTranslator-test/?myconfig=AANAYKCNYC');">Y aller</button>
    </div>
  </li>
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title">Sous-titrage anglais</div>
        <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais.</p>
        <button class="btn btn--block card__btn">Y aller</button>
      </div>
    </div>
  </li>
  <li class="cards__item">
    <div class="card">
      <div class="card__content">
        <div class="card__title">Sous-titrage anglais avec présentation .PPTX sur uncloud</div>
        <p class="card__text">Scenario d'un cours donné en Français sous-titré en Anglais. Les slides sont fournis via un lien public sur <a href="https://uncloud.univ-nantes.fr">uncloud</a>.</p>
        <p><input type="text" placeholder="lien public uncloud." class="btn btn--block card__btn" /></p>
        <button class="btn btn--block card__btn">Y aller</button>
      </div>
    </div>
  </li>
</ul>

</body></html>