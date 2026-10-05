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
| **Chat multilingue** | Outil de messagerie instantanée avec traduction intégrée |
| **Prise de notes automatique** | Génération de notes multilingues à partir du discours |
| **Diffusion de présentations** | Affichage de présentations sous-titré (PowerPoint, Pdf) |

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
| **PHP** | 8.1 | Runtime PHP | `php -v` |
| **Composer** | 2.x | Gestionnaire de dépendances PHP | `composer --version` |

### Outils CLI requis

| Outil | Version | Rôle | Installation |
|-------|---------|------|-------------|
| **Git** | 2.x | Contrôle de version | `sudo apt install git` |
| **Make** | 4.x | Exécution des tâches | `sudo apt install make` |
| **Unzip** | 6.x | Extraction des archives | `sudo apt install unzip` |

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
| **php** | ^8.1 | Langage PHP | Runtime |
| **[phpoffice/phppresentation](https://github.com/PHPOffice/PHPPresentation)** | ^1.0 | Lecture/écriture de présentations PowerPoint | Utilisée dans `PPTXNotesExtractor.php` (fallback) |
| **[phpoffice/common](https://github.com/PHPOffice/Common)** | ^1.0 | Bibliotheque commune PHPOffice | Dépendance transitive de phppresentation |
| **[vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)** | ^5.5 | Chargement des variables d'environnement | Utilisée dans `Security/init.php`, `public/index.php` |
| **[monolog/monolog](https://github.com/Seldaek/monolog)** | ^3.0 | Logging structuré | Utilisée dans `config/logging.php`, `Security/logger.php` |


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
```

### 3️⃣ Configurer l'environnement

```bash
# Copier le fichier d'exemple
cp .env.example .env

# Configurer les variables nécessaires (voir section Configuration ci-dessous)
nano .env  # ou utiliser votre éditeur préféré

# IMPORTANT : Configurer tables/tokens.json avec vos tokens API
# Copier tables/tokens.json.example si disponible, ou créer le fichier
nano tables/tokens.json
```


---

## ⚙️ Configuration

### 📄 Fichier `.env`

Copiez `.env.example` en `.env` et configurez **uniquement** les variables suivantes :

```bash
# Configuration minimale requise pour la production
APP_ENV=production        # Obligatoire : development, staging, ou production

# Serveur LibreTranslate (optionnel - défaut : https://translate.terraprint.co/translate)
LIBRETRANSLATESERVER=

# Proxy (optionnel - laisser vide si aucun)
HTTP_PROXY=
```

> **Important** : Les tokens API (GOOGLE_API_TOKENS, DEEPL_FREE_TOKENS, DEEPL_PRO_TOKENS, MICROSOFT_TRANSLATOR_KEY, etc.) **ne sont PAS configurés dans le fichier .env**. Ils doivent être définis dans le fichier **`tables/tokens.json`** avec la structure suivante :
>
> ```json
> {
>   "WHICHTRANSLATOR": ["DEEPLFREE"],
>   "GOOGLETOKENS": ["votre_token_google_1", "votre_token_google_2"],
>   "DEEPLFREETOKENS": ["votre_token_deepl_free"],
>   "DEEPLPROTOKENS": [],
>   "LIBRETRANSLATESERVER": "https://translate.terraprint.co/translate"
> }
> ```

### Configuration Apache

Le projet inclut un fichier `.htaccess` configuré pour :
- Rediriger toutes les requêtes vers `/public/`
- Gérer les exceptions (fichiers existants, `.well-known/`, etc.)

Assurez-vous que le module `rewrite` est activé :
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 🔧 Configuration du VirtualHost (exemple)

```apache
<VirtualHost *:80>
    ServerName ottis.votre-domaine.fr
    DocumentRoot /chemin/vers/omist/public
    
    <Directory /chemin/vers/omist/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/ottis-error.log
    CustomLog ${APACHE_LOG_DIR}/ottis-access.log combined
</VirtualHost>
```



### Production

#### Préparation

```bash
# Optimiser l'autoloader
composer install --no-dev --optimize-autoloader

# Vérifier la configuration
APP_ENV=production \
APP_DEBUG=false \
php public/index.php
```

#### Déploiement recommandé

- Utiliser **Apache** avec le `.htaccess` fourni
- Configurer les permissions :
  ```bash
  chown -R www-data:www-data .
  chmod -R 755 .
  chmod -R 775 tmp/ uploads/ logs/
  ```
- Activer la rotation des logs :
  ```bash
  logrotate -f /etc/logrotate.d/ottis
  ```

---


## 📁 Structure du projet

```
.
├── .env.example              # Modèle de configuration environnement
├── .htaccess                 # Configuration Apache (racine)
├── ARCHITECTURE.md           # Documentation de l'architecture MVC
├── LICENSE                   # Licence LGPL-3.0-only
├── Makefile                 # Tâches de qualité de code
├── composer.json            # Dépendances PHP
├── package.json             # Dépendances JavaScript (racine)
├── phpunit.xml              # Configuration PHPUnit
├── phpstan.neon             # Configuration PHPStan
│
├── config/                  # Configuration applicative
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
├── Security/                # Modules de sécurité (hérités)
│   ├── csrf.php             # Protection CSRF
│   ├── error_handler.php   # Gestionnaire d'erreurs
│   ├── init.php            # Initialisation
│   ├── load_env.php        # Chargement .env (fallback)
│   ├── logger.php          # Wrapper Monolog
│   ├── room_tokens.php     # Gestion des tokens de salle
│   ├── sanitize.php        # Sanitization
│   └── validate.php         # Validation
│
├── src/                    # Code source PHP (PSR-4: App\\)
│   ├── Controller/          # Contrôleurs MVC
│   │   ├── AuditorController.php
│   │   ├── BaseController.php      # Classe de base
│   │   ├── ConferenceController.php
│   │   ├── EmailController.php
│   │   ├── ExtractController.php
│   │   ├── HomeController.php
│   │   ├── ImageController.php
│   │   ├── SubtitlesController.php
│   │   ├── TextController.php
│   │   └── UploadController.php
│   ├── Model/               # Modèles (à développer)
│   ├── Security/            # Sécurité MVC
│   │   └── RoomTokenManager.php
│   ├── Service/             # Services
│   │   ├── LanguageLoader.php
│   │   ├── RandomNameGenerator.php
│   │   ├── RoomInfoService.php
│   │   ├── Router.php              # Routeur HTTP
│   │   └── TokenConfigService.php
│   └── Utils/               # Utilitaires
│       ├── Csrf.php
│       ├── PPTXNotesExtractor.php  # Extraction des notes PPTX
│       ├── Sanitizer.php
│       └── Validator.php
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
│   ├── partials/            # Partials (à créer)
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

Le projet suit une **architecture MVC (Modèle-Vue-Contrôleur)** moderne avec :

- **Front Controller** (`public/index.php`) : Point d'entrée unique
- **Routeur** (`src/Service/Router.php`) : Gestion des URLs et routage
- **Contrôleurs** (`src/Controller/`) : Logique métier
- **Services** (`src/Service/`) : Logique réutilisable
- **Vues** (`views/`) : Présentation (PHP + HTML)
- **Modèles** (`src/Model/`) : Accès aux données (à développer)

---

## Licence

Ce projet est distribué sous la licence **LGPL-3.0-only** (GNU Lesser General Public License version 3).

- **Fichier** : [LICENSE](LICENSE)
- **Résumé** : Vous pouvez utiliser, modifier et distribuer ce logiciel, sous réserve de maintenir la même licence pour les modifications.
- **Auteurs** : Jérémie Bourdon, Jeanne Dionisi (Nantes Université)

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

### Bonnes pratiques

- **PHP** : Utiliser les classes du namespace `App\`
- **Contrôleurs** : Étendre `BaseController`
- **Vues** : Utiliser `$this->escape()` pour les sorties utilisateur
- **Routes** : Définir dans `config/routes.php`
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
| Version | 0.1.0 |
| Langues supportées | 100+ |
| Contrôleurs | 10 |
| Services | 5 |
| Tests unitaires PHP | 2 fichiers |
| Tests JavaScript | 25+ fichiers |
| Dépendances PHP | 5 (production) + 3 (dev) |
| Dépendances JS | 6 (dev) + 2 (tests) |


