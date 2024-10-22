FROM php:8.3-cli-alpine3.20

ENV COMPOSER_ALLOW_SUPERUSER 1
ARG HOST_USER_ID

COPY files/bin/     /usr/local/bin/
COPY files/ini      /usr/local/etc/php/available-ini/

# SYS: Install required packages
RUN apk --no-cache upgrade
RUN apk --no-cache add``
RUN apk --no-cache add bash
RUN apk --no-cache add git
RUN apk --no-cache add sudo
RUN apk --no-cache add autoconf
RUN apk --no-cache add gcc
RUN apk --no-cache add g++
RUN apk --no-cache add make
RUN apk --no-cache add gettext
RUN apk --no-cache add shadow
RUN apk --no-cache add shadow
RUN apk --no-cache add nano
RUN apk --no-cache add zip
RUN apk --no-cache add unzip
RUN apk --no-cache add libzip-dev
RUN apk --no-cache add libpng-dev
RUN apk --no-cache add icu-dev
RUN apk add --update linux-headers

# PHP Extensions
RUN pecl install xdebug ctype iconv json pcre session xml zip inotify pcov igbinary redis mbstring

# PHP extensions enabling
RUN docker-php-ext-install pdo_mysql  gd
RUN docker-php-ext-install intl
RUN docker-php-ext-install gd
RUN docker-php-ext-install exif

RUN php-ext-enable php
RUN php-ext-enable pcov
RUN php-ext-enable exif
RUN php-ext-enable zip
RUN php-ext-enable gd

# Disabling PHP Extension Xdebug
RUN php-ext-disable xdebug
# Enabling PHP Extension opcache
RUN php-ext-enable opcache

# Install composer
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN wget https://get.symfony.com/cli/installer -O - | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /app
