<?php
/**
 * Script de vérification des permissions du dossier tmp/
 * Exécutez ce script pour vérifier la sécurité du dossier tmp/
 */

$tmpDir = __DIR__ . '/../tmp';

echo "=== Vérification des permissions du dossier tmp/ ===\n\n";

if (!file_exists($tmpDir)) {
    echo "❌ Le dossier tmp/ n'existe pas.\n";
    echo "Recommandation : Créez-le avec les commandes :\n";
    echo "  mkdir tmp\n";
    echo "  chown www-data:www-data tmp\n";
    echo "  chmod 750 tmp\n";
    exit(1);
}

$permissions = fileperms($tmpDir) & 0777;
$owner = fileowner($tmpDir);
$group = filegroup($tmpDir);

echo "Dossier : tmp/\n";
echo "Permissions : " . decoct($permissions) . "\n";
echo "Propriétaire : " . posix_getpwuid($owner)['name'] . "\n";
echo "Groupe : " . posix_getgrgid($group)['name'] . "\n\n";

// Vérifications
$ok = true;

// Vérifier les permissions (doivent être 750 ou 700)
if ($permissions != 0750 && $permissions != 0700) {
    echo "❌ Permissions non sécurisées : " . decoct($permissions) . "\n";
    echo "Recommandation : chmod 750 tmp/ (ou 700 pour plus de sécurité)\n";
    $ok = false;
} else {
    echo "✅ Permissions sécurisées : " . decoct($permissions) . "\n";
}

// Vérifier le propriétaire
if (posix_getpwuid($owner)['name'] != 'www-data' && posix_getpwuid($owner)['name'] != 'apache') {
    echo "⚠️  Propriétaire non standard : " . posix_getpwuid($owner)['name'] . "\n";
    echo "Recommandation : chown www-data:www-data tmp/\n";
    $ok = false;
} else {
    echo "✅ Propriétaire correct : " . posix_getpwuid($owner)['name'] . "\n";
}

// Vérifier le groupe
if (posix_getgrgid($group)['name'] != 'www-data') {
    echo "⚠️  Groupe non standard : " . posix_getgrgid($group)['name'] . "\n";
    echo "Recommandation : chown www-data:www-data tmp/\n";
    $ok = false;
} else {
    echo "✅ Groupe correct : " . posix_getgrgid($group)['name'] . "\n";
}

echo "\n";
if ($ok) {
    echo "✅ Le dossier tmp/ est correctement sécurisé.\n";
    exit(0);
} else {
    echo "⚠️  Des corrections sont nécessaires pour sécuriser le dossier tmp/.\n";
    echo "Exécutez les commandes suivantes :\n";
    echo "  sudo chown www-data:www-data tmp/\n";
    echo "  sudo chmod 750 tmp/\n";
    exit(1);
}
