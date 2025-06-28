#!/bin/bash
set -e

cd frontend
npm install --no-save

if [ "${APP_ENV}" == "dev" ]; then
    npm run watch -- --output-path=../public/angular --base-href /angular/browser/
else
    npm run build -- --output-path=../public/angular --base-href /angular/browser/
fi

echo "done"
