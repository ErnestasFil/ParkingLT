#!/bin/bash

if [ ! -f vendor/autoload.php ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan optimize:clear
    php artisan key:generate
    php artisan jwt:secret
    echo "Error: The .env file is missing. Create the .env file before starting the application."
    exit 1
fi

php artisan serve --port=$PORT --host=0.0.0.0 --env=.env
exec docker-php-entrypoint "$@"