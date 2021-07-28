FROM php:7.4.14-apache-buster
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
COPY php.ini "$PHP_INI_DIR/php.ini"
COPY vhost.conf /etc/apache2/sites-available/symfony-study.conf
RUN a2ensite symfony-study
