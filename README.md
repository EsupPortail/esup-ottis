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
| **Node.js + npm** | 22.x (LTS) / 10.x | Outils qualité JS (ESLint, Prettier, Vitest) pour `make check` / `make test` — développement uniquement ; installés via `cd public/js && npm install` | `node -v && npm -v` |

### Outils CLI requis

| Outil | Version | Rôle | Installation |
|-------|---------|------|-------------|
| **Git** | 2.x | Contrôle de version | `sudo apt install git` |
| **Make** | 4.x | Exécution des tâches | `sudo apt install make` |
| **LibreOffice** | 7.x | **Requis** : conversion PPTX → PDF et ODP → PPTX (`soffice --headless`) | `sudo apt install libreoffice` |
| **curl** (CLI) | 7.x | **Requis** : téléchargement de fichiers distants ("uncloud") et statistiques d'usage DeepL | `sudo apt install curl` |
| **ImageMagick** | 6.x ou 7.x | **Optionnel** : fallback PDF → Images si l'extension PHP `imagick` est absente | `sudo apt install imagemagick` |

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
| **mbstring** | — | Chaînes multi-octets (`mb_substr`) | `sudo apt install php-mbstring` | `php -m \| grep mbstring` |
| **fileinfo, ctype** | — | Détection MIME (`finfo_*`) et validation de type (`ctype_digit`) | généralement incluses | `php -m` |


---

## Installation

### Cloner le dépôt

