ARG VERSION
ARG APPLICATION_IMAGE

FROM nginx:${VERSION}-alpine AS base

RUN apk del freetype nginx-module-image-filter
RUN apk update && apk upgrade && apk add bash

# Copy nginx config
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# setup ssl snippets
RUN mkdir -p /etc/nginx/snippets
COPY ./docker/nginx/snippets/ssl-certificate.conf /etc/nginx/snippets/ssl-certificate.conf
COPY ./docker/nginx/snippets/ssl-params.conf  /etc/nginx/snippets/ssl-params.conf

# error pages
COPY ./docker/nginx/error/500.html /usr/share/nginx/html/500.html
COPY ./docker/nginx/error/502.html /usr/share/nginx/html/502.html
COPY ./docker/nginx/error/503.html /usr/share/nginx/html/503.html
COPY ./docker/nginx/error/504.html /usr/share/nginx/html/504.html

COPY ./docker/nginx/run/entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]
