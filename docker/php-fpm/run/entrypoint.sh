#!/bin/bash
set -e

if [ "${APP_ENV}" == "dev" ]; then
    composer install --no-interaction --optimize-autoloader
else
    composer install --no-dev --no-interaction --optimize-autoloader --classmap-authoritative
fi

# wait for mysql
sleep 20

##
# warmup cache
#
php bin/console cache:clear

# echo 'dumping environment variables to local file'
# composer dump-env prod # Currently no composer in production container

##
# run doctrine migrations
#
php bin/console doctrine:migrations:migrate --no-interaction

export -p > /tmp/env
mkdir -p /app/var/log
chown www-data:www-data /tmp/env
chown www-data:www-data -R /app/var

# exec is needed to make supervisord pid 1 and able to receive SIGTERM signal
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
