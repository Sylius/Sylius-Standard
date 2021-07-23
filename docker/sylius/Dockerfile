# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/compose/compose-file/#target

# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/compose/compose-file/#target

ARG PHP_VERSION=${PHP_VERSION:-7.4}

FROM php:${PHP_VERSION}-fpm-alpine AS phpfpm

# persistent / runtime deps
RUN apk add --no-cache \
        acl \
        fcgi \
        file \
        gettext \
        git \
        mariadb-client \
    ;

ARG PHP_EXT_APCU_VERSION=5.1.18
RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        coreutils \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libtool \
        libwebp-dev \
        libzip-dev \
        mariadb-dev \
        zlib-dev \
    ; \
    \
    docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-webp=/usr/include --with-freetype=/usr/include/; \
    docker-php-ext-configure zip; \
    docker-php-ext-install -j$(nproc) \
        exif \
        gd \
        intl \
        pdo_mysql \
        zip \
    ; \
    pecl install \
        apcu-${PHP_EXT_APCU_VERSION} \
    ; \
    pecl clear-cache; \
    docker-php-ext-enable \
        apcu \
        opcache \
    ; \
    \
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --no-cache --virtual .sylius-phpexts-rundeps $runDeps; \
    \
    apk del .build-deps
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/sylius/conf.d/* /usr/local/etc/php/conf.d
COPY docker/sylius/php-cli.ini.tmp /usr/local/etc/php/php-cli.ini.tmp
COPY docker/sylius/php-fpm.d/* /usr/local/etc/php-fpm.d

ENV PHP_DATE_TIMEZONE=${PHP_DATE_TIMEZONE} \
    PHP_POST_MAX_SIZE=${PHP_POST_MAX_SIZE} \
    PHP_UPLOAD_MAX_FILESIZE=${PHP_UPLOAD_MAX_FILESIZE} \
    PHP_UPLOAD_MAX_FILESIZE=${PHP_UPLOAD_MAX_FILESIZE} \
    PHP_OPCACHE_ENABLED=${PHP_OPCACHE_ENABLED} \
    PHP_OPCACHE_ENABLED_CLI=${PHP_OPCACHE_ENABLED_CLI} \
    PHP_FPM_LOG_LEVEL=${PHP_FPM_LOG_LEVEL} \
    PHP_FPM_ERROR_LOG=${PHP_FPM_ERROR_LOG} \
    PHP_FPM_WWW_LISTEN=${PHP_FPM_WWW_LISTEN} \
    PHP_FPM_PM=${PHP_FPM_PM} \
    PHP_FPM_WWW_PM_MAX_CHILDREN=${PHP_FPM_WWW_PM_MAX_CHILDREN} \
    PHP_FPM_WWW_PM_START_SERVERS=${PHP_FPM_WWW_PM_START_SERVERS} \
    PHP_FPM_WWW_PM_MIN_SPARE_SERVERS=${PHP_FPM_WWW_PM_MIN_SPARE_SERVERS} \
    PHP_FPM_WWW_PM_MAX_SPARE_SERVERS=${PHP_FPM_WWW_PM_MAX_SPARE_SERVERS} \
    PHP_FPM_WWW_PM_MAX_REQUESTS=${PHP_FPM_WWW_PM_MAX_REQUESTS}

# evaluate ENVVARS into the file
RUN sh -c "envsubst < /usr/local/etc/php-fpm.d/zzz-sylius.conf.tmp > /usr/local/etc/php-fpm.d/zzz-sylius.conf"
RUN for file in $(find /usr/local/etc/php/conf.d -regex '.*\.tmp'); do sh -c "envsubst < ${file} > ${file%.*}"; done
RUN sh -c "envsubst < /usr/local/etc/php/php-cli.ini.tmp > /usr/local/etc/php/php-cli.ini"

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN composer global require "symfony/flex" --prefer-dist --no-progress --classmap-authoritative;
ENV PATH="${PATH}:/root/.composer/vendor/bin"

FROM phpfpm AS sylius_server

WORKDIR /srv/sylius

# build for production
ARG APP_ENV=${APP_ENV}

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.* symfony.lock ./
RUN composer install --prefer-dist --no-autoloader --no-scripts --no-progress
RUN composer clear-cache

# copy only specifically what we need
COPY .env .env.prod .env.test .env.test_cached ./
COPY bin bin/
COPY config config/
COPY public public/
COPY src src/
COPY templates templates/
COPY translations translations/

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative; \
    APP_SECRET='' composer run-script post-install-cmd; \
    chmod +x bin/console; sync; \
    bin/console assets:install --no-interaction; \
    bin/console sylius:install:assets; \
    bin/console sylius:theme:assets:install public

VOLUME /srv/sylius/var

VOLUME /srv/sylius/public/media

COPY docker/sylius/container-healthcheck.sh /usr/local/bin/container-healthcheck
RUN chmod +x /usr/local/bin/container-healthcheck
HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["container-healthcheck"]

COPY docker/sylius/container-entrypoint.sh /usr/local/bin/container-entrypoint
RUN chmod +x /usr/local/bin/container-entrypoint
ENTRYPOINT ["container-entrypoint"]

CMD ["php-fpm"]
