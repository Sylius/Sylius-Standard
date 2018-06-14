ARG PHP_VERSION=7.2
ARG NODE_VERSION=10
ARG NGINX_VERSION=1.14

FROM php:${PHP_VERSION}-fpm-alpine AS sylius_php

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		file \
		gettext \
		git \
		mariadb-client \
	;

ARG APCU_VERSION=5.1.11
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
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
	docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include --with-webp-dir=/usr/include --with-freetype-dir=/usr/include/; \
	docker-php-ext-configure zip --with-libzip; \
	docker-php-ext-install -j$(nproc) \
		exif \
		gd \
		intl \
		pdo_mysql \
		zip \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
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
COPY docker/php/php.ini /usr/local/etc/php/php.ini

ARG COMPOSER_AUTH=
# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
	composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
	composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/sylius

# Build for production
ARG SYMFONY_ENV=prod

# Prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock ./
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-suggest; \
	composer clear-cache

COPY . ./

RUN set -eux; \
	mkdir -p var/cache var/logs; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync; \
	bin/console sylius:install:assets; \
	bin/console sylius:theme:assets:install
VOLUME /srv/sylius/var

VOLUME /srv/sylius/web/media

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

FROM node:${NODE_VERSION}-alpine AS sylius_nodejs

WORKDIR /srv/sylius

# Build for production
ARG NODE_ENV=production
ARG GULP_ENV=prod

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		g++ \
		gcc \
		make \
		python \
	;

# Prevent the reinstallation of vendors at every changes in the source code
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
	if [ "${NODE_ENV}" = 'production' ]; then \
		NODE_ENV=development yarn install; \
		yarn build; \
		yarn install; \
		yarn cache clean; \
	fi

COPY docker/nodejs/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["node"]

FROM nginx:${NGINX_VERSION}-alpine AS sylius_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

WORKDIR /srv/sylius

COPY --from=sylius_php /srv/sylius/web web/
COPY --from=sylius_nodejs /srv/sylius/web web/
