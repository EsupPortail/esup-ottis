# 📝 Documentation du Système de Logging - OMIST

## Introduction

Ce document décrit la configuration et l'utilisation du système de logging Monolog pour l'application OMIST Automatic Translator.

---

## Configuration

### Installation

Monolog est installé via Composer :

```bash
composer require monolog/monolog ^3.0
```

### Fichier de configuration

La configuration principale se trouve dans `config/logging.php`. Elle gère :

- **Environnements** : développement, staging, production
- **Niveaux de log** : DEBUG, INFO, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY
- **Rotation des fichiers** : Automatique selon l'environnement
- **Multiple handlers** : Fichiers + Console (en développement)

### Structure des logs

```
logs/
├── app.log              # Logs principaux (production)
├── errors.log           # Erreurs uniquement (production)
├── dev.log              # Logs de développement
├── dev-errors.log       # Erreurs en développement
└── staging.log          # Logs de staging
```

### Rotation des fichiers

| Environnement | Fichier principal | Durée de rétention | Niveau minimum |
|---------------|------------------|-------------------|----------------|
| Production    | `logs/app.log`   | 90 jours         | WARNING        |
| Production    | `logs/errors.log` | 1 an           | ERROR          |
| Staging       | `logs/staging.log` | 14 jours       | INFO           |
| Staging       | `logs/staging-errors.log` | 30 jours | ERROR          |
| Development   | `logs/dev.log`   | 7 jours          | DEBUG          |
| Development   | `logs/dev-errors.log` | 30 jours   | ERROR          |

---

## Utilisation

### Fonction principale

La fonction `logger()` retourne une instance de `\Monolog\Logger` :

```php
<?php
require_once 'Security/logger.php';

// Log un message
logger()->error('Une erreur est survenue');

// Log avec contexte
logger()->error('Erreur de connexion', [
    'user_id' => $userId,
    'ip' => $_SERVER['REMOTE_ADDR']
]);
```

### Helpers de niveau

Pour simplifier l'utilisation, des fonctions helpers sont disponibles :

```php
log_debug('Variables: {data}', ['data' => $variables]);
log_info('User logged in: {user}', ['user' => $userId]);
log_notice('Cache cleared');
log_warning('Invalid input: {input}', ['input' => $data]);
log_error('File not found: {path}', ['path' => $path]);
log_critical('Database connection failed');
log_alert('Security breach detected');
log_emergency('System shutdown');
```

### Canaux de log

Pour catégoriser les logs, utilisez les canaux spécifiques :

```php
// Sécurité
log_security('warning', 'CSRF token validation failed for IP: {ip}', [
    'ip' => $_SERVER['REMOTE_ADDR']
]);

// API
log_api('info', 'API call: {endpoint}', ['endpoint' => '/translate']);

// Base de données
log_database('error', 'Query failed: {query}', ['query' => $sql]);

// Ou directement avec withName()
logger()->withName('custom-channel')->info('Message personnalisé');
```

---

## Niveaux de log

| Niveau | Utilisation | Exemple |
|--------|-------------|---------|
| **DEBUG** | Développement, traçage détaillé | `logger()->debug('Variables: {data}', ['data' => $vars]);` |
| **INFO** | Événements importants normaux | `logger()->info('User logged in: {user}', ['user' => $id]);` |
| **NOTICE** | Événements normaux mais significatifs | `logger()->notice('Cache cleared');` |
| **WARNING** | Situations anormales non critiques | `logger()->warning('Invalid input: {input}');` |
| **ERROR** | Erreurs récupérables | `logger()->error('File not found: {path}');` |
| **CRITICAL** | Erreurs critiques, fonctionnalité perdue | `logger()->critical('Database connection failed');` |
| **ALERT** | Action immédiate requise | `logger()->alert('Security breach detected');` |
| **EMERGENCY** | Système inutilisable | `logger()->emergency('System shutdown');` |

---

## Bonnes pratiques

### ✅ À faire

1. **Utiliser le contexte structuré**
   ```php
   // ✅ Bon
   logger()->error('Failed to open file: {file}', ['file' => $filename]);
   
   // ❌ À éviter
   logger()->error('Failed to open file: ' . $filename);
   ```

2. **Choisir le bon niveau de log**
   - DEBUG : Traçage détaillé (développement uniquement)
   - INFO : Événements normaux importants
   - WARNING : Situations anormales
   - ERROR : Erreurs récupérables
   - CRITICAL : Erreurs bloquantes

3. **Utiliser des canaux pour catégoriser**
   ```php
   logger()->withName('security')->warning('Failed login attempt');
   logger()->withName('api')->info('Request processed');
   ```

4. **Inclure un contexte utile**
   ```php
   logger()->error('Database error', [
       'query' => $sql,
       'error' => $e->getMessage(),
       'user_id' => $userId
   ]);
   ```

5. **Logging des exceptions**
   ```php
   try {
       // code
   } catch (Exception $e) {
       logger()->error('Exception caught: {error}', [
           'error' => $e->getMessage(),
           'file' => $e->getFile(),
           'line' => $e->getLine(),
           'trace' => $e->getTraceAsString()
       ]);
   }
   ```

### ❌ À éviter

1. **Logs trop verbaux en production**
   ```php
   // ❌ À éviter en production
   logger()->debug('Processing request...');
   ```

2. **Messages de log non informatifs**
   ```php
   // ❌ Pas utile
   logger()->info('Something happened');
   
   // ✅ Meilleur
   logger()->info('User {user} created document {doc}', ['user' => $userId, 'doc' => $docId]);
   ```

