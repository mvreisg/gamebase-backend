FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libsodium-dev \
    libpq-dev \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    git && \
    docker-php-ext-install pdo pdo_mysql mysqli sodium

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

EXPOSE 80

WORKDIR /home/ubuntu/gamebase-backend

COPY . /home/ubuntu/gamebase-backend