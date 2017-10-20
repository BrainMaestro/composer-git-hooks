FROM composer:1.5

COPY . /app

RUN composer install

CMD ["/bin/sh", "./docker.sh"]
