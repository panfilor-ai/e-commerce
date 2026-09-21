FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
        default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
