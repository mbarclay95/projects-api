#! /bin/bash

artisan="/usr/local/bin/php /var/www/html/artisan"

$artisan migrate
$artisan db:seed --class=RolesAndPermissionsSeeder --force

$artisan config:clear
$artisan config:cache

$artisan route:clear
$artisan route:cache

supervisord
