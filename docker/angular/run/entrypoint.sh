#!/bin/bash
set -e

cd frontend
npm install --no-save

if [ "${APP_ENV}" == "dev" ]; then
    npm run watch
else
    npm run build
fi
