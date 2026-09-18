<?php

/**
 * Centralized security configuration
 */

// Environnement
$isProduction = !(strpos($_SERVER['SERVER_NAME'], 'localhost') !== false ||
                 strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
                 strpos($_SERVER['SERVER_NAME'], '.local') !== false);

// Configuration des erreurs
if ($isProduction) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', '/var/log/omist_errors.log');
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Content-Security-Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://analytics.univ-nantes.fr; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com; img-src 'self' data: https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; font-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com; connect-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://api.deepl.com https://app.wooclap.com https://analytics.univ-nantes.fr; frame-src https://app.wooclap.com; worker-src 'self' blob:;");

// Strict-Transport-Security (HSTS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
