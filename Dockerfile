FROM composer:1.5

WORKDIR /app

COPY ./composer.json ./composer.lock /app/

RUN composer install

COPY . /app/

RUN ./vendor/bin/phpunit
