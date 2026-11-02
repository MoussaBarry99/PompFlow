#!/bin/sh
set -e

php artisan migrate --force
php artisan pompflow:initialiser

# Pas de route:cache : web.php contient des routes à closure.
php artisan config:cache
php artisan view:cache

exec frankenphp php-server --root public --listen ":${PORT:-8080}"
