FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 www && useradd -g 1000 -u 1000 -s /bin/sh -d /var/www www

COPY --chown=www:www . /var/www

RUN chmod -R 755 /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/storage \
    && chmod +x /var/www/docker-entrypoint.sh

RUN composer install --optimize-autoloader --no-dev

RUN chown -R www:www /var/www

USER www

EXPOSE 9000

ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
