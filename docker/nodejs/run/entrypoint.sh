#!/bin/bash
set -e

npm install

if [ "${APP_ENV}" == "dev" ]; then
    npm run watch
else
    npm run build
fi
