<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Security/init.php';

// Écriture directe dans un fichier de log minimaliste
file_put_contents('/tmp/test_execution.log', 'Script readtextTranscriptSub.php exécuté à ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
error_reporting(E_ALL);
// Désactiver l'affichage des erreurs en production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/tmp/php_errors.log');

// Vérification du token CSRF
if (!verifyCsrfRequest()) {
    http_response_code(403);
    die('CSRF token validation failed');
}

// Chemin du fichier de log
$logFile = '/tmp/readtext_debug.log';

// Fonction pour écrire dans le fichier de log
function writeLog($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Log des paramètres reçus
writeLog('readtextTranscriptSub.php - Début de la requête');
writeLog('Paramètres reçus - ROOMID: ' . (isset($_POST['ROOMID']) ? $_POST['ROOMID'] : 'non défini') .
         ', ORDER: ' . (isset($_POST['ORDER']) ? $_POST['ORDER'] : 'non défini'));

// Récupérer et valider le ROOMID et room_token
$roomid = $_POST['ROOMID'] ?? '';
$roomToken = $_POST['room_token'] ?? '';

// Vérifier le room_token
if (!validateRoomToken($roomid, $roomToken)) {
    writeLog("ERREUR: Token invalide pour ROOMID=$roomid");
    http_response_code(403);
    echo 'ERREUR: Token invalide.';
    exit;
}

$order = isset($_POST['ORDER']) ? $_POST['ORDER'] : 0;

if (empty($roomid)) {
    writeLog('ERREUR: ROOMID non défini.');
    echo 'ERREUR: ROOMID manquant.';
    exit;
}

$filename = 'tmp/VerylastSub_' . $roomid;
writeLog("Recherche du fichier: $filename");

if (!file_exists($filename)) {
    writeLog("ERREUR: Fichier $filename introuvable.");
    echo "ERREUR: Fichier $filename introuvable.";
    exit;
}

if (!is_readable($filename)) {
    writeLog("ERREUR: Impossible de lire $filename (permissions insuffisantes).");
    echo "ERREUR: Impossible de lire $filename.";
    exit;
}

$fd = fopen($filename, 'r');
if (!$fd) {
    writeLog("ERREUR: Impossible d'ouvrir $filename.");
    echo "ERREUR: Impossible d'ouvrir $filename.";
    exit;
}

// Lire tout le contenu du fichier
$output = '';
while ($ligne = fgets($fd)) {
    $output .= $ligne;
}
fclose($fd);

writeLog("Contenu du fichier $filename: " . trim($output));
writeLog("Contenu brut ligne par ligne:");
$lines = explode("\n", trim($output));
foreach ($lines as $idx => $line) {
    writeLog("  Ligne $idx: " . trim($line));
}

if (empty(trim($output))) {
    writeLog("AVERTISSEMENT: Fichier $filename est vide.");
    echo 'INFO: Fichier vide.';
} else {
    // Préfixer chaque ligne avec un numéro séquentiel
    $lines = explode("\n", trim($output));
    $result = '';
    $lineNumber = 0;
    foreach ($lines as $line) {
        if (trim($line) !== '') {
            $result .= $lineNumber . ';' . $line . "\n";
            $lineNumber++;
        }
    }
    writeLog('Réponse envoyée: ' . trim($result));
    writeLog('readtextTranscriptSub.php - Nombre de lignes: ' . substr_count(trim($result), '\n') + 1);
    echo trim($result);
}

writeLog('readtextTranscriptSub.php - Fin de la requête');
