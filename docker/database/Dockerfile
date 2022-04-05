FROM debian:buster

# install dependencies
RUN set -x \
    # update packages cache from repos
    && apt-get update \
    # get curl
    && DEBIAN_FRONTEND=noninteractive apt-get install -y curl gnupg \
    # update packages cache from repos
    && apt-get update \
    # install packages
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        # servers and extensions
        libpq-dev libzip-dev libpng-dev libxslt1-dev librabbitmq-dev \
        pdo_pgsql zip gd xsl intl \
        postgresql-11 \
        postgresql-client-11 \
        redis-server \
        redis-tools \
        python3-psycopg2 \
        mongodb-org \
        acl \
        # additional utils
        nano \
        less

# create bsp database user and database
USER postgres
RUN /etc/init.d/postgresql start \
    && psql --command "CREATE USER sylius WITH SUPERUSER PASSWORD 'password';" \
    && createdb -O eb eb

USER root

# create VOLUMEs for postgres databases
VOLUME ["/etc/postgresql", "/var/lib/postgresql"]

# insert start script
COPY start-database.sh /start-database.sh
RUN chmod +x /start-database.sh

# start redis-server and postgresql
CMD /start-database.sh
