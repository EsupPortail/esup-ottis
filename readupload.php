<?php

/**
 * Script pour servir les fichiers uploadés de manière sécurisée
 *
 * Ce script valide le fichier demandé avant de le servir,
 * empêchant l'accès direct aux fichiers uploadés.
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Security/init.php';

// Vérifier que le paramètre file est présent
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    die('Fichier non spécifié');
}

$filename = $_GET['file'];

// Valider le nom de fichier (caractères alphanumériques, underscore, hyphen, point)
if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
    http_response_code(400);
    die('Nom de fichier invalide');
}

// Chemin vers le dossier uploads
$uploadDir = __DIR__ . '/uploads/';
$filepath = $uploadDir . $filename;

// Vérifier que le fichier existe et est dans le dossier uploads
if (!file_exists($filepath)) {
    http_response_code(404);
    die('Fichier introuvable');
}

// Vérifier que le fichier est bien dans le dossier uploads (pas de ../)
$realpath = realpath($filepath);
if (strpos($realpath, realpath($uploadDir)) !== 0) {
    http_response_code(403);
    die('Accès interdit');
}

// Déterminer le type MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $filepath);
finfo_close($finfo);

// Servir le fichier
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filepath));
header('Content-Disposition: inline; filename="' . e($filename) . '"');
header('Cache-Control: private, max-age=3600');

// Lire et envoyer le fichier
readfile($filepath);
exit;
