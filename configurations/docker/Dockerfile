########
# base #
########

FROM php:8.4.11-fpm AS base

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql mysqli \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

###############
# development #
###############

FROM base AS development

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist

RUN git config --global --add safe.directory /var/www/html

COPY . /var/www/html