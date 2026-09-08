#! /bin/bash

artisan="/usr/local/bin/php /var/www/html/artisan"

$artisan migrate
$artisan db:seed --class=RolesAndPermissionsSeeder --force

$artisan config:clear
$artisan config:cache

$artisan route:clear
$artisan route:cache

# The commands above run as root, so anything they write under storage/ or
# bootstrap/cache/ - including the Spatie permission cache - ends up owned by
# root. PHP-FPM serves requests as application:application and cannot
# overwrite a root-owned file, so ownership has to be normalized back before
# supervisord starts serving traffic.
chown -R application:application /var/www/html/storage /var/www/html/bootstrap/cache

supervisord
