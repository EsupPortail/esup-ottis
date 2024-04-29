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

#### *3. Création de la table*

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
  origin text
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

Ajouter à la fin du fichier : 
```

```
