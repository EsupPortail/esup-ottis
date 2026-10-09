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

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Activer les modules Apache utiles
RUN a2enmod rewrite \
    && mkdir -p /var/www/html \
    && mkdir -p /tmp \
    && mkdir -p /uploads \
    && mkdir -p /logs

# Code de l'application
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 tmp/ uploads/ logs/
RUN git config --global --add safe.directory /var/www/html
WORKDIR /var/www/html


RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && composer install --no-dev --prefer-dist --no-interaction \
    && composer validate --no-check-publish

EXPOSE 80
CMD ["apache2-foreground"]
