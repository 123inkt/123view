ARG VERSION=8.1

###################################################################################
# BASE                                                                            #
###################################################################################

# Base image
FROM php:${VERSION}-fpm-bullseye as base

RUN addgroup --system --gid 3000 app
RUN adduser --system --disabled-login --uid 3000 --gid 3000 app

# Install packages and remove libldap due to a known vulnerability and upgrade openssl
RUN apt-get update && apt-get purge -y libldap-2.4-2 \
    && apt-get install -y \
    supervisor \
    netcat \
    zip \
    libpng-dev \
    cron \
    libcurl4 \
    curl \
    libicu-dev \
    openssl=1.1.1n-0+deb11u2 \
    && rm -rf /var/lib/apt/lists/*

RUN pecl channel-update pecl.php.net && \
    pecl install apcu && \
    docker-php-ext-enable apcu && \
    docker-php-source delete

RUN docker-php-ext-install \
    opcache \
    pdo_mysql \
    intl

## Copy php default configuration
COPY ./docker/php-fpm/default.ini /usr/local/etc/php/conf.d/default.ini
COPY ./docker/php-fpm/opcache.preload.conf /usr/local/etc/php-fpm.d/opcache.preload.conf
COPY ./docker/php-fpm/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/php-fpm/cron/* /etc/cron.d/
RUN chmod -R 600 /etc/cron.d/

###################################################################################
# COMPOSER                                                                        #
###################################################################################

# Build composer in a separate stage to keep the final images as small as possible
# syntax=docker/dockerfile:experimental
FROM base as composer_build
ENV APP_ENV=prod

COPY --from=composer:2.3.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app
# Copy the lock file only, this way cache is used for this part unless the lock file changed
COPY ./composer.* /app/

RUN composer install --no-dev --no-interaction --no-autoloader --no-scripts --prefer-dist
COPY . /app
RUN composer install --no-dev --optimize-autoloader

# Use the previous composer_build also for dev (and test) composer installs for the already build cache
FROM base as composer_build_dev
ENV APP_ENV=dev

COPY --from=composer:2.3.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app
# Copy the lock file only, this way cache is used for this part unless the lock file changed
COPY ./composer.* /app/

RUN composer install --no-interaction --no-autoloader --no-scripts --prefer-dist
COPY . /app
RUN composer install --optimize-autoloader

###################################################################################
# FINAL STAGES                                                                    #
###################################################################################

# syntax=docker/dockerfile:experimental
FROM base as prod
ENV APP_ENV=prod
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

COPY --from=composer:2.3.7 /usr/bin/composer /usr/bin/composer
COPY --chown=www-data --from=composer_build_dev /app/ /app

# syntax=docker/dockerfile:experimental
FROM test as dev
ENV APP_ENV=dev
WORKDIR /app

RUN pecl install \
    xdebug \
    && docker-php-ext-enable xdebug

COPY ./docker/php-fpm/run/entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]
