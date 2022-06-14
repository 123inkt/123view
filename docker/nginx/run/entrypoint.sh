#!/bin/sh
set -e

sed -i s#%%PHP_FPM_HOST%%#$PHP_FPM_HOST#g /etc/nginx/conf.d/default.conf

nginx -g 'daemon off;'
