#!/bin/bash
set -e

##
# Apply custom php memory limit setting
#
sed -i "s/%PHP_MEMORY_LIMIT%/${PHP_MEMORY_LIMIT}/g" /usr/local/etc/php/conf.d/default.ini

if [ "${APP_ENV}" == "dev" ]; then
    composer install --no-interaction --optimize-autoloader --no-scripts
else
    composer install --no-dev --no-interaction --optimize-autoloader --classmap-authoritative --no-scripts
fi

##
# clear caches
#
php bin/console cache:pool:clear cache.system_clearer

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
