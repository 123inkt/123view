#!/bin/bash
set -e

if [ "${APP_ENV}" == "dev" ]; then
  composer config --global gitlab-oauth.gitlab.123dev.nl "$COMPOSER_TOKEN"
  composer install --no-interaction --optimize-autoloader
fi

php bin/console cache:clear

# echo 'dumping environment variables to local file'
# composer dump-env prod # Currently no composer in production container

php bin/console drscanner:migrations:migrate drscanner --no-interaction

# scm is only needed for non-production environments, on production the live-scm DB will be (read-only) mounted
if [ "${APP_ENV}" != "prod" ]; then
  php bin/console drscanner:migrations:migrate scm --no-interaction
  php bin/console drscanner:migrations:migrate drs --no-interaction
fi

if [ "${APP_ENV}" == "review" ] || [ "${APP_ENV}" == "stage" ] || [ "${APP_ENV}" == "dev" ]; then
  php bin/console doctrine:fixtures:load --group=scm --em scm --no-interaction
  php bin/console doctrine:fixtures:load --group=drs --em drs --no-interaction
  php bin/console doctrine:fixtures:load --group=drscanner --em drscanner --no-interaction
fi

if [ "${APP_ENV}" == "dev" ]; then
  php bin/console lexik:jwt:generate-keypair --skip-if-exists # secret is used for non-dev environments
  sleep 30 # Wait for mysql/rabbitmq
fi

export -p > /tmp/env
mkdir -p /app/var/log
chown www-data:www-data /tmp/env
chown www-data:www-data -R /app/var/cache/${APP_ENV}
chown www-data:www-data -R /app/var/log

# exec is needed to make supervisord pid 1 and able to receive SIGTERM signal
exec supervisord
