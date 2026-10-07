FROM php:8.2-apache

# Installer les extensions PHP requises
# Installer les binaires externes requis
# Optionnel : agent de messagerie pour l'envoi d'e-mails (/email/send)

RUN apt-get update && apt-get install -y php-imagick php-curl php-xml php-zip php-gd php-mbstring libreoffice curl postfix\
    git unzip curl \
    && rm -rf /var/lib/apt/lists/*

# Activer les modules Apache utiles
RUN a2enmod rewrite
RUN phpenmod imagick
RUN systemctl restart apache2

# Code de l'application
COPY . /var/www/html
WORKDIR /var/www/html
# COPY OTTIS/ .
# COPY php.ini /usr/local/etc/php/

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && composer install --no-dev --prefer-dist --no-interaction \
    && docker-php-ext-install pdo pdo_mysql php-gd \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer
