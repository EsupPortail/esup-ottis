<?php
/**
 * Sanitization Functions
 * 
 * Ce fichier contient les fonctions pour sanitizer les outputs
 * afin de protéger l'application contre les attaques XSS.
 */

/**
 * Sanitize une valeur pour l'affichage HTML
 * 
 * Cette fonction est un raccourci pour htmlspecialchars avec les bons flags.
 * Elle doit être utilisée pour toutes les variables utilisateur affichées dans le HTML.
 * 
 * @param string $value La valeur à sanitizer
 * @param int $flags Les flags pour htmlspecialchars (ENT_QUOTES par défaut)
 * @param string|null $encoding L'encodage (UTF-8 par défaut)
 * @param bool $doubleEncode Si on doit double-encoder
 * @return string La valeur sanitized
 */
function e(string $value, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, ?string $encoding = null, bool $doubleEncode = true): string
{
    if ($encoding === null) {
        $encoding = ini_get('default_charset') ?: 'UTF-8';
    }
    
    return htmlspecialchars($value, $flags, $encoding, $doubleEncode);
}

/**
 * Sanitize une valeur pour l'attribut HTML
 * 
 * @param string $value La valeur à sanitizer
 * @return string La valeur sanitized
 */
function e_attr(string $value): string
{
    return e($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true);
}

/**
 * Sanitize une valeur pour JavaScript
 * 
 * @param string $value La valeur à sanitizer
 * @return string La valeur sanitized
 */
function e_js(string $value): string
{
    // Pour JavaScript, on utilise json_encode qui gère correctement les caractères spéciaux
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
}

/**
 * Sanitize une URL pour l'affichage
 * 
 * @param string $url L'URL à sanitizer
 * @return string L'URL sanitized
 */
function e_url(string $url): string
{
    return filter_var($url, FILTER_SANITIZE_URL);
}
