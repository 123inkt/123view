#!/bin/bash
set -e

npm install --no-save

if [ "${APP_ENV}" == "dev" ]; then
    npm run watch &
else
    npm run build
fi

# exec is needed to make supervisord pid 1 and able to receive SIGTERM signal
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
