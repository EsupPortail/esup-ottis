# Pré-requis

Il faut le programme **cron**, **mysql** avec une base de données avec le format donné ci-dessous, la librairie python **plotly** et **kaleido**.
Il faudra ensuite éditer le fichier de configuration config.py.

## Installation sous Linux (Ubuntu)
```
  sudo apt install cron
  sudo apt install mysql-server
  pip3 install plotly
  pip3 install -U kaleido
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
CREATE TABLE tasks_queue
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  date_raw text,
  date_complete datetime,
  date_month text,
  date_year text, 
  user_id text,
  room_id text,
  tool text,
  origin text,
  ip_address
);
```

```
CREATE TABLE db_translatelog
(
  id BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  date_raw text,
  date_complete datetime,
  date_month text,
  date_year text, 
  origin text,
  percentage_char int,
  characters int,
  ip_address text
);
```

#### *4. Insertion de l'historique des connexions dans la base*

```
python first_complete_db.py
```

**Ne pas oublier d'adapter le fichier config_database_stats.php selon sa configuration.**

### Mise en place de CRON

**Voir quelle fréquence de mise à jour des graphiques.**

```
crontab -e
```

Ajouter à la fin du fichier : 
```
* * * * * python /chemin/absolu/du/fichier/omist_automatictranslator/Admin/generateStatsGraph.py
```

### Ecriture des nouvelles connexions dans la base

Décommenter le code suivant :
- Dans index.php la partie "Connexion avec la base de données pour les statistiques" (lignes 85 à 128) :
  ```
  try{
      $pdo_stats = new PDO("mysql:host=" . $dbhost_stats . ";dbname=" . $dbname_stats, $dbuser_stats, $dbpass_stats);
  }catch(PDOException $err){
      echo "Database connection problem: " . $err->getMessage();
  }

  [...]

      'room_id' => $room_id, 
      'tool' => $tool, 
      'origin' => $origin
  ]);
  ```
- Dans SendJob.php la partie "Connexion avec la base de données pour les statistiques" (lignes 156 à 199) :
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

- Dans SendJob_noDB.php la partie "Connexion avec la base de données pour les statistiques" (lignes 152 à 176) :
  ```
    $date_raw = gmdate("Y-m-d\TH:i:s\Z");
    $date_complete = gmdate("Y-m-d");
    $date_day = gmdate("d");

  [...]

    $fd_stats=fopen("fakeDB/db_stats.json","a");
    fputs($fd_stats,$json_stats."|||\n");
    fclose($fd_stats);
  ```