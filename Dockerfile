FROM php:8.2-fpm

# Mettre à jour et installer les paquets
RUN apt-get update && apt-get install -y --no-install-recommends libreoffice-core-nogui \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite \
    && mkdir -p /var/www/html/public/uploads /var/www/html/public/logs \
    && chmod -R 775 /var/www/html/public/uploads /var/www/html/public/logs \
    && chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/public/logs

# Code de l'application
COPY . /var/www/html/
WORKDIR /var/www/html

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && composer install --no-dev --prefer-dist --no-interaction \
    && composer validate --no-check-publish
