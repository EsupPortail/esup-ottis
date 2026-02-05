- [X] Enlever automatiquement les tics de parole ("en fait" par exemple) 
- [X] Bug import pptx dans Complete Lesson, manque le dernier slide
- [X] Ajouter un outil pour intégrer des notes (.txt) dans un .pptx dans les OMIST tools
- [X] Utiliser DeepL Translate dans les OMIST tools
- [X] Voir s'il est possible d'utiliser Argos Translate dans les OMIST Tools (ok pour FREETRANSLATE)
- [ ] Donner la possibilité d'avoir des pré-enregistrements des textes joués dans les animations (cela permettra de s'assurer que le texte pourra être lu dans certaines langues avec de voix "Microsoft" par exemple). Il faudra ajouter un champ supplémentaire aux notes des slides. Voir la faisabilité (facultatif)
- [X] Dans les OMIST Tools, donner la possibilité d'exécuter les conversions en tâche de fond. Cela permettra de traiter les animations longues. Pour cela, il faut utiliser cron et une file de tâches de conversions. A mettre en place. Il y aura des aspects d'interfaçage aussi.
- [X] WOOCLAP ne peut plus être intégré dans un iframe depuis les dernières versions de chrome. Voir ce qui peut coincer. Ceci est probablement dû à une nouvelle gestions des bac à sables.
- [X] Intégrer les outils de traduction des fichiers de type SCORM et XLIFF aux Omist Tools.
- [X] Mettre à jour les droits sur le serveur pour le token qui permet d'inviter une personne extérieure à l'université (Automatic Translator > Teacher > Share) : "This token has expired !"

Petit point d'étape :
1) J'ai intégré toutes les modifications faites dans les branches develop et master dans la branche test. J'ai aussi essayé de la nettoyer de tous les scripts obsolètes et inutiles (il y en a encore quelques uns). La branche "test" est donc en train de devenir la future branche déployée sur le nouveau serveur. La branche "master" est celle qui sert pour le serveur misc actuellement en exploitation. A mon retour, on discutera de l'avenir de ces différentes branches.
2) J'ai déployé sur misc la version test. Sur ce serveur, je n'ai pas été en mesure d'installer mysql. J'ai donc dupliqué les différents scripts (sendJob, isReady et ReadDatabase) pour faire en sorte qu'ils utilisent un fichier tampon plutôt qu'une BDD. ça fonctionne et c'est assez transparent. 
3) J'ai aussi séparé les paramètres de configuration de la première page en plusieurs parties (avec un accordéon). Il commençait à y en avoir beaucoup sur la même page. Au passage, j'ai créé un onglet "Slide Tools", ça évite de chercher le lien.
4) La semaine du 3/01, je te propose te regarder les différents points ci-dessous. Nb: il y en a pour plus d'une semaine (ce sont des choses que j'aimerai qu'on voit à partir de janvier), il ne s'agit donc pas de tout faire.

- [X] Se "pencher sur les retours qu'on a eu lors de la formation du 15 décembre et mettre en place un tutoriel pour l'utilisation des mots-clés pour les animations." _**(attendre l'outil d'édition des animations)**_

- [X] J'ai repéré quelques corrections à apporter
    - [X] Sur le masquage des icones. Si on fait cette suite d'actions "Show icons"->No, "Pin code ?"->Yes, "Show icons"->Yes, "Show icons"->No, la case "Pin code ?" est cochée dans on s'attendrait à voir le pin code mais il est caché. C'est un détail à mon avis.
    - [X] Sur les flèches à droite et gauche des sous-titres. La flèche à gauche n'est visible qu'après avoir déplacé la barre des sous-titres et la barre droite est très en dehors de l'écran (sur ma résolution au moins). Je propose de désolidariser ces flèches de la barre des sous-titres et de les placer en "position: fixed" en bas à gauche et droite de l'écran. ça sera plus simple.
