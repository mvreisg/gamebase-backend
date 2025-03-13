FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libsodium-dev \
    libpq-dev \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    git \
    && docker-php-ext-install pdo pdo_mysql mysqli sodium

RUN pecl install redis && \
    docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN service apache2 restart

EXPOSE 80
EXPOSE 6379
EXPOSE 3312

WORKDIR /var/www/html

COPY . /var/www/html

CMD ["apachectl", "-D", "FOREGROUND"]