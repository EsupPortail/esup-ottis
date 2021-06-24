# OMIST Tools

Les OMIST Tools sont des outils indépendants, complémentaires à la solution de traduction. Ils ont pour but principal de créer des animations web indépendantes à partir de supports de cours en PPTX ou en PDF. Ces animations sont aussi utilisables avec l'outil de traduction de cours en ligne.

Les OMIST Tools sont disponibles en version complète sous Linux et Mac OS X.

Commande à exécuter :
  wish Main.tk

L'outil de création simple d'animation (sans traduction) est aussi disponible sur le serveur qui héberge l'application de traduction à l'adresse :
  https://MYSERVERADRESS/OMIST_Tools

# Pré-requis

Les logiciels LibreOffice, Poppler ainsi que la bibliothèque python python-pptx sont requis.
Il faudra ensuite éditer le fichier de configuration config.py.

## Installation sous Linux (Ubuntu)

  sudo apt-get install libreoffice poppler-utils
  pip install python-pptx

## Installation sous Mac OS X

Pour LibreOffice, voir la procédure d'installation ici : [https://fr.libreoffice.org]
Pour poppler, utiliser brew.
  sudo brew install poppler
Enfin, pour python-pptx:
  pip install python-pptx
