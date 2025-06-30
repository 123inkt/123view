#!/bin/bash
set -e

cd frontend
npm install --no-save

if [ "${APP_ENV}" == "dev" ]; then
    echo "export const environment = {appName: '${APP_NAME}', apiPort: ${API_PORT}};" > src/environments/environment.development.ts
    npm run watch -- --configuration development
else
    echo "export const environment = {appName: '${APP_NAME}', apiPort: ${API_PORT}};" > src/environments/environment.production.ts
    npm run build -- --configuration production
fi
