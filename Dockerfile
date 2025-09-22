FROM php:8.3-fpm

RUN docker-php-ext-install pdo pdo_mysql mysqli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html