ARG PHP_VERSION=8.0
ARG NODE_VERSION=14
ARG NGINX_VERSION=1.21

FROM php:${PHP_VERSION}-apache-buster AS sylius_php

RUN apt-get update && apt-get install -y git \
    libpq-dev libzip-dev libpng-dev libxslt1-dev librabbitmq-dev \
    && docker-php-ext-install pdo_pgsql zip gd xsl intl pdo_mysql

RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis
RUN pecl install -o -f amqp \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable amqp

COPY --from=symfonycorp/cli:v4.21.6 /symfony /symfony
COPY --from=composer:2.0.8 /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install exif
#COPY php.ini "$PHP_INI_DIR/php.ini"
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.* symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-autoloader --no-scripts --no-progress; \
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
    bin/console sylius:install:assets; \
    bin/console sylius:theme:assets:install public

#VOLUME /srv/sylius/var
VOLUME /var/www/html/symfony-study

#VOLUME /srv/sylius/public/media
VOLUME /var/www/html/symfony-study/public/media

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

COPY docker/vhost.conf /etc/apache2/sites-available/symfony-study.conf

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

#RUN a2ensite symfony-study


FROM node:${NODE_VERSION}-alpine

#WORKDIR /srv/sylius
WORKDIR /var/www/html/symfony-study

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

COPY --from=sylius_php /var/www/html/symfony-study/vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private/
COPY --from=sylius_php /var/www/html/symfony-study/vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private/
COPY --from=sylius_php /var/www/html/symfony-study/vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private/

COPY --from=sylius_php /var/www/html/symfony-study/vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js
COPY --from=sylius_php /var/www/html/symfony-study/vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/gulpfile.babel.js vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/gulpfile.babel.js

COPY gulpfile.babel.js .babelrc ./
RUN set -eux; \
    GULP_ENV=prod yarn build

COPY docker/node/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["yarn", "watch"]

FROM nginx:${NGINX_VERSION}-alpine AS sylius_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

WORKDIR /var/www/html/symfony-study

COPY --from=sylius_php /var/www/html/symfony-study/public public/
COPY --from=sylius_node /var/www/html/symfony-study/public public/
