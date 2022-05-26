# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/compose/compose-file/#target

ARG PHP_VERSION=8.0
ARG NODE_VERSION=14
ARG NGINX_VERSION=1.21

FROM php:${PHP_VERSION}-fpm-alpine AS sylius_php

# persistent / runtime deps
RUN apk add --no-cache \
        acl \
        file \
        gettext \
        git \
        mariadb-client \
        unzip \
    ;

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions zip gd intl exif pdo_mysql opcache apcu xml curl mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-cli.ini /usr/local/etc/php/php-cli.ini

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/sylius

# build for production
ARG APP_ENV=prod

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.* symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-autoloader --no-interaction --no-scripts --no-progress; \
    composer clear-cache

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
    bin/console sylius:install:assets --no-interaction; \
    bin/console sylius:theme:assets:install public --no-interaction

VOLUME /srv/sylius/var

VOLUME /srv/sylius/public/media

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

FROM node:${NODE_VERSION}-alpine AS sylius_node

WORKDIR /srv/sylius

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		g++ \
		gcc \
		git \
		make \
		python2 \
	;

# prevent the reinstallation of vendors at every changes in the source code
COPY package.json yarn.lock ./
RUN set -eux; \
    yarn install; \
    yarn cache clean

COPY --from=sylius_php /srv/sylius/vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private/
COPY --from=sylius_php /srv/sylius/vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private/
COPY --from=sylius_php /srv/sylius/vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private/

COPY --from=sylius_php /srv/sylius/vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js
COPY --from=sylius_php /srv/sylius/vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/gulpfile.babel.js vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/gulpfile.babel.js

COPY gulpfile.babel.js .babelrc ./
RUN set -eux; \
    GULP_ENV=prod yarn build

COPY docker/node/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["yarn", "build"]

FROM nginx:${NGINX_VERSION}-alpine AS sylius_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

WORKDIR /srv/sylius

COPY --from=sylius_php /srv/sylius/public public/
COPY --from=sylius_node /srv/sylius/public public/
