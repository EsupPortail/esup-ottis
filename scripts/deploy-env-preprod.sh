#!/bin/bash
# Script de déploiement du fichier .env sur le serveur de preprod
# Ce script doit être exécuté SUR le serveur de preprod

# Chemin du répertoire de l'application sur preprod
APP_DIR="/var/www/html/omist-preprod.intra.univ-nantes.fr"  # À adapter selon la configuration réelle

# Vérifier que le script est exécuté dans le bon répertoire
if [ ! -f "$APP_DIR/.env.example" ]; then
    echo "Erreur: Le fichier .env.example n'existe pas dans $APP_DIR"
    echo "Veuillez vérifier le chemin ou exécuter ce script depuis le répertoire de l'application"
    exit 1
fi

# Vérifier si .env existe déjà
if [ -f "$APP_DIR/.env" ]; then
    echo "⚠️  Un fichier .env existe déjà dans $APP_DIR"
    echo "Sauvegarde du fichier existant..."
    cp "$APP_DIR/.env" "$APP_DIR/.env.bak-$(date +%Y%m%d-%H%M%S)"
    echo "Sauvegarde créée: .env.bak-$(date +%Y%m%d-%H%M%S)"
fi

# Créer .env à partir de .env.example
echo "Création du fichier .env à partir de .env.example..."
cp "$APP_DIR/.env.example" "$APP_DIR/.env"

# Configurer les permissions sécurisées
echo "Configuration des permissions..."
chown www-data:www-data "$APP_DIR/.env"
chmod 640 "$APP_DIR/.env"

# Redémarrer Apache
echo "Redémarrage d'Apache..."
sudo systemctl restart apache2

# Vérifier que tout s'est bien passé
echo ""
echo "=== Vérification ==="
echo "Fichier .env créé: $(ls -la $APP_DIR/.env)"
echo ""
echo "✅ Déploiement du fichier .env terminé avec succès!"
echo ""
echo "IMPORTANT: Vérifiez et configurez manuellement les valeurs suivantes dans .env:"
echo "  - GOOGLE_API_TOKENS"
echo "  - DEEPL_FREE_TOKENS"
echo "  - DEEPL_PRO_TOKENS"
echo "  - MICROSOFT_TRANSLATOR_KEY"
echo "  - MICROSOFT_TRANSLATOR_REGION"
echo "  - SENTRY_DSN"
echo ""
echo "Selon l'environnement, configurez aussi:"
echo "  - APP_ENV=staging (ou production)"
echo "  - APP_DEBUG=false"
