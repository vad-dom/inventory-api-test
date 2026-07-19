#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chmod -R 777 storage bootstrap/cache

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

php artisan config:clear

php artisan migrate --force

php artisan db:seed --force