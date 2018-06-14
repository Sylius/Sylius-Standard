#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
	mkdir -p var/cache var/logs web/media
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var web/media
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var web/media

	if [ "$SYMFONY_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-suggest --no-interaction
		bin/console sylius:install:assets --no-interaction
		bin/console sylius:theme:assets:install --no-interaction
		>&2 echo "Waiting for MySQL to be ready..."
		until select="$(echo 'SELECT 1' | mysql --host="${SYLIUS_DATABASE_HOST}" --database="${SYLIUS_DATABASE_NAME}_${SYMFONY_ENV}" --user="${SYLIUS_DATABASE_USER}" --password="${SYLIUS_DATABASE_PASSWORD}" --silent)" && [ "$select" = '1' ]; do
			sleep 1
		done
		bin/console sylius:install:database --no-interaction
	fi
fi

exec docker-php-entrypoint "$@"
