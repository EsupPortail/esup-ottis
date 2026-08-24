<?php
/**
 * CSRF Protection Functions
 * 
 * Ce fichier contient les fonctions pour générer et valider les tokens CSRF
 * afin de protéger l'application contre les attaques CSRF (Cross-Site Request Forgery).
 */

/**
 * Génère un token CSRF unique et le stocke en session
 * 
 * @return string Le token CSRF généré
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Valide un token CSRF contre celui stocké en session
 * 
 * @param string $token Le token à valider
 * @return bool Vrai si le token est valide, faux sinon
 */
function validateCsrfToken(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Affiche un champ hidden CSRF pour les formulaires
 * 
 * @return string Le champ HTML hidden contenant le token CSRF
 */
function csrfField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
}

/**
 * Vérifie si une requête POST contient un token CSRF valide
 * 
 * @return bool Vrai si le token est valide, faux sinon
 */
function verifyCsrfRequest(): bool
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true; // Pas une requête POST, pas de besoin de CSRF
    }
    
    $token = $_POST['csrf_token'] ?? '';
    
    if (!validateCsrfToken($token)) {
        // Log de la tentative CSRF
        error_log('CSRF token validation failed for IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        return false;
    }
    
    return true;
}

/**
 * Initialise la protection CSRF pour une page
 * Doit être appelée au début des scripts qui utilisent des formulaires
 */
function initCsrfProtection(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Assurer que le token est généré
    generateCsrfToken();
}
