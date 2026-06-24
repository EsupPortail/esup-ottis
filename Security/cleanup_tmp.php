<?php
/**
 * Script de nettoyage des fichiers temporaires du chat
 * À exécuter via cron : 0 3 * * * php /chemin/vers/cleanup_tmp.php
 */

$tmpDir = __DIR__ . '/tmp';

// Nettoyer les fichiers LinkQuestions_* de plus de 24h
cleanupFiles($tmpDir, 'LinkQuestions_*', 86400);

// Nettoyer les fichiers Translated_* de plus de 24h
cleanupFiles($tmpDir, 'Translated_*', 86400);

// Nettoyer les fichiers Verylast_* de plus de 24h
cleanupFiles($tmpDir, 'Verylast_*', 86400);

/**
 * Nettoyer les fichiers correspondant à un pattern
 * @param string $directory Répertoire à nettoyer
 * @param string $pattern Pattern de fichiers (ex: 'LinkQuestions_*')
 * @param int $maxAge Age maximum en secondes (par défaut 24h = 86400)
 */
function cleanupFiles($directory, $pattern, $maxAge = 86400) {
    if (!is_dir($directory)) {
        return;
    }
    
    $files = glob($directory . '/' . $pattern);
    $now = time();
    $deletedCount = 0;
    
    foreach ($files as $file) {
        if (filemtime($file) < $now - $maxAge) {
            if (unlink($file)) {
                $deletedCount++;
                error_log("Cleaned up: " . basename($file));
            } else {
                error_log("Failed to delete: " . basename($file));
            }
        }
    }
    
    return $deletedCount;
}

echo "Cleanup completed.\n";
?>