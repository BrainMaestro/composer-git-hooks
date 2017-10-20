FROM composer:1.5

COPY . /app

RUN composer install

RUN ./vendor/bin/phpunit
