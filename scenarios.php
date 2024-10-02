<?php require("./NoCache.php"); ?>
<?php 
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$_SESSION["from"]="scenarios";
?>
<!DOCTYPE html>
<html lang="fr-FR">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMIST - Scenarios</title>
    <link rel="icon" type="image/png" href="./images/favicon.svg">
    <link rel="stylesheet" href="./styles/scenarios.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <script src="./jquery/jquery.js"></script>
    <script src="./jquery/jquery-ui.min.js"></script>
    <script src="./js/google-translate-script.js"></script>
    <script src="./js/translate-button.js"></script>
    <script src="./js/common-func.js"></script>
    <script src="./js/scenarios-script.js"></script>
    <script src="./js/scenarios-carrousel.js"></script>
    <script>
    <?php
      if (isset($_GET["external"])) {
        echo "var isExternal='yes';\n";
      } else {
        echo "var isExternal='no';\n";
      }
    ?>
    </script>
  </head>
  <body onload="applyConfig();">
    <header>
      <nav>
        <a href="https://automatictranslator.sciences.univ-nantes.fr/AutomaticTranslator/scenarios.php"><img id="logoNU" src="./images/omist-top-logo-nav_ssfd.png" alt="Logo Nantes Université"></a>
        <menu>
          <li><a href="mailto:omist-dev@univ-nantes.fr" style="text-decoration: none" ><i class="fa fa-envelope fa-2x" id="iconeAideEmail"></i><span class="icone-menu">Contact</span></a></li>
          <li><a href="https://madoc.univ-nantes.fr/course/view.php?id=58282" target="_blank" style="text-decoration: none" ><i class="fa fa-question-circle fa-2x" id="iconeAideMadoc"></i><span class="icone-menu">Aide sur Madoc</span></a></li>
          <li id="google_translate_element"><button id="translateButton" type="button"><b>Translate this page</b></button></li>
        </menu>
      </nav>
    </header>
    <main>
      <div class="container">
        <div class="slider-wrapper">
          <div id="prev" class="slide-div">
            <button id="prev-slide" class="slide-button material-symbols-rounded">
              <i class="fa fa-chevron-left"></i>
            </button>
          </div>
          <div class="carousel-title">
            <h2> Choisissez votre scénario </h2>
            <p id="toogle-text"> Êtes-vous externe à Nantes Université ? <input id="toggle-external" class="toggle" type="checkbox" onclick="updateExternal()" title="External to Nantes Université"></p>
          </div>
          <ul class="image-list">
            <li class="image-item">
              <h5><i class="fa fa-deaf icon-scenario" aria-hidden="true"></i>Transcription Instantanée</h5>
              <button type="button" class="favorite-button" id="scenario-1-fav-opt" title="favorite"><i class="fa fa-star-o"></i></button>
              <hr>
              <p>Scénario d'un cours sous-titré en français de manière instantanée, par exemple pour un public mal-entendant.</p>
              <button type="button" class="scenario-button" id="scenario-1-button">Accéder au scénario</button>
            </li>
            <li class="image-item">
              <h5><span class="material-symbols-outlined icon-scenario">cast</span>Sous-titrage Anglais</h5>
              <button type="button" class="favorite-button" id="scenario-2-fav-opt" title="favorite"><i class="fa fa-star-o"></i></button>
              <hr>
              <h6>Projection</h6>
              <p>Pour un cours donné en français sous-titré en anglais.</p>
              <button type="button" class="scenario-button" id="scenario-2-button">Accéder au scénario</button>
            </li>
            <li class="image-item">
              <h5><i class="fa fa-file-powerpoint-o icon-scenario" aria-hidden="true"></i>Sous-titrage Anglais</h5>
              <button type="button" class="favorite-button" id="scenario-3-fav-opt" title="favorite"><i class="fa fa-star-o"></i></button>
              <hr>
              <h6>Présentation .PPTX sur UNCloud</h6>
              <p>Pour un cours donné en français sous-titré en anglais avec une présentation partagée par lien public sur UNCloud.</p>
              <p>Saisissez le lien de votre fichier ci-dessous puis cliquez sur "Accéder au scénario" :</p>
              <input type="text" id="scenario-3-input" title="Presentation link" placeholder="https://">
              <button type="button" class="scenario-button" id="scenario-3-button">Accéder au scénario</button>
            </li>
            <li class="image-item">
              <h5><span class="material-symbols-outlined icon-scenario">html</span>Sous-titrage Anglais</h5>
              <button type="button" class="favorite-button" id="scenario-4-fav-opt" title="favorite"><i class="fa fa-star-o"></i></button>
              <hr>
              <h6>Présentation .HTML sur UNCloud</h6>
              <p>Pour un cours donné en français sous-titré en anglais avec une présentation partagée par lien public sur UNCloud.</p>
              <p>Saisissez le lien de votre fichier ci-dessous puis cliquez sur "Accéder au scénario" :</p>
              <input type="text" id="scenario-4-input" title="Presentation link" placeholder="https://">
              <button type="button" class="scenario-button" id="scenario-4-button">Accéder au scénario</button>
            </li>
            <li class="image-item" id="scenario-parameter">
              <h5><i class="fa fa-wrench icon-scenario" aria-hidden="true"></i>Préparation de séance</h5>
              <button type="button" class="favorite-button" id="scenario-5-fav-opt" title="favorite"><i class="fa fa-star-o"></i></button>
              <hr>
              <p>Création d'un lien permettant de préparer une séance.</p>
              <input type="text" title="Définissez ici l'identifiant de connexion au cours. Il peut s'agir de n'importe quel mot, sans espace." class="card__input" id="persroomid" placeholder="Identifiant de séance (libre)" value="">
              <input type="text" title="Nombre de lignes maximal qui seront stockées dans le buffer. La valeur conseillée est 2" class="card__input" id="persmaxlines" placeholder="Nombre maximum de lignes dans le buffer" value="">
              <input type="text" id="persolink" title="Coller ici le lien uncloud public de l'endroit où se trouve l'animation pptx que vous souhaitez utiiser" placeholder="lien public uncloud (PPTX)." class="card__input">
              <input type="text" id="persohtmllink" title="Coller ici le lien uncloud public de l'endroit où se trouve l'animation html que vous souhaitez utiiser" placeholder="lien public uncloud (HTML)." class="card__input">
              <input type="text" id="persoconfig" aria-label="Code de configuration" title="Code de configuration" placeholder="code de configuration (à récupérer sur l'application)" class="card__input">
              <select id="persotab" title="Choix de la fenêtre qui sera ouverte par défaut.">
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
              <button type="button" class="scenario-button" id="scenario-5-button">Générer le lien</button>
            </li>
            <li class="image-item" style="display: none">
              <h5><span class="material-symbols-outlined icon-scenario">live_tv</span>Sous-titrage Anglais</h5>
              <button type="button" class="favorite-button" id="scenario-6-fav-opt" title="favorite"><i class="fa fa-star-o"></i></button>
              <hr>
              <h6>Traduction directe</h6>
              <p>Pour un cours donné en français sous-titré en anglais. La traduction se fait directement, la phrase se consolide au fur et à mesure.</p>
              <button type="button" class="scenario-button" id="scenario-6-button">Accéder au scénario</button>
            </li>
          </ul>
          <div id="next" class="slide-div">
            <button id="next-slide" class="slide-button material-symbols-rounded">
              <i class="fa fa-chevron-right"></i>
            </button>
          </div>
        </div>
        <div class="slider-scrollbar">
          <div class="scrollbar-track">
            <div class="scrollbar-thumb"></div>
          </div>
        </div>
      </div>
      <div class="container-direct-links">
        <div class="direct-links-title">
              <h2> Accès directs </h2>
              <p> Vous pouvez accéder directement à d'autres outils OMIST </p>
        </div>
        <ul id="direct-links">
        <li>
            <p> Accès à la plateforme enseignante sans pré-configuration. </p>
            <button type="button" id="direct-access-teacher">Plateforme enseignante</button>
          </li>
          <li>
            <p> Accès à l'ensemble des outils OMIST de manipulation de slides et de vidéos. </p>
            <button type="button" id="direct-access-tools">Outils de manipulation de slides</button>
          </li>
          <li>
            <p> Outil de traduction pour une conversation multilingue en duo. </p>
            <button type="button" id="direct-access-conv">Conversation à deux</button>
          </li>
        </ul>
      </div>
    </main>
    <footer>
      <div class="footer-left">
        <ul>
          <li><a href="https://next-isite.fr/" target="_blank"><img id="logoNEXT" src="./images/logo-next-NU.png" alt="Logo NEXT"></a></li>
        </ul>
      </div>
      <div class="footer-mentions">
        <p><button class="open-modal-btn" onclick="openModal('access')">Accessibilité</button></p>
        <p><button class="open-modal-btn" onclick="openModal('cookies')">Cookies</button></p>
        </div>
      <div class="footer-right">
        <div class="footer-support">
          <!-- <p><button class="open-modal-btn" onclick="openModal('access')">Accessibilité</button></p>
          <p><button class="open-modal-btn" onclick="openModal('cookies')">Cookies</button></p> -->
          <p>Avec le soutien de :&emsp; </p>
          <ul>
            <li><a href="https://www.economie.gouv.fr/france-2030" target="_blank"><img id="logoFrance2030" src="./images/logo-france2020_rouge.png" alt="Logo France 2030"></a></li>
            <li><a href="https://www.paysdelaloire.fr/" target="_blank"><img id="logoPaysDeLaLoire" src="./images/logo-region-pays-loire_bleu-court.png" alt="Logo Pays de la Loire"></a></li>
            <li><a href="https://metropole.nantes.fr/" target="_blank"><img id="logoNantesMetropole" src="./images/logo-nantes-metropole_quadri.jpg" alt="Logo Nantes Métropole"></a></li>
            <li><a href="https://www.euniwell.eu/" target="_blank"><img id="logoEUniWell" src="./images/logo-euniwell-brand-4.png" alt="Logo EUniWell"></a></li>
          </ul>
        </div>
      </div>
    </footer>
    <div class="modal-overlay hide" onclick="closeModal(event, true)">
        <div class="modal-wrapper">
            <div class="close-btn-wrapper">
                <button class="close-modal-btn" onclick="closeModal()">
                ×
                </button>
            </div>
            <h2 id="mention-title"></h2>
            <div class="modal-content" id="mention-content"></div>
        </div>
    </div>
  </body>
</html>