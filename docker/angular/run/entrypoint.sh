#!/bin/bash
set -e

cd frontend
npm install --no-save


if [ "${APP_ENV}" == "dev" ]; then
    CONFIG_FILE=src/environments/environment.development.ts
else
    CONFIG_FILE=src/environments/environment.production.ts
fi

cat <<EOF > ${CONFIG_FILE}
export const environment = {
    "appName": "${APP_NAME}",
    "appAuthPassword": ${APP_AUTH_PASSWORD},
    "appAuthAzureAd": ${APP_AUTH_AZURE_AD},
    "apiPort": ${API_PORT}
}
EOF

cat ${CONFIG_FILE}

if [ "${APP_ENV}" == "dev" ]; then
    npm run watch -- --configuration development
else
    npm run build -- --configuration production
fi
