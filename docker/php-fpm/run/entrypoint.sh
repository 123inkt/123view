#!/bin/bash
set -e

##
# Apply custom php memory limit setting
#
sed -i "s/memory_limit=2G/memory_limit=${PHP_MEMORY_LIMIT}/g" /usr/local/etc/php/conf.d/default.ini

if [ "${APP_ENV}" == "dev" ]; then
    composer install --no-interaction --optimize-autoloader --no-scripts
else
    composer install --no-dev --no-interaction --optimize-autoloader --classmap-authoritative --no-scripts
fi

mkdir -p /app/var/cache/prod
chmod -R a+rw /app/var/cache
chown www-data:www-data -R /app/var/cache

##
# clear caches
#
php bin/console cache:clear

##
# install assets
#
php bin/console assets:install

##
# run doctrine migrations
#
php bin/console doctrine:migrations:migrate --no-interaction

printenv > /etc/environment
mkdir -p /app/var/log
chmod -R a+rw /app/var
chown www-data:www-data -R /app/var

# exec is needed to make supervisord pid 1 and able to receive SIGTERM signal
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
