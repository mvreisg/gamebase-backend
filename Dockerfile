FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libsodium-dev \
    libpq-dev \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    git \
    redis-server && \
    docker-php-ext-install pdo pdo_mysql mysqli sodium

RUN pecl install redis && \
    docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite

EXPOSE 80

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install