FROM php:8.2-apache as php

RUN apt-get update -y \
    && apt-get install -y unzip libpq-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get install -y libmariadb-dev

WORKDIR /var/www/html

COPY . .

COPY --from=composer:2.4.4 /usr/bin/composer /usr/bin/composer

ENV PORT=80

COPY docker/bashCommands/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
