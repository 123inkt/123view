ARG VERSION
ARG APPLICATION_IMAGE

FROM nginx:${VERSION}-alpine as base

# https://github.com/nginxinc/docker-nginx/issues/657
RUN apk del freetype nginx-module-image-filter

# CVE-2022-1586 & CVE-2022-1587 see: https://avd.aquasec.com/nvd/cve-2022-1586 and https://avd.aquasec.com/nvd/cve-2022-1587
RUN apk --no-cache --virtual add pcre2=10.40-r0

RUN apk update && apk upgrade && apk add bash

# Copy nginx config
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy entrypoint script containing build steps
COPY ./docker/nginx/run/entrypoint.sh /run/entrypoint.sh

# Make script executable
RUN chmod +x /run/entrypoint.sh

# setup ssl snippets
COPY ./docker/nginx/ssl/dhparam.pem /etc/ssl/certs/dhparam.pem
RUN mkdir -p /etc/nginx/snippets
COPY ./docker/nginx/snippets/ssl-certificate.conf /etc/nginx/snippets/ssl-certificate.conf
COPY ./docker/nginx/snippets/ssl-params.conf  /etc/nginx/snippets/ssl-params.conf

##
# production
#
FROM base as prod

# setup https with production certificates
COPY ./docker/nginx/ssl/production/production.crt /etc/ssl/certs/commit-notification-cert.crt
COPY ./docker/nginx/ssl/production/production.key /etc/ssl/private/commit-notification-cert.key

ENTRYPOINT ["/run/entrypoint.sh"]

##
# development
#
FROM base as dev

# setup https with self-signed cert
COPY ./docker/nginx/ssl/dhparam.pem /etc/ssl/certs/dhparam.pem
COPY ./docker/nginx/ssl/development/nginx-selfsigned.crt /etc/ssl/certs/commit-notification-cert.crt
COPY ./docker/nginx/ssl/development/nginx-selfsigned.key /etc/ssl/private/commit-notification-cert.key

ENTRYPOINT ["/run/entrypoint.sh"]
