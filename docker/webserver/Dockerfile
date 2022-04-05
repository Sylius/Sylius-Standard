FROM debian:buster

# install dependencies
RUN set -x \
    # update packages cache from repos
    && apt-get update \
    && apt install -y wget gnupg2 lsb-release \
    && wget https://packages.sury.org/php/apt.gpg && apt-key add apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list \
    # install packages
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        # servers and extensions
        nginx \
        php8.1 \
        php8.1-cli \
        php8.1-common \
        php8.1-curl \
        php8.1-fpm \
        php8.1-gd \
        php8.1-intl \
        php8.1-mbstring \
        php8.1-pgsql \
        php8.1-xml \
        php8.1-dev \
        libgpgme-dev \
        php-amqp \
        php-pear \
        redis-tools \
        git \
        php-redis \
        php8.1-soap \
        sshfs \
        php8.1-zip \
        php-xmlrpc \
        acl \
        # additional utils
        nano \
        less \
    # create dir for php-fpm pid
    && mkdir /run/php \
    # set correct php.ini location for PEAR
    && pear config-set php_ini /etc/php/8.1/fpm/php.ini

# install php modules
RUN pecl install gnupg

# match php-cli configs with php-fpm
RUN mv /etc/php/8.1/cli/php.ini /etc/php/8.1/cli/php.ini.orig \
    && mv /etc/php/8.1/cli/conf.d /etc/php/8.1/cli/conf.d.orig \
    && ln -s /etc/php/8.1/fpm/php.ini /etc/php/8.1/cli/ \
    && ln -s /etc/php/8.1/fpm/conf.d /etc/php/8.1/cli/

# get composer
RUN curl --insecure https://getcomposer.org/download/1.10.5/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

# create room for files and logs (API)
RUN mkdir -m=777 -p /var/www/sylius/var/log \
    && mkdir -m=777 -p /var/www/sylius/var/logs \
    && chown -R www-data:www-data /var/www/sylius/var/log \
    && chown -R www-data:www-data /var/www/sylius/var/logs \
    && chown www-data:www-data /var/www/sylius/var \
    && chmod 777 /var/www/sylius/var

# insert start script
COPY start-webserver.sh /start-webserver.sh

# set working dir
WORKDIR /var/www/sylius
RUN chmod +x /start-webserver.sh

# start phpfpm-8.1 and nginx
CMD /start-webserver.sh
