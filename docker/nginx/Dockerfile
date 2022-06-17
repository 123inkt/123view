ARG VERSION
ARG APPLICATION_IMAGE

FROM nginx:${VERSION}-alpine

RUN addgroup -g 3000 -S app
RUN adduser -u 3000 -S -D -G app app

# https://github.com/nginxinc/docker-nginx/issues/657
RUN apk del freetype nginx-module-image-filter

# CVE-2022-1586 & CVE-2022-1587 see: https://avd.aquasec.com/nvd/cve-2022-1586 and https://avd.aquasec.com/nvd/cve-2022-1587
RUN apk --no-cache --virtual add pcre2=10.40-r0

# Copy nginx config
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy entrypoint script containing build steps
COPY ./docker/nginx/run/entrypoint.sh /run/entrypoint.sh

# Make script executable
RUN chmod +x /run/entrypoint.sh

ENTRYPOINT ["/run/entrypoint.sh"]