3. **Logging de données sensibles**
   ```php
   // ❌ NE JAMAIS logger des mots de passe, tokens, etc.
   logger()->info('User password: {password}', ['password' => $password]);
   
   // ✅ OK
   logger()->info('User authenticated: {user}', ['user' => $userId]);
   ```

4. **Utiliser error_log() directement**
   ```php
   // ❌ Ancienne méthode
   error_log('Erreur: ' . $message);
   
   // ✅ Nouvelle méthode
   log_error('Erreur: {message}', ['message' => $message]);
   ```

---

## Configuration avancée

### Personnaliser le format

Le format par défaut est :
```
2026-08-27 14:30:45.123456 [ERROR] security: CSRF token validation failed for IP: {ip}
```

Pour le modifier, éditez `config/logging.php` et modifiez le `LineFormatter`.

### Ajouter un handler personnalisé

Exemple pour envoyer les erreurs critiques par email :

```php
use Monolog\Handler\MailHandler;

$mailHandler = new MailHandler(
    ['admin@omist.com' => 'Admin'],
    'OMIST Application Error',
    Level::Critical
);
$logger->pushHandler($mailHandler);
```

### Ajouter un handler Slack

```php
use Monolog\Handler\SlackWebhookHandler;

$slackHandler = new SlackWebhookHandler(
    'https://hooks.slack.com/services/...',
    '#logs',
    'OMIST',
    true,
    null,
    Level::Error
);
$logger->pushHandler($slackHandler);
```

---

## Environnements

### Développement

- **Niveau minimum** : DEBUG (tout est loggé)
- **Handlers** : Console + Fichier (7 jours)
- **Fichiers** : `logs/dev.log`, `logs/dev-errors.log`

### Staging

- **Niveau minimum** : INFO
- **Handlers** : Fichier (14 jours)
- **Fichiers** : `logs/staging.log`, `logs/staging-errors.log`

### Production

- **Niveau minimum** : WARNING
- **Handlers** : Fichier (90 jours) + Erreurs (1 an)
- **Fichiers** : `logs/app.log`, `logs/errors.log`
- **Erreurs PHP fatales** : Toujours loggées (niveau CRITICAL)

---

## Migration depuis l'ancien système

### Remplacement de error_log()

| Ancien code | Nouveau code |
|-------------|--------------|
| `error_log('Message');` | `log_error('Message');` |
| `error_log('User: ' . $user);` | `log_error('User: {user}', ['user' => $user]);` |
| `error_log('Error: ' . $e->getMessage());` | `log_error('Error: {error}', ['error' => $e->getMessage()]);` |

### Migration progressive

1. Inclure `Security/logger.php` dans les fichiers existants
2. Remplacer `error_log()` par les fonctions `log_*`
3. Utiliser le contexte structuré
4. Choisir le bon niveau de log

---

## Dépannage

### Problème : Les logs ne s'affichent pas

**Solutions :**
1. Vérifier que `Security/init.php` est inclus
2. Vérifier que `APP_ENV` est correctement défini
3. Vérifier les permissions du dossier `logs/` (0777 recommandé)
4. Vérifier le niveau de log (en production, DEBUG ne s'affiche pas)

### Problème : Les logs sont trop nombreux

**Solutions :**
1. Augmenter le niveau minimum de log
2. Utiliser des canaux pour filtrer
3. Configurer la rotation des fichiers

### Problème : Les logs manquent des informations

**Solutions :**
1. Ajouter plus de contexte avec le tableau `$context`
2. Utiliser des canaux spécifiques
3. Vérifier que le format du log inclut tous les champs nécessaires

---

## Exemples complets

### Exemple 1 : Logging d'une opération API

```php
function translateText($text, $targetLang) {
    log_api('info', 'Translation requested', [
        'text_length' => strlen($text),
        'source_lang' => 'auto',
        'target_lang' => $targetLang
    ]);
    
    try {
        $result = callTranslationService($text, $targetLang);
        log_api('debug', 'Translation successful', [
            'result_length' => strlen($result)
        ]);
        return $result;
    } catch (Exception $e) {
        log_api('error', 'Translation failed', [
            'error' => $e->getMessage(),
            'text' => substr($text, 0, 100) . '...' // Limiter la taille
        ]);
        throw $e;
    }
}
```

### Exemple 2 : Logging de sécurité

```php
function validateUserAccess($userId, $resource) {
    if (!$user->hasAccess($resource)) {
        log_security('warning', 'Unauthorized access attempt', [
            'user_id' => $userId,
            'resource' => $resource,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        return false;
    }
    return true;
}
```

### Exemple 3 : Logging avec gestion d'erreurs

```php
function processUpload($file) {
    log_debug('Processing upload: {filename}', ['filename' => $file['name']]);
    
    if (!validateFileType($file)) {
        log_warning('Invalid file type uploaded', [
            'filename' => $file['name'],
            'type' => $file['type']
        ]);
        return false;
    }
    
    try {
        $result = saveFile($file);
        log_info('File uploaded successfully', [
            'filename' => $file['name'],
            'size' => $file['size']
        ]);
        return $result;
    } catch (Exception $e) {
        log_error('File upload failed', [
            'filename' => $file['name'],
            'error' => $e->getMessage()
        ]);
        return false;
    }
}
```

---

## Ressources

- **Site officiel Monolog** : https://github.com/Seldaek/monolog
- **Documentation PSR-3** : https://www.php-fig.org/psr/psr-3/
- **Handlers Monolog** : https://github.com/Seldaek/monolog/tree/main/src/Monolog/Handler
- **Formatters Monolog** : https://github.com/Seldaek/monolog/tree/main/src/Monolog/Formatter

---

*Document créé le 27 août 2026*  
*Version : 1.0*  
*Auteur : OMIST Development Team*
