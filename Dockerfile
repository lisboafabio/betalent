FROM php:8.4-fpm

COPY docker/php/custom_php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    nano \
    git zip unzip \
    libicu-dev \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        zip \
        exif \
        pcntl \
        gd \
    && apt-get clean &&  \
    docker-php-ext-install intl pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl gd

RUN curl -sS https://getcomposer.org/installer \
 | php -d allow_url_fopen=On -- \
 --install-dir=/usr/local/bin \
 --filename=composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
