FROM php:8.5-fpm@sha256:3525cfee24cd88e8a215adc8238e0046686907fced10bb0cac22da4bf889981a

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt-get update && \
    apt-get install -y libjpeg-dev libfreetype6-dev zlib1g-dev libpng-dev curl zip && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install gd pdo pdo_mysql && \
    pecl install apcu && docker-php-ext-enable apcu && \
    echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:2.9.5@sha256:33408676b911b57400f885f83f45947dbb9501b6af40c8d79c136a8bb6800e87 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
COPY lib lib

RUN composer install --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-dev