# OTTIS

> **Outil de Transcription et de Traduction Intelligente et Synchrone**

---

## À propos du projet

**OTTIS** est une solution open-source conçue pour faciliter la traduction instantanée dans un contexte pédagogique. Développée par l'équipe du projet (Nantes Université, Faculté des Sciences et Techniques), cette application permet de briser les barrières linguistiques entre enseignants et étudiants.

### Fonctionnalités principales

| Fonctionnalité | Description |
|---------------|-------------|
| **Reconnaissance vocale** | Capture et transcrit le discours de l'enseignant en temps réel |
| **Traduction multilingue** | Traduction du discours dans plus de 100 langues |
| **Synthèse vocale** | Restitution du discours traduit dans la langue de l'étudiant |
| **Chat multilingue** | Messagerie en temps réel (Server-Sent Events) avec traduction intégrée |
| **Prise de notes automatique** | Génération de notes multilingues à partir du discours |
| **Diffusion de présentations** | Affichage de présentations sous-titré (PowerPoint, Pdf) |
| **Interface bilingue** | Page de conférence traduite (français/anglais), changement de langue sans rechargement |
| **Interface auditeur** | Vue étudiant refondue (SPA, layout commun, RGAA 4.1) : sous-titres en direct, transcription téléchargeable (VO/traduite), pop-up de choix de langue |

### Cas d'usage

- **Cours magistraux** : Traduction simultanée pour les étudiants internationaux
- **Conférences** : Diffusion multilingue en temps réel
- **Travaux pratiques** : Assistance linguistique pour les étudiants
- **Réunions administratives** : Facilitation des échanges multiculturels

---

## Prérequis

### Environnement requis

| Composant | Version minimale | Description | Vérification |
|-----------|-----------------|-------------|--------------|
| **PHP** | 8.2 | Runtime PHP (requis par composer.json : `^8.2`) | `php -v` |
| **Composer** | 2.x | Gestionnaire de dépendances PHP | `composer --version` |
| **Node.js + npm** | 18.x / 9.x | Outils qualité JS (ESLint, Prettier, Vitest) pour `make check` / `make test` — développement uniquement ; installés via `cd public/js && npm install` | `node -v && npm -v` |

### Outils CLI requis

| Outil | Version | Rôle | Installation |
|-------|---------|------|-------------|
| **Git** | 2.x | Contrôle de version | `sudo apt install git` |
| **Make** | 4.x | Exécution des tâches | `sudo apt install make` |
| **Unzip** | 6.x | Extraction des archives | `sudo apt install unzip` |
| **LibreOffice** | 7.x | **Requis** : conversion PPTX → PDF et ODP → PPTX (`soffice --headless`) | `sudo apt install libreoffice` |
| **curl** (CLI) | 7.x | **Requis** : téléchargement de fichiers distants ("uncloud") et statistiques d'usage DeepL | `sudo apt install curl` |
| **ImageMagick** | 6.x ou 7.x | **Optionnel** : fallback PDF → Images si l'extension PHP `imagick` est absente | `sudo apt install imagemagick` |
| **MTA** (postfix, sendmail…) | — | **Optionnel** : envoi d'e-mails (`/email/send`, fonction PHP `mail()`) | `sudo apt install postfix` |

### Serveur Web

| Serveur | Configuration requise | Fichier de configuration |
|---------|----------------------|-------------------------|
| **Apache** | Module rewrite activé | `.htaccess` (fourni) |
| **Nginx** | Configuration personnalisée | À adapter |


---

## Dépendances

### Dépendances PHP (Production)

