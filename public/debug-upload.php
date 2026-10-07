<?php

// Désactive le buffer pour voir les erreurs brutes
while (ob_get_level()) {
    ob_end_clean();
}

// Active le logging complet
ini_set('display_errors', 0);  // ⚠️ Désactivé pour éviter le HTML dans la réponse
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/omist_debug.log');
error_reporting(E_ALL);

// Simule une requête POST avec un fichier test
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf_token'] = 'test_token'; // Token dummy pour éviter le CSRF

// Crée un fichier test PDF (pas besoin de conversion)
$testFile = '/tmp/test_debug.pdf';
file_put_contents($testFile, 'PDF TEST');
chmod($testFile, 0644);

// Simule $_FILES
$_FILES['presentation_file'] = [
    'name' => 'test_debug.pdf',
    'type' => 'application/pdf',
    'tmp_name' => $testFile,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testFile)
];

// Inclure l'autoloader
require __DIR__ . '/../vendor/autoload.php';

// Instancie le routeur
$router = new App\Service\Router();

// Instancie le contrôleur
$controller = new App\Controller\UploadController($router);

// Appelle la méthode handle() via reflection (contourne le protected)
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('handle');
$method->setAccessible(true); // Rend la méthode accessible
$method->invoke($controller);

// Nettoyage
@unlink($testFile);
