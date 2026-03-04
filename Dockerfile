FROM php:8.5-fpm

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt-get update && \
    apt-get install -y libjpeg-dev libfreetype6-dev zlib1g-dev libpng-dev curl zip && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install gd pdo pdo_mysql && \
    echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
COPY lib lib

RUN composer install --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-dev