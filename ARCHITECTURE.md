# Architecture MVC - OTTIS

## Structure des Dossiers

```
ottis/
├── config/                   # Fichiers de configuration
│   └── routes.php           # Définition centralisée des routes
├── public/                  # Point d'entrée web
│   ├── index.php            # Front Controller
│   └── .htaccess            # Configuration Apache
├── src/                     # Code source PHP (PSR-4)
│   ├── Controller/          # Contrôleurs MVC
│   │   ├── BaseController.php  # Classe de base des contrôleurs
│   │   └── UploadController.php, ... (contrôleurs métier)
│   ├── Core/                # Bootstrap de l'application
│   │   └── Bootstrap.php    # ErrorHandler -> Dotenv -> session 8h -> CAS -> CSRF
│   ├── Error/               # Gestion des erreurs
│   │   └── ErrorHandler.php # Handlers PHP + pages d'erreur
│   ├── Service/             # Services
│   │   └── Router.php, ... (services métier)
│   ├── Utils/               # Utilitaires
│   │   └── Csrf.php, Logger.php, Sanitizer.php, Validator.php, ...
│   └── Security/            # Sécurité
│       ├── CasAuthenticator.php  # Authentification CAS
│       └── RoomTokenManager.php  # Tokens par salle
├── views/                   # Vues MVC
│   ├── layouts/             # Layouts (default.php)
│   ├── errors/              # Pages d'erreur (404, 500, maintenance)
│   └── auditor/, conference/, ... (vues métier)
└── tables/                  # Données applicatives (tokens.json des traducteurs)
```

## Composants Clés

### 1. Front Controller (`public/index.php`)
- **Rôle** : Point d'entrée unique pour toutes les requêtes HTTP
- **Fonctionnalités** :
  - Initialisation de l'autoloading Composer
  - Bootstrap applicatif : \App\Core\Bootstrap::run() (erreurs, .env, session 8h, CAS, CSRF)
  - Création du routeur
  - Routage des requêtes vers les contrôleurs
  - Gestion des erreurs

### 2. Routeur (`src/Service/Router.php`)
- **Rôle** : Analyser les requêtes HTTP et les associer aux contrôleurs
- **Fonctionnalités** :
  - Support des méthodes HTTP (GET, POST, PUT, DELETE)
  - Routage par motifs (ex: `/conference/{roomId}`)
  - Génération d'URLs
  - Détection de la base path
  - Support des requêtes AJAX

### 3. BaseController (`src/Controller/BaseController.php`)
- **Rôle** : Classe de base pour tous les contrôleurs
- **Fonctionnalités** :
  - Gestion des vues (render, renderPartial)
  - Gestion des layouts
  - Gestion des sessions
  - Gestion des paramètres de requête (GET, POST)
  - Réponses HTTP (JSON, texte, erreur)
  - Sanitization des sorties
  - Chargement des traductions

### 4. Contrôleurs Concrets
- **Exemple** : `HomeController.php`
- **Structure** :
  ```php
  namespace App\Controller;
  
  class HomeController extends BaseController
  {
      public function index(): void
      {
          $this->viewData['pageTitle'] = 'Ottis';
          $this->render('home/index');
      }
  }
  ```

## Mise en Route

### Configuration Apache

1. **Fichier .htaccess à la racine** :
   - Redirige toutes les requêtes vers `/public/`
   - Exceptions : fichiers existants, `.well-known/`, `favicon.ico`, `robots.txt`

2. **Fichier .htaccess dans public/** :
   - Active le rewrite engine
   - Route toutes les requêtes vers `public/index.php`
   - Exceptions : fichiers et dossiers existants

### Routage

Les routes sont définies dans `config/routes.php` :

```php
return [
    '/' => [
        'controller' => \App\Controller\HomeController::class,
        'action' => 'index',
        'methods' => ['GET']
    ],
    '/conference/{roomId}' => [
        'controller' => \App\Controller\ConferenceController::class,
        'action' => 'show',
        'methods' => ['GET'],
        'params' => ['roomId']
    ],
];
```

### Autoloading PSR-4

L'autoloading est configuré dans `composer.json` :

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

Après modification des classes, exécuter :
```bash
composer dump-autoload
```

## Couche JavaScript (`public/js`)

- **Points d'entrée** : `conference-init.js`, `auditor-init.js`
- **Modules** : `lib/conference/`, `lib/auditor/`, `lib/chat/` (modules ES)
- **État partagé** : `lib/state/store.js` (store commun conference/auditor, initialisé depuis `window.pageData`)
- **Flux de données runtime** (fichiers `tmp/`, endpoints `/text/*`, sous-titres en direct) : voir la section « Flux des sous-titres en direct » du [README](README.md)

## Création d'une Nouvelle Page

### 1. Créer le Contrôleur

```php
// src/Controller/ConferenceController.php
namespace App\Controller;

class ConferenceController extends BaseController
{
    public function index(): void
    {
        $roomId = $this->getParam('ROOMID', md5(date('Y-m-d H:i:s')));
        
        $this->viewData['pageTitle'] = 'Conference';
        $this->viewData['roomId'] = $roomId;
        
        $this->render('conference/index');
    }
    
    public function show(string $roomId): void
    {
        $this->viewData['roomId'] = $roomId;
        $this->render('conference/show');
    }
}
```

### 2. Créer la Vue

```php
// views/conference/index.php
<div id="conference">
    <h1>Conference Room: <?= $this->escape($roomId) ?></h1>
    <!-- Contenu de la conference -->
</div>
```

### 3. Définir la Route

Ajouter dans `config/routes.php` :

```php
'/conference/{roomId}' => [
    'controller' => \App\Controller\ConferenceController::class,
    'action' => 'show',
    'methods' => ['GET'],
    'params' => ['roomId']
],
```

## Bonnes Pratiques

### Contrôleurs
- Utiliser `extends BaseController`
- Définir des méthodes d'action publiques (ex: `index()`, `show()`, `store()`)
- Utiliser `$this->viewData` pour passer des données aux vues
- Utiliser `$this->render()` pour afficher les vues
- Ne pas accéder directement à `$_GET`, `$_POST` (utiliser `$this->getParam()`, `$this->postParam()`)
- Ne pas sortir directement du HTML (toujours passer par une vue)

### Vues
- Utiliser `$this->escape()` pour toutes les sorties utilisateur
- Garder la logique métier dans les contrôleurs
- Utiliser les layouts pour le code commun
- Ne pas inclure directement des fichiers PHP
- Ne pas accéder directement à `$_SESSION`

### Routes
- Utiliser des noms de routes descriptifs
- Spécifier les méthodes HTTP supportées
- Utiliser des paramètres nommés pour les segments dynamiques
- Ne pas dupliquer les définitions de routes

## Outils de Développement

### Vérification de la syntaxe
```bash
php -l src/Controller/HomeController.php
```

### Rechargement de l'autoloading
```bash
composer dump-autoload
```

### Tests
```bash
# PHP (à la racine du projet ; sur ce poste, vider LD_LIBRARY_PATH, voir README)
LD_LIBRARY_PATH= php vendor/bin/phpunit

# JavaScript (Vitest)
cd public/js && LD_LIBRARY_PATH= npx vitest run
```

## Support

Pour des questions sur l'architecture MVC, contacter l'équipe de développement.

---

**Document créé** : 26 août 2026
**Version** : 1.2
