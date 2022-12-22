FROM dunglas/mercure as base

##
# production
#
FROM base as prod

# setup https with production certificates
COPY ./docker/ssl/production/production.crt /etc/ssl/certs/123view-cert.crt
COPY ./docker/ssl/production/production.key /etc/ssl/private/123view-cert.key

##
# development
#
FROM base as dev

# setup https with self-signed cert
COPY ./docker/ssl/development/development-self-signed.crt /etc/ssl/certs/123view-cert.crt
COPY ./docker/ssl/development/development-self-signed.key /etc/ssl/private/123view-cert.key
