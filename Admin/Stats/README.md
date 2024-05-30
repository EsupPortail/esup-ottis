# Pré-requis

Il faut le programme **cron**, **mysql** avec une base de données avec le format donné ci-dessous, les librairies python **pandas**, **plotly**, **geopy**, **ip2geotools**, **mysql-connector-python**, **time**, **nominatim**, et **kaleido**.
Il faudra ensuite éditer le fichier de configuration config.py.

## Installation sous Linux (Ubuntu)
```
  pip3 install -r requirements.txt
```
**OU**

```
  sudo apt install cron
  sudo apt install mysql-server
  pip3 install plotly
  pip3 install -U kaleido
  pip3 install mysql-connector-python
  pip3 install pandas
  pip3 install geopy
  pip3 install ip2geotools
  pip3 install time
  pip3 install nominatim
```


### Mise en place de la base de données

```
php CreateDatabase_stats.php
```
Si le programme ne fonctionne pas, il est possible de créer la base de données à la main, selon les étapes 1 à 4 suivantes.

#### *1. Connexion à MySQL*
```
mysql -u root -p
```

#### *2. Création de la base de données*

```
CREATE DATABASE db_stats;
USE db_stats;
```

#### *3. Création des tables*

```
CREATE TABLE statslog
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  date_raw text,
  date_complete datetime,
  date_day int, 
  date_month text,
  date_year text, 
  user_id text,
  room_id text,
  tool text,
  origin text,
  config_teacher text,
  ip_address text,
  city text,
  country text,
  latitude float,
  longitude float
);
```

```
CREATE TABLE translatelog
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  date_raw text,
  date_complete datetime,
  date_day int,
  date_month text,
  date_year text,
  origin_translate text,
  percentage_char int,
  characters int,
  char_max int
);
```

```
CREATE TABLE iplog
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  ip_address text,
  city text,
  country text,
  latitude float,
  longitude float
);
```

#### *4. Insertion de l'historique des connexions dans la base*

```
python first_complete_db.py
```

**Ne pas oublier d'adapter le fichier config_database_stats.php selon sa configuration.**

### Mise en place de CRON

```
crontab -e
```
**Voir quelle fréquence de mise à jour des graphiques. Ici : tous les jours à 22h**
Ajouter à la fin du fichier : 
```
00 22 * * * python /chemin/absolu/du/fichier/omist_automatictranslator/Admin/generateStatsGraph.py
```

### Ecriture des nouvelles connexions dans la base

Décommenter le code suivant :
- Dans index.php la partie "Connexion avec la base de données pour les statistiques" (lignes 96 à 133) :
  ```
  try{
      $pdo_stats = new PDO("mysql:host=" . $dbhost_stats . ";dbname=" . $dbname_stats, $dbuser_stats, $dbpass_stats);
  }catch(PDOException $err){
      echo "Database connection problem: " . $err->getMessage();
  }

  [...]

     'config_teacher' => $config_teacher,
     'ip_address' => $ip_address
  ]);
  ```
- Dans index.php (ligne 13) : 
```
include("./Admin/Stats/config_database_stats.php")
```
- Dans SendJob.php la partie "Connexion avec la base de données pour les statistiques" (lignes 165 à 196) :
  ```
  try{
      $pdo_stats = new PDO("mysql:host=" . $dbhost_stats . ";dbname=" . $dbname_stats, $dbuser_stats, $dbpass_stats);
  }catch(PDOException $err){
      echo "Database connection problem: " . $err->getMessage();
  }

  [...]

      'room_id' => $room_id, 
      'tool' => $tool, 
      'origin' => $origin,
      'ip_address' => $ip_address
  ]);
  ```
- Dans SendJob.php (ligne 3) : 
```
include("../Admin/Stats/config_database_stats.php")
```
- Dans SendJob_noDB.php la partie "Connexion avec la base de données pour les statistiques" (lignes 163 à 189) :
  ```
    $date_raw = gmdate("Y-m-d\TH:i:s\Z");
    $date_complete = gmdate("Y-m-d");
    $date_day = gmdate("d");

  [...]

    $fd_stats=fopen("fakeDB/db_stats.json","a");
    fputs($fd_stats,$json_stats."|||\n");
    fclose($fd_stats);
  ```
- Dans OMIST_Tools/index.php, dans la fonction SubmitJob (lignes 382 et 383) :
  ```
    var requestLog = new XMLHttpRequest();
    echo "requestLog.open(\"POST\", \"TranslateLogs.php\");\n"
  ```