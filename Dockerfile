FROM php:8.2-apache

# Mettre à jour et installer les paquets
RUN apt-get update && apt-get install -y --no-install-recommends \
    libmagickwand-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libreoffice \
    curl \
    postfix \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    curl \
    xml \
    zip \
    gd \
    mbstring \
    exif \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Activer les modules Apache utiles
RUN a2enmod rewrite
# RUN phpenmod imagick
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
