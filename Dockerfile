# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/compose/compose-file/#target

ARG PHP_VERSION=8.1
ARG NODE_VERSION=16
ARG NGINX_VERSION=1.21
ARG ALPINE_VERSION=3.15
ARG COMPOSER_VERSION=2.4
ARG PHP_EXTENSION_INSTALLER_VERSION=latest

FROM composer:${COMPOSER_VERSION} AS composer

FROM mlocati/php-extension-installer:${PHP_EXTENSION_INSTALLER_VERSION} AS php_extension_installer

FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} AS base

# persistent / runtime deps
RUN apk add --no-cache \
        acl \
        file \
        gettext \
        unzip \
    ;

COPY --from=php_extension_installer /usr/bin/install-php-extensions /usr/local/bin/

# default PHP image extensions
# ctype curl date dom fileinfo filter ftp hash iconv json libxml mbstring mysqlnd openssl pcre PDO pdo_sqlite Phar
# posix readline Reflection session SimpleXML sodium SPL sqlite3 standard tokenizer xml xmlreader xmlwriter zlib
RUN install-php-extensions apcu exif gd intl pdo_mysql opcache zip

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY docker/php/prod/php.ini        $PHP_INI_DIR/php.ini
COPY docker/php/prod/opcache.ini    $PHP_INI_DIR/conf.d/opcache.ini

# copy file required by opcache preloading
COPY config/preload.php /srv/sylius/config/preload.php

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/sylius

# build for production
ENV APP_ENV=prod

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.* symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-autoloader --no-interaction --no-scripts --no-progress --no-dev; \
    composer clear-cache

# copy only specifically what we need
COPY .env .env.prod ./
COPY assets assets/
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
    bin/console sylius:install:assets --no-interaction; \
    bin/console sylius:theme:assets:install public --no-interaction

VOLUME /srv/sylius/var

VOLUME /srv/sylius/public/media

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

FROM node:${NODE_VERSION}-alpine${ALPINE_VERSION} AS sylius_node

WORKDIR /srv/sylius

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		g++ \
		gcc \
		make \
	;

# prevent the reinstallation of vendors at every changes in the source code
COPY package.json yarn.* ./
RUN set -eux; \
    yarn install; \
    yarn cache clean

COPY --from=base /srv/sylius/vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private       vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private/
COPY --from=base /srv/sylius/vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private    vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private/
COPY --from=base /srv/sylius/vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private     vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private/
COPY --from=base /srv/sylius/assets ./assets

COPY webpack.config.js ./
RUN yarn build:prod

COPY docker/node/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["yarn", "build:prod"]

FROM base AS sylius_php_prod

COPY --from=sylius_node /srv/sylius/public/build public/build

FROM nginx:${NGINX_VERSION}-alpine AS sylius_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

WORKDIR /srv/sylius

COPY --from=base        /srv/sylius/public public/
COPY --from=sylius_node /srv/sylius/public public/

FROM sylius_php_prod AS sylius_php_dev

COPY docker/php/dev/php.ini        $PHP_INI_DIR/php.ini
COPY docker/php/dev/opcache.ini    $PHP_INI_DIR/conf.d/opcache.ini

WORKDIR /srv/sylius

ENV APP_ENV=dev

COPY .env.test .env.test_cached ./

RUN set -eux; \
    composer install --prefer-dist --no-autoloader --no-interaction --no-scripts --no-progress; \
    composer clear-cache

FROM sylius_php_prod AS sylius_cron

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		apk-cron \
	;

COPY docker/cron/crontab /etc/crontabs/root
COPY docker/cron/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["crond", "-f"]

FROM sylius_php_prod AS sylius_migrations_prod

COPY docker/migrations/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]

FROM sylius_php_dev AS sylius_migrations_dev

COPY docker/migrations/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

RUN composer dump-autoload --classmap-authoritative

ENTRYPOINT ["docker-entrypoint"]
