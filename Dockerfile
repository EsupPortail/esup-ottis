FROM php:8.2-fpm

# Mettre à jour et installer les paquets
RUN apt-get update && apt-get install -y --no-install-recommends libreoffice-core-nogui \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libmagickwand-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    curl \
    postfix \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
	&& docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /var/www/html/public/uploads /var/www/html/public/logs \
    && chmod -R 775 /var/www/html/public/uploads /var/www/html/public/logs \
    && chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/public/logs

# Code de l'application
COPY . /var/www/html/
WORKDIR /var/www/html

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" 
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer 
RUN rm composer-setup.php
RUN composer install --no-dev --prefer-dist --no-interaction
RUN composer validate --no-check-publish
