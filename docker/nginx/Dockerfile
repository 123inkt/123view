ARG VERSION
ARG APPLICATION_IMAGE

FROM nginx:${VERSION}-alpine as base

RUN apk del freetype nginx-module-image-filter
RUN apk update && apk upgrade && apk add bash

# Copy nginx config
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy entrypoint script containing build steps
COPY ./docker/nginx/run/entrypoint.sh /run/entrypoint.sh

# Make script executable
RUN chmod +x /run/entrypoint.sh

# setup ssl snippets
RUN mkdir -p /etc/nginx/snippets
COPY ./docker/nginx/snippets/ssl-certificate.conf /etc/nginx/snippets/ssl-certificate.conf
COPY ./docker/nginx/snippets/ssl-params.conf  /etc/nginx/snippets/ssl-params.conf

##
# production
#
FROM base as prod

# setup https with production certificates
COPY ./docker/ssl/production/dhparam.pem    /etc/ssl/certs/dhparam.pem
COPY ./docker/ssl/production/production.crt /etc/ssl/certs/123view-cert.crt
COPY ./docker/ssl/production/production.key /etc/ssl/private/123view-cert.key

ENTRYPOINT ["/run/entrypoint.sh"]

##
# development
#
FROM base as dev

# setup https with self-signed cert
COPY ./docker/ssl/dhparam.pem /etc/ssl/certs/dhparam.pem
COPY ./docker/ssl/development/development-self-signed.crt /etc/ssl/certs/123view-cert.crt
COPY ./docker/ssl/development/development-self-signed.key /etc/ssl/private/123view-cert.key

ENTRYPOINT ["/run/entrypoint.sh"]
