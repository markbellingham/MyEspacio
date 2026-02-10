FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libonig-dev libxml2-dev zip \
    libzip-dev mariadb-client \
    && docker-php-ext-install pdo pdo_mysql exif mbstring zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