_**(UPDATE Jeanne : affichage ok en local et sur la VM, à tester avec d'autres résolutions)**_

- [X] Continuer les tests sur whisper, et notamment voir s'il est possible d'utiliser les GPU dans une VM. Voir si la procédure décrire ici [https://www.xda-developers.com/how-use-gpu-virtualbox/] s'applique. Il vient de sortir une version qui tourne dans le navigateur de whisper [https://github.com/xenova/whisper-web]. A tester aussi surement. 
_**(UPDATE Jeanne : GPU -> pas de changement par rapport à avant les vacances au niveau de la rapidité. Voir si je n'ai pas fait une erreur de connexion. Pour la version de whisper en ligne -> on ne peut utiliser que les modèles tiny et base, qui ne sont pas assez performants pour nous.)**_ _**(UPDATE JB : J'ai commencé à installer une version un peu tunée de whisper web sur notre serveur. C'est un peu bancal car je ne suis pas très fort en reverse proxy apache et on ne peut pas tout faire non plus. ça sera à revoir de toutes façons lorsqu'on aura le serveur définitif. En tout cas, ça fonctionne ici : https://172.26.98.138, le reste des outils est accessible en http. ça a l'air de fonctionner avec le modèle medium)**_ 
_**(attendre d'avoir Linux sur la machine)**_

- [ ] Meta vient de sortir un nouveau modèle de language très impressionnant qui permet de faire de la traduction speech to speech en gardant la voix de la personne qui parle et quasiment en temps réel (2 secondes d'écart). C'est ici [https://github.com/facebookresearch/seamless_communication/]. Je pense qu'il faut le tester, faire les démos proposées sur HuggingFace (notamment celui [https://huggingface.co/spaces/facebook/seamless-streaming]), regarder le .iypnb, voir ce qui pourrait nous servir. _**(attendre d'avoir Linux sur la machine. Seamless streaming semble ne plus fonctionner)**_

- [X] Créer un outil d'édition des animations. La première phase va correspondre à reprendre un peu ce qu'on a déjà dans l'application enseignante et dans ExtractSlidesNotesAppHTML.php, pour charger une application dans un editeur minimaliste (les discours dans des textarea), et générer une application avec les modifications. Ceci uniquement pour voir la faisabilité de la chose. 
    

- [X] Ajouter dans les scénarios un lien vers Madoc

5) J'ai essayé de mettre en place une sorte de système de scénario tout fait pour faciliter la prise en main. J'ai donc ajouté quelques fonctionnalités accessibles via l'adresse (fournir un lien uncloud du PPTX ou du .HTML, accéder directement à un onglet,....). ça se trouve ici : [https://misc-sciences.univ-nantes.fr/AutomaticTranslator-test/scenarios.php].






Pour moi.
1) dans teacher-func.js (ou maintenant dans common-func.js), dans la définition de StudentCompleteTranslation2, pourquoi on fait appel à StudentCompleteTranslation dans le sinon ? Bug ?
2) dans teacher-func.js, revoir SlideBackward() (quand on retourne à la slide précédente, on veut revenir à la première phrase des commentaires, pas à la dernière)
3) On/Off du micro par les touches ou le pointeur laser ne fonctionne que dans l'onglet slides. Il faudrait que ça fonctionne au moins dans l'onglet wooclap. _**(UPDATE Jeanne : normalement ça fonctionne partout avec les touches du clavier)**_

## Partie dédiée à l'outil principal
- [X] Mettre le fullscreen en icône (c'est une image pour le moment)
- [X] Ajouter possibilité dans l'onglet Share : avoir seulement les sous-titres -> ouvre une fenêtre de la taille des sous-titres, avec la barre des sous-titres en haut _**(UPDATE : tester la traduction instantanée.)**_
    - [X] Mettre en forme quand on utilise la traduction live (cf feuille de style)
    - [ ] Police des sous-titres qui réduit quand il n'y a plus de place ?
- [X] OBSOLETE : Ajouter une barre d'avancement dans l'onglet Slide qui calcule le temps de lecture du sous-titre affiché. L'idée serait que l'orateur ne recommence à parler que lorsque la barre est à la moitié, pour permettre le temps de lecture. (censé déjà exister, à retrouver dans le code)
- [X] Changer send by mail dans l'onglet speech par Download (idem côté Student x2).
- [X] Changer le timeout de SlideNotes(). Si pptx : 30000, si HTML : 300000.
- [ ] ~~Réduire la barre d'outils quand on est en plein écran (scaleY à 10/20%, et à 1 quand onmouseover)~~
- [ ] la navigation au clavier ne fonctionne pas très bien. Ex : on peut naviguer dans le menu avec les flèches sauf quand on arrive dans l’onglet „Slides“ car les flèches servent ici à passer les slides. Il y a aussi des problèmes avec la navigation par tabulation. Imaginer de ne plus du tout naviguer avec les flèches dans le menu principal (et réserver les flèches pour faire passer les slides), utiliser la tabulation pour voyager dans toute l’application et avoir un ordre cohérent et ajouter quelques raccourcis (il y en a déjà dans l’application étudiante, par exemple la touche [s] amène directement vers les slides) ?

## Partie dédiée à l'outil étudiant
- [X] Voice speed : indiquer des valeurs et celle par défaut


## Partie dédiée aux OMIST Tools
- [X] Changer l'ordre de "convert pdf" et "convert pptx" pour avoir le même que pour la création d'animation
- [X] Dans AppTemplate.txt, mettre les images play/fullscreen/stop/pause au format SVG et harmoniser les couleurs : télécharger une image svg, l'ouvrir avec l'éditeur de texte pour récupérer le code, insérer le code dans le HTML avec des balises svg
- [X] Revoir l'interface de l'animation dans AppTemplate.txt
- [X] ~~Quand on convertit on un pptx en animation -> décalage des images (à tester si ça fait pareil quand c'est un pdf)~~ _**(UPDATE : lié à LibreOffice, préférable de passer par un PDF avant si on éviter un potentiel décalage.)**_
- [ ] ~~BUG : quand on ouvre une animation avec "click here" et pas les téléchagements : on ne peut pas faire pause, les animations défilent~~
- [ ] Faire une application qui permet, à partir d'une vidéo, d'en extraire le discours, de le traduire, de le faire lire par une voix de synthèse et de remettre le discours traduit dans la vidéo.
    - [ ] Enchaîner les tools "Audio/video file transcription" et "Subtitle reader" en un seul tool
- [ ] Translate PPTX : le texte dans les diagrammes/formes ne sont pas traduits
- [X] Create web animation PDF + txt : ajouter un txt type à télécharger pour avoir le bon format dans le fichier
- [X] Pour la traduction de fichier SRT : ajouter une condition dans le code pour le dernier timecode : si .srt -> 99,999 sinon -> 99.999
- [ ] **BUG : lorsqu'on créé une caspule à partir d'un PDF et d'un speech donné, la dernière slide n'est pas affichée. Pour contourner ce bug, il faut ajouter une slide de plus dans speech.txt que le nombre de slides réel.**
- [ ] **BUG : la traduction d'un fichier SCORM ne fonctionne pas, il renvoit le fichier initial. Tout fonctionne quand on utilise faketranslate.**

## Partie dédiée à l'éditeur d'animation
- [X] Supprimer les vertical tab (VT) des speechs;
- [X] Faire un affichage de la taille du curseur lors du choix; _**(UPDATE : affichage  du curseur "+" pour avoir un ordre d'idée. Ce n'est n'est pas la forme du curseur sélectionné par l'utilisateur qui est affiché.)**_
- [X] Laisser un curseur qui indique l'emplacement de la souris quand on choisit "mouse go" sur l'animation; _**(UPDATE : à vérifier sur d'autres résolutions d'écran.)**_
    - [X] Bug : curseur apparait si on clique sur l'image mais on n'a pas sélectionné "mouse go"
- [X] Léger décalage des coordonnées dans "mouse go" sur l'animation finale  _**(UPDATE : à vérifier sur d'autres résolutions d'écran.)**_
- [X] Image des slides rognée en bas quand on est en plein écran ou page à la taille de l'écran
- [X] "Mouse go" qui affiche par moment le font style au lieu de juste les coordonnées de la souris _**(UPDATE : à vérifier avec Claire qu'elle n'a plus l'erreur.)**_
- [X] Supprimer les retours à la ligne automatiques quand on ajoute un keyword
- [X] Réduire les bordures (border-collapse)
- [X] Ajouter barre scroll dans le speechArea (overflow-y)
- [X] Centre l'image de la slide sélectionnée (à voir si c'est compatible avec les coordonnées de la souris)
- [X] Possibilité de mettre l'éditeur d'animation en full screen
- [X] Sur les boutons, ajouter des titres, des icônes, harmoniser les couleurs avec le thème, voir pour faire des boutons arrondis
- [X] Si on utilise mouse go mais qu'on ne va pas jusqu'au bout (on ferme la pop-up avec la croix), le curseur reste sur l'image.
- [X] Insérer les keywords avant la phrase si le curseur est à 0, après la phrase sinon (pour le moment ça insère à la ligne n+1)  _**(UPDATE : voir avec Claire ses manipulations.)**_
- [X] Modifier toutes les boîtes d'alert (fait quitter le plein écran quand elles se déclenchent)
- [ ] ~~Pas intuitif de passer par toutes les langues (si on fait une modif à la main sur une langue, elle ne se répercute pas sur les autres). Limiter à juste la langue d'origine, puis l'utilisateur doit télécharger le speech et le remettre dans sa présentation avec le convert ? A voir si possible de télécharger direct un pptx en sortie ?~~
- [X] Pour addOptions : même si on close la fenêtre pour annuler notre action, le chanegement est quand même pris en compte
- [X] Quand on ajoute une nouvelle ligne (vide ou non), il faut qu'une nouvelle ligne (vide) soit ajoutée dans les autres langues
- [X] Voir si c'est faisable quand on modifie à la main un keyword, qu'il soit modifié dans toutes les langues
- [X] Voir pour le keyword notification : pour l'instant le même message est ajouté dans toutes les langues, on peut le modifier à la main dans chaque langue. Voir si quand on ajoute une notification, on ne peut pas ajouter la traduction dans les autres langues ?
- [X] Ne pas autoriser les cut & paste (car si on le fait sur des mots clés -> pas de répercussion sur les autres langues)
- [X] Changer le message addAllLanguages()
- [X] Faire un tutoriel
- [ ] Ajouter un nouveau mot-clé qui permet d'afficher des images (dans le même principe que pour notification et resources)

## Partie dédiée à la sécurisation de l'application
- [ ] Sur le serveur. Créer un user/group spécifique "apache". Modifier la configuration d'apache pour qu'il soit exécuté avec cet utilisateur (dans envvars). Modifier les droits des répertoires /var/www/html. /!\ : il faudra voir comment les tâches CRON, exécutées actuellement en root, se comportent (faire un crontab spécifique pour l'utilisateur apache ? "crontab -u apache -e").
- [X] Placer tous les scripts php qui ne sont qu'inclus dans des sous-répertoires adaptés (comme Security par exemple). ça concerne principalement config.php. Fermer les accès à ces répertoires (.htaccess ou via ssl-default.conf)
- [X] Faire la même chose que précédemment dans les OMIST_Tools si on déplace config.py, les fichiers impactés sont : 
    - OMIST_Tools/ExtractSpeech/SpeechExtractionFromPPTX.py
    - OMIST_Tools/ConvertPPTX/PPTX_TXT_to_PPTX.py
    - OMIST_Tools/TranslatePPTX/TranslatePPTX.py
    - OMIST_Tools/utils.py
    - OMIST_Tools/CreateApp/CreateMultiLingualAnim.py
    - OMIST_Tools/TranslateSCORM/TranslateSCORM.py
    - OMIST_Tools/TranslateXLIFF/TranslateXLIFF.py
    - OMIST_Tools/TranslateSRT/TranslateSRT.py
    - OMIST_Tools/ConvertPDF/PDF_TXT_to_PPTX.py

- [X] Dans les OMIST_Tools, il faut s'assurer que le "referer" est bien le site automatictranslator. ça revient à ajouter "include('Security/referer.php');" aux scripts concernés.

## Partie dédiée à la page Admin
- [ ] quoi mettre dedans ?
- [ ] quels niveaux privilèges différents ?
- [X] OBSOLETE : comment faire l'authentification externe ? Intégrer dans la page admin la génération de mdp pour htaccess, ou utiliser une autre méthode ?

## Partie dédiée à la télécommande
- [X] revoir l'appli

## Partie dédiée Libretranslate
- [ ] installer en local
- [ ] tester et voir si faisable
- [ ] si faisable : voir avec la DSIN s'ils peuvent fournir un serveur qui hébergerait

## Partie dédiée à la mesure de l'utilisation de l'outil
- [X] objectif : avoir le nombre d'utilisateurs, leur utilisation, l'utilisation des quotas de traduction (éventuellement par système voire clé utilisée) pour avoir un „modèle économique“, avec une interface de visualisation (avec des courbes qu’on peut directement copier dans des rapports)
- [X] un bout dans Stats.php, une écriture depuis index.php dans "tpm/Statslog.txt"
- [X] Bug : l'URL des OMIST Tools dans l'application enseignante est .../indexNOCAS.php, ce qui fausse l'user id dans la BD -> création d'un bouton dans Complete lesson
- [X] Ne prendre les données que pour la VM master
- [X] Mettre en place un basculement sur LibreTranslate quand le quota de Deepl est dépassé
- [ ] Mettre en place un mail automatique quand le quota de Deepl est dépassé

## Priorisation ? Mais ça n'est pas forcément à suivre

### Partie dédiée à l'éditeur d'animation
- ~~Voir pour le keyword notification...~~ ça me semble une très bonne idée.

### Partie "nouvelle authentification externe"
Notre premier compte "client" a été créé ce week-end (sans que ça soit fait par nous) pour quelqu'un d'Avignon (cyrielle.garson@univ-avignon.fr). En outre, actuellement on n'a pas trop d'informations sur la personne qui demande un compte. Là c'était facile car on savait d'où venait la demande. A FAIRE : 
- [X] Peut-être lui écrire pour savoir si ça s'est bien passé, en indiquant que ces comptes externes sont une nouveauté et qu'on aimerait un retour si possible.
- [X] réfléchir au champs qu'on pourrait ajouter au formulaire de demande de compte, ensuite ça sera assez facile d'adapter le formulaire et la BDD. **-> Université, l'utilisation (cours/conférence, traduction/transcription), comment la personne a connu OMIST ?**

### Partie dédiée à la télécommande
- revoir l'appli
J'ai un peu oublié ce dont il s'agit mais c'est vrai que ça aurait vraiment besoin d'un bon relooking. Pas prioritaire mais si tu te sens de le faire...


