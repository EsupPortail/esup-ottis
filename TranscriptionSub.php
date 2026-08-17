<?php
// Activer les erreurs en développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir l'en-tête JSON
header('Content-Type: application/json');

// Vérifier que les paramètres sont présents
if (!isset($_GET["texte"]) || !isset($_GET["ROOMID"])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'status' => 'error',
        'message' => 'Paramètres manquants : texte ou ROOMID.'
    ]);
    exit;
}

$texte = $_GET["texte"];
$roomId = $_GET["ROOMID"];

// Nettoyer le texte
$texte = html_entity_decode(strip_tags($texte));

// Remplacer les balises <span> si présentes
if (strpos($texte, "<span>") !== false) {
    $texte = str_replace("<span>", "<span style='font-size: 0.7em; font-style: italic;'>", $texte);
}

// Vérifier que le dossier tmp/ existe et est accessible
$tmpDir = "tmp/";
if (!file_exists($tmpDir)) {
    if (!mkdir($tmpDir, 0755, true)) {
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'status' => 'error',
            'message' => 'Impossible de créer le dossier tmp/.'
        ]);
        exit;
    }
}

// Sauvegarder le texte dans un fichier temporaire (mode "a" pour append/ajouter)
$filename = $tmpDir . "VerylastSub_" . $roomId;
$fd = @fopen($filename, "a");
if ($fd) {
    fwrite($fd, $texte . "\n");
    fclose($fd);

    // Retourner une réponse JSON avec succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Texte sauvegardé avec succès',
        'texte' => $texte,
        'roomId' => $roomId,
        'filename' => $filename
    ]);
} else {
    // Erreur lors de l'ouverture du fichier
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Impossible d\'ouvrir le fichier pour écriture.',
        'filename' => $filename,
        'permissions' => substr(sprintf('%o', fileperms($tmpDir)), -4)
    ]);
}
?>