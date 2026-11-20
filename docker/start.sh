#!/bin/sh
set -e

# Render memberi port lewat variabel PORT
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan config:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force   # aman diulang: seeder memakai updateOrCreate

exec apache2-foreground