FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip curl \
    && rm -rf /var/lib/apt/lists/*

# Activer les modules Apache utiles
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY GUDRUN/ .
COPY php.ini /usr/local/etc/php/

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && composer install --no-dev --prefer-dist --no-interaction \
    && docker-php-ext-install pdo pdo_mysql php-gd
