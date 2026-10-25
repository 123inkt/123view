#!/bin/sh
set -e

if [ -n "${NGINX_DEV_FRAME_ORIGIN:-}" ]; then
    if ! printf '%s\n' "$NGINX_DEV_FRAME_ORIGIN" | grep -Eq '^https?://[a-zA-Z0-9.-]+(:[0-9]+)?$' ||
        [ "$(printf '%s' "$NGINX_DEV_FRAME_ORIGIN" | wc -l)" -ne 0 ]; then
        echo >&2 'NGINX_DEV_FRAME_ORIGIN must be an HTTP(S) origin without a path.'
        exit 1
    fi
    printf 'add_header Content-Security-Policy "frame-ancestors '\''self'\'' %s" always;\n' \
        "$NGINX_DEV_FRAME_ORIGIN" > /tmp/123view-frame-options.conf
else
    printf 'add_header X-Frame-Options DENY;\n' > /tmp/123view-frame-options.conf
fi

nginx -g 'daemon off;'
