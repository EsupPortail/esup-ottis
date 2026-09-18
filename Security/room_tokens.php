<?php
/**
 * Gestion des tokens par ROOMID pour l'auditor
 * Permet l'accès multi-onglets/multi-appareils sans dépendre de la session PHP
 */

/**
 * Génère un token unique pour un ROOMID
 * 
 * @param string $roomid L'identifiant de la salle
 * @return string Le token généré
 */
function generateRoomToken($roomid) {
    $tokenDir = __DIR__ . '/tmp';
    $tokenFile = $tokenDir . '/room_token_' . basename($roomid);
    
    // Créer le répertoire tmp s'il n'existe pas
    if (!is_dir($tokenDir)) {
        mkdir($tokenDir, 0755, true);
    }
    
    // Si un token existe déjà pour ce ROOMID, le retourner
    if (file_exists($tokenFile)) {
        $content = explode('\n', trim(file_get_contents($tokenFile)));
        $existingToken = $content[0] ?? '';
        if (!empty($existingToken)) {
            log_debug("Reusing existing token for ROOMID={roomid}", ['roomid' => $roomid]);
            return $existingToken;
        }
    }
    
    // Sinon créer et sauvegarder un nouveau token
    $token = bin2hex(random_bytes(16));
    $result = file_put_contents($tokenFile, $token . '\n' . time()); // Stocker le token + timestamp
    if ($result === false) {
        log_error("Failed to write token file for ROOMID={roomid}", ['roomid' => $roomid]);
    }
    log_debug("Generated new token for ROOMID={roomid}", ['roomid' => $roomid]);
    return $token;
}

/**
 * Valide un token pour un ROOMID
 * 
 * @param string $roomid L'identifiant de la salle
 * @param string $token Le token à valider
 * @return bool True si le token est valide, false sinon
 */
function validateRoomToken($roomid, $token) {
    $tokenFile = __DIR__ . '/tmp/room_token_' . basename($roomid);
    
    if (!file_exists($tokenFile)) {
        return false;
    }
    
    $content = explode('\n', trim(file_get_contents($tokenFile)));
    $storedToken = $content[0] ?? '';
    $timestamp = (int)($content[1] ?? 0);
    
    // Token invalide si vide ou ne correspond pas
    if (empty($storedToken) || !hash_equals($storedToken, $token)) {
        return false;
    }
    
    // Nettoyer les tokens anciens (24h par défaut)
    cleanupExpiredTokens();
    
    return true;
}

/**
 * Nettoie les tokens expirés (24h)
 */
function cleanupExpiredTokens() {
    $tmpDir = __DIR__ . '/tmp';
    $now = time();
    $expiry = 24 * 3600; // 24 heures
    
    if (!is_dir($tmpDir)) {
        return;
    }
    
    foreach (glob($tmpDir . '/room_token_*') as $file) {
        $content = explode('\n', trim(file_get_contents($file)));
        $timestamp = (int)($content[1] ?? 0);
        
        if ($now - $timestamp > $expiry) {
            unlink($file);
        }
    }
}

/**
 * Nettoie le token pour un ROOMID spécifique
 * 
 * @param string $roomid L'identifiant de la salle
 */
function cleanupRoomToken($roomid) {
    $tokenFile = __DIR__ . '/tmp/room_token_' . basename($roomid);
    if (file_exists($tokenFile)) {
        unlink($tokenFile);
    }
}

/**
 * Vérifie si un token par ROOMID existe et est valide
 * 
 * @param string $roomid L'identifiant de la salle
 * @return bool True si un token valide existe
 */
function hasValidRoomToken($roomid) {
    $tokenFile = __DIR__ . '/tmp/room_token_' . basename($roomid);
    return file_exists($tokenFile);
}
