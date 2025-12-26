ARG VERSION=8.4

###################################################################################
# BASE                                                                            #
###################################################################################

# Base image
FROM php:${VERSION}-fpm-trixie AS base

# Install packages
RUN apt-get update \
    && apt-get install -y \
    cron \
    curl \
    git \
    libcurl4 \
    libicu-dev \
    librabbitmq-dev \
    netcat-openbsd \
    openssl \
    ripgrep \
    supervisor \
    wget \
    zip \
    && rm -rf /var/lib/apt/lists/*

RUN pecl channel-update pecl.php.net && \
    pecl install apcu && \
    pecl install amqp && \
    docker-php-ext-enable apcu && \
    docker-php-ext-enable amqp && \
    docker-php-source delete && \
    docker-php-ext-install opcache pdo_mysql intl

## Copy php default configuration
COPY ./docker/php-fpm/conf/default.ini /usr/local/etc/php/conf.d/default.ini
COPY ./docker/php-fpm/conf/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/php-fpm/conf/opcache.preload.conf /usr/local/etc/php-fpm.d/opcache.preload.conf
COPY ./docker/php-fpm/conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

## Copy composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

## Setup home
RUN mkdir -p /home/123view && chmod a+rwx /home/123view

###################################################################################
# FINAL STAGES                                                                    #
###################################################################################

# syntax=docker/dockerfile:experimental
FROM base AS prod
ENV APP_ENV=prod
WORKDIR /app

## copy production ini settings
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

## copy production cron
COPY ./docker/php-fpm/cron/crontab /etc/cron.d/crontab
RUN chmod -R 644 /etc/cron.d/

COPY ./docker/php-fpm/conf/production.ini /usr/local/etc/php/conf.d/production.ini
COPY ./docker/php-fpm/run/entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]


# syntax=docker/dockerfile:experimental
FROM base AS dev
ENV APP_ENV=dev
WORKDIR /app

RUN pecl install pcov && \
    pecl install xdebug && \
    docker-php-ext-enable pcov && \
    docker-php-ext-enable xdebug

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

COPY ./docker/php-fpm/conf/development.ini /usr/local/etc/php/conf.d/development.ini
COPY ./docker/php-fpm/run/entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]