```bash
# HTTPS
git clone https://github.com/EsupPortail/esup-ottis.git

# ou SSH
git clone git@github.com:EsupPortail/esup-ottis.git

cd esup-ottis
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
sudo apt-get install -y php-imagick php-curl php-xml php-zip php-mbstring
sudo phpenmod imagick
sudo systemctl restart apache2

# Installer les binaires externes requis
sudo apt-get install -y libreoffice curl
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

# Serveur CAS (CAS_HOST obligatoire si CAS activé - voir section dédiée)
CAS_HOST=
CAS_PORT=
CAS_URL=

# Proxy (optionnel - laisser vide si aucun)
HTTP_PROXY=

# Mesure d'audience Matomo (voir section "Mesure d'audience (Matomo)" ci-dessous)
MATOMO_URL=
MATOMO_SITE_ID=

# Vitesse de traduction (voir section "Vitesse de traduction" ci-dessous)
CONFERENCE_TRANSLATION_PARTIAL_FREQ=5
AUDITOR_TRANSLATION_PARTIAL_FREQ=5

# Langues d'interface (voir section "Langues d'interface" ci-dessous)
CONFERENCE_UI_LANGUAGE=fr
AUDITOR_UI_LANGUAGE=en
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

L'application s'appuie sur **apereo/phpcas** pour l'authentification centralisée (SSO). La configuration de base se trouve dans `config/cas.php` ; le serveur est surchargeable via le fichier `.env` :

```ini
CAS_ENABLED=true                      # Activation (unique interrupteur)
CAS_HOST=cas.mon-etablissement.fr     # OBLIGATOIRE si CAS activé (aucune valeur par défaut)
CAS_PORT=443                         # Port (optionnel, défaut générique : 443)
CAS_URL=/cas/                         # Contexte CAS (optionnel, défaut générique : /cas)
```

> **Important** : `CAS_HOST` est **obligatoire** dès que `CAS_ENABLED=true` : les valeurs de Nantes Université ne sont pas des valeurs par défaut. Sans hôte configuré, un avertissement est journalisé à chaque requête et l'authentification échoue avec une erreur explicite (les routes protégées renvoient alors une erreur).

- `CAS_PORT` / `CAS_URL` absents, vides ou invalides : défauts génériques `443` et `/cas` (avertissement en log si invalide).
- `server_validation` (validation du certificat du serveur CAS) et `cert_path` se règlent dans `config/cas.php` : passez `server_validation` à `true` et renseignez `cert_path` pour un établissement disposant d'une PKI.
- Contournement du CAS en développement : `APP_ENV=dev` ou `APP_ENV=local`, ou `dev_mode = true` dans `config/cas.php` (utilisateur factice `ottis-dev-user`). NB : `APP_ENV=development` ne contourne pas le CAS.

- **Routes protégées** (CAS obligatoire) : `/`, `/conference`, `/conference/{roomId}`
- **Routes publiques** (sans CAS) : `/auditor`, `/auditor/{roomId}`
- **Filtrage par affiliation** : l'accès aux zones protégées exige une affiliation `eduPersonAffiliation` parmi `faculty`, `staff`, `employee`, `teacher` (sinon HTTP 403).
- **Persistance de session** : la session PHP dure 8 h (cookie dédié) et est maintenue active pendant une conférence par un appel périodique à `/keepalive` (toutes les 10 minutes) ; si une reconnexion CAS devient nécessaire, elle est transparente (nettoyage du ticket, retour automatique à la page d'origine, salle conservée).
- **Reprise de salle** : un intervenant qui revient sur `/conference` retrouve sa salle la plus récente pendant 15 minutes après sa dernière activité (`RoomTokenManager::ROOM_RECOVERY_WINDOW = 900 s`, fenêtre rafraîchie par chaque heartbeat de la page conférence). Au-delà, une nouvelle salle lui est attribuée. À la reprise, le chat rejoue l'historique (SSE depuis l'index 0) et le transcript est rejoué dans les notebooks (`replayTranscriptHistory`, sans re-traduction).



### Mesure d'audience (Matomo)

OTTIS intègre le traceur Matomo, **désactivé par défaut**. L'activation se pilote par le fichier `.env` :

```ini
MATOMO_URL=https://analytics.mon-etablissement.fr/matomo.php   # URL complète du tracker
MATOMO_SITE_ID=42                                              # Identifiant du site
```

- `MATOMO_URL` absente, vide ou invalide, ou `MATOMO_SITE_ID` absent/invalide : Matomo reste désactivé — aucun script n'est émis côté client et le domaine n'apparaît pas dans la Content-Security-Policy.
- La CSP (émise par `public/index.php`) autorise automatiquement le domaine du tracker configuré dans `script-src` et `connect-src`.
- La page « Données personnelles » n'affiche la mention de mesure d'audience (avec le domaine du tracker) que si Matomo est activé.



### Vitesse de traduction (sous-titres partiels)

La fréquence des traductions intermédiaires (sous-titres en cours de dictée) se pilote par deux variables du fichier `.env`, une par rôle :

```ini
CONFERENCE_TRANSLATION_PARTIAL_FREQ=5    # côté intervenant
AUDITOR_TRANSLATION_PARTIAL_FREQ=5       # côté auditeur
```

| Valeur | Comportement |
|---|---|
| `0` | Chaque partiel est traduit (vitesse max, crédit DeepL max) |
| `N` (≥ 1) | 1 appel de traduction tous les N partiels |
| `none` | Aucune traduction intermédiaire : aucun texte non traduit n'est affiché, seul le texte final est traduit |

- Absente, vide ou invalide : `5` (comportement par défaut).
- Chaque variable est indépendante : conférence et auditor peuvent avoir des fréquences différentes.
- Les lignes finales (ajoutées au notebook) sont **toujours** traduites, quelle que soit la valeur.



### Langues d'interface (CONFERENCE_UI_LANGUAGE / AUDITOR_UI_LANGUAGE)

Langue d'affichage par défaut des pages, définie dans le fichier `.env` avec une variable par rôle :

```ini
CONFERENCE_UI_LANGUAGE=fr    # pages hors auditor (défaut actuel : fr)
AUDITOR_UI_LANGUAGE=en       # pages auditor (défaut actuel : en)
```

- Code sur 2 lettres disposant d'un fichier de traduction `public/lang/{code}.json` (actuellement : `fr`, `en`).
- Absentes ou vides : comportement actuel conservé (`fr` hors auditor, `en` pour l'auditor).
- Invalide ou non supportée (ex. `de`) : repli sur `en` + avertissement en log.
- Le paramètre `?lang=` et le sélecteur FR/EN de la navigation priment toujours sur ces valeurs (choix explicite de l'utilisateur).



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
- Activer la rotation des logs — créer `/etc/logrotate.d/ottis` :
  ```
  /home/omist/logs/*.log {
      daily
      rotate 14
      compress
      delaycompress
      missingok
      notifempty
      copytruncate
      dateext
  }
  ```
  - `copytruncate` est indispensable : PHP-FPM garde `fpm-error.log` ouvert
    en permanence ; sans lui, FPM continuerait à écrire dans le fichier renommé.
  - Premier passage immédiat, puis le timer système `logrotate.timer` prend le
    relais chaque nuit à minuit :
    ```bash
    logrotate -f /etc/logrotate.d/ottis
    ```
- Mettre en place le nettoyage quotidien du répertoire `tmp/` — créer
  `/etc/cron.d/ottis` :
  ```cron
  # Nettoyage quotidien des fichiers temporaires OMIST
  0 4 * * * www-data find /home/omist/tmp -mindepth 1 -delete
  ```
  - Passage à 4 h du matin, hors heures de cours. Les fichiers d'état
    (`room_alive_*`, `room_token_*`) sont recréés automatiquement par les
    heartbeat à la prochaine utilisation.
  - Le daemon cron recharge `/etc/cron.d/` automatiquement : aucun restart
    nécessaire après création du fichier.

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
│   │   └── *.js            # Points d'entrée (auditor-init.js, conference-init.js)
│   ├── lang/               # Fichiers de traduction JSON
│   ├── styles/            # Feuilles de style CSS
│   └── uploads/            # Fichiers uploadés
│
├── src/                    # Code source PHP (PSR-4: App\\)
│   ├── Controller/          # Contrôleurs MVC (BaseController + 8 contrôleurs métier)
│   ├── Core/                # Bootstrap applicatif
│   │   └── Bootstrap.php    # Erreurs -> .env -> session 8h -> CAS -> CSRF
│   ├── Error/               # Gestion des erreurs
│   │   └── ErrorHandler.php
│   ├── Security/            # Sécurité
│   │   ├── CasAuthenticator.php  # Authentification CAS
│   │   └── RoomTokenManager.php  # Tokens par salle
│   ├── Service/             # Services
│   │   └── Router.php, LanguageLoader.php, TokenConfigService.php, ... (7 services métier)
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
- Les fichiers ne sont pas tous au même endroit : `VerylastSub_{roomId}`, `PartialSub_{roomId}_{lang}` et `FinalSub_{roomId}_{lang}` vivent dans `sys_get_temp_dir()/ottis/` (répertoire système), `Link_{roomId}` dans `tmp/` du projet. Toute nouvelle lecture/écriture doit viser le bon répertoire.
- Cache de traduction par langue (`/tmp/ottis/`, partagé entre tous les clients d'une même langue) : `PartialSub_{roomId}_{lang}` conserve le partiel courant traduit (clé = empreinte md5 du texte source), `FinalSub_{roomId}_{lang}` accumule les lignes finalisées traduites (`lineid;texte` pour l'auditeur qui traduit, `C;texte` pour la conférence). `readTranscriptSub` rejoue cet historique à un auditor arrivant en cours de séance. Les fichiers `Translated_{roomId}` et `Verylast_{roomId}` sont retirés.

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

1. Vérifier les [issues existants](https://github.com/EsupPortail/esup-ottis/issues)
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
| Contrôleurs | 8 |
| Services | 7 |
| Tests unitaires PHP | 39 tests (2 fichiers) |
| Tests JavaScript | 19 fichiers |
| Dépendances PHP | 4 (production) + 3 (dev) |
| Dépendances JS | 13 (dev) + 2 (tests) |


