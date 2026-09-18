# Architecture MVC - OMIST

## 📁 Structure des Dossiers

```
omist/
├── config/                   # Fichiers de configuration
│   └── routes.php           # Définition centralisée des routes
├── public/                  # Point d'entrée web
│   ├── index.php            # Front Controller
│   └── .htaccess            # Configuration Apache
├── src/                     # Code source PHP (PSR-4)
│   ├── Controller/          # Contrôleurs MVC
│   │   ├── BaseController.php  # Classe de base des contrôleurs
│   │   └── HomeController.php  # Exemple: contrôleur de la page d'accueil
│   ├── Model/               # Modèles (à créer)
│   ├── Service/             # Services
│   │   └── Router.php       # Routeur HTTP
│   ├── Utils/               # Utilitaires
│   └── Security/            # Sécurité (existants)
│       ├── Csrf.php         # Protection CSRF
│       ├── Sanitizer.php    # Sanitization
│       └── Validator.php     # Validation
├── views/                   # Vues MVC
│   ├── home/                # Vues de la page d'accueil
│   │   └── index.php        # Vue d'accueil
│   ├── layouts/             # Layouts
│   │   └── default.php      # Layout par défaut
│   ├── conference/          # Vues de la conference (à créer)
│   └── partials/            # Partials (à créer)
├── assets/                  # Assets statiques (à créer)
│   ├── css/
│   ├── js/
│   └── images/
└── config.php              # Configuration (existante)
```

## 🎯 Composants Clés

### 1. Front Controller (`public/index.php`)
- **Rôle** : Point d'entrée unique pour toutes les requêtes HTTP
- **Fonctionnalités** :
  - Initialisation de l'autoloading Composer
  - Chargement des variables d'environnement
  - Création du routeur
  - Routage des requêtes vers les contrôleurs
  - Gestion des erreurs
  - Compatibilité ascendante avec l'ancien code

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
          $this->viewData['pageTitle'] = 'Omist';
          $this->render('home/index');
      }
  }
  ```

## 🚀 Mise en Route

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

## 📝 Création d'une Nouvelle Page

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

## 🔄 Compatibilité Ascendante

Le Front Controller maintient la compatibilité avec l'ancien code de plusieurs manières :

1. **Fallback vers les anciens fichiers PHP** : Si aucune route ne correspond, le système tente de trouver le fichier PHP correspondant à la racine.

2. **Maintien des URLs existantes** : Les routes pour `/conference.php`, `/auditor.php`, etc. sont définies dans le routeur.

3. **Inclusion des fichiers de configuration existants** : Les fichiers comme `Security/load_env.php` sont toujours chargés.

4. **Gestion des sessions** : La session est initialisée automatiquement dans le BaseController.

## 📊 Bonnes Pratiques

### Contrôleurs
- ✅ Utiliser `extends BaseController`
- ✅ Définir des méthodes d'action publiques (ex: `index()`, `show()`, `store()`)
- ✅ Utiliser `$this->viewData` pour passer des données aux vues
- ✅ Utiliser `$this->render()` pour afficher les vues
- ❌ Ne pas accéder directement à `$_GET`, `$_POST` (utiliser `$this->getParam()`, `$this->postParam()`)
- ❌ Ne pas sortir directement du HTML (toujours passer par une vue)

### Vues
- ✅ Utiliser `$this->escape()` pour toutes les sorties utilisateur
- ✅ Garder la logique métier dans les contrôleurs
- ✅ Utiliser les layouts pour le code commun
- ❌ Ne pas inclure directement des fichiers PHP
- ❌ Ne pas accéder directement à `$_SESSION`

### Routes
- ✅ Utiliser des noms de routes descriptifs
- ✅ Spécifier les méthodes HTTP supportées
- ✅ Utiliser des paramètres nommés pour les segments dynamiques
- ❌ Ne pas dupliquer les définitions de routes

## 🔧 Outils de Développement

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
# À créer : tests pour les contrôleurs
./vendor/bin/phpunit tests/Controller/
```

## 📋 Tâches Restantes (Tâche 12)

- [x] Créer la structure de dossiers MVC
- [x] Créer le Front Controller
- [x] Créer le Routeur
- [x] Créer le BaseController
- [x] Créer un exemple de contrôleur (HomeController)
- [x] Créer le layout par défaut
- [x] Créer une vue d'exemple
- [x] Configurer les routes
- [ ] Créer les contrôleurs manquants (ConferenceController, AuditorController, etc.)
- [ ] Migrer la logique des anciens fichiers PHP vers les contrôleurs
- [ ] Créer les vues pour toutes les pages
- [ ] Tester la compatibilité avec l'ancien code
- [ ] Documenter les endpoints API

## 📞 Support

Pour des questions sur l'architecture MVC, contacter l'équipe de développement.

---

**Document créé** : 26 août 2026  
**Version** : 1.0  
**Auteur** : Mistral Vibe (Tâche 12 - Réorganisation en architecture MVC)
