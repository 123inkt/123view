ARG VERSION
ARG APPLICATION_IMAGE

FROM nginx:${VERSION}-alpine AS base

RUN apk del freetype nginx-module-image-filter
RUN apk update && apk upgrade && apk add bash

COPY ./docker/nginx/run/entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]
