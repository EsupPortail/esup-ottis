<?php
/**
 * Chargement des variables d'environnement depuis le fichier .env
 * 
 * Ce fichier charge les variables depuis .env si phpdotenv n'est pas disponible.
 * Une fois phpdotenv installé via composer, ce fichier peut être supprimé.
 */

if (!function_exists('loadEnvFile')) {
    /**
     * Charge les variables d'environnement depuis un fichier .env
     * 
     * @param string $path Chemin vers le fichier .env
     * @return void
     */
    function loadEnvFile(string $path = null): void
    {
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }
        
        if (!file_exists($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorer les commentaires
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Trouver la position du premier = 
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            
            // Retirer les quotes si présentes
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            // Définir la variable d'environnement
            if (!empty($key)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
                
                // aussi définir comme variable globale pour rétrocompatibilité
                if (!defined($key)) {
                    $GLOBALS[$key] = $value;
                }
            }
        }
    }
}

// Charger le fichier .env
loadEnvFile();
