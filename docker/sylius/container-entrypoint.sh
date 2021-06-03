#!/bin/sh
set -e

sh -c "envsubst < /usr/local/etc/php-fpm.d/zzz-sylius.conf.tmp > /usr/local/etc/php-fpm.d/zzz-sylius.conf";
sh -c "envsubst < /usr/local/etc/php/php-cli.ini.tmp > /usr/local/etc/php/php-cli.ini";

for file in $(find /usr/local/etc/php/conf.d -regex '.*\.tmp'); do
    sh -c "envsubst < ${file} > ${file%.*}";
done

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
    mkdir -p var/cache var/log public/media
    setfacl -R -m u:www-data:rwx -m u:"$(whoami)":rwx var public/media
    setfacl -dR -m u:www-data:rwx -m u:"$(whoami)":rwx var public/media

    until bin/console doctrine:query:sql "select 1" >/dev/null 2>&1; do
        (>&2 echo "Waiting for MySQL to be ready...")
        sleep 1
    done

    bin/console doctrine:migrations:migrate --no-interaction
fi

exec docker-php-entrypoint "$@"