| Dépendance | Version | Rôle | Utilisation |
|------------|---------|------|-------------|
| **php** | ^8.2 | Langage PHP | Runtime |
| **[phpoffice/phppresentation](https://github.com/PHPOffice/PHPPresentation)** | ^1.2 | Lecture/écriture de présentations PowerPoint | Utilisée dans `PPTXNotesExtractor.php` (fallback) |
| **[phpoffice/common](https://github.com/PHPOffice/Common)** | ^1.1 | Bibliotheque commune PHPOffice | Dépendance transitive de phppresentation |
| **[vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)** | ^5.5 | Chargement des variables d'environnement | Utilisée dans `src/Core/Bootstrap.php` |
| **[monolog/monolog](https://github.com/Seldaek/monolog)** | ^3.5 | Logging structuré | Utilisée dans `config/logging.php`, `src/Utils/Logger.php` |
| **[apereo/phpcas](https://github.com/apereo/phpCAS)** | ^1.6.2 | Authentification CAS (SSO) | Utilisée dans `src/Security/CasAuthenticator.php` |

### Extensions PHP requises

| Extension | Version | Rôle | Installation | Vérification |
|-----------|---------|------|-------------|--------------|
| **imagick** | latest | Conversion PDF → Images (méthode primaire) | `sudo apt install php-imagick` + `sudo phpenmod imagick` | `php -m \| grep imagick` |
| **curl** | — | Appels API traduction (DeepL, Google…) et phpCAS | `sudo apt install php-curl` | `php -m \| grep curl` |
| **dom, xml, simplexml, xmlreader/writer** | — | Lecture des PPTX/ODP, phpCAS | `sudo apt install php-xml` | `php -m \| grep dom` |
| **zip** | — | Extraction des archives PPTX/ODP | `sudo apt install php-zip` | `php -m \| grep zip` |
| **gd** | — | Traitement d'images (phpoffice/phpspreadsheet, transitive) | `sudo apt install php-gd` | `php -m \| grep gd` |
| **mbstring, iconv** | — | Chaînes multi-octets (phpspreadsheet) | `sudo apt install php-mbstring` | `php -m \| grep mbstring` |
| **fileinfo, ctype, zlib** | — | Requises par les bibliothèques PHPOffice | généralement incluses | `php -m` |


---

## Installation

### Cloner le dépôt

```bash
# HTTPS
git clone https://gitlab.intra.univ-nantes.fr/dsin/spin/omist.git

# ou SSH
git clone git@gitlab.intra.univ-nantes.fr:dsin/spin/omist.git

cd omist
```

### Installer les dépendances PHP (production)

```bash
# Installer Composer (si non installé)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
php composer-setup.php && \
php -r "unlink('composer-setup.php');" && \
mv composer.phar /usr/local/bin/composer

# Installer UNIQUEMENT les dépendances de production (pas de dev)
composer install --no-dev --optimize-autoloader

# Installer les extensions PHP requises
sudo apt-get install -y php-imagick php-curl php-xml php-zip php-gd php-mbstring
sudo phpenmod imagick
sudo systemctl restart apache2

# Installer les binaires externes requis
sudo apt-get install -y libreoffice curl

# Optionnel : agent de messagerie pour l'envoi d'e-mails (/email/send)
sudo apt-get install -y postfix
```

### Installer les dépendances de développement

```bash
composer install
```

### Configurer l'environnement

```bash
# Copier le fichier d'exemple
cp .env.example .env

# Configurer les variables nécessaires (voir section Configuration ci-dessous)
nano .env  # ou utiliser votre éditeur préféré

# IMPORTANT : Configurer les tokens API de traduction
cp tables/tokens.json.example tables/tokens.json
nano tables/tokens.json
```




---

## Configuration

### Fichier php.ini

upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_time = 300

# Imagick (pour la conversion PDF → Images)
# Vérifier que l'extension est chargée
extension=imagick


### Fichier ottis.conf

Un exemple complet et générique est fourni dans
[`examples/ottis.conf.example`](examples/ottis.conf.example) : copier le fichier vers
`/etc/php/<version>/fpm/pool.d/ottis.conf`, adapter les chemins (`/srv/ottis`,
`/run/php/ottis.sock`) et le propriétaire du pool, puis recharger FPM
(`sudo systemctl reload php<version>-fpm`).


### Fichier `.env`

Copiez `.env.example` en `.env` et configurez **uniquement** les variables suivantes :

```bash
# Configuration minimale requise pour la production
APP_ENV=production        # Obligatoire : development, staging, ou production

# Authentification CAS (true/false - voir section "Authentification CAS" ci-dessous)
CAS_ENABLED=false

# Proxy (optionnel - laisser vide si aucun)
HTTP_PROXY=
```

> **Toute la configuration de la traduction se fait dans `tables/tokens.json`** (copiez `tables/tokens.json.example`) : choix du traducteur, tokens API et serveur LibreTranslate. Le `.env` ne porte que les réglages d'infrastructure. Structure :
>
> ```json
> {
>   "WHICHTRANSLATOR": ["DEEPLFREE"],
>   "GOOGLETOKENS": ["votre_token_google_1", "votre_token_google_2"],
>   "DEEPLFREETOKENS": ["votre_token_deepl_free"],
>   "DEEPLPROTOKENS": [],
>   "LIBRETRANSLATESERVER": ["https://translate.terraprint.co/translate"]
> }
> ```
>
> Les clés lues par le code (`src/Service/TokenConfigService.php`) sont : `WHICHTRANSLATOR`, `GOOGLETOKENS`, `DEEPLFREETOKENS`, `DEEPLPROTOKENS`, `LIBRETRANSLATESERVER` (défaut appliqué si absente ou vide).

### Configuration Apache

Le projet inclut un fichier `.htaccess` configuré pour :
- Rediriger toutes les requêtes vers `/public/`
- Gérer les exceptions (fichiers existants, `.well-known/`, etc.)

Assurez-vous que le module `rewrite` est activé :
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Configuration du VirtualHost (exemple)

Un exemple complet est fourni dans
[`examples/ottis-vhost.conf.example`](examples/ottis-vhost.conf.example) : DocumentRoot
vers `public/`, socket FPM du pool (`/run/php/ottis.sock`), `AllowOverride All`.



### Authentification CAS (config/cas.php)

L'application s'appuie sur **apereo/phpcas** pour l'authentification centralisée (SSO). La configuration se trouve dans `config/cas.php` :

```php
return [
    'enabled' => (bool) (getenv('CAS_ENABLED') ?: false),   // Piloté par la variable CAS_ENABLED du fichier .env
    'host' => 'login.univ-nantes.fr',
    'port' => 443,
    'url' => '/cas26m/',
    'server_validation' => false,
    'cert_path' => null,
    'dev_mode' => false,
    // ...
];
```

> **Important** : l'activation du CAS se pilote par la variable `CAS_ENABLED` du fichier `.env` (désactivée par défaut dans le dépôt). Le fichier `config/cas.php` pointe vers le serveur CAS de Nantes Université : adaptez `host`, `port` et `url` à votre établissement avant de passer `CAS_ENABLED=true`, sinon les routes protégées renverront une erreur d'authentification.

- **Routes protégées** (CAS obligatoire) : `/`, `/conference`, `/conference/{roomId}`
- **Routes publiques** (sans CAS) : `/auditor`, `/auditor/{roomId}`
- **Filtrage par affiliation** : l'accès aux zones protégées exige une affiliation `eduPersonAffiliation` parmi `faculty`, `staff`, `employee`, `teacher` (sinon HTTP 403).
- **Persistance de session** : la session PHP dure 8 h (cookie dédié) et est maintenue active pendant une conférence par un appel périodique à `/keepalive` (toutes les 10 minutes) ; si une reconnexion CAS devient nécessaire, elle est transparente (nettoyage du ticket, retour automatique à la page d'origine, salle conservée).



### Production

#### Préparation

```bash
# Optimiser l'autoloader
composer install --no-dev --optimize-autoloader

# Vérifier la configuration
APP_ENV=production \
php public/index.php
```

#### Déploiement recommandé

- Utiliser **Apache** avec le `.htaccess` fourni
- Configurer les permissions :
  ```bash
  mkdir -p tmp/ uploads/ logs/
  chown -R www-data:www-data .
  chmod -R 755 .
  chmod -R 775 tmp/ uploads/ logs/
  ```
- Activer la rotation des logs :
  ```bash
  logrotate -f /etc/logrotate.d/ottis
  ```

---


## Structure du projet

```
.
├── .env.example              # Modèle de configuration environnement
├── .htaccess                 # Configuration Apache (racine)
├── ARCHITECTURE.md           # Documentation de l'architecture MVC
├── examples/                 # Exemples de configuration serveur (FPM, VirtualHost)
├── LICENSE                   # Licence Apache 2.0
├── Makefile                 # Tâches de qualité de code
├── composer.json            # Dépendances PHP
├── includes/                 # Includes procéduraux (chat-component.php)
├── phpunit.xml              # Configuration PHPUnit
├── phpstan.neon             # Configuration PHPStan
├── tables/                   # Données applicatives (tokens.json des traducteurs)
│
├── config/                  # Configuration applicative
│   ├── cas.php              # Configuration de l'authentification CAS
│   ├── logging.php          # Configuration Monolog
│   ├── routes.php           # Définition des routes MVC
│   └── security.php          # Configuration de sécurité
│
├── public/                  # Point d'entrée web
│   ├── .htaccess            # Configuration Apache
│   ├── index.php            # Front Controller MVC
│   ├── js/                 # Code JavaScript
│   │   ├── package.json     # Dépendances JS locales
│   │   ├── vitest.config.js # Configuration Vitest
│   │   ├── lib/             # Bibliothèques JavaScript personnalisées
│   │   │   ├── auditor/     # Module auditeur
│   │   │   ├── chat/        # Module de chat
│   │   │   ├── conference/  # Module de conférence
│   │   │   ├── dom/         # Utilitaires DOM
│   │   │   ├── language/   # Gestion des langues
│   │   │   ├── network/    # Utilitaires réseau
│   │   │   ├── pdf/        # Gestion PDF
│   │   │   ├── state/      # Gestion d'état
│   │   │   └── utils/      # Utilitaires divers
│   │   ├── tests/           # Tests JavaScript
│   │   └── *.js            # Points d'entrée (auditor-init.js, conference-init.js, home-init.js)
│   ├── lang/               # Fichiers de traduction JSON
│   ├── styles/            # Feuilles de style CSS
│   └── uploads/            # Fichiers uploadés
│
├── src/                    # Code source PHP (PSR-4: App\\)
│   ├── Controller/          # Contrôleurs MVC (BaseController + 10 contrôleurs métier)
│   ├── Core/                # Bootstrap applicatif
│   │   └── Bootstrap.php    # Erreurs -> .env -> session 8h -> CAS -> CSRF
│   ├── Error/               # Gestion des erreurs
│   │   └── ErrorHandler.php
│   ├── Security/            # Sécurité
│   │   ├── CasAuthenticator.php  # Authentification CAS
│   │   └── RoomTokenManager.php  # Tokens par salle
│   ├── Service/             # Services
│   │   ├── Conversion/      # Convertisseurs PPTX/ODP -> PDF
│   │   └── Router.php, LanguageLoader.php, ... (services métier)
│   └── Utils/               # Utilitaires
│       ├── Csrf.php, Logger.php, Sanitizer.php, Validator.php
│       └── PPTXNotesExtractor.php, ... (extracteurs PPTX)
│
├── tests/                   # Tests PHP
│   ├── Integration/
│   └── Unit/
│       └── Security/
│           ├── SanitizeTest.php
│           └── ValidateTest.php
│
├── views/                   # Vues MVC
│   ├── auditor/            # Vues auditeur
│   ├── conference/          # Vues conférence
│   ├── errors/              # Pages d'erreur
│   │   ├── 404.php
│   │   ├── 500.php
│   │   └── maintenance.php
│   ├── extract/             # Vues extraction
│   ├── home/                # Vues accueil
│   ├── layouts/            # Layouts
│   │   └── default.php
│   ├── pages/               # Vues de pages
│   ├── partials/            # Partials
│   ├── remote/              # Vues distantes
│   ├── subtitles/           # Vues sous-titres
│   └── upload/              # Vues upload
│
├── scripts/                 # Scripts shell
├── tools/                   # Outils de développement
├── tmp/                    # Fichiers temporaires
├── logs/                   # Logs applicatifs
└── uploads/                # Fichiers uploadés
```

---

## Architecture

Le projet suit une **architecture MVC (Modèle-Vue-Contrôleur)** : point d'entrée unique (`public/index.php`), routeur (`config/routes.php`), contrôleurs et services PSR-4 (`src/`), vues (`views/`), initialisés par `src/Core/Bootstrap.php` (erreurs, `.env`, session 8 h, CAS, CSRF).

Flux d'une requête :

```
HTTP Request -> Apache .htaccess -> public/index.php -> Router::match() -> Controller::action() -> Response (HTML, JSON, redirect)
```

Le détail — structure des dossiers, composants clés, création d'une page, bonnes pratiques — est documenté dans [ARCHITECTURE.md](ARCHITECTURE.md).

### Flux des sous-titres en direct

```
Conference (dictée)                          Auditor (sous-titres)
----------------------                       ----------------------
Web Speech API (partiels 2-5x/s)
  |  EcrirePartial
  v
POST /extract/transcription-sub
  |  ExtractController::transcriptionSub
  v
/tmp/ottis/VerylastSub_{roomId}  <---------- POST /text/read-last-sub (poll 300 ms)
   (dernière ligne = partiel courant)            TextController::readLastSub
   (déjà traduit dans la langue des              (lecture depuis la fin, room token)
    sous-titres de la conférence)                |
                                                 |  langue auditor != langue des sous-titres ?
                                                 v
                                                POST /text/translate (throttle 1/5 par phrase)
                                                 v
                                               #fixed-title / #titre
```

Points clés :

- Le partiel n'écrit que dans les zones sous-titres transitoires (`#fixed-title`, `#titre`) : le notebook et l'output de la vue Transcription restent alimentés uniquement par les lignes finalisées.
- Les lignes finalisées empruntent un chemin distinct : `/text/save` -> `tmp/Link_{roomId}`, lues par le poll `/text/read` (2 s) ; elles remplacent le partiel à la fin de chaque phrase.
- Les fichiers ne sont pas tous au même endroit : `VerylastSub_{roomId}` vit dans `sys_get_temp_dir()/ottis/` (répertoire système), `Link_{roomId}` et `Verylast_{roomId}` dans `tmp/` du projet. Toute nouvelle lecture/écriture doit viser le bon répertoire (voir le bug silencieux de `readTranscriptSub` dans `TODO.md`).

---

## Licence

Ce projet est distribué sous la licence **Apache 2.0**.

- **Fichier** : [LICENSE](LICENSE)
- **Résumé** : Vous pouvez utiliser, modifier et distribuer ce logiciel selon les termes de la licence Apache 2.0.
- **Auteurs OMIST** : Jérémie BOURDON, Jeanne DIONISI (Nantes Université), Thibaut AURIACOMBE (Nantes Université)
- **Auteurs OTTIS** : Thibaut AURIACOMBE (Nantes Université)

---

## Usage de l'intelligence artificielle

Le développement de ce projet a été assisté par des outils d'intelligence
artificielle générative (agent de programmation IA), sous la supervision et la
responsabilité pleine de l'équipe de développement.

### Périmètre d'usage

- Rédaction et remaniement de code PHP et JavaScript
- Rédaction de la documentation technique (README, ARCHITECTURE)
- Écriture de tests unitaires et d'intégration
- Analyse statique, correction de bugs et revue de code assistée

### Cadre et garanties

- Tout code généré ou modifié par l'IA a été relu et validé par un membre de
  l'équipe avant intégration
- Les comportements sont vérifiés à la suite de tests (`make test`) et
  d'analyse statique (`make check`)
- L'IA n'a jamais eu accès à des secrets : les tokens API et mots de passe
  restent hors du dépôt
- Les choix d'architecture et les décisions fonctionnelles relèvent de
  l'équipe ; l'IA est un outil d'assistance, pas un auteur

---

## Contribution

### Comment contribuer

1. **Forker le dépôt**
2. **Créer une branche** : `git checkout -b feature/ma-fonctionnalite`
3. **Respecter les standards** :
   - Code PHP : PSR-12
   - Code JavaScript : ESLint (Airbnb)
   - Commits : Conventions Angular (`feat:`, `fix:`, `docs:`, etc.)
4. **Exécuter les tests** : `make test`
5. **Soumettre une Pull Request**

### Outils de qualité

| Commande | Rôle |
|----------|------|
| `make check` | Vérifier la qualité du code (PHP + JS) |
| `make fix` | Corriger automatiquement (PHP + JS) |
| `make check-php` | PHPStan (niveau 5) + PHP-CS-Fixer (PSR-12) + PHPUnit |
| `make check-js` | ESLint |
| `make test` | Tests PHP + JavaScript |

Commandes directes utiles :

```bash
vendor/bin/phpstan analyse                     # analyse statique complète
vendor/bin/php-cs-fixer fix --dry-run --diff  # vérifier le style sans modifier
vendor/bin/php-cs-fixer fix path/to/file.php   # corriger un fichier
vendor/bin/phpstan analyse path/to/file.php   # analyser un fichier
```

`make help` liste toutes les cibles disponibles.

### Bonnes pratiques

- **PHP** : Utiliser les classes du namespace `App\`
- **Contrôleurs** : Étendre `BaseController`
- **Vues** : Utiliser `$this->escape()` pour les sorties utilisateur
- **Validation** : Valider les entrées via `App\Utils\Validator`
- **Routes** : Définir dans `config/routes.php`
- **Production** : HTTPS obligatoire
- **Tests** : Couvrir les nouvelles fonctionnalités

### Rapport de bugs

1. Vérifier les [issues existants](https://gitlab.intra.univ-nantes.fr/dsin/spin/omist/-/issues)
2. Créer un nouvel issue avec :
   - Étapes pour reproduire
   - Comportement attendu
   - Comportement réel
   - Version de PHP et du navigateur

---

## Support

Pour des questions sur :
- **L'architecture MVC** : Consulter [ARCHITECTURE.md](ARCHITECTURE.md)
- **Les dépendances** : Consulter ce README
- **Le développement** : Contacter l'équipe du projet

---

## Statistiques du projet

| Métrique | Valeur |
|----------|--------|
| Version | 0.4.0 |
| Langues supportées | 100+ |
| Contrôleurs | 10 |
| Services | 12 |
| Tests unitaires PHP | 39 tests (2 fichiers) |
| Tests JavaScript | 25+ fichiers |
| Dépendances PHP | 6 (production) + 3 (dev) |
| Dépendances JS | 6 (dev) + 2 (tests) |


