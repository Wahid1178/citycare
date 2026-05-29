FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    unzip git libssl-dev pkg-config autoconf g++ make \
    && pecl install mongodb-1.20.0 \
    && docker-php-ext-enable mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080

CMD php -S 0.0.0.0:$PORT