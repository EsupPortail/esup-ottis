# OMIST Tools

Les OMIST Tools sont des outils indépendants, complémentaires à la solution de traduction. Ils ont pour but principal de créer des animations web indépendantes à partir de supports de cours en PPTX ou en PDF. Ces animations sont aussi utilisables avec l'outil de traduction de cours en ligne.

L'outil de création simple d'animation (sans traduction) est aussi disponible sur le serveur qui héberge l'application de traduction à l'adresse :
  https://MYSERVERADRESS/OMIST_Tools

# Pré-requis

Les logiciels LibreOffice, Poppler ainsi que les bibliothèques python python-pptx et lxml sont requis.
Il faut également le programme **cron**, **mysql** avec une base de données avec le format donné ci-dessous et un **dossier /var/html/uploads/**.
Il faudra ensuite éditer le fichier de configuration config.py.

## Installation sous Linux (Ubuntu)
```
  sudo apt-get install libreoffice poppler-utils
  pip install python-pptx
  sudo apt install cron
  sudo apt install mysql-server
  pip3 install lxml
```

### Mise en place de la base de données

```
php CreateDatabase.php
```
Si le programme ne fonctionne pas, il est possible de créer la base de données à la main, selon les étapes 1 à 3 suivantes.

#### *1. Connexion à MySQL*
```
mysql -u root -p
```

#### *2. Création de la base de données*

```
CREATE DATABASE db_tasks;
USE db_tasks;
```

#### *3. Création de la table*

```
CREATE TABLE tasks_queue
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  command_to_do TEXT,
  path_file TEXT,
  randompath TEXT,
  lang_from TEXT,
  lang_to TEXT,
  sub_length TEXT,
  sub_punct TEXT,
  path_file2 TEXT,
  slide_notes TEXT,
  translate_lang TEXT,
  aspect_ratio TEXT,
  keep_aspect_ratio TEXT,
  delay_slides TEXT,
  long_run TEXT,
  licence TEXT
);
```

**Ne pas oublier d'adapter le fichier OMIST_Tools/config_database.php selon sa configuration.**

### Mise en place de CRON

```
crontab -e
```

Ajouter à la fin du fichier : 
```
* * * * * php /chemin/absolu/du/fichier/omist_automatictranslator/OMIST_Tools/ReadDatabase.php
0 5 * * * bash /chemin/absolu/du/fichier/omist_automatictranslator/OMIST_Tools/DeleteZIP.sh
```


