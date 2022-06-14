ARG DOCKER_PROXY="gitlab.123dev.nl/123/dependency_proxy/containers"
ARG VERSION=8.1

###################################################################################
# BASE                                                                            #
###################################################################################

# Base image
# syntax=docker/dockerfile:experimental
FROM ${DOCKER_PROXY}/php:${VERSION}-fpm-bullseye AS base

# Install packages and remove libldap due to a known vulnerability and upgrade openssl
RUN apt-get update && apt-get purge -y libldap-2.4-2 \
    && apt-get install -y \
    librabbitmq-dev \
    supervisor \
    netcat \
    zip \
    libpng-dev \
    cron \
    libcurl4 \
    curl \
    openssl=1.1.1n-0+deb11u2 \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo_mysql \
    pcntl \
    gd

RUN pecl install \
    amqp \
    && docker-php-ext-enable amqp

## Copy php default configuration
COPY ./docker/php-fpm/default.ini /usr/local/etc/php/conf.d/default.ini
COPY ./docker/php-fpm/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/php-fpm/cron/* /etc/cron.d/
RUN chmod -R 600 /etc/cron.d/

###################################################################################
# COMPOSER                                                                        #
###################################################################################

# Build composer in a separate stage to keep the final images as small as possible
# syntax=docker/dockerfile:experimental
FROM base as composer_build
ARG COMPOSER_TOKEN
ENV APP_ENV=prod

COPY --from=composer:2.2.12 /usr/bin/composer /usr/bin/composer

WORKDIR /app
# Copy the lock file only, this way cache is used for this part unless the lock file changed
COPY ./composer.* /app/

RUN composer config gitlab-oauth.gitlab.123dev.nl ${COMPOSER_TOKEN}
RUN composer install --no-dev --no-interaction --no-autoloader --no-scripts --prefer-dist
COPY . /app
RUN composer install --no-dev --optimize-autoloader
RUN composer config --unset gitlab-oauth.gitlab.123dev.nl

# Use the previous composer_build also for dev (and test) composer installs for the already build cache
FROM base as composer_build_dev
ARG COMPOSER_TOKEN
ENV APP_ENV=dev

COPY --from=composer:2.2.12 /usr/bin/composer /usr/bin/composer

WORKDIR /app
# Copy the lock file only, this way cache is used for this part unless the lock file changed
COPY ./composer.* /app/

RUN composer config gitlab-oauth.gitlab.123dev.nl ${COMPOSER_TOKEN}
RUN composer install --no-interaction --no-autoloader --no-scripts --prefer-dist
COPY . /app
RUN composer install --optimize-autoloader
RUN composer config --unset gitlab-oauth.gitlab.123dev.nl

###################################################################################
# FINAL STAGES                                                                    #
###################################################################################

# syntax=docker/dockerfile:experimental
FROM base as prod
ENV APP_ENV=prod
ARG PIPELINE_NUMBER=dev
ENV BUILDNUMBER=$PIPELINE_NUMBER
WORKDIR /app

## Copy only from composer_build stage and set owner to php-fpm default user
COPY --chown=www-data --from=composer_build /app/ /app

COPY ./docker/php-fpm/run/entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]

# syntax=docker/dockerfile:experimental
FROM base as test
ENV APP_ENV=test
WORKDIR /app

RUN pecl install \
    pcov \
    && docker-php-ext-enable pcov

COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer
COPY --chown=www-data --from=composer_build_dev /app/ /app

# syntax=docker/dockerfile:experimental
FROM test as dev
ARG COMPOSER_TOKEN='overwrite-please'
ENV COMPOSER_TOKEN=$COMPOSER_TOKEN
ENV APP_ENV=dev
WORKDIR /app

RUN pecl install \
    xdebug \
    && docker-php-ext-enable xdebug

COPY ./docker/php-fpm/run/entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]
