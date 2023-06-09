#!/bin/bash
cd /var/www/html
sleep 5
yes | php artisan config:cache
yes | php artisan migrate
yes | php artisan passport:install
yes | php artisan db:seed