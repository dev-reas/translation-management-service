#!/bin/sh

set -e

echo "Running migrations..."
php artisan migrate --force

echo "Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Optimizing..."
php artisan optimize

echo "Starting PHP-FPM..."
exec php-fpm
