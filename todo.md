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

- [X] Te "pencher sur les retours qu'on a eu lors de la formation du 15 décembre et mettre en place un tutoriel pour l'utilisation des mots-clés pour les animations." _**(attendre l'outil d'édition des animations)**_

- [X] J'ai repéré quelques corrections à apporter
    - [X] Sur le masquage des icones. Si on fait cette suite d'actions "Show icons"->No, "Pin code ?"->Yes, "Show icons"->Yes, "Show icons"->No, la case "Pin code ?" est cochée dans on s'attendrait à voir le pin code mais il est caché. C'est un détail à mon avis.
    - [X] Sur les flèches à droite et gauche des sous-titres. La flèche à gauche n'est visible qu'après avoir déplacé la barre des sous-titres et la barre droite est très en dehors de l'écran (sur ma résolution au moins). Je propose de désolidariser ces flèches de la barre des sous-titres et de les placer en "position: fixed" en bas à gauche et droite de l'écran. ça sera plus simple.
_**(UPDATE Jeanne : affichage ok en local et sur la VM, à tester avec d'autres résolutions)**_

- [ ] Continuer les tests sur whisper, et notamment voir s'il est possible d'utiliser les GPU dans une VM. Voir si la procédure décrire ici [https://www.xda-developers.com/how-use-gpu-virtualbox/] s'applique. Il vient de sortir une version qui tourne dans le navigateur de whisper [https://github.com/xenova/whisper-web]. A tester aussi surement. 
_**(UPDATE Jeanne : GPU -> pas de changement par rapport à avant les vacances au niveau de la rapidité. Voir si je n'ai pas fait une erreur de connexion. Pour la version de whisper en ligne -> on ne peut utiliser que les modèles tiny et base, qui ne sont pas assez performants pour nous.)**_ _**(UPDATE JB : J'ai commencé à installer une version un peu tunée de whisper web sur notre serveur. C'est un peu bancal car je ne suis pas très fort en reverse proxy apache et on ne peut pas tout faire non plus. ça sera à revoir de toutes façons lorsqu'on aura le serveur définitif. En tout cas, ça fonctionne ici : https://172.26.98.138, le reste des outils est accessible en http. ça a l'air de fonctionner avec le modèle medium)**_ 
_**(attendre d'avoir Linux sur la machine)**_

- [ ] Meta vient de sortir un nouveau modèle de language très impressionnant qui permet de faire de la traduction speech to speech en gardant la voix de la personne qui parle et quasiment en temps réel (2 secondes d'écart). C'est ici [https://github.com/facebookresearch/seamless_communication/]. Je pense qu'il faut le tester, faire les démos proposées sur HuggingFace (notamment celui [https://huggingface.co/spaces/facebook/seamless-streaming]), regarder le .iypnb, voir ce qui pourrait nous servir. _**(attendre d'avoir Linux sur la machine. Seamless streaming semble ne plus fonctionner)**_

- [ ] Créer un outil d'édition des animations. La première phase va correspondre à reprendre un peu ce qu'on a déjà dans l'application enseignante et dans ExtractSlidesNotesAppHTML.php, pour charger une application dans un editeur minimaliste (les discours dans des textarea), et générer une application avec les modifications. Ceci uniquement pour voir la faisabilité de la chose. 
    - [ ] Supprimer les vertical tab (VT) des speechs;
    - [ ] Faire un affichage de la taille du curseur lors du choix;
    - [ ] Laisser un curseur qui indique l'emplacement de la souris quand on choisit "mouse go" sur l'animation;
    - [ ] Vérifier les formes du curseur sur les différents navigateurs (pas tous les mêmes sur Edge ?);
    - [ ] Image des slides rognée en bas quand on est en plein écran ou page à la taille de l'écran
    - [ ] Les images et les textes bougent quand on fait une animation ? (même avec l'outil de création d'animation à partir d'un pptx)
    - [ ] "Mouse go" qui affiche par moment le font style au lieu de juste les coordonnées de la souris

5) J'ai essayé de mettre en place une sorte de système de scénario tout fait pour faciliter la prise en main. J'ai donc ajouté quelques fonctionnalités accessibles via l'adresse (fournir un lien uncloud du PPTX ou du .HTML, accéder directement à un onglet,....). ça se trouve ici : [https://misc-sciences.univ-nantes.fr/AutomaticTranslator-test/scenarios.php].






Pour moi.
1) dans teacher-func.js (ou maintenant dans common-func.js), dans la définition de StudentCompleteTranslation2, pourquoi on fait appel à StudentCompleteTranslation dans le sinon ? Bug ?
2) dans teacher-func.js, revoir SlideBackward() (quand on retourne à la slide précédente, on veut revenir à la première phrase des commentaires, pas à la dernière)
3) On/Off du micro par les touches ou le pointeur laser ne fonctionne que dans l'onglet slides. Il faudrait que ça fonctionne au moins dans l'onglet wooclap. _**(UPDATE Jeanne : normalement ça fonctionne partout avec les touches du clavier)**_
 
