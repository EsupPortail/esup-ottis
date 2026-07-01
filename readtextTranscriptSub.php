<?php
require_once __DIR__ . '/Security/room_tokens.php';
require_once __DIR__ . '/Security/validate_room_token.php';

// Vérifier le room_token
requireRoomToken();

// Écriture directe dans un fichier de log minimaliste
file_put_contents('/tmp/test_execution.log', "Script readtextTranscriptSub.php exécuté à " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Chemin du fichier de log
$logFile = '/tmp/readtext_debug.log';

// Fonction pour écrire dans le fichier de log
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Log des paramètres reçus
writeLog("readtextTranscriptSub.php - Début de la requête");
writeLog("Paramètres reçus - ROOMID: " . (isset($_POST["ROOMID"]) ? $_POST["ROOMID"] : "non défini") .
         ", ORDER: " . (isset($_POST["ORDER"]) ? $_POST["ORDER"] : "non défini"));

// $roomid et $order sont déjà définis par la vérification ci-dessus
$order = isset($_POST["ORDER"]) ? $_POST["ORDER"] : 0;

if (empty($roomid)) {
    writeLog("ERREUR: ROOMID non défini.");
    echo "ERREUR: ROOMID manquant.";
    exit;
}

$filename = "tmp/VerylastSub_" . $roomid;
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

$fd = fopen($filename, "r");
if (!$fd) {
    writeLog("ERREUR: Impossible d'ouvrir $filename.");
    echo "ERREUR: Impossible d'ouvrir $filename.";
    exit;
}

// Lire tout le contenu du fichier
$output = "";
while ($ligne = fgets($fd)) {
    $output .= $ligne;
}
fclose($fd);

writeLog("Contenu du fichier $filename: " . trim($output));

if (empty(trim($output))) {
    writeLog("AVERTISSEMENT: Fichier $filename est vide.");
    echo "INFO: Fichier vide.";
} else {
    // Préfixer chaque ligne avec $order
    $lines = explode("\n", trim($output));
    $result = "";
    foreach ($lines as $line) {
        if (trim($line) !== "") {
            $result .= $order . ";" . $line . "\n";
        }
    }
    writeLog("Réponse envoyée: " . trim($result));
    echo trim($result);
}

writeLog("readtextTranscriptSub.php - Fin de la requête");
?>